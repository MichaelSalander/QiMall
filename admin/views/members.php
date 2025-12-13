<?php
/**
 * 管理後台 - 會員管理
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// 獲取所有用戶角色
$user_roles_table = $wpdb->prefix . 'QiMall_user_roles';
$members = $wpdb->get_results("
    SELECT ur.*, u.display_name, u.user_email 
    FROM $user_roles_table ur
    LEFT JOIN {$wpdb->users} u ON ur.user_id = u.ID
    ORDER BY ur.created_at DESC
");
?>

<div class="wrap">
    <h1>會員管理</h1>
    
    <div class="coshare-section">
        <h2>所有會員</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>會員</th>
                    <th>制度組</th>
                    <th>角色</th>
                    <th>購買金額</th>
                    <th>已獲收益</th>
                    <th>已推薦</th>
                    <th>回本進度</th>
                    <th>狀態</th>
                    <th>加入日期</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="9">尚無會員資料</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($members as $member): 
                        $progress = $member->purchase_price > 0 ? ($member->total_earned / $member->purchase_price) * 100 : 0;
                        $group = QiMall_Group::get($member->group_code);
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($member->display_name); ?></strong><br>
                            <small><?php echo esc_html($member->user_email); ?></small>
                        </td>
                        <td><?php echo $group ? esc_html($group->group_name) : $member->group_code; ?></td>
                        <td>
                            <span class="role-badge <?php echo $member->role_type; ?>">
                                <?php echo $member->role_type === 'shareholder' ? '股東' : '合夥人'; ?>
                            </span>
                        </td>
                        <td>NT$ <?php echo number_format($member->purchase_price, 2); ?></td>
                        <td>NT$ <?php echo number_format($member->total_earned, 2); ?></td>
                        <td><?php echo $member->referral_count; ?> 人</td>
                        <td>
                            <div class="progress-container">
                                <div class="progress-bar-mini" style="width: <?php echo min($progress, 100); ?>%"></div>
                            </div>
                            <small><?php echo number_format($progress, 1); ?>%</small>
                        </td>
                        <td>
                            <?php if ($member->status === 'active'): ?>
                                <span class="status-active">進行中</span>
                            <?php elseif ($member->status === 'completed'): ?>
                                <span class="status-completed">已完成</span>
                            <?php else: ?>
                                <span class="status-cancelled">已取消</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($member->created_at)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.role-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    display: inline-block;
}

.role-badge.shareholder {
    background: #667eea;
    color: white;
}

.role-badge.partner {
    background: #764ba2;
    color: white;
}

.progress-container {
    width: 100px;
    height: 10px;
    background: #e0e0e0;
    border-radius: 5px;
    overflow: hidden;
    margin-bottom: 3px;
}

.progress-bar-mini {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
}

.status-completed {
    color: #2ecc71;
    font-weight: bold;
}

.status-cancelled {
    color: #e74c3c;
    font-weight: bold;
}
</style>

