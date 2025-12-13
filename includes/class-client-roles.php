<?php
/**
 * 客戶角色與測試帳號
 */
if (!defined('ABSPATH')) { exit; }

class QiMall_Client_Roles {
    public static function init() {
        add_action('init', array(__CLASS__, 'register_roles'));    
        add_action('init', array(__CLASS__, 'maybe_create_test_user'), 20);
    }

    public static function register_roles() {
        // 通用能力
        $base_caps = array(
            'read' => true,
            'QiMall_client_portal' => true,
            'QiMall_view_members' => true,
            'QiMall_view_rewards' => true,
            'QiMall_view_orders'  => true,
        );
        // 角色：檢視
        add_role('QiMall_client_viewer', 'CoShare 客戶-檢視', $base_caps);
        // 角色：管理
        $manager = $base_caps; $manager['QiMall_edit_client_settings'] = true;
        add_role('QiMall_client_manager', 'CoShare 客戶-管理', $manager);
        // 角色：客戶管理員（全部客戶功能）
        $admin = $manager; $admin['list_users'] = true; $admin['edit_users'] = true;
        add_role('QiMall_client_admin', 'CoShare 客戶-管理員', $admin);
    }

    public static function maybe_create_test_user() {
        // 僅執行一次
        if (get_option('QiMall_test_client_created')) return;
        $username = 'client_admin';
        $password = 'ClientAdmin!123';
        $email    = 'client.admin@example.com';
        if (!username_exists($username) && !email_exists($email)) {
            $user_id = wp_create_user($username, $password, $email);
            if (!is_wp_error($user_id)) {
                $user = new WP_User($user_id);
                $user->set_role('QiMall_client_admin');
                update_user_meta($user_id, 'first_name', 'Client');
                update_user_meta($user_id, 'last_name', 'Admin');
            }
        }
        update_option('QiMall_test_client_created', 1);
    }
}

