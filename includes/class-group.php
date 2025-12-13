<?php
/**
 * 制度組管理類別
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Group {
    
    /**
     * 建立制度組
     */
    public static function create($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_groups';
        
        $wpdb->insert(
            $table_name,
            array(
                'group_code' => sanitize_text_field($data['group_code']),
                'group_name' => sanitize_text_field($data['group_name']),
                'shareholder_product_name' => sanitize_text_field($data['shareholder_product_name']),
                'partner_product_name' => sanitize_text_field($data['partner_product_name']),
                'shareholder_profit_rate' => isset($data['shareholder_profit_rate']) ? floatval($data['shareholder_profit_rate']) : 0,
                'shareholder_dividend_rate' => isset($data['shareholder_dividend_rate']) ? floatval($data['shareholder_dividend_rate']) : 0,
                'shareholder_referral_count' => isset($data['shareholder_referral_count']) ? intval($data['shareholder_referral_count']) : 0,
                'partner_referral_count' => isset($data['partner_referral_count']) ? intval($data['partner_referral_count']) : 3,
                'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1
            ),
            array('%s', '%s', '%s', '%s', '%f', '%f', '%d', '%d', '%d')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * 獲取制度組
     */
    public static function get($group_code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_groups';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE group_code = %s", $group_code)
        );
    }
    
    /**
     * 獲取所有制度組
     */
    public static function get_all($active_only = true) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_groups';
        
        $sql = "SELECT * FROM $table_name";
        if ($active_only) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY id ASC";
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * 更新制度組
     */
    public static function update($group_code, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_groups';
        
        return $wpdb->update(
            $table_name,
            $data,
            array('group_code' => $group_code),
            array('%s', '%s', '%s', '%s', '%f', '%f', '%d', '%d', '%d'),
            array('%s')
        );
    }
    
    /**
     * 刪除制度組
     */
    public static function delete($group_code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_groups';
        
        return $wpdb->delete(
            $table_name,
            array('group_code' => $group_code),
            array('%s')
        );
    }
}

