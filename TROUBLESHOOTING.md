# 🚨 緊急修復：PHP 執行超時

## 問題原因
WooCommerce 語言檔載入或插件初始化造成 PHP 執行超時 (120秒)

## ⚡ 快速修復方案

### 方案 1: 增加 PHP 執行時間限制（推薦）

編輯 `wp-config.php`，在 `/* That's all, stop editing! */` **之前**添加：

```php
// 增加 PHP 執行時間限制
set_time_limit(300);  // 設為 5 分鐘
ini_set('max_execution_time', 300);

// 增加記憶體限制
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

### 方案 2: 修改 XAMPP PHP 設定

編輯 `C:\xampp\php\php.ini`:

找到並修改以下行：
```ini
max_execution_time = 300    ; 從 120 改為 300
memory_limit = 256M         ; 從 128M 改為 256M
```

修改後重啟 Apache。

### 方案 3: 暫時停用 WooCommerce 語言包

如果是 WooCommerce 語言檔問題，暫時停用：

```php
// 在 wp-config.php 添加
define('WPLANG', '');  // 使用英文版
```

或重新命名語言檔：
```
C:\xampp\htdocs\wordpress\wp-content\languages\plugins\woocommerce-zh_TW.l10n.php
改名為
woocommerce-zh_TW.l10n.php.bak
```

### 方案 4: 停用客戶模式（如果剛啟用）

如果剛啟用客戶模式後出現問題，在 `wp-config.php` 中註解掉：

```php
// define('QiMall_CLIENT_MODE', true);  // 暫時停用
```

---

## 🔍 診斷步驟

### 1. 檢查 PHP 錯誤日誌

查看 `C:\xampp\php\logs\php_error_log`

### 2. 啟用 WordPress 除錯模式

編輯 `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

查看日誌：`wp-content/debug.log`

### 3. 測試停用插件

逐一停用插件測試：
1. 重新命名 `wp-content/plugins/QiMall` 為 `QiMall.bak`
2. 重新命名 `wp-content/plugins/woocommerce` 為 `woocommerce.bak`
3. 看哪個導致問題

---

## 🛠️ 永久解決方案

### 優化 QiMall System 初始化

已在 `QiMall.php` 添加類別存在檢查：

```php
public function init() {
    // 檢查類別是否存在再初始化
    if (class_exists('QiMall_Referral')) {
        QiMall_Referral::init();
    }
    
    if (is_admin() && class_exists('QiMall_Client_Dashboard') 
        && defined('QiMall_CLIENT_MODE') && QiMall_CLIENT_MODE === true) {
        QiMall_Client_Dashboard::init();
    }
}
```

### 延遲載入客戶模式

在 `wp-config.php` 中使用條件載入：

```php
// 只在特定用戶啟用客戶模式
if (is_admin() && isset($_SERVER['REMOTE_USER']) && $_SERVER['REMOTE_USER'] === 'client_username') {
    define('QiMall_CLIENT_MODE', true);
}
```

---

## ✅ 建議操作順序

1. **立即**: 增加 `wp-config.php` 執行時間限制
2. **重啟**: Apache 服務
3. **測試**: 重新訪問網站
4. **如果還有問題**: 暫時停用 WooCommerce 語言包
5. **如果仍有問題**: 停用 QiMall System，檢查其他插件

---

## 📞 如果問題持續

1. 檢查 `php_error_log` 找出具體錯誤
2. 確認 XAMPP 版本 (建議使用最新版)
3. 確認 PHP 版本 >= 7.4
4. 檢查是否有插件衝突

---

## 🔄 恢復正常後

重新啟用插件順序：
1. WooCommerce (不含語言包)
2. QiMall System (不啟用客戶模式)
3. 測試正常後再啟用客戶模式
4. 最後安裝語言包

---

立即執行**方案 1**，應該就能解決問題！

