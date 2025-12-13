<?php
/**
 * 前台公開介面類別
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Public {
    
    public function __construct() {
        // 註冊短代碼
        add_shortcode('QiMall_member_dashboard', array($this, 'member_dashboard_shortcode'));
        add_shortcode('QiMall_earnings', array($this, 'earnings_shortcode'));
        add_shortcode('QiMall_referral_link', array($this, 'referral_link_shortcode'));
        // 客戶專用入口
        add_shortcode('QiMall_client_login', array($this, 'client_login_shortcode'));
        add_shortcode('QiMall_client_admin', array($this, 'client_admin_shortcode'));
        
        // 前台腳本和樣式
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // AJAX處理
        add_action('wp_ajax_QiMall_get_earnings', array($this, 'ajax_get_earnings'));
        add_action('wp_ajax_QiMall_get_team_stats', array($this, 'ajax_get_team_stats'));
        
        // WooCommerce 整合
        add_action('woocommerce_order_status_completed', array($this, 'on_order_completed'));
        add_action('woocommerce_checkout_order_processed', array($this, 'process_referral'));
    }
    
    /**
     * 載入前台腳本和樣式
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'coshare-public-css',
            QiMall_PLUGIN_URL . 'public/css/public.css',
            array(),
            QiMall_VERSION
        );
        
        wp_enqueue_script(
            'coshare-public-js',
            QiMall_PLUGIN_URL . 'public/js/public.js',
            array('jquery'),
            QiMall_VERSION,
            true
        );
        
        wp_localize_script('coshare-public-js', 'qimallPublic', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('QiMall_public_nonce')
        ));
    }
    
    /**
     * 會員中心短代碼
     */
    public function member_dashboard_shortcode($atts) {
        ob_start();
        include QiMall_PLUGIN_DIR . 'public/views/member-dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * 收益查詢短代碼
     */
    public function earnings_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>請先登入以查看收益</p>';
        }
        
        ob_start();
        include QiMall_PLUGIN_DIR . 'public/views/earnings.php';
        return ob_get_clean();
    }
    
    /**
     * 推薦連結短代碼
     */
    public function referral_link_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>請先登入以獲取推薦連結</p>';
        }
        
        $user_id = get_current_user_id();
        $referral_url = add_query_arg('ref', $user_id, home_url());
        
        return sprintf(
            '<div class="coshare-referral-box">
                <p><strong>您的推薦連結：</strong></p>
                <input type="text" value="%s" readonly class="coshare-referral-input">
                <button class="button copy-referral-link">複製連結</button>
            </div>',
            esc_url($referral_url)
        );
    }
    
    /**
     * 客戶登入頁面
     */
    public function client_login_shortcode($atts) {
        // 已登入的重導已提前在 template_redirect 執行，這裡僅給提示文字避免 headers already sent 警告
        if (is_user_logged_in()) {
            return '<p>已登入，正在導向客戶後台...</p>';
        }
        ob_start();
        include QiMall_PLUGIN_DIR . 'public/views/client-login.php';
        return ob_get_clean();
    }
    
    /**
     * 客戶後台入口（前台渲染）
     */
    public function client_admin_shortcode($atts) {
        // 未登入的重導已在 template_redirect 前置處理
        if (!is_user_logged_in()) {
            return '<p>請先登入，正在導向登入頁...</p>';
        }
        // 權限檢查：需具備 client portal 能力
        if (!current_user_can('QiMall_client_portal') && !current_user_can('manage_options')) {
            return '<p>您沒有權限存取此頁面。</p>';
        }
        ob_start();
        include QiMall_PLUGIN_DIR . 'public/views/client-admin.php';
        return ob_get_clean();
    }
    
    /**
     * AJAX 獲取收益資料
     */
    public function ajax_get_earnings() {
        check_ajax_referer('QiMall_public_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('未登入');
        }
        
        $stats = QiMall_Reward::get_statistics($user_id);
        
        wp_send_json_success($stats);
    }
    
    /**
     * AJAX 獲取團隊統計
     */
    public function ajax_get_team_stats() {
        check_ajax_referer('QiMall_public_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('未登入');
        }
        
        $stats = QiMall_Distribution_Calculator::get_team_statistics($user_id);
        
        wp_send_json_success($stats);
    }
    
    /**
     * 訂單完成處理
     */
    public function on_order_completed($order_id) {
        // 觸發獎勵計算
        QiMall_Reward_Calculator::process_order_rewards($order_id);
    }
    
    /**
     * 處理推薦關係
     */
    public function process_referral($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        
        if (!$user_id) {
            return;
        }
        
        // 檢查是否已有推薦人
        $existing_referrer = get_user_meta($user_id, 'QiMall_referrer', true);
        if ($existing_referrer) {
            return;
        }
        
        // 從URL獲取推薦人
        $ref = isset($_GET['ref']) ? intval($_GET['ref']) : 0;
        if (!$ref || $ref === $user_id) {
            return;
        }
        
        // 保存推薦關係
        update_user_meta($user_id, 'QiMall_referrer', $ref);
        
        // 建立分銷關係
        QiMall_Distribution_Calculator::create_distribution($user_id, $ref);
    }
}

// 初始化前台介面
new QiMall_Public();

