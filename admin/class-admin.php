<?php
/**
 * 管理後台類別
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * 添加管理選單
     */
    public function add_admin_menu() {
        // 主選單
        add_menu_page(
            '消費股東系統',
            '消費股東',
            'manage_options',
            'coshare-system',
            array($this, 'dashboard_page'),
            'dashicons-groups',
            30
        );
        
        // 儀表板
        add_submenu_page(
            'coshare-system',
            '儀表板',
            '儀表板',
            'manage_options',
            'coshare-system',
            array($this, 'dashboard_page')
        );
        
        // 制度組管理
        add_submenu_page(
            'coshare-system',
            '制度組管理',
            '制度組管理',
            'manage_options',
            'coshare-groups',
            array($this, 'groups_page')
        );
        
        // 商品管理
        add_submenu_page(
            'coshare-system',
            '商品管理',
            '商品管理',
            'manage_options',
            'coshare-products',
            array($this, 'products_page')
        );
        
        // 會員管理
        add_submenu_page(
            'coshare-system',
            '會員管理',
            '會員管理',
            'manage_options',
            'coshare-members',
            array($this, 'members_page')
        );
        
        // 獎勵記錄
        add_submenu_page(
            'coshare-system',
            '獎勵記錄',
            '獎勵記錄',
            'manage_options',
            'coshare-rewards',
            array($this, 'rewards_page')
        );
        
        // 積分管理
        add_submenu_page(
            'coshare-system',
            '積分管理',
            '積分管理',
            'manage_options',
            'coshare-points',
            array($this, 'points_page')
        );
        
        // 系統設定
        add_submenu_page(
            'coshare-system',
            '系統設定',
            '系統設定',
            'manage_options',
            'coshare-settings',
            array($this, 'settings_page')
        );
        
        // 測試資料
        add_submenu_page(
            'coshare-system',
            '測試資料',
            '🧪 測試資料',
            'manage_options',
            'coshare-demo-data',
            array($this, 'demo_data_page')
        );
    }
    
    /**
     * 載入管理腳本和樣式
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'qimall') === false) {
            return;
        }
        
        wp_enqueue_style(
            'coshare-admin-css',
            QiMall_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            QiMall_VERSION
        );
        
        wp_enqueue_script(
            'coshare-admin-js',
            QiMall_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery'),
            QiMall_VERSION,
            true
        );
        
        wp_localize_script('coshare-admin-js', 'qimallAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('QiMall_admin_nonce')
        ));
    }
    
    /**
     * 註冊設定
     */
    public function register_settings() {
        register_setting('QiMall_settings', 'QiMall_distribution_settings');
        register_setting('QiMall_settings', 'QiMall_general_settings');
    }
    
    /**
     * 儀表板頁面
     */
    public function dashboard_page() {
        include QiMall_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * 制度組管理頁面
     */
    public function groups_page() {
        include QiMall_PLUGIN_DIR . 'admin/views/groups.php';
    }
    
    /**
     * 商品管理頁面
     */
    public function products_page() {
        include QiMall_PLUGIN_DIR . 'admin/views/products.php';
    }
    
    /**
     * 會員管理頁面
     */
    public function members_page() {
        include QiMall_PLUGIN_DIR . 'admin/views/members.php';
    }
    
    /**
     * 獎勵記錄頁面
     */
    public function rewards_page() {
        include QiMall_PLUGIN_DIR . 'admin/views/rewards.php';
    }
    
    /**
     * 積分管理頁面
     */
    public function points_page() {
        include QiMall_PLUGIN_DIR . 'admin/views/points.php';
    }
    
    /**
     * 系統設定頁面
     */
    public function settings_page() {
        include QiMall_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * 測試資料頁面
     */
    public function demo_data_page() {
        include QiMall_PLUGIN_DIR . 'admin/views/demo-data.php';
    }
}

// 初始化管理介面
new QiMall_Admin();

