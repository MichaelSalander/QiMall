<?php
/**
 * 商品管理頁面（快速綁定介面）
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

// 處理快速綁定
if (isset($_POST['QiMall_bind_product']) && check_admin_referer('QiMall_bind_product_action', 'QiMall_bind_product_nonce')) {
    $product_id = intval($_POST['product_id']);
    $group_code = sanitize_text_field($_POST['group_code']);
    $product_type = sanitize_text_field($_POST['product_type']);
    
    $result = QiMall_Product::bind($product_id, $group_code, $product_type);
    
    if (is_wp_error($result)) {
        $error_message = '❌ ' . $result->get_error_message();
    } else {
        $success_message = '✅ 商品綁定成功！';
    }
}

// 處理解除綁定
if (isset($_GET['action']) && $_GET['action'] === 'unbind' && isset($_GET['product_id']) && check_admin_referer('unbind_product_' . $_GET['product_id'])) {
    QiMall_Product::unbind(intval($_GET['product_id']));
    $success_message = '✅ 已解除商品綁定！';
}

// 取得所有 WooCommerce 商品
$all_products = QiMall_Product::get_all_wc_products();
$groups = QiMall_Group::get_all();
?>

<div class="wrap">
    <h1>商品管理</h1>
    <p class="description">管理 WooCommerce 商品與消費股東制度的綁定關係</p>
    
    <?php if (isset($success_message)): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($success_message); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (empty($all_products)): ?>
        <div class="notice notice-warning">
            <p>⚠️ 尚未建立任何 WooCommerce 商品。請先前往 <a href="<?php echo admin_url('post-new.php?post_type=product'); ?>">商品 → 新增商品</a> 建立商品。</p>
        </div>
    <?php else: ?>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <select id="filter-status">
                    <option value="">所有商品</option>
                    <option value="bound">已綁定</option>
                    <option value="unbound">未綁定</option>
                </select>
                <button type="button" class="button" onclick="filterProducts()">篩選</button>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="25%">商品名稱</th>
                    <th width="10%">價格</th>
                    <th width="15%">制度組</th>
                    <th width="10%">類型</th>
                    <th width="10%">狀態</th>
                    <th width="25%">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_products as $wp_product): 
                    $product = wc_get_product($wp_product->ID);
                    $config = QiMall_Product::get($wp_product->ID);
                    $is_bound = $config !== null;
                    $status_class = $is_bound ? 'bound' : 'unbound';
                ?>
                    <tr class="product-row" data-status="<?php echo esc_attr($status_class); ?>">
                        <td><?php echo $wp_product->ID; ?></td>
                        <td>
                            <strong><?php echo esc_html($product->get_name()); ?></strong>
                            <div class="row-actions">
                                <span><a href="<?php echo get_edit_post_link($wp_product->ID); ?>" target="_blank">編輯商品</a></span>
                            </div>
                        </td>
                        <td><?php echo wc_price($product->get_regular_price()); ?></td>
                        <td>
                            <?php if ($is_bound): ?>
                                <?php 
                                $group = QiMall_Group::get($config->group_code);
                                echo $group ? esc_html($group->group_name) : esc_html($config->group_code);
                                ?>
                            <?php else: ?>
                                <span class="description">未綁定</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($is_bound): ?>
                                <?php if ($config->product_type === 'shareholder'): ?>
                                    <span class="dashicons dashicons-star-filled" style="color:#f0b429;"></span> 股東
                                <?php elseif ($config->product_type === 'partner'): ?>
                                    <span class="dashicons dashicons-groups" style="color:#2271b1;"></span> 合夥人
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="description">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($is_bound): ?>
                                <span class="status-badge status-active">✓ 已啟用</span>
                            <?php else: ?>
                                <span class="status-badge status-inactive">- 未綁定</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($is_bound): ?>
                                <button type="button" class="button" onclick="editBinding(<?php echo $wp_product->ID; ?>)">編輯綁定</button>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=coshare-products&action=unbind&product_id=' . $wp_product->ID), 'unbind_product_' . $wp_product->ID); ?>" 
                                   class="button button-link-delete" 
                                   onclick="return confirm('確定要解除此商品的綁定嗎？')">解除綁定</a>
                            <?php else: ?>
                                <button type="button" class="button button-primary" onclick="quickBind(<?php echo $wp_product->ID; ?>, '<?php echo esc_js($product->get_name()); ?>')">快速綁定</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
    <?php endif; ?>
    
    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>💡 使用說明</h2>
        <ul style="line-height: 2;">
            <li><strong>方式 A：在 WooCommerce 編輯商品頁面直接設定</strong><br>
                前往 商品 → 編輯商品 → 滾動到「消費股東設定」標籤頁</li>
            <li><strong>方式 B：在此頁面快速綁定</strong><br>
                點擊「快速綁定」按鈕，選擇制度組和商品類型</li>
        </ul>
        
        <h3>商品類型說明</h3>
        <table class="widefat" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th>類型</th>
                    <th>享有權益</th>
                    <th>數量限制</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="dashicons dashicons-star-filled" style="color:#f0b429;"></span> <strong>股東商品</strong></td>
                    <td>商城分潤、團隊分紅、推薦回本</td>
                    <td><strong>每個制度組限1個</strong></td>
                </tr>
                <tr>
                    <td><span class="dashicons dashicons-groups" style="color:#2271b1;"></span> <strong>合夥人商品</strong></td>
                    <td>推薦回本(推三回一)</td>
                    <td><strong>每個制度組限1個</strong></td>
                </tr>
                <tr>
                    <td><span class="dashicons dashicons-cart" style="color:#46b450;"></span> <strong>一般商品(參與分銷)</strong></td>
                    <td>購買時產生二級分銷獎勵給推薦人</td>
                    <td>無限制</td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107;">
            <p style="margin: 0;">
                <strong>⚠️ 防呆機制：</strong><br>
                每個制度組只能綁定 <strong>1個股東商品</strong> 和 <strong>1個合夥人商品</strong>。<br>
                如需綁定新商品，請先解除現有綁定。
            </p>
        </div>
    </div>
</div>

<!-- 快速綁定彈窗 -->
<div id="quick-bind-modal" style="display:none;">
    <div class="coshare-modal-overlay" onclick="closeModal()"></div>
    <div class="coshare-modal">
        <h2>快速綁定商品</h2>
        <form method="post" id="bind-form">
            <?php wp_nonce_field('QiMall_bind_product_action', 'QiMall_bind_product_nonce'); ?>
            <input type="hidden" name="QiMall_bind_product" value="1">
            <input type="hidden" name="product_id" id="bind-product-id">
            
            <table class="form-table">
                <tr>
                    <th>商品</th>
                    <td id="bind-product-name"></td>
                </tr>
                <tr>
                    <th><label for="bind-group-code">制度組 *</label></th>
                    <td>
                        <select name="group_code" id="bind-group-code" required style="width: 100%;">
                            <option value="">請選擇制度組...</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?php echo esc_attr($group->group_code); ?>">
                                    <?php echo esc_html($group->group_name . ' (' . $group->group_code . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="bind-product-type">商品類型 *</label></th>
                    <td>
                        <select name="product_type" id="bind-product-type" required style="width: 100%;">
                            <option value="">請選擇類型...</option>
                            <option value="shareholder">股東商品</option>
                            <option value="partner">合夥人商品</option>
                        </select>
                        <p class="description">
                            <strong>股東：</strong>享有商城分潤、團隊分紅、推薦回本、分銷收益<br>
                            <strong>合夥人：</strong>享有推薦回本(推三回一)、分銷收益
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="button" class="button" onclick="closeModal()">取消</button>
                <button type="submit" class="button button-primary">確定綁定</button>
            </p>
        </form>
    </div>
</div>

<style>
.status-badge {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
}
.status-active {
    background: #d4edda;
    color: #155724;
}
.status-inactive {
    background: #f8f9fa;
    color: #6c757d;
}
.coshare-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    z-index: 100000;
}
.coshare-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    border-radius: 4px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    z-index: 100001;
    min-width: 500px;
    max-width: 600px;
}
.coshare-modal h2 {
    margin-top: 0;
}
</style>

<script>
<?php if (isset($error_message)): ?>
// 顯示錯誤彈窗
jQuery(document).ready(function($) {
    alert('<?php echo esc_js($error_message); ?>');
});
<?php endif; ?>

function quickBind(productId, productName) {
    document.getElementById('bind-product-id').value = productId;
    document.getElementById('bind-product-name').textContent = productName;
    document.getElementById('bind-group-code').value = '';
    document.getElementById('bind-product-type').value = '';
    document.getElementById('quick-bind-modal').style.display = 'block';
}

function editBinding(productId) {
    window.location.href = '<?php echo admin_url('post.php?action=edit&post='); ?>' + productId;
}

function closeModal() {
    document.getElementById('quick-bind-modal').style.display = 'none';
}

function filterProducts() {
    var status = document.getElementById('filter-status').value;
    var rows = document.querySelectorAll('.product-row');
    
    rows.forEach(function(row) {
        if (status === '' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

