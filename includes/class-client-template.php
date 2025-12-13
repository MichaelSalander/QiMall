<?php
if (!defined('ABSPATH')) { exit; }

/**
 * 客戶後台獨立模板：避免載入主題 header/footer 與前台導覽
 */
class QiMall_Client_Template {
    public static function init() {
        add_filter('template_include', array(__CLASS__, 'filter_template'), 20);
    }

    /**
     * 過濾模板：針對 client-login / client-admin 直接使用外掛提供的簡潔模板
     */
    public static function filter_template($template) {
        if (is_page('client-login') || is_page('client-admin')) {
            return QiMall_PLUGIN_DIR . 'public/templates/client-portal-template.php';
        }
        return $template;
    }
}

QiMall_Client_Template::init();

