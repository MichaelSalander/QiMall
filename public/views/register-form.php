<?php
/**
 * 前台視圖 - 註冊表單
 */

if (!defined('ABSPATH')) {
    exit;
}

// 獲取推薦人資訊 (從Cookie或URL)
$referrer_info = null;
$referral_code = '';

// 優先檢查Cookie
if (isset($_COOKIE['QiMall_referral_code'])) {
    $referral_code = sanitize_text_field($_COOKIE['QiMall_referral_code']);
    $referrer_id = QiMall_Referral::get_user_id_by_code($referral_code);
    
    if ($referrer_id) {
        $referrer_info = QiMall_Referral::get_referrer_info($referrer_id);
    }
}
?>

<div class="qimall-register-form">
    <?php if ($referrer_info): ?>
    <div class="referrer-info">
        <div class="referrer-icon">🎁</div>
        <div class="referrer-content">
            <h3>歡迎加入!</h3>
            <p>您的推薦人是: <strong class="referrer-name" id="referrer-name"><?php echo esc_html($referrer_info['name']); ?></strong></p>
        </div>
    </div>
    <?php endif; ?>
    
    <h2>註冊新帳號</h2>
    
    <form method="post" action="<?php echo esc_url(site_url('wp-login.php?action=register', 'login_post')); ?>" id="qimall-register-form">
        <p>
            <label for="user_login">用戶名 <span class="required">*</span></label>
            <input type="text" name="user_login" id="user_login" class="input" required>
        </p>
        
        <p>
            <label for="user_email">電子郵件 <span class="required">*</span></label>
            <input type="email" name="user_email" id="user_email" class="input" required>
        </p>
        
        <p>
            <label for="QiMall_referral_code">推薦代碼 <span class="optional">(選填)</span></label>
            <input type="text" 
                   name="QiMall_referral_code" 
                   id="QiMall_referral_code" 
                   class="input" 
                   value="<?php echo esc_attr($referral_code); ?>"
                   placeholder="例如: ZHANG123">
            <span class="referral-feedback" id="referral-feedback"></span>
            <span class="help-text">如有推薦人請輸入推薦代碼,留空則由總店推薦</span>
        </p>
        
        <p class="info-text">
            密碼將自動發送到您的電子郵件
        </p>
        
        <p>
            <button type="submit" class="qimall-btn qimall-btn-primary">註冊</button>
        </p>
    </form>
    
    <p class="login-link">
        已有帳號? <a href="#" onclick="showLoginForm(); return false;">立即登入</a>
    </p>
</div>

<style>
.qimall-register-form {
    max-width: 450px;
    margin: 0 auto;
    padding: 30px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.referrer-info {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.referrer-icon {
    font-size: 36px;
    margin-right: 15px;
}

.referrer-content h3 {
    margin: 0 0 5px 0;
    color: white;
    font-size: 18px;
}

.referrer-content p {
    margin: 0;
    font-size: 14px;
}

.referrer-name {
    color: #ffd700;
    font-size: 16px;
}

.qimall-register-form h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
}

.qimall-register-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #555;
}

.qimall-register-form .required {
    color: #dc3545;
}

.qimall-register-form .optional {
    color: #6c757d;
    font-weight: normal;
    font-size: 14px;
}

.qimall-register-form .input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.qimall-register-form .input:focus {
    outline: none;
    border-color: #2271b1;
    box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1);
}

.referral-feedback {
    display: block;
    margin-top: 5px;
    font-size: 14px;
    min-height: 20px;
}

.referral-feedback.success {
    color: #28a745;
}

.referral-feedback.success::before {
    content: "✓ ";
}

.referral-feedback.error {
    color: #dc3545;
}

.referral-feedback.error::before {
    content: "✗ ";
}

.help-text {
    display: block;
    margin-top: 5px;
    font-size: 13px;
    color: #6c757d;
}

.info-text {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    font-size: 14px;
    color: #6c757d;
    margin: 15px 0;
}

.qimall-btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    transition: all 0.3s;
}

.qimall-btn-primary {
    background: #2271b1;
    color: white;
}

.qimall-btn-primary:hover {
    background: #135e96;
}

.login-link {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
}

.login-link a {
    color: #2271b1;
    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}
</style>

<script>
jQuery(document).ready(function($) {
    var timeout = null;
    
    $('#QiMall_referral_code').on('input', function() {
        clearTimeout(timeout);
        var code = $(this).val().trim();
        var $feedback = $('#referral-feedback');
        var $referrerName = $('#referrer-name');
        
        if (code === '') {
            $feedback.removeClass('success error').text('');
            return;
        }
        
        // 延遲0.5秒後驗證
        timeout = setTimeout(function() {
            $feedback.removeClass('success error').text('驗證中...');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'QiMall_verify_referral_code',
                    code: code
                },
                success: function(response) {
                    if (response.success) {
                        $feedback.removeClass('error').addClass('success')
                            .text('您的推薦人是: ' + response.data.name);
                        
                        // 更新推薦人名稱顯示
                        if ($referrerName.length) {
                            $referrerName.text(response.data.name);
                        }
                    } else {
                        $feedback.removeClass('success').addClass('error')
                            .text(response.data.message);
                    }
                },
                error: function() {
                    $feedback.removeClass('success').addClass('error')
                        .text('驗證失敗,請稍後再試');
                }
            });
        }, 500);
    });
    
    // 初始驗證(如果有預填值)
    if ($('#QiMall_referral_code').val().trim() !== '') {
        $('#QiMall_referral_code').trigger('input');
    }
});
</script>


