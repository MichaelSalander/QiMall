<?php
/**
 * 管理後台 - 制度組管理
 */

if (!defined('ABSPATH')) {
    exit;
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['QiMall_group_action'])) {
    check_admin_referer('QiMall_group_action');
    
    $action = sanitize_text_field($_POST['QiMall_group_action']);
    
    if ($action === 'add') {
        $group_data = array(
            'group_code' => sanitize_text_field($_POST['group_code']),
            'group_name' => sanitize_text_field($_POST['group_name']),
            'shareholder_product_name' => sanitize_text_field($_POST['shareholder_product_name']),
            'partner_product_name' => sanitize_text_field($_POST['partner_product_name']),
            'shareholder_profit_rate' => floatval($_POST['shareholder_profit_rate']),
            'shareholder_dividend_rate' => floatval($_POST['shareholder_dividend_rate']),
            'shareholder_referral_count' => intval($_POST['shareholder_referral_count']),
            'partner_referral_count' => intval($_POST['partner_referral_count']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        );
        
        QiMall_Group::create($group_data);
        echo '<div class="notice notice-success"><p>制度組已建立</p></div>';
    }
}

$groups = QiMall_Group::get_all(false);
?>

<div class="wrap">
    <h1>制度組管理</h1>
    
    <div class="coshare-groups-page">
        <!-- 新增制度組表單 -->
        <div class="coshare-section">
            <h2>新增制度組</h2>
            <form method="post" action="">
                <?php wp_nonce_field('QiMall_group_action'); ?>
                <input type="hidden" name="QiMall_group_action" value="add">
                
                <table class="form-table">
                    <tr>
                        <th><label for="group_code">制度組代號 *</label></th>
                        <td>
                            <input type="text" id="group_code" name="group_code" class="regular-text" required>
                            <p class="description">例如：group_01, group_02</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="group_name">制度組名稱 *</label></th>
                        <td>
                            <input type="text" id="group_name" name="group_name" class="regular-text" required>
                            <p class="description">例如：制度組①</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="shareholder_product_name">股東商品名稱 *</label></th>
                        <td>
                            <input type="text" id="shareholder_product_name" name="shareholder_product_name" class="regular-text" required>
                            <p class="description">例如：消費股東A</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="partner_product_name">合夥人商品名稱 *</label></th>
                        <td>
                            <input type="text" id="partner_product_name" name="partner_product_name" class="regular-text" required>
                            <p class="description">例如：消費合夥人B</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="shareholder_profit_rate">股東商城分潤比例 (%)</label></th>
                        <td>
                            <input type="number" id="shareholder_profit_rate" name="shareholder_profit_rate" step="0.01" min="0" max="100" value="0">
                            <p class="description">X₁% - 股東可獲得的商城分潤比例</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="shareholder_dividend_rate">股東團隊分紅比例 (%)</label></th>
                        <td>
                            <input type="number" id="shareholder_dividend_rate" name="shareholder_dividend_rate" step="0.01" min="0" max="100" value="0">
                            <p class="description">Y₁% - 股東可獲得的團隊分紅比例</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="shareholder_referral_count">股東推薦回本人數</label></th>
                        <td>
                            <input type="number" id="shareholder_referral_count" name="shareholder_referral_count" min="0" value="0">
                            <p class="description">N₁ - 股東推薦多少人購買可回本（0表示無此機制）</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="partner_referral_count">合夥人推薦回本人數</label></th>
                        <td>
                            <input type="number" id="partner_referral_count" name="partner_referral_count" min="0" value="3">
                            <p class="description">推三返一 - 合夥人推薦多少人購買可回本</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="is_active">啟用狀態</label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="is_active" name="is_active" value="1" checked>
                                啟用此制度組
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" class="button button-primary" value="建立制度組">
                </p>
            </form>
        </div>
        
        <!-- 現有制度組列表 -->
        <div class="coshare-section">
            <h2>現有制度組</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>代號</th>
                        <th>名稱</th>
                        <th>股東商品</th>
                        <th>合夥人商品</th>
                        <th>分潤比例</th>
                        <th>分紅比例</th>
                        <th>股東回本</th>
                        <th>合夥人回本</th>
                        <th>狀態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($groups)): ?>
                        <tr>
                            <td colspan="10">尚無制度組</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($groups as $group): ?>
                        <tr>
                            <td><code><?php echo esc_html($group->group_code); ?></code></td>
                            <td><strong><?php echo esc_html($group->group_name); ?></strong></td>
                            <td><?php echo esc_html($group->shareholder_product_name); ?></td>
                            <td><?php echo esc_html($group->partner_product_name); ?></td>
                            <td><?php echo number_format($group->shareholder_profit_rate, 2); ?>%</td>
                            <td><?php echo number_format($group->shareholder_dividend_rate, 2); ?>%</td>
                            <td><?php echo $group->shareholder_referral_count > 0 ? $group->shareholder_referral_count . '人' : '無'; ?></td>
                            <td><?php echo $group->partner_referral_count . '人'; ?></td>
                            <td>
                                <?php if ($group->is_active): ?>
                                    <span class="status-active">●啟用</span>
                                <?php else: ?>
                                    <span class="status-inactive">●停用</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="#" class="button button-small">編輯</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.coshare-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.coshare-section h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #0073aa;
}

.status-active {
    color: #46b450;
}

.status-inactive {
    color: #dc3232;
}
</style>

