<?php
/**
 * 推薦系統核心類別
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Referral {
    
    /**
     * 初始化
     */
    public static function init() {
        // 捕捉推薦連結參數
        add_action('template_redirect', array(__CLASS__, 'capture_referral_code'));
        
        // 用戶註冊時記錄推薦關係
        add_action('user_register', array(__CLASS__, 'save_referral_on_register'), 10, 1);
        
        // WooCommerce 結帳註冊時記錄推薦關係
        add_action('woocommerce_created_customer', array(__CLASS__, 'save_referral_on_register'), 10, 1);
        
        // AJAX: 驗證推薦代碼
        add_action('wp_ajax_QiMall_verify_referral_code', array(__CLASS__, 'ajax_verify_referral_code'));
        add_action('wp_ajax_nopriv_QiMall_verify_referral_code', array(__CLASS__, 'ajax_verify_referral_code'));
        
        // AJAX: 修改推薦代碼
        add_action('wp_ajax_QiMall_update_referral_code', array(__CLASS__, 'ajax_update_referral_code'));
    }
    
    /**
     * 捕捉推薦連結參數
     */
    public static function capture_referral_code() {
        if (isset($_GET['ref']) && !empty($_GET['ref'])) {
            $referral_code = sanitize_text_field($_GET['ref']);
            
            // 驗證推薦代碼是否有效
            $referrer_id = self::get_user_id_by_code($referral_code);
            
            if ($referrer_id) {
                // 只有在沒有 Cookie 時才設定 (先到先得)
                if (!isset($_COOKIE['QiMall_referral_code'])) {
                    setcookie('QiMall_referral_code', $referral_code, time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN);
                    $_COOKIE['QiMall_referral_code'] = $referral_code; // 立即可用
                }
            }
        }
    }
    
    /**
     * 用戶註冊時記錄推薦關係
     */
    public static function save_referral_on_register($user_id) {
        // 檢查是否已有推薦人 (避免重複記錄)
        if (get_user_meta($user_id, 'QiMall_referrer', true)) {
            return;
        }
        
        $referrer_id = null;
        $referral_code = null;
        
        // 優先級1: 檢查 POST 資料 (手動輸入優先)
        if (isset($_POST['QiMall_referral_code']) && !empty($_POST['QiMall_referral_code'])) {
            $referral_code = sanitize_text_field($_POST['QiMall_referral_code']);
            $referrer_id = self::get_user_id_by_code($referral_code);
        }
        
        // 優先級2: 檢查 Cookie
        if (!$referrer_id && isset($_COOKIE['QiMall_referral_code'])) {
            $referral_code = sanitize_text_field($_COOKIE['QiMall_referral_code']);
            $referrer_id = self::get_user_id_by_code($referral_code);
        }
        
        // 優先級3: 使用預設推薦人
        if (!$referrer_id) {
            $referrer_id = self::get_default_referrer_id();
        }
        
        // 防呆: 不能推薦自己
        if ($referrer_id == $user_id) {
            $referrer_id = self::get_default_referrer_id();
        }
        
        // 記錄推薦人
        if ($referrer_id) {
            update_user_meta($user_id, 'QiMall_referrer', $referrer_id);
            
            // 建立二級分銷關係
            self::create_distribution_relationship($user_id, $referrer_id);
            
            // 清除 Cookie
            if (isset($_COOKIE['QiMall_referral_code'])) {
                setcookie('QiMall_referral_code', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
            }
        }
        
        // 為新用戶生成推薦代碼
        self::generate_referral_code($user_id);
    }
    
    /**
     * 生成推薦代碼
     */
    public static function generate_referral_code($user_id) {
        // 檢查是否已有推薦代碼
        if (get_user_meta($user_id, 'QiMall_referral_code', true)) {
            return;
        }
        
        // 生成格式: USER + 4位隨機英數字
        $max_attempts = 10;
        $code = null;
        
        for ($i = 0; $i < $max_attempts; $i++) {
            $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
            $code = 'USER' . $random;
            
            // 檢查是否重複
            if (!self::get_user_id_by_code($code)) {
                break;
            }
            $code = null;
        }
        
        if ($code) {
            update_user_meta($user_id, 'QiMall_referral_code', $code);
            update_user_meta($user_id, 'QiMall_referral_code_changes_left', 1);
        }
    }
    
    /**
     * 建立二級分銷關係
     */
    private static function create_distribution_relationship($user_id, $parent_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'QiMall_distribution';
        
        // 檢查是否已存在
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $table WHERE user_id = %d",
            $user_id
        ));
        
        if ($exists) {
            return; // 已存在,不重複建立
        }
        
        // 獲取推薦人的推薦人 (爺爺輩)
        $grandparent_id = get_user_meta($parent_id, 'QiMall_referrer', true);
        
        // 獲取分銷設定
        $settings = get_option('QiMall_distribution_settings', array(
            'level_1_rate' => 10,
            'level_2_rate' => 5
        ));
        
        // 插入分銷關係
        $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'parent_id' => $parent_id,
                'grandparent_id' => $grandparent_id ? $grandparent_id : null,
                'level_1_rate' => $settings['level_1_rate'],
                'level_2_rate' => $settings['level_2_rate'],
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%f', '%f', '%s')
        );
    }
    
    /**
     * 根據推薦代碼獲取用戶ID
     */
    public static function get_user_id_by_code($code) {
        global $wpdb;
        
        if (empty($code)) {
            return null;
        }
        
        // 精確比對 (大小寫敏感)
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} 
            WHERE meta_key = 'QiMall_referral_code' 
            AND BINARY meta_value = %s
            LIMIT 1",
            $code
        ));
        
        return $user_id ? intval($user_id) : null;
    }
    
    /**
     * 獲取推薦人資訊
     */
    public static function get_referrer_info($user_id) {
        $referrer_id = get_user_meta($user_id, 'QiMall_referrer', true);
        
        if (!$referrer_id) {
            return null;
        }
        
        // 檢查是否為預設推薦人
        $default_referrer_id = self::get_default_referrer_id();
        
        if ($referrer_id == $default_referrer_id) {
            // 顯示預設名稱 (總店)
            $default_name = get_option('QiMall_default_referrer_name', '總店');
            return array(
                'id' => $referrer_id,
                'name' => $default_name,
                'is_default' => true
            );
        }
        
        $referrer = get_userdata($referrer_id);
        if (!$referrer) {
            return null;
        }
        
        return array(
            'id' => $referrer_id,
            'name' => $referrer->display_name,
            'email' => $referrer->user_email,
            'is_default' => false
        );
    }
    
    /**
     * 獲取預設推薦人ID
     */
    public static function get_default_referrer_id() {
        $default_id = get_option('QiMall_default_referrer_id', 1);
        
        // 驗證該用戶是否存在
        if (!get_userdata($default_id)) {
            $default_id = 1; // 回退到 user_id = 1
        }
        
        return intval($default_id);
    }
    
    /**
     * AJAX: 驗證推薦代碼
     */
    public static function ajax_verify_referral_code() {
        $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        
        if (empty($code)) {
            wp_send_json_error(array('message' => '請輸入推薦代碼'));
        }
        
        $user_id = self::get_user_id_by_code($code);
        
        if (!$user_id) {
            wp_send_json_error(array('message' => '推薦代碼不存在,請重新輸入'));
        }
        
        // 檢查是否為預設推薦人
        $default_referrer_id = self::get_default_referrer_id();
        
        if ($user_id == $default_referrer_id) {
            $name = get_option('QiMall_default_referrer_name', '總店');
        } else {
            $user = get_userdata($user_id);
            $name = $user ? $user->display_name : '未知';
        }
        
        wp_send_json_success(array(
            'user_id' => $user_id,
            'name' => $name
        ));
    }
    
    /**
     * AJAX: 修改推薦代碼
     */
    public static function ajax_update_referral_code() {
        // 檢查登入
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => '請先登入'));
        }
        
        $user_id = get_current_user_id();
        $new_code = isset($_POST['new_code']) ? sanitize_text_field($_POST['new_code']) : '';
        
        // 檢查修改次數
        $changes_left = intval(get_user_meta($user_id, 'QiMall_referral_code_changes_left', true));
        
        if ($changes_left <= 0) {
            wp_send_json_error(array('message' => '您已使用完修改次數'));
        }
        
        // 驗證新代碼
        $validation = self::validate_referral_code($new_code, $user_id);
        
        if (!$validation['valid']) {
            wp_send_json_error(array('message' => $validation['message']));
        }
        
        // 更新推薦代碼
        update_user_meta($user_id, 'QiMall_referral_code', $new_code);
        update_user_meta($user_id, 'QiMall_referral_code_changes_left', $changes_left - 1);
        
        wp_send_json_success(array(
            'message' => '推薦代碼修改成功!',
            'new_code' => $new_code,
            'changes_left' => $changes_left - 1
        ));
    }
    
    /**
     * 驗證推薦代碼
     */
    public static function validate_referral_code($code, $exclude_user_id = null) {
        // 檢查長度
        $length = strlen($code);
        if ($length < 4 || $length > 20) {
            return array('valid' => false, 'message' => '推薦代碼長度必須在 4-20 字元之間');
        }
        
        // 檢查字元 (只允許英數字和底線)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $code)) {
            return array('valid' => false, 'message' => '推薦代碼只能包含英數字和底線');
        }
        
        // 檢查是否重複 (大小寫敏感)
        $existing_user_id = self::get_user_id_by_code($code);
        
        if ($existing_user_id && $existing_user_id != $exclude_user_id) {
            return array('valid' => false, 'message' => '此推薦代碼已被使用,請換一個');
        }
        
        // 可選: 敏感詞過濾
        $blocked_words = array('admin', 'administrator', 'system', 'test');
        foreach ($blocked_words as $word) {
            if (stripos($code, $word) !== false) {
                return array('valid' => false, 'message' => '推薦代碼包含不允許的字詞');
            }
        }
        
        return array('valid' => true, 'message' => '推薦代碼可用');
    }
    
    /**
     * 獲取用戶的推薦連結
     */
    public static function get_referral_url($user_id) {
        $code = get_user_meta($user_id, 'QiMall_referral_code', true);
        
        if (!$code) {
            return null;
        }
        
        return add_query_arg('ref', $code, home_url('/'));
    }
}

// 初始化
QiMall_Referral::init();

