<?php
/**
 * 客戶入口/重導/外觀
 */
if (!defined('ABSPATH')) { exit; }

class QiMall_Client_Portal {
    public static function init() {
        // 客戶角色禁止進入 wp-admin
        add_action('admin_init', array(__CLASS__, 'redirect_client_from_admin'));
        // 隱藏管理列
        add_filter('show_admin_bar', array(__CLASS__, 'maybe_hide_admin_bar'));
        // 前台頁面重導（已登入不顯示登入頁、未登入不可進入 client-admin）
        add_action('template_redirect', array(__CLASS__, 'frontend_login_redirects'));
    }

    private static function is_client_role() {
        if (!is_user_logged_in()) return false;
        $user = wp_get_current_user();
        $roles = (array) $user->roles;
        return array_intersect($roles, array('QiMall_client_admin','QiMall_client_manager','QiMall_client_viewer'));
    }

    public static function redirect_client_from_admin() {
        if (!self::is_client_role()) return;
        // 允許編輯個人檔案與 admin-ajax
        $p = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
        if (false !== strpos($p, 'profile.php') || false !== strpos($p, 'admin-ajax.php')) return;
        // 重導到客戶後台
        wp_safe_redirect(home_url('/client-admin'));
        exit;
    }

    public static function maybe_hide_admin_bar($show) {
        if (self::is_client_role()) return false;
        return $show;
    }

    /**
     * 前台登入頁與客戶後台頁的前置重導，避免短代碼晚期輸出造成 headers already sent
     */
    public static function frontend_login_redirects() {
        if (is_page('client-login') && is_user_logged_in()) {
            wp_safe_redirect(home_url('/client-admin'));
            exit;
        }
        if (is_page('client-admin') && !is_user_logged_in()) {
            wp_safe_redirect(home_url('/client-login'));
            exit;
        }
    }
}

