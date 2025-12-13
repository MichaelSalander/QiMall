<?php
/**
 * 定時任務管理類別
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Cron {
    
    /**
     * 設定定時任務
     */
    public static function schedule_events() {
        // 每月積分發放（每月1日凌晨2點）
        if (!wp_next_scheduled('QiMall_monthly_points_distribution')) {
            wp_schedule_event(
                strtotime('first day of next month 02:00:00'),
                'monthly',
                'QiMall_monthly_points_distribution'
            );
        }
        
        // 每日獎勵計算（每天凌晨3點）
        if (!wp_next_scheduled('QiMall_daily_reward_calculation')) {
            wp_schedule_event(
                strtotime('tomorrow 03:00:00'),
                'daily',
                'QiMall_daily_reward_calculation'
            );
        }
        
        // 註冊鉤子
        add_action('QiMall_monthly_points_distribution', array(__CLASS__, 'distribute_monthly_points'));
        add_action('QiMall_daily_reward_calculation', array(__CLASS__, 'calculate_daily_rewards'));
    }
    
    /**
     * 清除定時任務
     */
    public static function clear_scheduled_events() {
        wp_clear_scheduled_hook('QiMall_monthly_points_distribution');
        wp_clear_scheduled_hook('QiMall_daily_reward_calculation');
    }
    
    /**
     * 每月積分發放
     */
    public static function distribute_monthly_points() {
        global $wpdb;
        
        $rewards_table = $wpdb->prefix . 'QiMall_rewards';
        $points_table = $wpdb->prefix . 'QiMall_points';
        
        // 獲取本月所有已計算但未發放的獎勵
        $rewards = $wpdb->get_results(
            "SELECT user_id, SUM(amount) as total_amount 
            FROM $rewards_table 
            WHERE status = 'calculated' 
            GROUP BY user_id"
        );
        
        foreach ($rewards as $reward) {
            $user_id = $reward->user_id;
            $amount = floatval($reward->total_amount);
            
            // 更新或建立積分帳戶
            $existing = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $points_table WHERE user_id = %d", $user_id)
            );
            
            if ($existing) {
                // 更新現有帳戶
                $wpdb->update(
                    $points_table,
                    array(
                        'total_points' => $existing->total_points + $amount,
                        'available_points' => $existing->available_points + $amount,
                        'last_settlement_date' => current_time('mysql')
                    ),
                    array('user_id' => $user_id),
                    array('%f', '%f', '%s'),
                    array('%d')
                );
            } else {
                // 建立新帳戶
                $wpdb->insert(
                    $points_table,
                    array(
                        'user_id' => $user_id,
                        'total_points' => $amount,
                        'available_points' => $amount,
                        'pending_points' => 0,
                        'used_points' => 0,
                        'last_settlement_date' => current_time('mysql')
                    ),
                    array('%d', '%f', '%f', '%f', '%f', '%s')
                );
            }
            
            // 標記獎勵為已發放
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $rewards_table SET status = 'paid', paid_at = %s 
                    WHERE user_id = %d AND status = 'calculated'",
                    current_time('mysql'),
                    $user_id
                )
            );
            
            // 建立交易記錄
            QiMall_Transaction::create(
                $user_id,
                'reward',
                $amount,
                null,
                sprintf('月度積分發放 (%s)', date('Y-m'))
            );
            
            // 發送通知（可選）
            do_action('QiMall_points_distributed', $user_id, $amount);
        }
        
        // 記錄日誌
        error_log(sprintf(
            '[CoShare] Monthly points distributed: %d users, total processed',
            count($rewards)
        ));
    }
    
    /**
     * 每日獎勵計算
     */
    public static function calculate_daily_rewards() {
        global $wpdb;
        $rewards_table = $wpdb->prefix . 'QiMall_rewards';
        
        // 獲取所有待計算的獎勵
        $pending_rewards = QiMall_Reward::get_pending_rewards();
        
        $calculated_count = 0;
        
        foreach ($pending_rewards as $reward) {
            // 標記為已計算
            QiMall_Reward::mark_as_calculated($reward->id);
            $calculated_count++;
        }
        
        // 記錄日誌
        if ($calculated_count > 0) {
            error_log(sprintf(
                '[CoShare] Daily reward calculation: %d rewards calculated',
                $calculated_count
            ));
        }
    }
    
    /**
     * 手動觸發月度積分發放（用於測試）
     */
    public static function manual_distribute_points() {
        self::distribute_monthly_points();
        return array(
            'success' => true,
            'message' => '積分發放完成'
        );
    }
}

// 註冊自定義定時週期
add_filter('cron_schedules', function($schedules) {
    $schedules['monthly'] = array(
        'interval' => 30 * DAY_IN_SECONDS,
        'display' => __('每月一次', 'QiMall')
    );
    return $schedules;
});

