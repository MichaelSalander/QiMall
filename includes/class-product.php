<?php
/**
 * 商品管理類別（重構版 - 使用 WooCommerce + post_meta）
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

class QiMall_Product {
    
    /**
     * 綁定商品到制度組（使用 post_meta）
     */
    public static function bind($product_id, $group_code, $product_type) {
        // 防呆檢查：該制度組是否已有相同類型的商品綁定
        $existing = self::get_by_group($group_code, $product_type);
        
        if (!empty($existing)) {
            // 檢查是否為自己（編輯現有綁定）
            $is_self = false;
            foreach ($existing as $item) {
                if ($item->product_id == $product_id) {
                    $is_self = true;
                    break;
                }
            }
            
            // 如果不是自己，且已有其他商品綁定，則拒絕
            if (!$is_self) {
                $type_name = $product_type === 'shareholder' ? '股東商品' : '合夥人商品';
                $existing_product = wc_get_product($existing[0]->product_id);
                $existing_name = $existing_product ? $existing_product->get_name() : '未知商品';
                
                return new WP_Error(
                    'duplicate_binding',
                    sprintf(
                        '綁定失敗！制度組「%s」已有%s「%s」（ID: %d）。每個制度組只能綁定一個股東商品和一個合夥人商品。請先解除現有綁定，或選擇其他制度組。',
                        $group_code,
                        $type_name,
                        $existing_name,
                        $existing[0]->product_id
                    )
                );
            }
        }
        
        update_post_meta($product_id, '_QiMall_enabled', 'yes');
        update_post_meta($product_id, '_QiMall_group_code', $group_code);
        update_post_meta($product_id, '_QiMall_product_type', $product_type);
        
        do_action('QiMall_product_bound', $product_id, $group_code, $product_type);
        
        return true;
    }
    
    /**
     * 解除商品綁定
     */
    public static function unbind($product_id) {
        delete_post_meta($product_id, '_QiMall_enabled');
        delete_post_meta($product_id, '_QiMall_group_code');
        delete_post_meta($product_id, '_QiMall_product_type');
        
        do_action('QiMall_product_unbound', $product_id);
        
        return true;
    }
    
    /**
     * 取得商品配置
     */
    public static function get($product_id) {
        $enabled = get_post_meta($product_id, '_QiMall_enabled', true);
        
        if ($enabled !== 'yes') {
            return null;
        }
        
        $group_code = get_post_meta($product_id, '_QiMall_group_code', true);
        $product_type = get_post_meta($product_id, '_QiMall_product_type', true);
        
        // 兼容舊代碼，使用 plan_type 作為別名
        return (object) array(
            'product_id' => $product_id,
            'group_code' => $group_code,
            'product_type' => $product_type,
            'plan_type' => $product_type  // 兼容舊代碼
        );
    }
    
    /**
     * 取得制度組的所有商品
     */
    public static function get_by_group($group_code, $product_type = null) {
        $meta_query = array(
            array(
                'key' => '_QiMall_enabled',
                'value' => 'yes'
            ),
            array(
                'key' => '_QiMall_group_code',
                'value' => $group_code
            )
        );
        
        if ($product_type) {
            $meta_query[] = array(
                'key' => '_QiMall_product_type',
                'value' => $product_type
            );
        }
        
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_query' => $meta_query
        );
        
        $products = get_posts($args);
        $results = array();
        
        foreach ($products as $product) {
            $config = self::get($product->ID);
            if ($config) {
                $results[] = $config;
            }
        }
        
        return $results;
    }
    
    /**
     * 取得所有股東商品
     */
    public static function get_shareholder_products() {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_QiMall_enabled',
                    'value' => 'yes'
                ),
                array(
                    'key' => '_QiMall_product_type',
                    'value' => 'shareholder'
                )
            )
        );
        
        $products = get_posts($args);
        $results = array();
        
        foreach ($products as $product) {
            $results[] = self::get($product->ID);
        }
        
        return $results;
    }
    
    /**
     * 取得所有合夥人商品
     */
    public static function get_partner_products() {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_QiMall_enabled',
                    'value' => 'yes'
                ),
                array(
                    'key' => '_QiMall_product_type',
                    'value' => 'partner'
                )
            )
        );
        
        $products = get_posts($args);
        $results = array();
        
        foreach ($products as $product) {
            $results[] = self::get($product->ID);
        }
        
        return $results;
    }
    
    /**
     * 取得所有已綁定商品
     */
    public static function get_all() {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_QiMall_enabled',
                    'value' => 'yes'
                )
            )
        );
        
        $products = get_posts($args);
        $results = array();
        
        foreach ($products as $product) {
            $results[] = self::get($product->ID);
        }
        
        return $results;
    }
    
    /**
     * 取得所有 WooCommerce 商品（包含未綁定的）
     */
    public static function get_all_wc_products() {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        return get_posts($args);
    }
    
    /**
     * 檢查商品是否已綁定
     */
    public static function is_bound($product_id) {
        return get_post_meta($product_id, '_QiMall_enabled', true) === 'yes';
    }
    
    /**
     * 檢查商品是否為股東商品
     */
    public static function is_shareholder_product($product_id) {
        $config = self::get($product_id);
        return $config && $config->product_type === 'shareholder';
    }
    
    /**
     * 檢查商品是否為合夥人商品
     */
    public static function is_partner_product($product_id) {
        $config = self::get($product_id);
        return $config && $config->product_type === 'partner';
    }
    
    /**
     * 在 WooCommerce 商品頁面加入自訂面板
     */
    public static function add_product_data_tab($tabs) {
        $tabs['qimall'] = array(
            'label' => __('消費股東設定', 'qimall'),
            'target' => 'QiMall_product_data',
            'class' => array('show_if_simple', 'show_if_variable'),
            'priority' => 80
        );
        return $tabs;
    }
    
    /**
     * 渲染商品設定面板內容
     */
    public static function add_product_data_panel() {
        global $post;
        
        $enabled = get_post_meta($post->ID, '_QiMall_enabled', true);
        $group_code = get_post_meta($post->ID, '_QiMall_group_code', true);
        $product_type = get_post_meta($post->ID, '_QiMall_product_type', true);
        $distribution_enabled = get_post_meta($post->ID, '_QiMall_distribution_enabled', true);
        
        // 取得所有制度組
        $groups = QiMall_Group::get_all();
        
        // 檢查是否有綁定錯誤訊息
        $error_message = get_transient('QiMall_bind_error_' . $post->ID);
        if ($error_message) {
            delete_transient('QiMall_bind_error_' . $post->ID);
        }
        
        ?>
        <div id="QiMall_product_data" class="panel woocommerce_options_panel">
            
            <?php if ($error_message): ?>
                <div class="notice notice-error inline" style="margin: 15px;">
                    <p><strong>❌ <?php echo esc_html($error_message); ?></strong></p>
                </div>
            <?php endif; ?>
            
            <div class="options_group">
                <?php
                woocommerce_wp_checkbox(array(
                    'id' => '_QiMall_enabled',
                    'label' => __('啟用消費股東制度', 'qimall'),
                    'description' => __('購買此商品的用戶將參與選定的制度組', 'qimall'),
                    'value' => $enabled === 'yes' ? 'yes' : 'no'
                ));
                ?>
            </div>
            
            <div class="options_group coshare-settings" style="<?php echo $enabled === 'yes' ? '' : 'display:none;'; ?>">
                <?php
                woocommerce_wp_select(array(
                    'id' => '_QiMall_group_code',
                    'label' => __('制度組', 'qimall'),
                    'options' => self::get_group_options($groups),
                    'value' => $group_code
                ));
                
                woocommerce_wp_select(array(
                    'id' => '_QiMall_product_type',
                    'label' => __('商品類型', 'qimall'),
                    'options' => array(
                        '' => __('請選擇...', 'qimall'),
                        'shareholder' => __('股東商品', 'qimall'),
                        'partner' => __('合夥人商品', 'qimall')
                    ),
                    'value' => $product_type
                ));
                ?>
                
                <p class="form-field">
                    <label>&nbsp;</label>
                    <span class="description">
                        💡 <strong>股東商品:</strong> 購買後成為股東,享有商城分潤、團隊分紅、推薦回本<br>
                        💡 <strong>合夥人商品:</strong> 購買後成為合夥人,享有推薦回本(推三回一)<br>
                        ⚠️ <strong>限制:</strong> 每個制度組只能綁定一個股東商品和一個合夥人商品
                    </span>
                </p>
            </div>
            
            <div class="options_group coshare-distribution-section">
                <hr style="margin: 20px 0;">
                <?php
                woocommerce_wp_checkbox(array(
                    'id' => '_QiMall_distribution_enabled',
                    'label' => __('參與二級分銷', 'qimall'),
                    'description' => __('購買此商品時,推薦人可獲得一級分銷獎勵,推薦人的推薦人可獲得二級分銷獎勵', 'qimall'),
                    'value' => $distribution_enabled === 'yes' ? 'yes' : 'no'
                ));
                ?>
                
                <p class="form-field coshare-distribution-info">
                    <label>&nbsp;</label>
                    <span class="description">
                        💰 <strong>二級分銷說明:</strong><br>
                        • 用戶購買此商品時,其推薦人獲得「一級分銷獎勵」<br>
                        • 推薦人的推薦人獲得「二級分銷獎勵」<br>
                        • 分銷比例在「消費股東 → 系統設定」中統一設定<br>
                        • <strong style="color:#d63638;">⚠️ 注意:股東/合夥人商品已有專屬獎勵機制,不應同時啟用分銷</strong>
                    </span>
                </p>
                
                <p class="form-field coshare-distribution-warning" style="display:none; background:#fff3cd; padding:10px; border-left:4px solid #f0b429;">
                    <label>&nbsp;</label>
                    <span class="description" style="color:#856404;">
                        <strong>⚠️ 不建議同時啟用</strong><br>
                        此商品已設定為股東/合夥人商品,已享有「推薦回本」機制。<br>
                        如果再啟用「二級分銷」,推薦人將同時獲得兩種獎勵,可能造成獎勵過高。<br>
                        建議只選擇其中一種獎勵機制。
                    </span>
                </p>
            </div>
            
            <script>
            jQuery(function($) {
                // 切換消費股東制度
                $('#_QiMall_enabled').change(function() {
                    if ($(this).is(':checked')) {
                        $('.coshare-settings').slideDown();
                        checkDistributionConflict();
                    } else {
                        $('.coshare-settings').slideUp();
                        checkDistributionConflict();
                    }
                });
                
                // 切換二級分銷
                $('#_QiMall_distribution_enabled').change(function() {
                    checkDistributionConflict();
                });
                
                // 檢查衝突
                function checkDistributionConflict() {
                    var isCoShareEnabled = $('#_QiMall_enabled').is(':checked');
                    var isDistributionEnabled = $('#_QiMall_distribution_enabled').is(':checked');
                    
                    if (isCoShareEnabled && isDistributionEnabled) {
                        // 顯示警告
                        $('.coshare-distribution-warning').slideDown();
                        $('.coshare-distribution-info').hide();
                    } else {
                        // 隱藏警告
                        $('.coshare-distribution-warning').slideUp();
                        $('.coshare-distribution-info').show();
                    }
                }
                
                // 初始檢查
                checkDistributionConflict();
            });
            </script>
        </div>
        <?php
    }
    
    /**
     * 儲存商品設定
     */
    public static function save_product_data($post_id) {
        $enabled = isset($_POST['_QiMall_enabled']) ? 'yes' : 'no';
        $group_code = isset($_POST['_QiMall_group_code']) ? sanitize_text_field($_POST['_QiMall_group_code']) : '';
        $product_type = isset($_POST['_QiMall_product_type']) ? sanitize_text_field($_POST['_QiMall_product_type']) : '';
        $distribution_enabled = isset($_POST['_QiMall_distribution_enabled']) ? 'yes' : 'no';
        
        // 儲存分銷設定
        update_post_meta($post_id, '_QiMall_distribution_enabled', $distribution_enabled);
        
        if ($enabled === 'yes' && $group_code && $product_type) {
            $result = self::bind($post_id, $group_code, $product_type);
            
            // 如果綁定失敗（返回 WP_Error），顯示錯誤訊息
            if (is_wp_error($result)) {
                set_transient('QiMall_bind_error_' . $post_id, $result->get_error_message(), 30);
            }
        } else {
            self::unbind($post_id);
        }
    }
    
    /**
     * 取得制度組選項
     */
    private static function get_group_options($groups) {
        $options = array('' => __('請選擇...', 'qimall'));
        
        foreach ($groups as $group) {
            $options[$group->group_code] = $group->group_name . ' (' . $group->group_code . ')';
        }
        
        return $options;
    }
}

// 註冊 WooCommerce 掛鉤
add_filter('woocommerce_product_data_tabs', array('QiMall_Product', 'add_product_data_tab'));
add_action('woocommerce_product_data_panels', array('QiMall_Product', 'add_product_data_panel'));
add_action('woocommerce_process_product_meta', array('QiMall_Product', 'save_product_data'));

