<?php
if (!defined('ABSPATH')) { exit; }

/**
 * 客戶入口相關 REST API 端點
 */
class QiMall_Client_REST {
    /**
     * 初始化：註冊路由
     */
    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
    }

    /**
     * 註冊路由
     */
    public static function register_routes() {
        register_rest_route('qimall/v1', '/stats', array(
            'methods'  => 'GET',
            'callback' => array(__CLASS__, 'get_stats'),
            'permission_callback' => array(__CLASS__, 'can_access_client_portal'),
        ));
        register_rest_route('qimall/v1', '/members', array(
            'methods'  => 'GET',
            'callback' => array(__CLASS__, 'get_members'),
            'permission_callback' => array(__CLASS__, 'can_access_client_portal'),
            'args' => array(
                'page' => array('default' => 1),
                'per_page' => array('default' => 20),
                'search' => array('default' => ''),
                'role' => array('default' => ''),
                'orderby' => array('default' => 'registered'),
                'order' => array('default' => 'DESC'),
                'format' => array('default' => 'json'),
            )
        ));
        register_rest_route('qimall/v1', '/rewards', array(
            'methods'  => 'GET',
            'callback' => array(__CLASS__, 'get_rewards'),
            'permission_callback' => array(__CLASS__, 'can_access_client_portal'),
            'args' => array(
                'page' => array('default' => 1),
                'per_page' => array('default' => 20),
                'status' => array('default' => ''),
                'reward_type' => array('default' => ''),
                'orderby' => array('default' => 'created_at'),
                'order' => array('default' => 'DESC'),
            )
        ));
        register_rest_route('qimall/v1', '/orders', array(
            'methods'  => 'GET',
            'callback' => array(__CLASS__, 'get_orders'),
            'permission_callback' => array(__CLASS__, 'can_access_client_portal'),
            'args' => array(
                'page' => array('default' => 1),
                'per_page' => array('default' => 20),
                'search' => array('default' => ''),
                'status' => array('default' => ''),
                'orderby' => array('default' => 'date'),
                'order' => array('default' => 'DESC'),
            )
        ));
        register_rest_route('qimall/v1', '/settings', array(
            array(
                'methods'  => 'GET',
                'callback' => array(__CLASS__, 'get_settings'),
                'permission_callback' => array(__CLASS__, 'can_access_client_portal'),
            ),
            array(
                'methods'  => 'POST',
                'callback' => array(__CLASS__, 'update_settings'),
                'permission_callback' => array(__CLASS__, 'can_edit_settings'),
                'args' => array(
                    'portal_title' => array('required' => false),
                    'primary_color' => array('required' => false),
                    'logo_url' => array('required' => false),
                    'business' => array('required' => false),
                )
            )
        ));
        // Groups
        register_rest_route('qimall/v1', '/groups', array(
            array(
                'methods'  => 'GET',
                'callback' => array(__CLASS__, 'get_groups'),
                'permission_callback' => array(__CLASS__, 'can_access_client_portal'),
            ),
            array(
                'methods'  => 'POST',
                'callback' => array(__CLASS__, 'create_group'),
                'permission_callback' => array(__CLASS__, 'can_edit_settings'),
            ),
            array(
                'methods'  => 'PUT',
                'callback' => array(__CLASS__, 'update_group'),
                'permission_callback' => array(__CLASS__, 'can_edit_settings'),
            ),
            array(
                'methods'  => 'DELETE',
                'callback' => array(__CLASS__, 'delete_group'),
                'permission_callback' => array(__CLASS__, 'can_edit_settings'),
                'args' => array(
                    'group_code' => array('required' => true),
                )
            )
        ));
        // Products
        register_rest_route('qimall/v1', '/products', array(
            'methods'  => 'GET',
            'callback' => array(__CLASS__, 'get_products'),
            'permission_callback' => array(__CLASS__, 'can_access_client_portal'),
            'args' => array(
                'page' => array('default' => 1),
                'per_page' => array('default' => 20),
                'search' => array('default' => ''),
                'group_code' => array('default' => ''),
                'QiMall_enabled' => array('default' => ''),
            )
        ));
        // Product meta updates (bind qimall fields)
        register_rest_route('qimall/v1', '/products/meta', array(
            'methods'  => 'POST',
            'callback' => array(__CLASS__, 'update_product_meta'),
            'permission_callback' => array(__CLASS__, 'can_edit_settings'),
            'args' => array(
                'product_id' => array('required' => true),
                'QiMall_enabled' => array('required' => false),
                'group_code' => array('required' => false),
                'product_type' => array('required' => false),
                'distribution_enabled' => array('required' => false),
            )
        ));
    }

    /**
     * 權限檢查：必須具備任一客戶角色 capability
     */
    public static function can_access_client_portal($request) {
        if (!is_user_logged_in()) {
            return new WP_Error('not_logged_in', __('未登入', 'QiMall'), array('status' => 401));
        }
        $user = wp_get_current_user();
        // 允許具管理權限者（WP 管理員）也可進入，方便站主測試
        $is_admin = in_array('administrator', (array)$user->roles, true) || current_user_can('manage_options');
        // 使用 capability 以便後續更精細控管
        if (!$is_admin && !user_can($user, 'QiMall_client_portal')) {
            return new WP_Error('no_permission', __('無客戶入口權限', 'QiMall'), array('status' => 403));
        }
        if (!self::rate_limit_ok($user->ID)) {
            return new WP_Error('rate_limited', __('請求過於頻繁，稍後再試', 'QiMall'), array('status' => 429));
        }
        return true;
    }

    /** 編輯設定權限檢查 */
    public static function can_edit_settings($request) {
        if (!is_user_logged_in()) {
            return new WP_Error('not_logged_in', __('未登入', 'QiMall'), array('status' => 401));
        }
        $user = wp_get_current_user();
        $is_admin = in_array('administrator', (array)$user->roles, true) || current_user_can('manage_options');
        if (!$is_admin && !user_can($user, 'QiMall_edit_client_settings')) {
            return new WP_Error('no_permission', __('無設定編輯權限', 'QiMall'), array('status' => 403));
        }
        if (!self::rate_limit_ok($user->ID)) {
            return new WP_Error('rate_limited', __('請求過於頻繁，稍後再試', 'QiMall'), array('status' => 429));
        }
        return true;
    }

    /**
     * 回傳統計資料
     */
    public static function get_stats($request) {
        global $wpdb;

        $roles = array('QiMall_client_admin','QiMall_client_manager','QiMall_client_viewer');
        $counts = array(
            'total_clients' => 0,
            'total_client_admins' => 0,
            'total_client_managers' => 0,
            'total_client_viewers' => 0,
            'total_orders' => 0,
            'recent_orders' => array(),
        );

        foreach ($roles as $role) {
            $args = array('role' => $role, 'fields' => 'ID', 'number' => -1);
            $users = get_users($args);
            $count = is_array($users) ? count($users) : 0;
            switch ($role) {
                case 'QiMall_client_admin': $counts['total_client_admins'] = $count; break;
                case 'QiMall_client_manager': $counts['total_client_managers'] = $count; break;
                case 'QiMall_client_viewer': $counts['total_client_viewers'] = $count; break;
            }
            $counts['total_clients'] += $count;
        }

        // WooCommerce 訂單統計（若已啟用）
        if (class_exists('WC_Order')) {
            $order_query = new WP_Query(array(
                'post_type' => 'shop_order',
                'post_status' => array('wc-processing','wc-completed','wc-on-hold','wc-pending','wc-refunded','wc-cancelled'),
                'posts_per_page' => 5,
                'orderby' => 'date',
                'order' => 'DESC',
                'fields' => 'ids'
            ));
            $counts['recent_orders'] = $order_query->posts;

            // 總訂單數（不限制數量）
            $total_orders = new WP_Query(array(
                'post_type' => 'shop_order',
                'post_status' => array('wc-processing','wc-completed','wc-on-hold','wc-pending','wc-refunded','wc-cancelled'),
                'posts_per_page' => 1,
                'fields' => 'ids'
            ));
            $counts['total_orders'] = $total_orders->found_posts;
        }

        // TODO: 已發放 / 待發放收益：後續串接獎勵計算結果
        $counts['paid_rewards'] = 0;
        $counts['pending_rewards'] = 0;

        return rest_ensure_response(array(
            'success' => true,
            'data' => $counts,
        ));
    }

    /**
     * 取得會員列表（分頁 + 搜尋 + 角色過濾）
     */
    public static function get_members($request) {
        $page = max(1, intval($request->get_param('page')));
        $per_page = intval($request->get_param('per_page'));
        if ($per_page < 1) { $per_page = 20; }
        if ($per_page > 100) { $per_page = 100; }
        $search = sanitize_text_field($request->get_param('search'));
        $role   = sanitize_text_field($request->get_param('role'));
        $orderby = sanitize_text_field($request->get_param('orderby'));
        $order = strtoupper(sanitize_text_field($request->get_param('order')));
        if (!in_array($order, array('ASC','DESC'), true)) { $order = 'DESC'; }
        $format = sanitize_text_field($request->get_param('format'));

        $allowed_orderby = array('id' => 'ID', 'registered' => 'registered', 'email' => 'email', 'login' => 'login');
        $orderby_mapped = isset($allowed_orderby[$orderby]) ? $allowed_orderby[$orderby] : 'registered';

        $query_args = array(
            'number' => $per_page,
            'paged' => $page,
            'orderby' => $orderby_mapped,
            'order' => $order,
        );

        if ($search) {
            $query_args['search'] = '*' . $search . '*';
            $query_args['search_columns'] = array('user_login','user_email','user_nicename');
        }
        if ($role) {
            $query_args['role'] = $role; // 例如 QiMall_client_viewer
        }

        $user_query = new WP_User_Query($query_args);
        $found = intval($user_query->get_total());
        $items = array();
        foreach ($user_query->get_results() as $u) {
            $items[] = self::format_member_item($u);
        }

        $total_pages = $per_page > 0 ? ceil($found / $per_page) : 1;
        if ($format === 'csv') {
            $csv_rows = array();
            $csv_rows[] = 'ID,Login,Display Name,Email,Roles,Registered,Referrer';
            foreach ($items as $it) {
                $csv_rows[] = sprintf('%d,%s,%s,%s,%s,%s,%s',
                    $it['id'],
                    self::csv_escape($it['login']),
                    self::csv_escape($it['display_name']),
                    self::csv_escape($it['email']),
                    self::csv_escape(implode('|', $it['roles'])),
                    self::csv_escape($it['registered']),
                    self::csv_escape($it['referrer_name'])
                );
            }
            $csv = implode("\n", $csv_rows) . "\n";
            $response = new WP_REST_Response($csv, 200);
            $response->set_headers(array(
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename=members.csv'
            ));
            return $response;
        }
        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'items' => $items,
                'total' => $found,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => $total_pages,
            )
        ));
    }

    /**
     * 格式化單一會員資料
     */
    private static function format_member_item($user) {
        $referrer_id = get_user_meta($user->ID, 'QiMall_referrer', true);
        $referrer_display = '';
        if ($referrer_id) {
            $ref_u = get_user_by('id', $referrer_id);
            if ($ref_u) {
                $referrer_display = $ref_u->display_name ?: $ref_u->user_login;
            }
        }
        return array(
            'id' => $user->ID,
            'login' => $user->user_login,
            'display_name' => $user->display_name,
            'email' => $user->user_email,
            'roles' => $user->roles,
            'registered' => $user->user_registered,
            'referrer_id' => $referrer_id ? intval($referrer_id) : null,
            'referrer_name' => $referrer_display,
        );
    }

    /** 取得獎勵列表 */
    public static function get_rewards($request) {
        global $wpdb;
        $page = max(1, intval($request->get_param('page')));
        $per_page = intval($request->get_param('per_page'));
        if ($per_page < 1) { $per_page = 20; }
        if ($per_page > 100) { $per_page = 100; }
        $status = sanitize_text_field($request->get_param('status'));
        $reward_type = sanitize_text_field($request->get_param('reward_type'));
        $orderby = sanitize_text_field($request->get_param('orderby'));
        $order = strtoupper(sanitize_text_field($request->get_param('order')));
        if (!in_array($order, array('ASC','DESC'), true)) { $order = 'DESC'; }
        $allowed_orderby = array('created_at','amount');
        if (!in_array($orderby, $allowed_orderby, true)) { $orderby = 'created_at'; }
        $table = $wpdb->prefix . 'QiMall_rewards';
        $where = 'WHERE 1=1';
        $params = array();
        if ($status) { $where .= ' AND status = %s'; $params[] = $status; }
        if ($reward_type) { $where .= ' AND reward_type = %s'; $params[] = $reward_type; }
        $offset = ($page - 1) * $per_page;
        $sql = $wpdb->prepare("SELECT * FROM $table $where ORDER BY $orderby $order LIMIT %d OFFSET %d", array_merge($params, array($per_page, $offset)));
        $rows = $wpdb->get_results($sql);
        $count_sql = $wpdb->prepare("SELECT COUNT(*) FROM $table $where", $params);
        $total = intval($wpdb->get_var($count_sql));
        foreach ($rows as $r) { if ($r->calculation_data) { $r->calculation_data = json_decode($r->calculation_data, true); } }
        // 合計
        $sum_sql = $wpdb->prepare("SELECT SUM(amount) FROM $table $where", $params);
        $total_amount = floatval($wpdb->get_var($sum_sql));
        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'items' => $rows,
                'total' => $total,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => $per_page ? ceil($total / $per_page) : 1,
                'total_amount' => $total_amount,
            )
        ));
    }

    /** 取得訂單列表（精簡） */
    public static function get_orders($request) {
        $page = max(1, intval($request->get_param('page')));
        $per_page = intval($request->get_param('per_page'));
        if ($per_page < 1) { $per_page = 20; }
        if ($per_page > 100) { $per_page = 100; }
        $search = sanitize_text_field($request->get_param('search'));
        $status = sanitize_text_field($request->get_param('status'));
        $orderby = sanitize_text_field($request->get_param('orderby'));
        $order = strtoupper(sanitize_text_field($request->get_param('order')));
        if (!in_array($order, array('ASC','DESC'), true)) { $order = 'DESC'; }
        $allowed_orderby = array('date','id','total');
        if (!in_array($orderby, $allowed_orderby, true)) { $orderby = 'date'; }
        if (!class_exists('WC_Order')) {
            return rest_ensure_response(array('success' => true, 'data' => array('items' => array(), 'total' => 0)));
        }
        $query_args = array(
            'post_type' => 'shop_order',
            'post_status' => array('wc-processing','wc-completed','wc-on-hold','wc-pending','wc-refunded','wc-cancelled'),
            'paged' => $page,
            'posts_per_page' => $per_page,
        );
        if ($search) { $query_args['s'] = $search; }
        if ($status) { $query_args['post_status'] = array($status); }
        switch ($orderby) {
            case 'id': $query_args['orderby'] = 'ID'; break;
            case 'total': $query_args['meta_key'] = '_order_total'; $query_args['orderby'] = 'meta_value_num'; break;
            default: $query_args['orderby'] = 'date';
        }
        $query_args['order'] = $order;
        $q = new WP_Query($query_args);
        $items = array();
        foreach ($q->posts as $p) {
            $order_obj = wc_get_order($p->ID);
            if (!$order_obj) continue;
            $items[] = array(
                'id' => $p->ID,
                'date' => $order_obj->get_date_created() ? $order_obj->get_date_created()->date_i18n('Y-m-d H:i:s') : '',
                'status' => $order_obj->get_status(),
                'total' => $order_obj->get_total(),
                'currency' => $order_obj->get_currency(),
                'customer_id' => $order_obj->get_user_id(),
                'customer_name' => $order_obj->get_billing_first_name() . ' ' . $order_obj->get_billing_last_name(),
            );
        }
        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'items' => $items,
                'total' => intval($q->found_posts),
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => $per_page ? ceil($q->found_posts / $per_page) : 1,
            )
        ));
    }

    /** 取得品牌設定 */
    public static function get_settings($request) {
        $branding = get_option('QiMall_branding', array(
            'portal_title' => 'QiMall管理系統',
            'primary_color' => '#4f46e5',
            'logo_url' => ''
        ));
        $business = get_option('QiMall_settings', self::default_business_settings());
        // 強制轉換舊標題字樣為 QiMall 管理系統
        $portal = isset($branding['portal_title']) ? $branding['portal_title'] : 'QiMall管理系統';
        if ($portal === '' || $portal === 'Co-Share 客戶中心') { $portal = 'QiMall管理系統'; }
        return rest_ensure_response(array('success' => true, 'data' => array(
            'portal_title' => $portal,
            'primary_color' => isset($branding['primary_color'])?$branding['primary_color']:'#4f46e5',
            'logo_url' => isset($branding['logo_url'])?$branding['logo_url']:'',
            'business' => $business,
        )));
    }

    /** 更新品牌設定 */
    public static function update_settings($request) {
        $portal_title = sanitize_text_field($request->get_param('portal_title'));
        $primary_color = sanitize_hex_color($request->get_param('primary_color'));
        $logo_url = esc_url_raw($request->get_param('logo_url'));
        $branding = get_option('QiMall_branding', array());
        if ($portal_title !== null && $portal_title !== '') { $branding['portal_title'] = $portal_title; }
        if ($primary_color) { $branding['primary_color'] = $primary_color; }
        if ($logo_url !== null) { $branding['logo_url'] = $logo_url; }
        update_option('QiMall_branding', $branding);

        // Business settings
        $incoming_business = $request->get_param('business');
        $business = get_option('QiMall_settings', self::default_business_settings());
        if (is_array($incoming_business)) {
            $business = self::sanitize_business_settings($incoming_business, $business);
            update_option('QiMall_settings', $business);
        }
        self::audit('update_settings', array('user' => get_current_user_id()));
        // 回傳時同樣套用標題校正
        $portal = isset($branding['portal_title']) ? $branding['portal_title'] : 'QiMall管理系統';
        if ($portal === '' || $portal === 'Co-Share 客戶中心') { $portal = 'QiMall管理系統'; }
        return rest_ensure_response(array('success' => true, 'data' => array(
            'portal_title' => $portal,
            'primary_color' => isset($branding['primary_color'])?$branding['primary_color']:'#4f46e5',
            'logo_url' => isset($branding['logo_url'])?$branding['logo_url']:'',
            'business' => get_option('QiMall_settings', self::default_business_settings()),
        )));
    }

    /** 預設業務設定 */
    private static function default_business_settings() {
        return array(
            'referral' => array(
                'cookie_days' => 30,
                'default_referrer_user_id' => 0,
                'allow_self_referral' => false,
            ),
            't3r1' => array(
                'enabled' => false,
                'required_count' => 3,
                'refund_type' => 'percentage', // percentage|fixed
                'refund_value' => 100,
                'time_limit_days' => 0,
            ),
            'rewards' => array(
                'shareholder_rate' => 20,
                'partner_rate' => 10,
                'rounding_mode' => 'round', // floor|ceil|round
                'rounding_decimals' => 2,
            ),
            'distribution' => array(
                'enabled' => false,
                'levels' => 2,
                'level1_rate' => 5,
                'level2_rate' => 2,
            ),
            'orders' => array(
                'eligible_statuses' => array('wc-processing','wc-completed'),
                'clawback_on_refund' => true,
                'refund_statuses' => array('wc-refunded','wc-cancelled'),
            ),
            'payout' => array(
                'payout_cycle' => 'manual', // manual|daily|weekly|monthly
                'minimum_payout' => 100,
            ),
        );
    }

    /** 清洗/合併業務設定 */
    private static function sanitize_business_settings($in, $base) {
        $d = self::default_business_settings();
        $out = is_array($base) ? $base : $d;
        // referral
        if (isset($in['referral']) && is_array($in['referral'])) {
            $r = $in['referral'];
            if (isset($r['cookie_days'])) $out['referral']['cookie_days'] = max(0, intval($r['cookie_days']));
            if (isset($r['default_referrer_user_id'])) $out['referral']['default_referrer_user_id'] = max(0, intval($r['default_referrer_user_id']));
            if (isset($r['allow_self_referral'])) $out['referral']['allow_self_referral'] = (bool)$r['allow_self_referral'];
        }
        // t3r1
        if (isset($in['t3r1']) && is_array($in['t3r1'])) {
            $t = $in['t3r1'];
            if (isset($t['enabled'])) $out['t3r1']['enabled'] = (bool)$t['enabled'];
            if (isset($t['required_count'])) $out['t3r1']['required_count'] = max(1, intval($t['required_count']));
            if (isset($t['refund_type'])) {
                $type = in_array($t['refund_type'], array('percentage','fixed'), true) ? $t['refund_type'] : 'percentage';
                $out['t3r1']['refund_type'] = $type;
            }
            if (isset($t['refund_value'])) $out['t3r1']['refund_value'] = floatval($t['refund_value']);
            if (isset($t['time_limit_days'])) $out['t3r1']['time_limit_days'] = max(0, intval($t['time_limit_days']));
        }
        // rewards
        if (isset($in['rewards']) && is_array($in['rewards'])) {
            $rw = $in['rewards'];
            if (isset($rw['shareholder_rate'])) $out['rewards']['shareholder_rate'] = floatval($rw['shareholder_rate']);
            if (isset($rw['partner_rate'])) $out['rewards']['partner_rate'] = floatval($rw['partner_rate']);
            if (isset($rw['rounding_mode'])) {
                $rm = in_array($rw['rounding_mode'], array('floor','ceil','round'), true) ? $rw['rounding_mode'] : 'round';
                $out['rewards']['rounding_mode'] = $rm;
            }
            if (isset($rw['rounding_decimals'])) $out['rewards']['rounding_decimals'] = max(0, intval($rw['rounding_decimals']));
        }
        // distribution
        if (isset($in['distribution']) && is_array($in['distribution'])) {
            $ds = $in['distribution'];
            if (isset($ds['enabled'])) $out['distribution']['enabled'] = (bool)$ds['enabled'];
            if (isset($ds['levels'])) $out['distribution']['levels'] = max(1, min(2, intval($ds['levels'])));
            if (isset($ds['level1_rate'])) $out['distribution']['level1_rate'] = floatval($ds['level1_rate']);
            if (isset($ds['level2_rate'])) $out['distribution']['level2_rate'] = floatval($ds['level2_rate']);
        }
        // orders
        if (isset($in['orders']) && is_array($in['orders'])) {
            $od = $in['orders'];
            if (isset($od['eligible_statuses']) && is_array($od['eligible_statuses'])) {
                $clean = array(); foreach ($od['eligible_statuses'] as $s) { $s = sanitize_text_field($s); if ($s !== '') $clean[] = $s; }
                $out['orders']['eligible_statuses'] = array_values(array_unique($clean));
            }
            if (isset($od['clawback_on_refund'])) $out['orders']['clawback_on_refund'] = (bool)$od['clawback_on_refund'];
            if (isset($od['refund_statuses']) && is_array($od['refund_statuses'])) {
                $clean = array(); foreach ($od['refund_statuses'] as $s) { $s = sanitize_text_field($s); if ($s !== '') $clean[] = $s; }
                $out['orders']['refund_statuses'] = array_values(array_unique($clean));
            }
        }
        // payout
        if (isset($in['payout']) && is_array($in['payout'])) {
            $po = $in['payout'];
            if (isset($po['payout_cycle'])) {
                $pc = in_array($po['payout_cycle'], array('manual','daily','weekly','monthly'), true) ? $po['payout_cycle'] : 'manual';
                $out['payout']['payout_cycle'] = $pc;
            }
            if (isset($po['minimum_payout'])) $out['payout']['minimum_payout'] = max(0, floatval($po['minimum_payout']));
        }
        return $out;
    }

    /** 簡易速率限制：每使用者每分鐘最多 120 次 */
    private static function rate_limit_ok($user_id) {
        $key = 'QiMall_rl_' . $user_id;
        $data = get_transient($key);
        $now = time();
        if (!$data || !isset($data['t']) || $now - $data['t'] > 60) {
            set_transient($key, array('t' => $now, 'c' => 1), 120);
            return true;
        }
        if ($data['c'] >= 120) {
            return false;
        }
        $data['c']++;
        set_transient($key, $data, 120);
        return true;
    }

    /** 簡易審計：存入最近 50 筆於 option */
    private static function audit($action, $extra = array()) {
        $log = get_option('QiMall_audit_log', array());
        $log[] = array(
            'time' => current_time('mysql'),
            'action' => $action,
            'user_id' => isset($extra['user']) ? intval($extra['user']) : 0,
        );
        if (count($log) > 50) { $log = array_slice($log, -50); }
        update_option('QiMall_audit_log', $log);
    }

    /** CSV escape */
    private static function csv_escape($value) {
        $value = (string) $value;
        if (preg_match('/[",\n]/', $value)) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }

    /** 取得制度組列表 */
    public static function get_groups($request) {
        if (!class_exists('QiMall_Group')) {
            return rest_ensure_response(array('success' => true, 'data' => array()));
        }
        // 使用現有類別的方法名稱 `get_all`
        $groups = QiMall_Group::get_all(true);
        return rest_ensure_response(array('success' => true, 'data' => $groups));
    }

    /** 新增制度組 */
    public static function create_group($request) {
        $data = array(
            'group_code' => sanitize_text_field($request->get_param('group_code')),
            'group_name' => sanitize_text_field($request->get_param('group_name')),
            'shareholder_product_name' => sanitize_text_field($request->get_param('shareholder_product_name')),
            'partner_product_name' => sanitize_text_field($request->get_param('partner_product_name')),
            'is_active' => intval($request->get_param('is_active')) ? 1 : 0,
        );
        if (empty($data['group_code']) || empty($data['group_name'])) {
            return new WP_Error('invalid_group', __('缺少必要欄位', 'QiMall'), array('status' => 400));
        }
        $res = QiMall_Group::create($data);
        self::audit('create_group', array('user' => get_current_user_id()));
        return rest_ensure_response(array('success' => (bool)$res, 'data' => $data));
    }

    /** 更新制度組 */
    public static function update_group($request) {
        $group_code = sanitize_text_field($request->get_param('group_code'));
        if (!$group_code) {
            return new WP_Error('invalid_group', __('缺少 group_code', 'QiMall'), array('status' => 400));
        }
        $data = array();
        // 可更新欄位擴充：名稱、商品名、啟用、分潤參數、推薦參數等
        $fields = array(
            'group_name',
            'shareholder_product_name',
            'partner_product_name',
            'is_active',
            'shareholder_rate',
            'partner_rate',
            'referral_required',
            'referral_bonus_rate',
        );
        foreach ($fields as $k) {
            $v = $request->get_param($k);
            if ($v !== null) { $data[$k] = is_string($v) ? sanitize_text_field($v) : intval($v); }
        }
        $res = QiMall_Group::update($group_code, $data);
        self::audit('update_group', array('user' => get_current_user_id()));
        return rest_ensure_response(array('success' => (bool)$res));
    }

    /** 刪除制度組 */
    public static function delete_group($request) {
        $group_code = sanitize_text_field($request->get_param('group_code'));
        if (!$group_code) {
            return new WP_Error('invalid_group', __('缺少 group_code', 'QiMall'), array('status' => 400));
        }
        if (!class_exists('QiMall_Group')) {
            return new WP_Error('missing_class', __('找不到 QiMall_Group 類別', 'QiMall'), array('status' => 500));
        }
        // 檢查是否有產品綁定該 group，若有則阻止刪除（避免殘留）
        $bound_count = 0;
        if (class_exists('WP_Query')) {
            $q = new WP_Query(array(
                'post_type' => 'product',
                'post_status' => 'any',
                'meta_query' => array(
                    array('key' => '_QiMall_group_code', 'value' => $group_code, 'compare' => '=')
                ),
                'fields' => 'ids',
                'posts_per_page' => 1,
            ));
            $bound_count = intval($q->found_posts);
        }
        if ($bound_count > 0) {
            return new WP_Error('group_in_use', __('此制度組仍被產品綁定，請先解除綁定', 'QiMall'), array('status' => 400));
        }
        $res = QiMall_Group::delete($group_code);
        self::audit('delete_group', array('user' => get_current_user_id()));
        return rest_ensure_response(array('success' => (bool)$res));
    }

    /** 取得產品列表（含 qimall meta） */
    public static function get_products($request) {
        $page = max(1, intval($request->get_param('page')));
        $per_page = intval($request->get_param('per_page'));
        if ($per_page < 1) { $per_page = 20; }
        if ($per_page > 100) { $per_page = 100; }
        $search = sanitize_text_field($request->get_param('search'));
        $group_code = sanitize_text_field($request->get_param('group_code'));
        $QiMall_enabled = sanitize_text_field($request->get_param('QiMall_enabled'));
        if (!class_exists('WP_Query')) {
            return rest_ensure_response(array('success' => true, 'data' => array('items'=>array(),'total'=>0)));
        }
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'paged' => $page,
            'posts_per_page' => $per_page,
        );
        if ($search) { $args['s'] = $search; }
        $meta_query = array('relation' => 'AND');
        if ($group_code) {
            $meta_query[] = array('key' => '_QiMall_group_code', 'value' => $group_code, 'compare' => '=');
        }
        if ($QiMall_enabled !== '') {
            $meta_query[] = array('key' => '_QiMall_enabled', 'value' => $QiMall_enabled ? '1' : '0', 'compare' => '=');
        }
        if (count($meta_query) > 1) { $args['meta_query'] = $meta_query; }
        $q = new WP_Query($args);
        $items = array();
        foreach ($q->posts as $p) {
            $items[] = array(
                'id' => $p->ID,
                'name' => $p->post_title,
                'QiMall_enabled' => get_post_meta($p->ID, '_QiMall_enabled', true) === '1',
                'group_code' => get_post_meta($p->ID, '_QiMall_group_code', true),
                'product_type' => get_post_meta($p->ID, '_QiMall_product_type', true),
                'distribution_enabled' => get_post_meta($p->ID, '_QiMall_distribution_enabled', true) === '1',
            );
        }
        return rest_ensure_response(array(
            'success' => true,
            'data' => array(
                'items' => $items,
                'total' => intval($q->found_posts),
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => $per_page ? ceil($q->found_posts / $per_page) : 1,
            )
        ));
    }

    /** 更新單一產品的 qimall 相關 meta */
    public static function update_product_meta($request) {
        $product_id = intval($request->get_param('product_id'));
        if ($product_id <= 0) {
            return new WP_Error('invalid_product', __('產品 ID 無效', 'QiMall'), array('status' => 400));
        }
        $QiMall_enabled = $request->get_param('QiMall_enabled');
        $group_code = sanitize_text_field($request->get_param('group_code'));
        $product_type = sanitize_text_field($request->get_param('product_type'));
        $distribution_enabled = $request->get_param('distribution_enabled');

        // 若提供 group_code，檢查制度組是否存在
        if ($group_code !== null && $group_code !== '') {
            if (!class_exists('QiMall_Group')) {
                return new WP_Error('missing_class', __('找不到 QiMall_Group 類別', 'QiMall'), array('status' => 500));
            }
            $all = QiMall_Group::get_all(false);
            $exists = false;
            foreach ($all as $g) { if (isset($g->group_code) && $g->group_code === $group_code) { $exists = true; break; } }
            if (!$exists) {
                return new WP_Error('group_not_found', __('制度組不存在', 'QiMall'), array('status' => 400));
            }
        }

        // 更新對應 meta（僅更新提供的欄位）
        if ($QiMall_enabled !== null) {
            update_post_meta($product_id, '_QiMall_enabled', $QiMall_enabled ? '1' : '0');
        }
        if ($group_code !== null) {
            update_post_meta($product_id, '_QiMall_group_code', $group_code);
        }
        if ($product_type !== null) {
            update_post_meta($product_id, '_QiMall_product_type', $product_type);
        }
        if ($distribution_enabled !== null) {
            update_post_meta($product_id, '_QiMall_distribution_enabled', $distribution_enabled ? '1' : '0');
        }
        self::audit('update_product_meta', array('user' => get_current_user_id()));
        return rest_ensure_response(array('success' => true));
    }
}

// 啟動
QiMall_Client_REST::init();

