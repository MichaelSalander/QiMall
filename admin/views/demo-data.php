<?php
/**
 * 測試資料管理頁面
 * 
 * @package QiMall
 */

if (!defined('ABSPATH')) {
    exit;
}

// 處理表單提交
$message = '';
$message_type = '';

if (isset($_POST['generate_demo_data']) && check_admin_referer('QiMall_demo_data_action', 'QiMall_demo_data_nonce')) {
    require_once QiMall_PLUGIN_DIR . 'includes/class-demo-data.php';
    $result = QiMall_Demo_Data::generate_all();
    
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
    
    if ($result['success']) {
        $message .= '<br><br><strong>生成結果：</strong><br>';
        $message .= '制度組：' . count($result['data']['groups']) . ' 個<br>';
        $message .= '商品：' . count($result['data']['products']) . ' 個<br>';
        $message .= '商品綁定：' . count($result['data']['bindings']) . ' 個<br>';
        $message .= '測試用戶：' . count($result['data']['users']) . ' 個<br>';
        $message .= '推薦關係：' . count($result['data']['referrals']) . ' 個<br>';
        $message .= '測試訂單：' . count($result['data']['orders']) . ' 個<br>';
        $message .= '計算獎勵：' . count($result['data']['rewards']) . ' 個訂單<br>';
    }
}

if (isset($_POST['clear_demo_data']) && check_admin_referer('QiMall_demo_data_action', 'QiMall_demo_data_nonce')) {
    require_once QiMall_PLUGIN_DIR . 'includes/class-demo-data.php';
    $result = QiMall_Demo_Data::clear_all();
    
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
    
    if ($result['success']) {
        $message .= '<br><br><strong>清除結果：</strong><br>';
        $message .= '訂單：' . $result['data']['orders_deleted'] . ' 個<br>';
        $message .= '商品：' . $result['data']['products_deleted'] . ' 個<br>';
        $message .= '用戶：' . $result['data']['users_deleted'] . ' 個<br>';
    }
}
?>

<div class="wrap">
    <h1>🧪 測試資料生成器</h1>
    
    <?php if ($message): ?>
        <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
            <p><?php echo wp_kses_post($message); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="card" style="max-width: 800px;">
        <h2>⚡ 一鍵生成測試資料</h2>
        <p>節省您的時間！點擊下方按鈕，系統將自動建立：</p>
        
        <ul style="line-height: 2;">
            <li>✅ <strong>2個制度組</strong>（group_01、group_02）</li>
            <li>✅ <strong>5個測試商品</strong>（2個股東商品、2個合夥人商品、1個一般商品）</li>
            <li>✅ <strong>商品綁定</strong>（自動綁定商品到制度組）</li>
            <li>✅ <strong>5個測試用戶</strong>（test_user_01 ~ 05，密碼：test123456）</li>
            <li>✅ <strong>推薦關係</strong>（建立多層推薦網路）</li>
            <li>✅ <strong>5個測試訂單</strong>（已完成狀態）</li>
            <li>✅ <strong>自動計算獎勵</strong>（所有收益類型）</li>
        </ul>
        
        <form method="post" style="margin-top: 20px;">
            <?php wp_nonce_field('QiMall_demo_data_action', 'QiMall_demo_data_nonce'); ?>
            
            <p>
                <button type="submit" name="generate_demo_data" class="button button-primary button-hero">
                    🚀 一鍵生成所有測試資料
                </button>
            </p>
            
            <p class="description">
                ⏱️ <strong>只需 5 秒鐘</strong>，即可完成所有測試準備！<br>
                💡 如果資料已存在，系統會跳過重複建立。
            </p>
        </form>
    </div>
    
    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>📋 生成後您可以測試：</h2>
        
        <table class="widefat" style="margin-top: 15px;">
            <thead>
                <tr>
                    <th>測試項目</th>
                    <th>操作說明</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>1. 查看會員資料</strong></td>
                    <td>前往「會員管理」查看 5 個測試用戶的身份</td>
                </tr>
                <tr>
                    <td><strong>2. 查看獎勵記錄</strong></td>
                    <td>前往「獎勵記錄」查看自動計算的收益</td>
                </tr>
                <tr>
                    <td><strong>3. 查看推薦關係</strong></td>
                    <td>使用測試用戶登入，前往「會員中心」查看團隊</td>
                </tr>
                <tr>
                    <td><strong>4. 測試積分發放</strong></td>
                    <td>前往「積分管理」點擊「立即發放積分」</td>
                </tr>
                <tr>
                    <td><strong>5. 前台測試</strong></td>
                    <td>登入測試用戶，前往自動建立的頁面查看</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>🗑️ 清除測試資料</h2>
        <p>測試完成後，可以一鍵清除所有測試資料：</p>
        
        <form method="post" style="margin-top: 15px;" onsubmit="return confirm('確定要清除所有測試資料嗎？此操作無法復原！');">
            <?php wp_nonce_field('QiMall_demo_data_action', 'QiMall_demo_data_nonce'); ?>
            
            <p>
                <button type="submit" name="clear_demo_data" class="button button-secondary">
                    🗑️ 清除所有測試資料
                </button>
            </p>
            
            <p class="description" style="color: #d63638;">
                ⚠️ <strong>警告：</strong>此操作將刪除所有測試用戶、商品、訂單和獎勵記錄！
            </p>
        </form>
    </div>
    
    <div class="card" style="max-width: 800px; margin-top: 20px; background: #f0f6fc; border-left: 4px solid #0073aa;">
        <h2>💡 使用提示</h2>
        
        <h3>測試用戶登入資訊</h3>
        <table class="widefat">
            <thead>
                <tr>
                    <th>用戶名</th>
                    <th>密碼</th>
                    <th>顯示名稱</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>test_user_01</code></td>
                    <td><code>test123456</code></td>
                    <td>測試用戶A（股東）</td>
                </tr>
                <tr>
                    <td><code>test_user_02</code></td>
                    <td><code>test123456</code></td>
                    <td>測試用戶B（合夥人）</td>
                </tr>
                <tr>
                    <td><code>test_user_03</code></td>
                    <td><code>test123456</code></td>
                    <td>測試用戶C（股東）</td>
                </tr>
                <tr>
                    <td><code>test_user_04</code></td>
                    <td><code>test123456</code></td>
                    <td>測試用戶D（一般會員）</td>
                </tr>
                <tr>
                    <td><code>test_user_05</code></td>
                    <td><code>test123456</code></td>
                    <td>測試用戶E（一般會員）</td>
                </tr>
            </tbody>
        </table>
        
        <h3 style="margin-top: 20px;">推薦關係結構</h3>
        <pre style="background: white; padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
測試用戶A (股東)
├── 推薦了 → 測試用戶B (合夥人)
│   └── 推薦了 → 測試用戶D
└── 推薦了 → 測試用戶C (股東)
    └── 推薦了 → 測試用戶E
        </pre>
        
        <h3 style="margin-top: 20px;">快速測試流程</h3>
        <ol style="line-height: 2;">
            <li>點擊「一鍵生成所有測試資料」</li>
            <li>前往「獎勵記錄」查看自動計算的收益</li>
            <li>前往「積分管理」點擊「立即發放積分」</li>
            <li>登入測試用戶查看前台頁面</li>
            <li>測試完成後點擊「清除所有測試資料」</li>
        </ol>
    </div>
</div>

<style>
    .card h2 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
    }
    
    .card h3 {
        margin-top: 15px;
        margin-bottom: 10px;
        color: #23282d;
    }
    
    .card ul {
        margin-left: 20px;
    }
    
    .button-hero {
        padding: 10px 30px !important;
        height: auto !important;
        font-size: 16px !important;
    }
</style>

