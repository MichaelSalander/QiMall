<?php
/**
 * 前台視圖 - 收益查詢
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$reward_stats = QiMall_Reward::get_statistics($user_id);

// 獲取最近的獎勵記錄
$recent_rewards = QiMall_Reward::get_user_rewards($user_id, array(
    'limit' => 20,
    'offset' => 0
));
?>

<div class="qimall-earnings-page">
    <h2>我的收益</h2>
    
    <!-- 收益總覽 -->
    <div class="earnings-overview">
        <div class="overview-card">
            <div class="card-icon">💰</div>
            <div class="card-content">
                <h3>總收益</h3>
                <p class="amount">NT$ <?php echo number_format($reward_stats['total_calculated'] + $reward_stats['total_paid'], 2); ?></p>
            </div>
        </div>
        
        <div class="overview-card">
            <div class="card-icon">⏳</div>
            <div class="card-content">
                <h3>待結算</h3>
                <p class="amount">NT$ <?php echo number_format($reward_stats['total_pending'], 2); ?></p>
            </div>
        </div>
        
        <div class="overview-card">
            <div class="card-icon">✅</div>
            <div class="card-content">
                <h3>已發放</h3>
                <p class="amount">NT$ <?php echo number_format($reward_stats['total_paid'], 2); ?></p>
            </div>
        </div>
    </div>
    
    <!-- 各類收益統計 -->
    <div class="earnings-breakdown">
        <h3>收益明細</h3>
        <div class="breakdown-grid">
            <div class="breakdown-item">
                <div class="item-header">
                    <span class="icon">💵</span>
                    <span class="label">商城分潤</span>
                </div>
                <p class="item-amount">NT$ <?php echo number_format($reward_stats['by_type']['profit'] ?? 0, 2); ?></p>
                <p class="item-desc">股東專屬收益</p>
            </div>
            
            <div class="breakdown-item">
                <div class="item-header">
                    <span class="icon">📈</span>
                    <span class="label">團隊分紅</span>
                </div>
                <p class="item-amount">NT$ <?php echo number_format($reward_stats['by_type']['dividend'] ?? 0, 2); ?></p>
                <p class="item-desc">股東專屬收益</p>
            </div>
            
            <div class="breakdown-item">
                <div class="item-header">
                    <span class="icon">🎁</span>
                    <span class="label">推薦回本</span>
                </div>
                <p class="item-amount">NT$ <?php echo number_format($reward_stats['by_type']['referral'] ?? 0, 2); ?></p>
                <p class="item-desc">達成推薦目標獎勵</p>
            </div>
            
            <div class="breakdown-item">
                <div class="item-header">
                    <span class="icon">👥</span>
                    <span class="label">分銷收益</span>
                </div>
                <p class="item-amount">NT$ <?php echo number_format($reward_stats['by_type']['distribution'] ?? 0, 2); ?></p>
                <p class="item-desc">一級+二級分銷</p>
            </div>
        </div>
    </div>
    
    <!-- 最近收益記錄 -->
    <div class="earnings-history">
        <h3>最近收益記錄</h3>
        <?php if (empty($recent_rewards)): ?>
            <div class="empty-state">
                <p>暫無收益記錄</p>
            </div>
        <?php else: ?>
            <div class="history-table">
                <table>
                    <thead>
                        <tr>
                            <th>日期</th>
                            <th>類型</th>
                            <th>金額</th>
                            <th>狀態</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_rewards as $reward): 
                            $type_labels = array(
                                'profit' => '商城分潤',
                                'dividend' => '團隊分紅',
                                'referral' => '推薦回本',
                                'distribution' => '分銷收益'
                            );
                            $status_labels = array(
                                'pending' => '待結算',
                                'calculated' => '已計算',
                                'paid' => '已發放'
                            );
                        ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i', strtotime($reward->created_at)); ?></td>
                            <td>
                                <span class="reward-type <?php echo $reward->reward_type; ?>">
                                    <?php echo $type_labels[$reward->reward_type] ?? $reward->reward_type; ?>
                                </span>
                            </td>
                            <td class="amount-col">
                                <strong>NT$ <?php echo number_format($reward->amount, 2); ?></strong>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $reward->status; ?>">
                                    <?php echo $status_labels[$reward->status] ?? $reward->status; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.qimall-earnings-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.qimall-earnings-page h2 {
    margin-bottom: 30px;
    color: #2c3e50;
}

.qimall-earnings-page h3 {
    margin-bottom: 20px;
    color: #34495e;
    font-size: 20px;
}

.earnings-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.overview-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.card-icon {
    font-size: 50px;
}

.card-content h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
}

.card-content .amount {
    margin: 0;
    font-size: 28px;
    font-weight: bold;
    color: white;
}

.earnings-breakdown,
.earnings-history {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.breakdown-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.breakdown-item {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.item-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.item-header .icon {
    font-size: 24px;
}

.item-header .label {
    font-weight: 600;
    color: #2c3e50;
}

.item-amount {
    font-size: 24px;
    font-weight: bold;
    color: #667eea;
    margin: 10px 0;
}

.item-desc {
    font-size: 13px;
    color: #7f8c8d;
    margin: 0;
}

.history-table {
    overflow-x: auto;
}

.history-table table {
    width: 100%;
    border-collapse: collapse;
}

.history-table th,
.history-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #ecf0f1;
}

.history-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.history-table tr:hover {
    background: #f8f9fa;
}

.reward-type {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}

.reward-type.profit {
    background: #e3f2fd;
    color: #1976d2;
}

.reward-type.dividend {
    background: #f3e5f5;
    color: #7b1fa2;
}

.reward-type.referral {
    background: #fff3e0;
    color: #e65100;
}

.reward-type.distribution {
    background: #e0f2f1;
    color: #00695c;
}

.amount-col {
    font-weight: bold;
    color: #27ae60;
}

.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.pending {
    background: #fff3cd;
    color: #856404;
}

.status-badge.calculated {
    background: #cce5ff;
    color: #004085;
}

.status-badge.paid {
    background: #d4edda;
    color: #155724;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #95a5a6;
}

@media (max-width: 768px) {
    .earnings-overview,
    .breakdown-grid {
        grid-template-columns: 1fr;
    }
    
    .history-table {
        font-size: 14px;
    }
    
    .history-table th,
    .history-table td {
        padding: 10px;
    }
}
</style>


