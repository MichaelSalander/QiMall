<?php
/**
 * 管理後台 - 系統設定
 */

if (!defined('ABSPATH')) {
    exit;
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['QiMall_settings_submit'])) {
    check_admin_referer('QiMall_settings_action');
    
    // 保存分銷設定
    $distribution_settings = array(
        'level_1_rate' => floatval($_POST['level_1_rate']),
        'level_2_rate' => floatval($_POST['level_2_rate'])
    );
    update_option('QiMall_distribution_settings', $distribution_settings);
    
    // 保存一般設定
    $general_settings = array(
        'enable_auto_calculation' => isset($_POST['enable_auto_calculation']) ? 1 : 0,
        'enable_monthly_distribution' => isset($_POST['enable_monthly_distribution']) ? 1 : 0
    );
    update_option('QiMall_general_settings', $general_settings);
    
    echo '<div class="notice notice-success"><p>設定已儲存</p></div>';
}

$distribution_settings = get_option('QiMall_distribution_settings', array(
    'level_1_rate' => 10,
    'level_2_rate' => 5
));

$general_settings = get_option('QiMall_general_settings', array(
    'enable_auto_calculation' => 1,
    'enable_monthly_distribution' => 1
));
?>

<div class="wrap">
    <h1>系統設定</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('QiMall_settings_action'); ?>
        <input type="hidden" name="QiMall_settings_submit" value="1">
        
        <!-- 分銷設定 -->
        <div class="coshare-section">
            <h2>二級分銷設定</h2>
            <table class="form-table">
                <tr>
                    <th><label for="level_1_rate">一級分銷比例 (%)</label></th>
                    <td>
                        <input type="number" id="level_1_rate" name="level_1_rate" 
                               step="0.01" min="0" max="100" 
                               value="<?php echo esc_attr($distribution_settings['level_1_rate']); ?>">
                        <p class="description">直推會員購買時，推薦人可獲得的分潤比例</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="level_2_rate">二級分銷比例 (%)</label></th>
                    <td>
                        <input type="number" id="level_2_rate" name="level_2_rate" 
                               step="0.01" min="0" max="100" 
                               value="<?php echo esc_attr($distribution_settings['level_2_rate']); ?>">
                        <p class="description">二級會員購買時，二級推薦人可獲得的分潤比例</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- 一般設定 -->
        <div class="coshare-section">
            <h2>一般設定</h2>
            <table class="form-table">
                <tr>
                    <th>自動計算獎勵</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_auto_calculation" value="1" 
                                   <?php checked($general_settings['enable_auto_calculation'], 1); ?>>
                            啟用每日自動計算獎勵
                        </label>
                        <p class="description">系統將於每日凌晨3點自動計算前一天的獎勵</p>
                    </td>
                </tr>
                <tr>
                    <th>每月積分發放</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_monthly_distribution" value="1" 
                                   <?php checked($general_settings['enable_monthly_distribution'], 1); ?>>
                            啟用每月自動發放積分
                        </label>
                        <p class="description">系統將於每月1日凌晨2點自動發放上月累積的積分</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <p class="submit">
            <input type="submit" class="button button-primary" value="儲存設定">
        </p>
    </form>
    
    <!-- 系統資訊 -->
    <div class="coshare-section">
        <h2>系統資訊</h2>
        <table class="form-table">
            <tr>
                <th>外掛版本</th>
                <td><?php echo QiMall_VERSION; ?></td>
            </tr>
            <tr>
                <th>資料庫版本</th>
                <td><?php echo get_option('QiMall_version', 'N/A'); ?></td>
            </tr>
            <tr>
                <th>啟用時間</th>
                <td><?php echo get_option('QiMall_activated_time', 'N/A'); ?></td>
            </tr>
            <tr>
                <th>下次積分發放</th>
                <td>
                    <?php 
                    $next_run = wp_next_scheduled('QiMall_monthly_points_distribution');
                    echo $next_run ? date('Y-m-d H:i:s', $next_run) : '未排程';
                    ?>
                </td>
            </tr>
        </table>
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
</style>

