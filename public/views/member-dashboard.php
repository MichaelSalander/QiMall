<?php
/**
 * 前台視圖 - 會員中心
 */

if (!defined('ABSPATH')) {
    exit;
}

// ===== 檢查登入狀態 =====
if (!is_user_logged_in()) {
    include dirname(__FILE__) . '/login-register-tabs.php';
    return;
}
// ===== 檢查結束 =====

$user_id = get_current_user_id();
$user_roles = QiMall_UserRole::get_user_roles($user_id);
$reward_stats = QiMall_Reward::get_statistics($user_id);
$team_stats = QiMall_Distribution_Calculator::get_team_statistics($user_id);

global $wpdb;
$points_table = $wpdb->prefix . 'QiMall_points';
$points = $wpdb->get_row($wpdb->prepare("SELECT * FROM $points_table WHERE user_id = %d", $user_id));
?>

<div class="qimall-member-dashboard">
    <!-- 會員資訊 -->
    <div class="qimall-member-header">
        <h2>會員中心</h2>
        <p>歡迎回來，<?php echo wp_get_current_user()->display_name; ?>！</p>
    </div>
    
    <!-- 積分餘額 -->
    <div class="qimall-points-section">
        <div class="points-card">
            <div class="points-icon">💰</div>
            <div class="points-content">
                <h3>可用積分</h3>
                <p class="points-amount"><?php echo $points ? number_format($points->available_points, 2) : '0.00'; ?></p>
            </div>
        </div>
        <div class="points-card">
            <div class="points-icon">⏳</div>
            <div class="points-content">
                <h3>待結算積分</h3>
                <p class="points-amount"><?php echo $points ? number_format($points->pending_points, 2) : '0.00'; ?></p>
            </div>
        </div>
        <div class="points-card">
            <div class="points-icon">📊</div>
            <div class="points-content">
                <h3>累計總積分</h3>
                <p class="points-amount"><?php echo $points ? number_format($points->total_points, 2) : '0.00'; ?></p>
            </div>
        </div>
    </div>
    
    <!-- 我的身份 -->
    <div class="qimall-roles-section">
        <h3>我的身份</h3>
        <?php if (empty($user_roles)): ?>
            <p>您尚未加入任何制度組</p>
        <?php else: ?>
            <div class="roles-grid">
                <?php foreach ($user_roles as $role): 
                    $group = QiMall_Group::get($role->group_code);
                ?>
                <div class="role-card <?php echo $role->status; ?>">
                    <div class="role-header">
                        <span class="role-badge <?php echo $role->role_type; ?>">
                            <?php echo $role->role_type === 'shareholder' ? '股東' : '合夥人'; ?>
                        </span>
                        <span class="role-status"><?php echo $role->status === 'active' ? '進行中' : '已完成'; ?></span>
                    </div>
                    <h4><?php echo esc_html($group->group_name); ?></h4>
                    <div class="role-stats">
                        <div class="stat-item">
                            <span class="label">購買金額</span>
                            <span class="value">NT$ <?php echo number_format($role->purchase_price, 2); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="label">已獲收益</span>
                            <span class="value">NT$ <?php echo number_format($role->total_earned, 2); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="label">已推薦</span>
                            <span class="value"><?php echo $role->referral_count; ?> 人</span>
                        </div>
                        <div class="stat-item">
                            <span class="label">回本進度</span>
                            <span class="value">
                                <?php 
                                $percentage = $role->purchase_price > 0 ? ($role->total_earned / $role->purchase_price) * 100 : 0;
                                echo number_format($percentage, 1) . '%';
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min($percentage, 100); ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- 收益統計 -->
    <div class="qimall-earnings-section">
        <h3>收益統計</h3>
        <div class="earnings-grid">
            <div class="earning-item">
                <span class="icon">💵</span>
                <div class="content">
                    <span class="label">商城分潤</span>
                    <span class="amount">NT$ <?php echo number_format($reward_stats['by_type']['profit'] ?? 0, 2); ?></span>
                </div>
            </div>
            <div class="earning-item">
                <span class="icon">📈</span>
                <div class="content">
                    <span class="label">團隊分紅</span>
                    <span class="amount">NT$ <?php echo number_format($reward_stats['by_type']['dividend'] ?? 0, 2); ?></span>
                </div>
            </div>
            <div class="earning-item">
                <span class="icon">🎁</span>
                <div class="content">
                    <span class="label">推薦回本</span>
                    <span class="amount">NT$ <?php echo number_format($reward_stats['by_type']['referral'] ?? 0, 2); ?></span>
                </div>
            </div>
            <div class="earning-item">
                <span class="icon">👥</span>
                <div class="content">
                    <span class="label">分銷收益</span>
                    <span class="amount">NT$ <?php echo number_format($reward_stats['by_type']['distribution'] ?? 0, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 團隊統計 -->
    <div class="qimall-team-section">
        <h3>我的團隊</h3>
        <div class="team-stats">
            <div class="team-stat">
                <span class="label">一級會員</span>
                <span class="value"><?php echo $team_stats['level_1_count']; ?> 人</span>
                <span class="earnings">收益: NT$ <?php echo number_format($team_stats['level_1_earnings'], 2); ?></span>
            </div>
            <div class="team-stat">
                <span class="label">二級會員</span>
                <span class="value"><?php echo $team_stats['level_2_count']; ?> 人</span>
                <span class="earnings">收益: NT$ <?php echo number_format($team_stats['level_2_earnings'], 2); ?></span>
            </div>
        </div>
    </div>
    
    <!-- 推薦連結 -->
    <div class="qimall-referral-section">
        <h3>推薦連結</h3>
        <div class="referral-box">
            <input type="text" 
                   value="<?php echo esc_url(add_query_arg('ref', $user_id, home_url())); ?>" 
                   readonly 
                   class="referral-input"
                   id="referral-link">
            <button type="button" class="button copy-button" data-target="#referral-link">
                📋 複製連結
            </button>
        </div>
        <p class="help-text">分享此連結給好友，當他們購買商品時，您將獲得分銷收益！</p>
    </div>
</div>

<style>
.qimall-member-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.qimall-member-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.qimall-member-header h2 {
    margin: 0 0 10px 0;
    color: white;
}

.qimall-points-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.points-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.points-icon {
    font-size: 40px;
}

.points-content h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #666;
}

.points-amount {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
    color: #2c3e50;
}

.qimall-roles-section,
.qimall-earnings-section,
.qimall-team-section,
.qimall-referral-section {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 20px;
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.role-card {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    background: #f9f9f9;
}

.role-card.active {
    border-color: #667eea;
    background: #f0f4ff;
}

.role-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}

.role-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.role-badge.shareholder {
    background: #667eea;
    color: white;
}

.role-badge.partner {
    background: #764ba2;
    color: white;
}

.role-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin: 15px 0;
}

.stat-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.stat-item .label {
    font-size: 12px;
    color: #666;
}

.stat-item .value {
    font-size: 16px;
    font-weight: bold;
    color: #2c3e50;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 15px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
}

.earnings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.earning-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.earning-item .icon {
    font-size: 30px;
}

.earning-item .content {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.earning-item .label {
    font-size: 12px;
    color: #666;
}

.earning-item .amount {
    font-size: 18px;
    font-weight: bold;
    color: #2c3e50;
}

.team-stats {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.team-stat {
    flex: 1;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.team-stat .label {
    font-size: 14px;
    color: #666;
}

.team-stat .value {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
}

.team-stat .earnings {
    font-size: 12px;
    color: #667eea;
}

.referral-box {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.referral-input {
    flex: 1;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.copy-button {
    padding: 12px 24px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

.copy-button:hover {
    background: #5568d3;
}

.help-text {
    margin-top: 10px;
    font-size: 13px;
    color: #666;
    font-style: italic;
}

@media (max-width: 768px) {
    .qimall-points-section,
    .roles-grid,
    .earnings-grid {
        grid-template-columns: 1fr;
    }
    
    .team-stats {
        flex-direction: column;
    }
    
    .referral-box {
        flex-direction: column;
    }
}
</style>


