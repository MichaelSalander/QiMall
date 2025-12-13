<?php
/**
 * 前台視圖 - 登入表單
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="qimall-login-form">
    <h2>登入會員中心</h2>
    
    <form method="post" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>">
        <p>
            <label for="user_login">用戶名或電子郵件</label>
            <input type="text" name="log" id="user_login" class="input" required>
        </p>
        
        <p>
            <label for="user_pass">密碼</label>
            <input type="password" name="pwd" id="user_pass" class="input" required>
        </p>
        
        <p class="remember-me">
            <label>
                <input type="checkbox" name="rememberme" value="forever">
                記住我
            </label>
        </p>
        
        <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink()); ?>">
        
        <p>
            <button type="submit" class="qimall-btn qimall-btn-primary">登入</button>
        </p>
    </form>
    
    <p class="forgot-password">
        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">忘記密碼?</a>
    </p>
</div>

<style>
.qimall-login-form {
    max-width: 400px;
    margin: 0 auto;
}

.qimall-login-form h2 {
    margin-top: 0;
    margin-bottom: 20px;
}

.qimall-login-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.qimall-login-form .input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.qimall-login-form .input:focus {
    outline: none;
    border-color: #2271b1;
    box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1);
}

.remember-me {
    font-size: 14px;
}

.remember-me label {
    font-weight: normal;
    display: inline;
}

.forgot-password {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
}

.forgot-password a {
    color: #2271b1;
    text-decoration: none;
}

.forgot-password a:hover {
    text-decoration: underline;
}
</style>

