<?php
/**
 * 前台視圖 - 登入/註冊Tab容器
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="qimall-auth-container">
    <div class="qimall-auth-tabs">
        <button class="tab-btn active" onclick="switchTab(event, 'login')">登入</button>
        <button class="tab-btn" onclick="switchTab(event, 'register')">註冊</button>
    </div>
    
    <!-- 登入表單 -->
    <div id="login-tab" class="tab-content active">
        <?php include 'login-form.php'; ?>
    </div>
    
    <!-- 註冊表單 -->
    <div id="register-tab" class="tab-content">
        <?php include 'register-form.php'; ?>
    </div>
</div>

<script>
function switchTab(event, tab) {
    // 切換按鈕狀態
    var buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(function(btn) {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // 切換內容
    var contents = document.querySelectorAll('.tab-content');
    contents.forEach(function(content) {
        content.classList.remove('active');
    });
    document.getElementById(tab + '-tab').classList.add('active');
}

function showLoginForm() {
    var loginBtn = document.querySelector('.tab-btn:first-child');
    if (loginBtn) {
        loginBtn.click();
    }
}
</script>

<style>
.qimall-auth-container {
    max-width: 550px;
    margin: 50px auto;
    padding: 20px;
}

.qimall-auth-tabs {
    display: flex;
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 30px;
}

.tab-btn {
    flex: 1;
    padding: 15px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    color: #666;
    transition: all 0.3s;
    position: relative;
}

.tab-btn:hover {
    color: #2271b1;
}

.tab-btn.active {
    color: #2271b1;
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 3px;
    background: #2271b1;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

