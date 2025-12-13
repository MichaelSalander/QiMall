<?php
/**
 * 管理後台 - 積分管理
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$points_table = $wpdb->prefix . 'QiMall_points';

// 獲取所有積分帳戶
$points_accounts = $wpdb->get_results(
    "SELECT p.*, u.display_name, u.user_email 
    FROM $points_table p
    LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
    ORDER BY p.total_points DESC"
);

// 統計數據
$total_points = $wpdb->get_var("SELECT SUM(total_points) FROM $points_table");
$total_available = $wpdb->get_var("SELECT SUM(available_points) FROM $points_table");
$total_pending = $wpdb->get_var("SELECT SUM(pending_points) FROM $points_table");
?>

<div class="wrap">
    <h1>積分管理</h1>
    
    <!-- 統計摘要 -->
    <div class="coshare-stats-summary">
        <div class="summary-item">
            <span class="label">總積分</span>
            <span class="value"><?php echo number_format($total_points, 2); ?></span>
        </div>
        <div class="summary-item">
            <span class="label">可用積分</span>
            <span class="value"><?php echo number_format($total_available, 2); ?></span>
        </div>
        <div class="summary-item">
            <span class="label">待結算積分</span>
            <span class="value"><?php echo number_format($total_pending, 2); ?></span>
        </div>
        <div class="summary-item">
            <span class="label">帳戶數量</span>
            <span class="value"><?php echo number_format(count($points_accounts)); ?></span>
        </div>
    </div>
    
    <!-- 積分帳戶列表 -->
    <div class="coshare-section">
        <h2>積分帳戶</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>會員</th>
                    <th>總積分</th>
                    <th>可用積分</th>
                    <th>待結算積分</th>
                    <th>已使用積分</th>
                    <th>最後結算日期</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($points_accounts)): ?>
                    <tr>
                        <td colspan="7">暫無積分帳戶</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($points_accounts as $account): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($account->display_name); ?></strong><br>
                            <small><?php echo esc_html($account->user_email); ?></small>
                        </td>
                        <td><strong><?php echo number_format($account->total_points, 2); ?></strong></td>
                        <td class="points-available"><?php echo number_format($account->available_points, 2); ?></td>
                        <td class="points-pending"><?php echo number_format($account->pending_points, 2); ?></td>
                        <td class="points-used"><?php echo number_format($account->used_points, 2); ?></td>
                        <td>
                            <?php echo $account->last_settlement_date ? date('Y-m-d', strtotime($account->last_settlement_date)) : '-'; ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=coshare-members&user_id=' . $account->user_id); ?>" 
                               class="button button-small">查看詳情</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- 結算操作 -->
    <div class="coshare-section">
        <h2>結算操作</h2>
        <p class="description">系統會在每月1日凌晨2點自動發放積分。如需手動執行，請點擊下方按鈕。</p>
        <button type="button" class="button button-primary" id="manual-settle-points">
            立即發放積分
        </button>
        <p class="help-text">
            <strong>注意：</strong>此操作將把所有「已計算」狀態的獎勵轉換為積分並發放給用戶。
        </p>
    </div>
</div>

<style>
.coshare-stats-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.summary-item {
    background: white;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.summary-item .label {
    font-size: 13px;
    color: #666;
}

.summary-item .value {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
}

.points-available {
    color: #2ecc71;
    font-weight: bold;
}

.points-pending {
    color: #f39c12;
    font-weight: bold;
}

.points-used {
    color: #95a5a6;
}

.help-text {
    margin-top: 15px;
    padding: 10px;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    font-size: 13px;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#manual-settle-points').on('click', function() {
        if (!confirm('確定要立即發放積分嗎？此操作將處理所有待結算的獎勵。')) {
            return;
        }
        
        var button = $(this);
        button.prop('disabled', true).text('處理中...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'QiMall_manual_distribute_points',
                nonce: '<?php echo wp_create_nonce('QiMall_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || '積分發放完成');
                    location.reload();
                } else {
                    alert(response.data.message || '發放失敗');
                    button.prop('disabled', false).text('立即發放積分');
                }
            },
            error: function() {
                alert('發放失敗，請稍後再試');
                button.prop('disabled', false).text('立即發放積分');
            }
        });
    });
});
</script>

