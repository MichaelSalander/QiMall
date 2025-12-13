<?php
/**
 * Plugin Name: Co-Share System (瘨祥?⊥蝟餌絞)
 * Plugin URI: https://example.com/QiMall
 * Description: 憭摨衣?瘨祥?⊥??憭乩犖蝞∠?蝟餌絞嚗?蝡?????血??研???蝝?鈭??
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: QiMall
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// ?脫迫?湔閮芸?
if (!defined('ABSPATH')) {
    exit;
}

// 摰儔憭?撣豢
define('QiMall_VERSION', '1.0.0');
define('QiMall_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('QiMall_PLUGIN_URL', plugin_dir_url(__FILE__));
define('QiMall_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * 銝餉?憭?憿
 */
class QiMall {
    
    /**
     * ?桐?撖虫?
     */
    private static $instance = null;
    
    /**
     * ?脣??桐?撖虫?
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 撱箸??賣
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * 頛靘陷瑼?
     */
    private function load_dependencies() {
        // 頛鞈?摨恍???
        require_once QiMall_PLUGIN_DIR . 'includes/class-database.php';
        
        // 頛?詨?憿
        require_once QiMall_PLUGIN_DIR . 'includes/class-user-role.php';
        require_once QiMall_PLUGIN_DIR . 'includes/class-product.php';
        require_once QiMall_PLUGIN_DIR . 'includes/class-reward.php';
        require_once QiMall_PLUGIN_DIR . 'includes/class-transaction.php';
        require_once QiMall_PLUGIN_DIR . 'includes/class-group.php';
        require_once QiMall_PLUGIN_DIR . 'includes/class-demo-data.php';
        require_once QiMall_PLUGIN_DIR . 'includes/class-referral.php';
        
        // 頛閮?撘?
        require_once QiMall_PLUGIN_DIR . 'includes/class-reward-calculator.php';
        require_once QiMall_PLUGIN_DIR . 'includes/class-distribution-calculator.php';
        
        // 頛蝞∠?隞
        if (is_admin()) {
            require_once QiMall_PLUGIN_DIR . 'admin/class-admin.php';
        }
        
        // 頛?隞
        require_once QiMall_PLUGIN_DIR . 'public/class-public.php';
        
        // 摰Ｘ?亙???莎?摰Ｚˊ???堆?
        require_once QiMall_PLUGIN_DIR . 'includes/class-client-roles.php';
        require_once QiMall_PLUGIN_DIR . 'includes/class-client-portal.php';
        // 摰Ｘ REST API 蝡舫?
        require_once QiMall_PLUGIN_DIR . 'includes/class-client-rest.php';
        // 摰Ｘ?函?璅⊥
        require_once QiMall_PLUGIN_DIR . 'includes/class-client-template.php';
        
        // 頛摰?隞餃?
        require_once QiMall_PLUGIN_DIR . 'includes/class-cron.php';
        
        // 頛AJAX????
        require_once QiMall_PLUGIN_DIR . 'includes/ajax-handlers.php';
    }
    
    /**
     * ???摮?
     */
    private function init_hooks() {
        // 憭???文?
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // 憭???文?
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // 憭??貉??文?
        register_uninstall_hook(__FILE__, array('QiMall', 'uninstall'));
        
        // 頛???
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // ????雿?
        add_action('init', array($this, 'init'));
    }
    
    /**
     * 憭??
     */
    public function activate() {
        // 撱箇?鞈?摨怨”??
        QiMall_Database::create_tables();
        
        // 撱箇??身?嗅漲蝯?
        $this->create_default_groups();
        
        // 撱箇???
        $this->create_frontend_pages();
        
        // 閮剖?摰?隞餃?
        QiMall_Cron::schedule_events();
        
        // 皜?神閬?
        flush_rewrite_rules();
        
        // 閮????
        update_option('QiMall_activated_time', current_time('mysql'));
        update_option('QiMall_version', QiMall_VERSION);
    }
    
    /**
     * 憭??
     */
    public function deactivate() {
        // 皜摰?隞餃?
        QiMall_Cron::clear_scheduled_events();
        
        // 皜?神閬?
        flush_rewrite_rules();
    }
    
    /**
     * 憭??貉?
     */
    public static function uninstall() {
        // ?芷鞈?摨怨”?潘??舫嚗?
        // QiMall_Database::drop_tables();
        
        // ?芷?賊?
        delete_option('QiMall_activated_time');
        delete_option('QiMall_version');
        delete_option('QiMall_settings');
        delete_option('QiMall_pages_created');
        delete_option('QiMall_page_ids');
        
        // ?舫嚗?方?遣蝡??
        // $page_ids = get_option('QiMall_page_ids', array());
        // foreach ($page_ids as $page_id) {
        //     wp_delete_post($page_id, true);
        // }
    }
    
    /**
     * 頛???
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'QiMall',
            false,
            dirname(QiMall_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * ????
     */
    public function init() {
        // ????衣頂蝯?
        if (class_exists('QiMall_Referral')) {
            QiMall_Referral::init();
        }
        
        // 閮餃?摰Ｘ閫?葫閰血董???芸銵?甈∴?
        if (class_exists('QiMall_Client_Roles')) {
            QiMall_Client_Roles::init();
        }
        
        // ?摰Ｘ?亙?摩嚗?撠??admin bar 蝑?
        if (class_exists('QiMall_Client_Portal')) {
            QiMall_Client_Portal::init();
        }
        
        // 閮餃??芸?蝢拇?蝡???憿?
        do_action('QiMall_init');

        // 蝣箔?敹???摮嚗???撱箇??◤?芷嚗?
        $this->maybe_ensure_pages();
    }
    
    /**
     * 撱箇??身?嗅漲蝯?
     */
    private function create_default_groups() {
        $groups = array(
            array(
                'group_code' => 'group_01',
                'group_name' => '?嗅漲蝯?',
                'shareholder_product_name' => '瘨祥?⊥A',
                'partner_product_name' => '瘨祥?丰鈭慷',
                'is_active' => 1
            ),
            array(
                'group_code' => 'group_02',
                'group_name' => '?嗅漲蝯',
                'shareholder_product_name' => '瘨祥?⊥C',
                'partner_product_name' => '瘨祥?丰鈭慣',
                'is_active' => 1
            )
        );
        
        foreach ($groups as $group) {
            QiMall_Group::create($group);
        }
    }
    
    /**
     * 撱箇???
     */
    private function create_frontend_pages() {
        // 銝??湔?拚嚗?閮梯?撱箇撩憭梢???
        $pages = array(
            array(
                'title' => '?銝剖?',
                'slug' => 'qimall-member-center',
                'content' => '[QiMall_member_dashboard]'
            ),
            array(
                'title' => '???嗥?',
                'slug' => 'qimall-earnings',
                'content' => '[QiMall_earnings]'
            ),
            array(
                'title' => '?刻憟賢?',
                'slug' => 'qimall-referral',
                'content' => '[QiMall_referral_link]'
            ),
            // 摰Ｘ撠?亙????wp-admin嚗?
            array(
                'title' => 'Client Login',
                'slug' => 'client-login',
                'content' => '[QiMall_client_login]'
            ),
            array(
                'title' => 'Client Admin',
                'slug' => 'client-admin',
                'content' => '[QiMall_client_admin]'
            )
        );
        
        $created_page_ids = array();
        
        foreach ($pages as $page_data) {
            // 瑼Ｘ??臬撌脣???
            $page_check = get_page_by_path($page_data['slug']);
            
            if (!$page_check) {
                // 撱箇??
                $page_id = wp_insert_post(array(
                    'post_title' => $page_data['title'],
                    'post_name' => $page_data['slug'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_author' => 1,
                    'comment_status' => 'closed',
                    'ping_status' => 'closed'
                ));
                
                if ($page_id) {
                    $created_page_ids[$page_data['slug']] = $page_id;
                }
            } else {
                $created_page_ids[$page_data['slug']] = $page_check->ID;
            }
        }
        
        // ?脣?撱箇?????ID
        update_option('QiMall_page_ids', $created_page_ids);
        update_option('QiMall_pages_created', true); // 璅?撌脣?閰血遣蝡?
    }

    /**
     * 瘥活 init ?炎?仿??菟??Ｘ?血??剁?蝻箏仃??撱?
     */
    private function maybe_ensure_pages() {
        $required_slugs = array(
            'client-login' => '[QiMall_client_login]',
            'client-admin' => '[QiMall_client_admin]'
        );
        $updated = false;
        $page_ids = get_option('QiMall_page_ids', array());
        foreach ($required_slugs as $slug => $shortcode) {
            $existing = get_page_by_path($slug);
            if (!$existing) {
                $page_id = wp_insert_post(array(
                    'post_title' => ucwords(str_replace('-', ' ', $slug)),
                    'post_name' => $slug,
                    'post_content' => $shortcode,
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_author' => 1,
                    'comment_status' => 'closed',
                    'ping_status' => 'closed'
                ));
                if ($page_id) {
                    $page_ids[$slug] = $page_id;
                    $updated = true;
                }
            } else {
                $page_ids[$slug] = $existing->ID;
            }
        }
        if ($updated) {
            update_option('QiMall_page_ids', $page_ids);
        }
    }
}

/**
 * ?脣?憭?銝餃祕靘?
 */
function qimall() {
    return QiMall::get_instance();
}

// ??憭?
qimall();

