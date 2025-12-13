<?php
/**
 * 商品管理類別
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Product {
    
    /**
     * 建立商品配置
     */
    public static function create($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_products';
        
        $wpdb->insert(
            $table_name,
            array(
                'product_id' => intval($data['product_id']),
                'group_code' => sanitize_text_field($data['group_code']),
                'plan_type' => sanitize_text_field($data['plan_type']),
                'price' => floatval($data['price']),
                'rebate_rules' => isset($data['rebate_rules']) ? wp_json_encode($data['rebate_rules']) : null,
                'profit_rate' => isset($data['profit_rate']) ? floatval($data['profit_rate']) : 0,
                'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1
            ),
            array('%d', '%s', '%s', '%f', '%s', '%f', '%d')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * 獲取商品配置
     */
    public static function get($product_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_products';
        
        $product = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE product_id = %d", $product_id)
        );
        
        if ($product && $product->rebate_rules) {
            $product->rebate_rules = json_decode($product->rebate_rules, true);
        }
        
        return $product;
    }
    
    /**
     * 根據制度組獲取商品
     */
    public static function get_by_group($group_code, $plan_type = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_products';
        
        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE group_code = %s", $group_code);
        
        if ($plan_type) {
            $sql .= $wpdb->prepare(" AND plan_type = %s", $plan_type);
        }
        
        $sql .= " AND is_active = 1";
        
        $results = $wpdb->get_results($sql);
        
        foreach ($results as $product) {
            if ($product->rebate_rules) {
                $product->rebate_rules = json_decode($product->rebate_rules, true);
            }
        }
        
        return $results;
    }
    
    /**
     * 獲取所有股東商品
     */
    public static function get_shareholder_products() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_products';
        
        return $wpdb->get_results(
            "SELECT * FROM $table_name WHERE plan_type = 'shareholder' AND is_active = 1"
        );
    }
    
    /**
     * 獲取所有合夥人商品
     */
    public static function get_partner_products() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_products';
        
        return $wpdb->get_results(
            "SELECT * FROM $table_name WHERE plan_type = 'partner' AND is_active = 1"
        );
    }
    
    /**
     * 更新商品配置
     */
    public static function update($product_id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_products';
        
        if (isset($data['rebate_rules']) && is_array($data['rebate_rules'])) {
            $data['rebate_rules'] = wp_json_encode($data['rebate_rules']);
        }
        
        return $wpdb->update(
            $table_name,
            $data,
            array('product_id' => $product_id),
            null,
            array('%d')
        );
    }
    
    /**
     * 刪除商品配置
     */
    public static function delete($product_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_products';
        
        return $wpdb->delete(
            $table_name,
            array('product_id' => $product_id),
            array('%d')
        );
    }
    
    /**
     * 檢查商品是否為股東商品
     */
    public static function is_shareholder_product($product_id) {
        $product = self::get($product_id);
        return $product && $product->plan_type === 'shareholder';
    }
    
    /**
     * 檢查商品是否為合夥人商品
     */
    public static function is_partner_product($product_id) {
        $product = self::get($product_id);
        return $product && $product->plan_type === 'partner';
    }
}

