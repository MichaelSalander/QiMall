<?php
/**
 * 管理後台 - 儀表板
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// 統計數據
$total_shareholders = $wpdb->get_var(
    "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}QiMall_user_roles 
    WHERE role_type = 'shareholder' AND status = 'active'"
);

$total_partners = $wpdb->get_var(
    "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}QiMall_user_roles 
    WHERE role_type = 'partner' AND status = 'active'"
);

$total_rewards_pending = $wpdb->get_var(
    "SELECT SUM(amount) FROM {$wpdb->prefix}QiMall_rewards WHERE status = 'pending'"
);

$total_rewards_paid = $wpdb->get_var(
    "SELECT SUM(amount) FROM {$wpdb->prefix}QiMall_rewards WHERE status = 'paid'"
);

$active_groups = QiMall_Group::get_all(true);
?>

<div class="wrap">
    <h1>消費股東系統 - 儀表板</h1>
    
    <div class="coshare-dashboard">
        <!-- 統計卡片 -->
        <div class="coshare-stats-grid">
            <div class="coshare-stat-card">
                <div class="stat-icon shareholder">📊</div>
                <div class="stat-content">
                    <h3>活躍股東</h3>
                    <p class="stat-number"><?php echo number_format($total_shareholders); ?></p>
                </div>
            </div>
            
            <div class="coshare-stat-card">
                <div class="stat-icon partner">🤝</div>
                <div class="stat-content">
                    <h3>活躍合夥人</h3>
                    <p class="stat-number"><?php echo number_format($total_partners); ?></p>
                </div>
            </div>
            
            <div class="coshare-stat-card">
                <div class="stat-icon pending">⏳</div>
                <div class="stat-content">
                    <h3>待結算獎勵</h3>
                    <p class="stat-number">NT$ <?php echo number_format($total_rewards_pending, 2); ?></p>
                </div>
            </div>
            
            <div class="coshare-stat-card">
                <div class="stat-icon paid">✅</div>
                <div class="stat-content">
                    <h3>已發放獎勵</h3>
                    <p class="stat-number">NT$ <?php echo number_format($total_rewards_paid, 2); ?></p>
                </div>
            </div>
        </div>
        
        <!-- 制度組狀態 -->
        <div class="coshare-section">
            <h2>制度組狀態</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>制度組</th>
                        <th>股東商品</th>
                        <th>合夥人商品</th>
                        <th>股東數量</th>
                        <th>合夥人數量</th>
                        <th>狀態</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_groups as $group): 
                        $sh_count = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}QiMall_user_roles 
                            WHERE group_code = %s AND role_type = 'shareholder' AND status = 'active'",
                            $group->group_code
                        ));
                        
                        $pt_count = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}QiMall_user_roles 
                            WHERE group_code = %s AND role_type = 'partner' AND status = 'active'",
                            $group->group_code
                        ));
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($group->group_name); ?></strong></td>
                        <td><?php echo esc_html($group->shareholder_product_name); ?></td>
                        <td><?php echo esc_html($group->partner_product_name); ?></td>
                        <td><?php echo number_format($sh_count); ?></td>
                        <td><?php echo number_format($pt_count); ?></td>
                        <td>
                            <?php if ($group->is_active): ?>
                                <span class="status-active">啟用</span>
                            <?php else: ?>
                                <span class="status-inactive">停用</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- 快速操作 -->
        <div class="coshare-section">
            <h2>快速操作</h2>
            <div class="coshare-quick-actions">
                <a href="<?php echo admin_url('admin.php?page=coshare-groups'); ?>" class="button button-primary">
                    管理制度組
                </a>
                <a href="<?php echo admin_url('admin.php?page=coshare-products'); ?>" class="button button-primary">
                    管理商品
                </a>
                <a href="<?php echo admin_url('admin.php?page=coshare-members'); ?>" class="button button-primary">
                    查看會員
                </a>
                <a href="<?php echo admin_url('admin.php?page=coshare-rewards'); ?>" class="button button-primary">
                    獎勵記錄
                </a>
                <button type="button" class="button" id="manual-distribute-points">
                    手動發放積分
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.coshare-dashboard {
    margin-top: 20px;
}

.coshare-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.coshare-stat-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.stat-icon {
    font-size: 40px;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #f5f5f5;
}

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #666;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
    color: #333;
}

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

.coshare-quick-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.status-active {
    color: #46b450;
    font-weight: bold;
}

.status-inactive {
    color: #dc3232;
    font-weight: bold;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#manual-distribute-points').on('click', function() {
        if (!confirm('確定要手動發放本月積分嗎？')) {
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
                alert(response.data.message || '積分發放完成');
                location.reload();
            },
            error: function() {
                alert('發放失敗，請稍後再試');
                button.prop('disabled', false).text('手動發放積分');
            }
        });
    });
});
</script>

