<?php
/**
 * 管理後台 - 商品管理
 */

if (!defined('ABSPATH')) {
    exit;
}

// 獲取所有WooCommerce商品
$wc_products = wc_get_products(array('limit' => -1));
$QiMall_products = QiMall_Product::get_by_group('', null);
$groups = QiMall_Group::get_all(false);
?>

<div class="wrap">
    <h1>商品管理</h1>
    
    <div class="coshare-section">
        <h2>綁定商品到制度組</h2>
        <p class="description">選擇 WooCommerce 商品並將其綁定到制度組，設定為股東商品或合夥人商品。</p>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>商品名稱</th>
                    <th>價格</th>
                    <th>制度組</th>
                    <th>商品類型</th>
                    <th>狀態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($wc_products as $product): 
                    $config = QiMall_Product::get($product->get_id());
                ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($product->get_name()); ?></strong><br>
                        <small>ID: <?php echo $product->get_id(); ?></small>
                    </td>
                    <td>NT$ <?php echo number_format($product->get_price(), 2); ?></td>
                    <td>
                        <?php if ($config): 
                            $group = QiMall_Group::get($config->group_code);
                            echo $group ? esc_html($group->group_name) : $config->group_code;
                        else: ?>
                            <span style="color: #999;">未綁定</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($config): ?>
                            <span class="plan-type-badge <?php echo $config->plan_type; ?>">
                                <?php 
                                echo $config->plan_type === 'shareholder' ? '股東商品' : 
                                     ($config->plan_type === 'partner' ? '合夥人商品' : '通用商品'); 
                                ?>
                            </span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($config && $config->is_active): ?>
                            <span class="status-active">●啟用</span>
                        <?php elseif ($config): ?>
                            <span class="status-inactive">●停用</span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($config): ?>
                            <button class="button button-small">編輯</button>
                            <button class="button button-small delete-action">解除綁定</button>
                        <?php else: ?>
                            <button class="button button-primary button-small">綁定</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="coshare-section">
        <h2>已綁定商品統計</h2>
        <div class="stats-grid">
            <?php foreach ($groups as $group): 
                $sh_products = QiMall_Product::get_by_group($group->group_code, 'shareholder');
                $pt_products = QiMall_Product::get_by_group($group->group_code, 'partner');
            ?>
            <div class="stat-box">
                <h4><?php echo esc_html($group->group_name); ?></h4>
                <p>股東商品: <?php echo count($sh_products); ?> 個</p>
                <p>合夥人商品: <?php echo count($pt_products); ?> 個</p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.plan-type-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    display: inline-block;
}

.plan-type-badge.shareholder {
    background: #667eea;
    color: white;
}

.plan-type-badge.partner {
    background: #764ba2;
    color: white;
}

.plan-type-badge.common {
    background: #95a5a6;
    color: white;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.stat-box {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.stat-box h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.stat-box p {
    margin: 5px 0;
    color: #666;
}
</style>

