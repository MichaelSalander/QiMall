<?php
/**
 * 用戶角色管理類別
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_UserRole {
    
    /**
     * 建立用戶角色
     */
    public static function create($user_id, $group_code, $role_type, $plan_id, $order_id, $price) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_user_roles';
        
        // 檢查同組是否已有角色（組內互斥）
        $existing = self::get_user_role_in_group($user_id, $group_code);
        if ($existing) {
            return new WP_Error('role_exists', '此制度組已有角色，同組角色互斥');
        }
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'group_code' => $group_code,
                'role_type' => $role_type,
                'plan_id' => $plan_id,
                'order_id' => $order_id,
                'purchase_price' => $price,
                'status' => 'active',
                'activated_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%d', '%d', '%f', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * 獲取用戶在特定組的角色
     */
    public static function get_user_role_in_group($user_id, $group_code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_user_roles';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d AND group_code = %s AND status = 'active'",
                $user_id,
                $group_code
            )
        );
    }
    
    /**
     * 獲取用戶所有角色
     */
    public static function get_user_roles($user_id, $status = 'active') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_user_roles';
        
        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id);
        
        if ($status) {
            $sql .= $wpdb->prepare(" AND status = %s", $status);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * 檢查用戶是否為股東
     */
    public static function is_shareholder($user_id, $group_code = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_user_roles';
        
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND role_type = 'shareholder' AND status = 'active'",
            $user_id
        );
        
        if ($group_code) {
            $sql .= $wpdb->prepare(" AND group_code = %s", $group_code);
        }
        
        return $wpdb->get_var($sql) > 0;
    }
    
    /**
     * 檢查用戶是否為合夥人
     */
    public static function is_partner($user_id, $group_code = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_user_roles';
        
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND role_type = 'partner' AND status = 'active'",
            $user_id
        );
        
        if ($group_code) {
            $sql .= $wpdb->prepare(" AND group_code = %s", $group_code);
        }
        
        return $wpdb->get_var($sql) > 0;
    }
    
    /**
     * 更新收益
     */
    public static function add_earnings($role_id, $amount) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_user_roles';
        
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table_name SET total_earned = total_earned + %f WHERE id = %d",
                $amount,
                $role_id
            )
        );
    }
    
    /**
     * 增加推薦計數
     */
    public static function increment_referral_count($role_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_user_roles';
        
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE $table_name SET referral_count = referral_count + 1 WHERE id = %d",
                $role_id
            )
        );
    }
    
    /**
     * 檢查是否已回本
     */
    public static function check_breakeven($role_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_user_roles';
        
        $role = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $role_id)
        );
        
        if (!$role) {
            return false;
        }
        
        // 如果已獲得的收益大於等於購買價格，則回本
        if ($role->total_earned >= $role->purchase_price) {
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'completed',
                    'completed_at' => current_time('mysql')
                ),
                array('id' => $role_id),
                array('%s', '%s'),
                array('%d')
            );
            return true;
        }
        
        return false;
    }
    
    /**
     * 更新角色狀態
     */
    public static function update_status($role_id, $status) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_user_roles';
        
        $data = array('status' => $status);
        
        if ($status === 'completed') {
            $data['completed_at'] = current_time('mysql');
        }
        
        return $wpdb->update(
            $table_name,
            $data,
            array('id' => $role_id),
            array('%s', '%s'),
            array('%d')
        );
    }
}

