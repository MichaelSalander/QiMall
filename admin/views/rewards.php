<?php
/**
 * 管理後台 - 獎勵記錄
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// 分頁參數
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($paged - 1) * $per_page;

// 篩選參數
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$type_filter = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';

$rewards_table = $wpdb->prefix . 'QiMall_rewards';

// 建立查詢
$where = "1=1";
if ($status_filter) {
    $where .= $wpdb->prepare(" AND status = %s", $status_filter);
}
if ($type_filter) {
    $where .= $wpdb->prepare(" AND reward_type = %s", $type_filter);
}

// 獲取總數
$total = $wpdb->get_var("SELECT COUNT(*) FROM $rewards_table WHERE $where");

// 獲取獎勵記錄
$rewards = $wpdb->get_results(
    "SELECT r.*, u.display_name, u.user_email 
    FROM $rewards_table r
    LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
    WHERE $where
    ORDER BY r.created_at DESC
    LIMIT $per_page OFFSET $offset"
);

$total_pages = ceil($total / $per_page);
?>

<div class="wrap">
    <h1>獎勵記錄</h1>
    
    <!-- 篩選器 -->
    <div class="coshare-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="coshare-rewards">
            
            <select name="status">
                <option value="">所有狀態</option>
                <option value="pending" <?php selected($status_filter, 'pending'); ?>>待結算</option>
                <option value="calculated" <?php selected($status_filter, 'calculated'); ?>>已計算</option>
                <option value="paid" <?php selected($status_filter, 'paid'); ?>>已發放</option>
            </select>
            
            <select name="type">
                <option value="">所有類型</option>
                <option value="profit" <?php selected($type_filter, 'profit'); ?>>商城分潤</option>
                <option value="dividend" <?php selected($type_filter, 'dividend'); ?>>團隊分紅</option>
                <option value="referral" <?php selected($type_filter, 'referral'); ?>>推薦回本</option>
                <option value="distribution" <?php selected($type_filter, 'distribution'); ?>>分銷收益</option>
            </select>
            
            <input type="submit" class="button" value="篩選">
            <a href="<?php echo admin_url('admin.php?page=coshare-rewards'); ?>" class="button">重置</a>
        </form>
    </div>
    
    <!-- 統計摘要 -->
    <div class="coshare-stats-summary">
        <div class="summary-item">
            <span class="label">總記錄數</span>
            <span class="value"><?php echo number_format($total); ?></span>
        </div>
        <div class="summary-item">
            <span class="label">待結算金額</span>
            <span class="value">NT$ <?php 
                echo number_format($wpdb->get_var("SELECT SUM(amount) FROM $rewards_table WHERE status = 'pending'"), 2);
            ?></span>
        </div>
        <div class="summary-item">
            <span class="label">已發放金額</span>
            <span class="value">NT$ <?php 
                echo number_format($wpdb->get_var("SELECT SUM(amount) FROM $rewards_table WHERE status = 'paid'"), 2);
            ?></span>
        </div>
    </div>
    
    <!-- 獎勵列表 -->
    <div class="coshare-section">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>會員</th>
                    <th>制度組</th>
                    <th>獎勵類型</th>
                    <th>金額</th>
                    <th>狀態</th>
                    <th>建立時間</th>
                    <th>發放時間</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rewards)): ?>
                    <tr>
                        <td colspan="7">暫無獎勵記錄</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rewards as $reward): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($reward->display_name); ?></strong><br>
                            <small><?php echo esc_html($reward->user_email); ?></small>
                        </td>
                        <td><?php echo esc_html($reward->group_code); ?></td>
                        <td>
                            <span class="reward-type-badge <?php echo $reward->reward_type; ?>">
                                <?php 
                                $type_labels = array(
                                    'profit' => '商城分潤',
                                    'dividend' => '團隊分紅',
                                    'referral' => '推薦回本',
                                    'distribution' => '分銷收益'
                                );
                                echo $type_labels[$reward->reward_type] ?? $reward->reward_type;
                                ?>
                            </span>
                        </td>
                        <td><strong>NT$ <?php echo number_format($reward->amount, 2); ?></strong></td>
                        <td>
                            <?php if ($reward->status === 'pending'): ?>
                                <span class="status-pending">待結算</span>
                            <?php elseif ($reward->status === 'calculated'): ?>
                                <span class="status-calculated">已計算</span>
                            <?php else: ?>
                                <span class="status-paid">已發放</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($reward->created_at)); ?></td>
                        <td>
                            <?php echo $reward->paid_at ? date('Y-m-d H:i', strtotime($reward->paid_at)) : '-'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- 分頁 -->
        <?php if ($total_pages > 1): ?>
        <div class="tablenav">
            <div class="tablenav-pages">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $paged
                ));
                ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.coshare-filters {
    background: white;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
}

.coshare-filters form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.coshare-filters select {
    min-width: 150px;
}

.coshare-stats-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.summary-item {
    background: white;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.summary-item .label {
    font-size: 12px;
    color: #666;
}

.summary-item .value {
    font-size: 20px;
    font-weight: bold;
    color: #2c3e50;
}

.reward-type-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    display: inline-block;
}

.reward-type-badge.profit {
    background: #3498db;
    color: white;
}

.reward-type-badge.dividend {
    background: #9b59b6;
    color: white;
}

.reward-type-badge.referral {
    background: #e67e22;
    color: white;
}

.reward-type-badge.distribution {
    background: #16a085;
    color: white;
}

.status-pending {
    color: #f39c12;
    font-weight: bold;
}

.status-calculated {
    color: #3498db;
    font-weight: bold;
}

.status-paid {
    color: #2ecc71;
    font-weight: bold;
}
</style>

