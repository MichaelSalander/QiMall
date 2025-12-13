<?php
/**
 * 獎勵計算引擎
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Reward_Calculator {
    
    /**
     * 處理訂單完成後的獎勵計算
     */
    public static function process_order_rewards($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $user_id = $order->get_user_id();
        if (!$user_id) {
            return;
        }
        
        // 遍歷訂單商品
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $product_config = QiMall_Product::get($product_id);
            
            // 檢查商品是否啟用分銷
            $distribution_enabled = get_post_meta($product_id, '_QiMall_distribution_enabled', true) === 'yes';
            
            // 如果商品有綁定到制度組(股東/合夥人商品)
            if ($product_config) {
                // 計算商城分潤
                self::calculate_profit_share($order_id, $product_id, $product_config);
                
                // 計算團隊分紅
                self::calculate_team_dividend($order_id, $product_id, $product_config);
                
                // 計算推薦回本
                self::calculate_referral_rebate($order_id, $user_id, $product_config);
            }
            
            // 只有啟用分銷的商品才計算二級分銷(不限於股東/合夥人商品)
            if ($distribution_enabled) {
                self::calculate_distribution($order_id, $user_id, $product_id);
            }
        }
    }
    
    /**
     * 計算商城分潤（股東專屬）
     */
    private static function calculate_profit_share($order_id, $product_id, $product_config) {
        global $wpdb;
        $user_roles_table = $wpdb->prefix . 'QiMall_user_roles';
        
        // 獲取該制度組的所有活躍股東
        $shareholders = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $user_roles_table 
                WHERE group_code = %s AND role_type = 'shareholder' AND status = 'active'",
                $product_config->group_code
            )
        );
        
        if (empty($shareholders)) {
            return;
        }
        
        $order = wc_get_order($order_id);
        $order_total = $order->get_total();
        
        // 獲取制度組配置
        $group = QiMall_Group::get($product_config->group_code);
        if (!$group || $group->shareholder_profit_rate <= 0) {
            return;
        }
        
        // 計算總分潤金額
        $total_profit = $order_total * ($group->shareholder_profit_rate / 100);
        
        // 按股東數量平分
        $profit_per_shareholder = $total_profit / count($shareholders);
        
        // 為每個股東建立獎勵記錄
        foreach ($shareholders as $shareholder) {
            QiMall_Reward::create(array(
                'user_id' => $shareholder->user_id,
                'group_code' => $product_config->group_code,
                'role_type' => 'shareholder',
                'reward_type' => 'profit',
                'amount' => $profit_per_shareholder,
                'source_order_id' => $order_id,
                'calculation_data' => array(
                    'order_total' => $order_total,
                    'profit_rate' => $group->shareholder_profit_rate,
                    'shareholder_count' => count($shareholders),
                    'calculation_method' => 'equal_split'
                ),
                'status' => 'pending'
            ));
            
            // 更新股東累計收益
            QiMall_UserRole::add_earnings($shareholder->id, $profit_per_shareholder);
            
            // 檢查是否已回本
            QiMall_UserRole::check_breakeven($shareholder->id);
        }
    }
    
    /**
     * 計算團隊分紅（股東專屬）
     */
    private static function calculate_team_dividend($order_id, $product_id, $product_config) {
        global $wpdb;
        $user_roles_table = $wpdb->prefix . 'QiMall_user_roles';
        
        // 獲取該制度組的所有活躍股東
        $shareholders = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $user_roles_table 
                WHERE group_code = %s AND role_type = 'shareholder' AND status = 'active'",
                $product_config->group_code
            )
        );
        
        if (empty($shareholders)) {
            return;
        }
        
        $order = wc_get_order($order_id);
        $order_total = $order->get_total();
        
        // 獲取制度組配置
        $group = QiMall_Group::get($product_config->group_code);
        if (!$group || $group->shareholder_dividend_rate <= 0) {
            return;
        }
        
        // 計算總分紅金額
        $total_dividend = $order_total * ($group->shareholder_dividend_rate / 100);
        
        // 按股東數量平分
        $dividend_per_shareholder = $total_dividend / count($shareholders);
        
        // 為每個股東建立獎勵記錄
        foreach ($shareholders as $shareholder) {
            QiMall_Reward::create(array(
                'user_id' => $shareholder->user_id,
                'group_code' => $product_config->group_code,
                'role_type' => 'shareholder',
                'reward_type' => 'dividend',
                'amount' => $dividend_per_shareholder,
                'source_order_id' => $order_id,
                'calculation_data' => array(
                    'order_total' => $order_total,
                    'dividend_rate' => $group->shareholder_dividend_rate,
                    'shareholder_count' => count($shareholders),
                    'calculation_method' => 'equal_split'
                ),
                'status' => 'pending'
            ));
            
            // 更新股東累計收益
            QiMall_UserRole::add_earnings($shareholder->id, $dividend_per_shareholder);
            
            // 檢查是否已回本
            QiMall_UserRole::check_breakeven($shareholder->id);
        }
    }
    
    /**
     * 計算推薦回本
     */
    private static function calculate_referral_rebate($order_id, $buyer_id, $product_config) {
        global $wpdb;
        
        // 獲取推薦人
        $referrer_id = get_user_meta($buyer_id, 'QiMall_referrer', true);
        if (!$referrer_id) {
            return;
        }
        
        // 檢查推薦人在此制度組的角色
        $referrer_role = QiMall_UserRole::get_user_role_in_group($referrer_id, $product_config->group_code);
        if (!$referrer_role || $referrer_role->status !== 'active') {
            return;
        }
        
        // 記錄推薦關係
        $referrals_table = $wpdb->prefix . 'QiMall_referrals';
        $wpdb->insert(
            $referrals_table,
            array(
                'referrer_id' => $referrer_id,
                'referee_id' => $buyer_id,
                'group_code' => $product_config->group_code,
                'plan_type' => $product_config->plan_type,
                'order_id' => $order_id,
                'is_counted' => 0
            ),
            array('%d', '%d', '%s', '%s', '%d', '%d')
        );
        
        // 增加推薦計數
        QiMall_UserRole::increment_referral_count($referrer_role->id);
        
        // 獲取制度組配置
        $group = QiMall_Group::get($product_config->group_code);
        
        // 檢查回本條件
        $required_count = 0;
        if ($referrer_role->role_type === 'shareholder') {
            $required_count = $group->shareholder_referral_count;
        } elseif ($referrer_role->role_type === 'partner') {
            $required_count = $group->partner_referral_count;
        }
        
        if ($required_count > 0 && $referrer_role->referral_count >= $required_count) {
            // 達到回本條件，發放回本獎勵
            $rebate_amount = $referrer_role->purchase_price;
            
            QiMall_Reward::create(array(
                'user_id' => $referrer_id,
                'group_code' => $product_config->group_code,
                'role_type' => $referrer_role->role_type,
                'reward_type' => 'referral',
                'amount' => $rebate_amount,
                'source_order_id' => $order_id,
                'source_user_id' => $buyer_id,
                'calculation_data' => array(
                    'referral_count' => $referrer_role->referral_count,
                    'required_count' => $required_count,
                    'rebate_type' => $referrer_role->role_type === 'partner' ? 'push_three_return_one' : 'referral_rebate'
                ),
                'status' => 'pending'
            ));
            
            // 更新累計收益
            QiMall_UserRole::add_earnings($referrer_role->id, $rebate_amount);
            
            // 標記為已回本
            QiMall_UserRole::check_breakeven($referrer_role->id);
        }
    }
    
    /**
     * 計算二級分銷
     */
    private static function calculate_distribution($order_id, $buyer_id, $product_id) {
        global $wpdb;
        $distribution_table = $wpdb->prefix . 'QiMall_distribution';
        
        // 獲取買家的分銷關係
        $distribution = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $distribution_table WHERE user_id = %d", $buyer_id)
        );
        
        if (!$distribution) {
            return;
        }
        
        $order = wc_get_order($order_id);
        $order_total = $order->get_total();
        
        // 一級分銷
        if ($distribution->parent_id && $distribution->level_1_rate > 0) {
            $level_1_amount = $order_total * ($distribution->level_1_rate / 100);
            
            QiMall_Reward::create(array(
                'user_id' => $distribution->parent_id,
                'group_code' => 'common',
                'role_type' => 'distributor',
                'reward_type' => 'distribution',
                'amount' => $level_1_amount,
                'source_order_id' => $order_id,
                'source_user_id' => $buyer_id,
                'calculation_data' => array(
                    'level' => 1,
                    'rate' => $distribution->level_1_rate,
                    'order_total' => $order_total
                ),
                'status' => 'pending'
            ));
            
            // 更新累計收益
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $distribution_table SET total_level_1_earnings = total_level_1_earnings + %f 
                    WHERE user_id = %d",
                    $level_1_amount,
                    $distribution->parent_id
                )
            );
        }
        
        // 二級分銷
        if ($distribution->grandparent_id && $distribution->level_2_rate > 0) {
            $level_2_amount = $order_total * ($distribution->level_2_rate / 100);
            
            QiMall_Reward::create(array(
                'user_id' => $distribution->grandparent_id,
                'group_code' => 'common',
                'role_type' => 'distributor',
                'reward_type' => 'distribution',
                'amount' => $level_2_amount,
                'source_order_id' => $order_id,
                'source_user_id' => $buyer_id,
                'calculation_data' => array(
                    'level' => 2,
                    'rate' => $distribution->level_2_rate,
                    'order_total' => $order_total
                ),
                'status' => 'pending'
            ));
            
            // 更新累計收益
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $distribution_table SET total_level_2_earnings = total_level_2_earnings + %f 
                    WHERE user_id = %d",
                    $level_2_amount,
                    $distribution->grandparent_id
                )
            );
        }
    }
}

