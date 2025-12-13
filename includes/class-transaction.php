<?php
/**
 * 交易記錄管理類別
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Transaction {
    
    /**
     * 建立交易記錄
     */
    public static function create($user_id, $type, $amount, $related_id = null, $description = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_transactions';
        
        // 獲取當前餘額
        $balance = self::get_user_balance($user_id);
        $balance_before = $balance;
        $balance_after = $balance + $amount;
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'transaction_type' => $type,
                'amount' => $amount,
                'balance_before' => $balance_before,
                'balance_after' => $balance_after,
                'related_id' => $related_id,
                'description' => $description
            ),
            array('%d', '%s', '%f', '%f', '%f', '%d', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * 獲取用戶餘額
     */
    public static function get_user_balance($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_transactions';
        
        $balance = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT balance_after FROM $table_name 
                WHERE user_id = %d ORDER BY id DESC LIMIT 1",
                $user_id
            )
        );
        
        return $balance ? floatval($balance) : 0;
    }
    
    /**
     * 獲取用戶交易記錄
     */
    public static function get_user_transactions($user_id, $args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_transactions';
        
        $defaults = array(
            'type' => null,
            'start_date' => null,
            'end_date' => null,
            'limit' => 20,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id);
        
        if ($args['type']) {
            $sql .= $wpdb->prepare(" AND transaction_type = %s", $args['type']);
        }
        
        if ($args['start_date']) {
            $sql .= $wpdb->prepare(" AND created_at >= %s", $args['start_date']);
        }
        
        if ($args['end_date']) {
            $sql .= $wpdb->prepare(" AND created_at <= %s", $args['end_date']);
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($args['limit']) {
            $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }
        
        return $wpdb->get_results($sql);
    }
}

