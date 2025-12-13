<?php
/**
 * 測試資料生成器
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Demo_Data {
    
    /**
     * 一鍵生成所有測試資料
     */
    public static function generate_all() {
        global $wpdb;
        
        $results = array(
            'success' => true,
            'message' => '',
            'data' => array()
        );
        
        try {
            // 1. 建立制度組
            $group_result = self::create_demo_groups();
            $results['data']['groups'] = $group_result;
            
            // 2. 建立測試商品
            $product_result = self::create_demo_products();
            $results['data']['products'] = $product_result;
            
            // 3. 綁定商品到制度組
            $binding_result = self::bind_products_to_groups($product_result);
            $results['data']['bindings'] = $binding_result;
            
            // 4. 建立測試會員
            $user_result = self::create_demo_users();
            $results['data']['users'] = $user_result;
            
            // 5. 建立推薦關係
            $referral_result = self::create_demo_referrals($user_result);
            $results['data']['referrals'] = $referral_result;
            
            // 6. 建立測試訂單
            $order_result = self::create_demo_orders($user_result, $product_result);
            $results['data']['orders'] = $order_result;
            
            // 7. 計算獎勵
            $reward_result = self::calculate_demo_rewards();
            $results['data']['rewards'] = $reward_result;
            
            $results['message'] = '✅ 測試資料生成成功！';
            
        } catch (Exception $e) {
            $results['success'] = false;
            $results['message'] = '❌ 錯誤：' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * 建立測試制度組
     */
    private static function create_demo_groups() {
        global $wpdb;
        $table = $wpdb->prefix . 'QiMall_groups';
        
        $groups = array(
            array(
                'group_code' => 'group_01',
                'group_name' => '制度組①',
                'shareholder_product_name' => '消費股東A',
                'partner_product_name' => '消費合夥人B',
                'shareholder_profit_rate' => 5.0,
                'shareholder_dividend_rate' => 3.0,
                'shareholder_rebate_count' => 10,
                'partner_rebate_count' => 3,
                'is_active' => 1
            ),
            array(
                'group_code' => 'group_02',
                'group_name' => '制度組②',
                'shareholder_product_name' => '消費股東C',
                'partner_product_name' => '消費合夥人D',
                'shareholder_profit_rate' => 8.0,
                'shareholder_dividend_rate' => 5.0,
                'shareholder_rebate_count' => 15,
                'partner_rebate_count' => 5,
                'is_active' => 1
            )
        );
        
        $created = array();
        foreach ($groups as $group) {
            // 檢查是否已存在
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE group_code = %s",
                $group['group_code']
            ));
            
            if (!$exists) {
                $wpdb->insert($table, $group);
                $created[] = $group['group_code'];
            }
        }
        
        return $created;
    }
    
    /**
     * 建立測試商品
     */
    private static function create_demo_products() {
        $products = array(
            array(
                'name' => '消費股東A',
                'price' => 10000,
                'type' => 'shareholder',
                'group' => 'group_01'
            ),
            array(
                'name' => '消費合夥人B',
                'price' => 3000,
                'type' => 'partner',
                'group' => 'group_01'
            ),
            array(
                'name' => '消費股東C',
                'price' => 20000,
                'type' => 'shareholder',
                'group' => 'group_02'
            ),
            array(
                'name' => '消費合夥人D',
                'price' => 5000,
                'type' => 'partner',
                'group' => 'group_02'
            ),
            array(
                'name' => '一般商品E',
                'price' => 500,
                'type' => 'normal',
                'group' => null
            )
        );
        
        $created = array();
        foreach ($products as $product_data) {
            // 檢查商品是否已存在
            $existing = get_posts(array(
                'post_type' => 'product',
                'title' => $product_data['name'],
                'posts_per_page' => 1
            ));
            
            if (!empty($existing)) {
                $product_id = $existing[0]->ID;
                $product_obj = wc_get_product($product_id);
                $created[$product_data['type']] = array(
                    'id' => $product_id,
                    'name' => $product_data['name'],
                    'price' => $product_obj->get_regular_price(),
                    'group' => $product_data['group'],
                    'type' => $product_data['type']
                );
                continue;
            }
            
            // 建立 WooCommerce 商品
            $product = new WC_Product_Simple();
            $product->set_name($product_data['name']);
            $product->set_regular_price($product_data['price']);
            $product->set_status('publish');
            $product->set_catalog_visibility('visible');
            $product->set_description('這是系統自動建立的測試商品');
            $product_id = $product->save();
            
            $created[$product_data['type']] = array(
                'id' => $product_id,
                'name' => $product_data['name'],
                'price' => $product_data['price'],
                'group' => $product_data['group'],
                'type' => $product_data['type']
            );
        }
        
        return $created;
    }
    
    /**
     * 綁定商品到制度組
     */
    private static function bind_products_to_groups($products) {
        $bindings = array();
        
        foreach ($products as $type => $product) {
            // 一般商品不綁定制度組,但啟用二級分銷
            if ($type === 'normal') {
                update_post_meta($product['id'], '_QiMall_distribution_enabled', 'yes');
                continue;
            }
            
            if (!isset($product['group']) || empty($product['group'])) {
                continue;
            }
            
            // 使用新的 post_meta 方式綁定
            QiMall_Product::bind($product['id'], $product['group'], $type);
            $bindings[] = $product['name'];
        }
        
        return $bindings;
    }
    
    /**
     * 建立測試會員
     */
    private static function create_demo_users() {
        $users_data = array(
            array('username' => 'test_user_01', 'email' => 'user01@test.com', 'display_name' => '測試用戶A'),
            array('username' => 'test_user_02', 'email' => 'user02@test.com', 'display_name' => '測試用戶B'),
            array('username' => 'test_user_03', 'email' => 'user03@test.com', 'display_name' => '測試用戶C'),
            array('username' => 'test_user_04', 'email' => 'user04@test.com', 'display_name' => '測試用戶D'),
            array('username' => 'test_user_05', 'email' => 'user05@test.com', 'display_name' => '測試用戶E')
        );
        
        $created = array();
        foreach ($users_data as $user_data) {
            // 檢查用戶是否已存在
            $user = get_user_by('login', $user_data['username']);
            
            if (!$user) {
                $user_id = wp_create_user(
                    $user_data['username'],
                    'test123456',
                    $user_data['email']
                );
                
                if (!is_wp_error($user_id)) {
                    wp_update_user(array(
                        'ID' => $user_id,
                        'display_name' => $user_data['display_name']
                    ));
                    $created[] = $user_id;
                } else {
                    $user_id = null;
                }
            } else {
                $user_id = $user->ID;
                $created[] = $user_id;
            }
        }
        
        return $created;
    }
    
    /**
     * 建立推薦關係
     */
    private static function create_demo_referrals($user_ids) {
        global $wpdb;
        $table = $wpdb->prefix . 'QiMall_referrals';
        
        if (count($user_ids) < 3) {
            return array();
        }
        
        $referrals = array(
            array('referrer_id' => $user_ids[0], 'referee_id' => $user_ids[1], 'level' => 1),
            array('referrer_id' => $user_ids[0], 'referee_id' => $user_ids[2], 'level' => 1),
            array('referrer_id' => $user_ids[1], 'referee_id' => $user_ids[3], 'level' => 1),
            array('referrer_id' => $user_ids[2], 'referee_id' => $user_ids[4], 'level' => 1)
        );
        
        $created = array();
        foreach ($referrals as $referral) {
            // 檢查推薦關係是否已存在
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE referrer_id = %d AND referee_id = %d",
                $referral['referrer_id'],
                $referral['referee_id']
            ));
            
            if (!$exists) {
                $wpdb->insert($table, array_merge($referral, array(
                    'created_at' => current_time('mysql')
                )));
                $created[] = $referral;
            }
        }
        
        return $created;
    }
    
    /**
     * 建立測試訂單
     */
    private static function create_demo_orders($user_ids, $products) {
        if (empty($user_ids) || empty($products)) {
            return array();
        }
        
        $orders_data = array(
            array('user_id' => $user_ids[0], 'product_type' => 'shareholder', 'quantity' => 1),
            array('user_id' => $user_ids[1], 'product_type' => 'partner', 'quantity' => 1),
            array('user_id' => $user_ids[2], 'product_type' => 'shareholder', 'quantity' => 1),
            array('user_id' => $user_ids[3], 'product_type' => 'normal', 'quantity' => 2),
            array('user_id' => $user_ids[4], 'product_type' => 'normal', 'quantity' => 3)
        );
        
        $created = array();
        foreach ($orders_data as $order_data) {
            if (!isset($products[$order_data['product_type']])) {
                continue;
            }
            
            $product_info = $products[$order_data['product_type']];
            
            // 建立訂單
            $order = wc_create_order(array('customer_id' => $order_data['user_id']));
            
            // 加入商品
            $order->add_product(wc_get_product($product_info['id']), $order_data['quantity']);
            
            // 計算總額
            $order->calculate_totals();
            
            // 設定訂單狀態為已完成
            $order->update_status('completed', '測試訂單自動完成');
            
            $created[] = array(
                'order_id' => $order->get_id(),
                'user_id' => $order_data['user_id'],
                'product' => $product_info['name'],
                'total' => $order->get_total()
            );
        }
        
        return $created;
    }
    
    /**
     * 計算測試獎勵
     */
    private static function calculate_demo_rewards() {
        // 取得所有已完成的訂單
        $orders = wc_get_orders(array(
            'status' => 'completed',
            'limit' => 50
        ));
        
        $calculated = array();
        foreach ($orders as $order) {
            QiMall_Reward_Calculator::process_order_rewards($order->get_id());
            $calculated[] = $order->get_id();
        }
        
        return $calculated;
    }
    
    /**
     * 清除所有測試資料
     */
    public static function clear_all() {
        global $wpdb;
        
        $results = array(
            'success' => true,
            'message' => '',
            'data' => array()
        );
        
        try {
            // 1. 刪除測試訂單
            $orders = wc_get_orders(array(
                'limit' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_customer_user',
                        'value' => array_map(function($u) {
                            return get_user_by('login', $u)->ID;
                        }, array('test_user_01', 'test_user_02', 'test_user_03', 'test_user_04', 'test_user_05')),
                        'compare' => 'IN'
                    )
                )
            ));
            
            foreach ($orders as $order) {
                $order->delete(true);
            }
            $results['data']['orders_deleted'] = count($orders);
            
            // 2. 刪除測試商品
            $test_products = array('消費股東A', '消費合夥人B', '消費股東C', '消費合夥人D', '一般商品E');
            $deleted_products = 0;
            foreach ($test_products as $product_name) {
                $products = get_posts(array(
                    'post_type' => 'product',
                    'title' => $product_name,
                    'posts_per_page' => 1
                ));
                
                if (!empty($products)) {
                    wp_delete_post($products[0]->ID, true);
                    $deleted_products++;
                }
            }
            $results['data']['products_deleted'] = $deleted_products;
            
            // 3. 刪除測試用戶
            $test_users = array('test_user_01', 'test_user_02', 'test_user_03', 'test_user_04', 'test_user_05');
            $deleted_users = 0;
            foreach ($test_users as $username) {
                $user = get_user_by('login', $username);
                if ($user) {
                    wp_delete_user($user->ID);
                    $deleted_users++;
                }
            }
            $results['data']['users_deleted'] = $deleted_users;
            
            // 4. 清空資料庫表格（除了制度組）
            $tables = array(
                'QiMall_products',
                'QiMall_user_roles',
                'QiMall_rewards',
                'QiMall_transactions',
                'QiMall_referrals',
                'QiMall_points',
                'QiMall_distribution'
            );
            
            foreach ($tables as $table) {
                $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}{$table}");
            }
            
            $results['message'] = '✅ 測試資料清除成功！';
            
        } catch (Exception $e) {
            $results['success'] = false;
            $results['message'] = '❌ 錯誤：' . $e->getMessage();
        }
        
        return $results;
    }
}

