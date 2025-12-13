<?php
/**
 * 獎勵管理類別
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Reward {
    
    /**
     * 建立獎勵記錄
     */
    public static function create($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_rewards';
        
        $calculation_data = isset($data['calculation_data']) ? wp_json_encode($data['calculation_data']) : null;
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => intval($data['user_id']),
                'group_code' => sanitize_text_field($data['group_code']),
                'role_type' => sanitize_text_field($data['role_type']),
                'reward_type' => sanitize_text_field($data['reward_type']),
                'amount' => floatval($data['amount']),
                'source_order_id' => isset($data['source_order_id']) ? intval($data['source_order_id']) : null,
                'source_user_id' => isset($data['source_user_id']) ? intval($data['source_user_id']) : null,
                'calculation_data' => $calculation_data,
                'status' => isset($data['status']) ? sanitize_text_field($data['status']) : 'pending'
            ),
            array('%d', '%s', '%s', '%s', '%f', '%d', '%d', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * 獲取用戶獎勵
     */
    public static function get_user_rewards($user_id, $args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_rewards';
        
        $defaults = array(
            'status' => null,
            'reward_type' => null,
            'group_code' => null,
            'start_date' => null,
            'end_date' => null,
            'limit' => null,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id);
        
        if ($args['status']) {
            $sql .= $wpdb->prepare(" AND status = %s", $args['status']);
        }
        
        if ($args['reward_type']) {
            $sql .= $wpdb->prepare(" AND reward_type = %s", $args['reward_type']);
        }
        
        if ($args['group_code']) {
            $sql .= $wpdb->prepare(" AND group_code = %s", $args['group_code']);
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
        
        $results = $wpdb->get_results($sql);
        
        foreach ($results as $reward) {
            if ($reward->calculation_data) {
                $reward->calculation_data = json_decode($reward->calculation_data, true);
            }
        }
        
        return $results;
    }
    
    /**
     * 獲取用戶總收益
     */
    public static function get_user_total_earnings($user_id, $status = 'calculated') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_rewards';
        
        return floatval($wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(amount) FROM $table_name WHERE user_id = %d AND status = %s",
                $user_id,
                $status
            )
        ));
    }
    
    /**
     * 獲取待結算獎勵
     */
    public static function get_pending_rewards($limit = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_rewards';
        
        $sql = "SELECT * FROM $table_name WHERE status = 'pending' ORDER BY created_at ASC";
        
        if ($limit) {
            $sql .= $wpdb->prepare(" LIMIT %d", $limit);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * 標記為已計算
     */
    public static function mark_as_calculated($reward_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_rewards';
        
        return $wpdb->update(
            $table_name,
            array(
                'status' => 'calculated',
                'calculated_at' => current_time('mysql')
            ),
            array('id' => $reward_id),
            array('%s', '%s'),
            array('%d')
        );
    }
    
    /**
     * 標記為已發放
     */
    public static function mark_as_paid($reward_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_rewards';
        
        return $wpdb->update(
            $table_name,
            array(
                'status' => 'paid',
                'paid_at' => current_time('mysql')
            ),
            array('id' => $reward_id),
            array('%s', '%s'),
            array('%d')
        );
    }
    
    /**
     * 批量標記為已發放
     */
    public static function batch_mark_as_paid($reward_ids) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_rewards';
        
        if (empty($reward_ids)) {
            return false;
        }
        
        $ids = array_map('intval', $reward_ids);
        $ids_placeholder = implode(',', $ids);
        
        return $wpdb->query(
            "UPDATE $table_name SET status = 'paid', paid_at = '" . current_time('mysql') . "' 
            WHERE id IN ($ids_placeholder)"
        );
    }
    
    /**
     * 獲取統計數據
     */
    public static function get_statistics($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_rewards';
        
        $stats = array(
            'total_pending' => 0,
            'total_calculated' => 0,
            'total_paid' => 0,
            'by_type' => array()
        );
        
        // 按狀態統計
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT status, SUM(amount) as total FROM $table_name 
                WHERE user_id = %d GROUP BY status",
                $user_id
            )
        );
        
        foreach ($results as $row) {
            $stats['total_' . $row->status] = floatval($row->total);
        }
        
        // 按類型統計
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT reward_type, SUM(amount) as total FROM $table_name 
                WHERE user_id = %d AND status IN ('calculated', 'paid') GROUP BY reward_type",
                $user_id
            )
        );
        
        foreach ($results as $row) {
            $stats['by_type'][$row->reward_type] = floatval($row->total);
        }
        
        return $stats;
    }
}

