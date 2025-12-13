<?php
/**
 * 分銷計算類別
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Distribution_Calculator {
    
    /**
     * 建立用戶分銷關係
     */
    public static function create_distribution($user_id, $parent_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_distribution';
        
        // 檢查是否已存在
        $exists = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE user_id = %d", $user_id)
        );
        
        if ($exists) {
            return false;
        }
        
        $grandparent_id = null;
        
        // 如果有父級，獲取祖父級
        if ($parent_id) {
            $parent = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $parent_id)
            );
            
            if ($parent) {
                $grandparent_id = $parent->parent_id;
            }
        }
        
        // 獲取分銷比例設定
        $settings = get_option('QiMall_distribution_settings', array(
            'level_1_rate' => 10,
            'level_2_rate' => 5
        ));
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'parent_id' => $parent_id,
                'grandparent_id' => $grandparent_id,
                'level_1_rate' => $settings['level_1_rate'],
                'level_2_rate' => $settings['level_2_rate']
            ),
            array('%d', '%d', '%d', '%f', '%f')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * 獲取用戶的下級
     */
    public static function get_children($user_id, $level = 1) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_distribution';
        
        if ($level === 1) {
            return $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM $table_name WHERE parent_id = %d", $user_id)
            );
        } elseif ($level === 2) {
            return $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM $table_name WHERE grandparent_id = %d", $user_id)
            );
        }
        
        return array();
    }
    
    /**
     * 獲取用戶團隊統計
     */
    public static function get_team_statistics($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_distribution';
        
        $stats = array(
            'level_1_count' => 0,
            'level_2_count' => 0,
            'level_1_earnings' => 0,
            'level_2_earnings' => 0
        );
        
        // 一級人數
        $stats['level_1_count'] = intval($wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE parent_id = %d", $user_id)
        ));
        
        // 二級人數
        $stats['level_2_count'] = intval($wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE grandparent_id = %d", $user_id)
        ));
        
        // 獲取收益
        $distribution = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id)
        );
        
        if ($distribution) {
            $stats['level_1_earnings'] = floatval($distribution->total_level_1_earnings);
            $stats['level_2_earnings'] = floatval($distribution->total_level_2_earnings);
        }
        
        return $stats;
    }
}

