<?php
if (!defined('ABSPATH')) { exit; }
?>
<div class="qimall-client-login">
  <div class="login-card">
    <h2>客戶登入</h2>
    <?php
      // 顯示 WP 登入表單（但以本站樣式包起來）
      wp_login_form(array(
        'redirect' => home_url('/client-admin'),
        'label_username' => '帳號',
        'label_password' => '密碼',
        'label_log_in'  => '登入',
        'remember' => true,
      ));
    ?>
    <p class="hint">如需協助，請聯絡系統管理員。</p>
  </div>
</div>
<style>
.qimall-client-login{display:flex;justify-content:center;align-items:center;min-height:60vh;background:#f7f7fb}
.qimall-client-login .login-card{background:#fff;padding:30px;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,0.08);min-width:320px}
.qimall-client-login h2{margin-top:0}
.qimall-client-login .hint{color:#888}
</style>

