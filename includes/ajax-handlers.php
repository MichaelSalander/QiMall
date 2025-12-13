<?php
/**
 * AJAX 處理函數
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 手動發放積分
 */
add_action('wp_ajax_QiMall_manual_distribute_points', 'QiMall_ajax_manual_distribute_points');
function QiMall_ajax_manual_distribute_points() {
    check_ajax_referer('QiMall_admin_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => '權限不足'));
    }
    
    try {
        QiMall_Cron::distribute_monthly_points();
        wp_send_json_success(array('message' => '積分發放完成！'));
    } catch (Exception $e) {
        wp_send_json_error(array('message' => '發放失敗：' . $e->getMessage()));
    }
}

/**
 * 獲取用戶收益數據
 */
add_action('wp_ajax_QiMall_get_user_earnings', 'QiMall_ajax_get_user_earnings');
function QiMall_ajax_get_user_earnings() {
    check_ajax_referer('QiMall_public_nonce', 'nonce');
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(array('message' => '請先登入'));
    }
    
    $stats = QiMall_Reward::get_statistics($user_id);
    wp_send_json_success($stats);
}

/**
 * 獲取團隊數據
 */
add_action('wp_ajax_QiMall_get_team_data', 'QiMall_ajax_get_team_data');
function QiMall_ajax_get_team_data() {
    check_ajax_referer('QiMall_public_nonce', 'nonce');
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(array('message' => '請先登入'));
    }
    
    $stats = QiMall_Distribution_Calculator::get_team_statistics($user_id);
    wp_send_json_success($stats);
}

/**
 * 綁定商品到制度組
 */
add_action('wp_ajax_QiMall_bind_product', 'QiMall_ajax_bind_product');
function QiMall_ajax_bind_product() {
    check_ajax_referer('QiMall_admin_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => '權限不足'));
    }
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $group_code = isset($_POST['group_code']) ? sanitize_text_field($_POST['group_code']) : '';
    $plan_type = isset($_POST['plan_type']) ? sanitize_text_field($_POST['plan_type']) : '';
    
    if (!$product_id || !$group_code || !$plan_type) {
        wp_send_json_error(array('message' => '參數不完整'));
    }
    
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(array('message' => '商品不存在'));
    }
    
    $data = array(
        'product_id' => $product_id,
        'group_code' => $group_code,
        'plan_type' => $plan_type,
        'price' => $product->get_price(),
        'is_active' => 1
    );
    
    $result = QiMall_Product::create($data);
    
    if ($result) {
        wp_send_json_success(array('message' => '綁定成功'));
    } else {
        wp_send_json_error(array('message' => '綁定失敗'));
    }
}

/**
 * 解除商品綁定
 */
add_action('wp_ajax_QiMall_unbind_product', 'QiMall_ajax_unbind_product');
function QiMall_ajax_unbind_product() {
    check_ajax_referer('QiMall_admin_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => '權限不足'));
    }
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    if (!$product_id) {
        wp_send_json_error(array('message' => '參數不完整'));
    }
    
    $result = QiMall_Product::delete($product_id);
    
    if ($result) {
        wp_send_json_success(array('message' => '解除綁定成功'));
    } else {
        wp_send_json_error(array('message' => '解除綁定失敗'));
    }
}

