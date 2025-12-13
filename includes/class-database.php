<?php
/**
 * 資料庫管理類別
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Database {
    
    /**
     * 建立所有資料表
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // 建立制度組表
        self::create_groups_table($charset_collate);
        
        // 商品表已移除 - 改用 WooCommerce post_meta
        // 商品設定現在儲存在：
        // - _QiMall_enabled (yes/no)
        // - _QiMall_group_code (group_01, group_02, etc.)
        // - _QiMall_product_type (shareholder/partner)
        
        // 建立用戶角色表
        self::create_user_roles_table($charset_collate);
        
        // 建立獎勵記錄表
        self::create_rewards_table($charset_collate);
        
        // 建立交易記錄表
        self::create_transactions_table($charset_collate);
        
        // 建立推薦關係表
        self::create_referrals_table($charset_collate);
        
        // 建立積分帳戶表
        self::create_points_table($charset_collate);
        
        // 建立分銷關係表
        self::create_distribution_table($charset_collate);
    }
    
    /**
     * 建立制度組表
     */
    private static function create_groups_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_groups';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            group_code varchar(50) NOT NULL,
            group_name varchar(100) NOT NULL,
            shareholder_product_name varchar(100) NOT NULL,
            partner_product_name varchar(100) NOT NULL,
            shareholder_profit_rate decimal(5,2) DEFAULT 0.00 COMMENT '股東商城分潤比例%',
            shareholder_dividend_rate decimal(5,2) DEFAULT 0.00 COMMENT '股東團隊分紅比例%',
            shareholder_referral_count int(11) DEFAULT 0 COMMENT '股東推薦回本人數',
            partner_referral_count int(11) DEFAULT 3 COMMENT '合夥人推薦回本人數',
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY group_code (group_code)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * 建立商品表
     */
    private static function create_products_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_products';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id bigint(20) UNSIGNED NOT NULL COMMENT 'WooCommerce商品ID',
            group_code varchar(50) NOT NULL,
            plan_type varchar(20) NOT NULL COMMENT 'shareholder/partner/common',
            price decimal(10,2) NOT NULL DEFAULT 0.00,
            rebate_rules text COMMENT 'JSON格式的回本規則',
            profit_rate decimal(5,2) DEFAULT 0.00 COMMENT '分潤比例',
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY group_code (group_code),
            KEY plan_type (plan_type)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * 建立用戶角色表
     */
    private static function create_user_roles_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_user_roles';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            group_code varchar(50) NOT NULL,
            role_type varchar(20) NOT NULL COMMENT 'shareholder/partner',
            plan_id bigint(20) UNSIGNED NOT NULL COMMENT '對應商品ID',
            order_id bigint(20) UNSIGNED NOT NULL COMMENT '購買訂單ID',
            purchase_price decimal(10,2) NOT NULL DEFAULT 0.00,
            total_earned decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '已獲得總收益',
            referral_count int(11) NOT NULL DEFAULT 0 COMMENT '已推薦人數',
            status varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active/completed/cancelled',
            activated_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY group_code (group_code),
            KEY role_type (role_type),
            KEY status (status),
            UNIQUE KEY user_group_role (user_id, group_code, role_type)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * 建立獎勵記錄表
     */
    private static function create_rewards_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_rewards';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            group_code varchar(50) NOT NULL,
            role_type varchar(20) NOT NULL,
            reward_type varchar(50) NOT NULL COMMENT 'profit/dividend/referral/distribution',
            amount decimal(10,2) NOT NULL DEFAULT 0.00,
            source_order_id bigint(20) UNSIGNED DEFAULT NULL COMMENT '來源訂單ID',
            source_user_id bigint(20) UNSIGNED DEFAULT NULL COMMENT '來源用戶ID',
            calculation_data text COMMENT 'JSON格式的計算詳情',
            status varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending/calculated/paid',
            calculated_at datetime DEFAULT NULL,
            paid_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY group_code (group_code),
            KEY reward_type (reward_type),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * 建立交易記錄表
     */
    private static function create_transactions_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_transactions';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            transaction_type varchar(50) NOT NULL COMMENT 'purchase/reward/withdrawal',
            amount decimal(10,2) NOT NULL DEFAULT 0.00,
            balance_before decimal(10,2) NOT NULL DEFAULT 0.00,
            balance_after decimal(10,2) NOT NULL DEFAULT 0.00,
            related_id bigint(20) UNSIGNED DEFAULT NULL COMMENT '相關記錄ID',
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY transaction_type (transaction_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * 建立推薦關係表
     */
    private static function create_referrals_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_referrals';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            referrer_id bigint(20) UNSIGNED NOT NULL COMMENT '推薦人ID',
            referee_id bigint(20) UNSIGNED NOT NULL COMMENT '被推薦人ID',
            group_code varchar(50) NOT NULL,
            plan_type varchar(20) NOT NULL,
            order_id bigint(20) UNSIGNED NOT NULL,
            is_counted tinyint(1) DEFAULT 0 COMMENT '是否已計入回本',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY referrer_id (referrer_id),
            KEY referee_id (referee_id),
            KEY group_code (group_code),
            KEY is_counted (is_counted)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * 建立積分帳戶表
     */
    private static function create_points_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_points';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            total_points decimal(10,2) NOT NULL DEFAULT 0.00,
            available_points decimal(10,2) NOT NULL DEFAULT 0.00,
            pending_points decimal(10,2) NOT NULL DEFAULT 0.00,
            used_points decimal(10,2) NOT NULL DEFAULT 0.00,
            last_settlement_date datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * 建立分銷關係表
     */
    private static function create_distribution_table($charset_collate) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'QiMall_distribution';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            parent_id bigint(20) UNSIGNED DEFAULT NULL COMMENT '一級推薦人',
            grandparent_id bigint(20) UNSIGNED DEFAULT NULL COMMENT '二級推薦人',
            level_1_rate decimal(5,2) DEFAULT 0.00 COMMENT '一級分銷比例',
            level_2_rate decimal(5,2) DEFAULT 0.00 COMMENT '二級分銷比例',
            total_level_1_earnings decimal(10,2) DEFAULT 0.00,
            total_level_2_earnings decimal(10,2) DEFAULT 0.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id),
            KEY parent_id (parent_id),
            KEY grandparent_id (grandparent_id)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * 刪除所有資料表
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'QiMall_groups',
            $wpdb->prefix . 'QiMall_products',
            $wpdb->prefix . 'QiMall_user_roles',
            $wpdb->prefix . 'QiMall_rewards',
            $wpdb->prefix . 'QiMall_transactions',
            $wpdb->prefix . 'QiMall_referrals',
            $wpdb->prefix . 'QiMall_points',
            $wpdb->prefix . 'QiMall_distribution'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
}

