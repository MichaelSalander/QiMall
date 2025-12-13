# 客戶模式配置指南

## 🎯 啟用客戶專用管理介面

### 方式一：修改 wp-config.php（推薦）

在 `wp-config.php` 文件中添加以下常數定義：

```php
/**
 * 啟用 Co-Share System 客戶模式
 * 啟用後將顯示簡化的管理介面，隱藏複雜的技術功能
 */
define('QiMall_CLIENT_MODE', true);
```

**位置**: 在 `/* That's all, stop editing! Happy publishing. */` 這行之前添加

### 方式二：在主題 functions.php 中添加

```php
// 啟用消費股東系統客戶模式
add_action('after_setup_theme', function() {
    if (!defined('QiMall_CLIENT_MODE')) {
        define('QiMall_CLIENT_MODE', true);
    }
});
```

---

## ✨ 客戶模式功能

### 啟用後的變化

#### 1. 新增獨立選單
- 📊 總覽 - 簡潔的統計儀表板
- 👥 會員管理 - 查看所有股東與合夥人
- 💰 收益記錄 - 查看所有獎勵明細
- ⚙️ 系統設定 - 簡化的系統設定

#### 2. 隱藏複雜功能
- ❌ WordPress 文章管理
- ❌ 媒體庫
- ❌ 頁面管理
- ❌ 留言管理
- ❌ 外觀主題
- ❌ 外掛管理
- ❌ 工具選單
- ❌ WooCommerce 複雜設定

#### 3. 保留必要功能
- ✅ 訂單管理（簡化版）
- ✅ 基本設定
- ✅ 消費股東管理

#### 4. 優化體驗
- 隱藏更新通知
- 清理管理列
- 現代化界面設計
- 卡片式統計展示

---

## 🖥️ 客戶模式截圖預覽

### 總覽頁面
```
┌─────────────────────────────────────┐
│ 📊 消費股東系統總覽                  │
├─────────────────────────────────────┤
│                                     │
│  👋 歡迎回來！                      │
│  這是您的消費股東系統管理中心        │
│                                     │
│  ┌──────┐  ┌──────┐  ┌──────┐      │
│  │ 總會員 │  │ 總訂單 │  │ 已發放 │   │
│  │  123  │  │  456  │  │ 78,900│   │
│  └──────┘  └──────┘  └──────┘      │
│                                     │
│  ⚡ 快速操作                         │
│  [查看會員] [收益記錄] [系統設定]    │
│                                     │
└─────────────────────────────────────┘
```

### 會員管理
```
┌─────────────────────────────────────┐
│ 👥 會員管理                          │
├─────────────────────────────────────┤
│ [所有角色 ▼] [搜尋...] [🔍 搜尋]    │
├─────────────────────────────────────┤
│ 會員  │ 角色  │ 推薦人數 │ 累計收益 │
│ 張三  │ 股東  │ 5人     │ $12,000 │
│ 李四  │合夥人 │ 3人     │ $8,500  │
└─────────────────────────────────────┘
```

---

## 🔧 自訂配置

### 調整隱藏的選單

編輯 `includes/class-client-dashboard.php`:

```php
public static function remove_unnecessary_menus() {
    if (defined('QiMall_CLIENT_MODE') && QiMall_CLIENT_MODE === true) {
        // 取消註解以保留某些功能
        // remove_menu_page('upload.php');  // 保留媒體庫
        // remove_menu_page('users.php');   // 保留用戶管理
    }
}
```

### 自訂客戶權限

可以搭配 WordPress 角色系統，創建專門的「客戶」角色：

```php
// 在主題 functions.php 中
add_action('init', function() {
    add_role('QiMall_client', '消費股東客戶', array(
        'read' => true,
        'manage_options' => true,  // 允許訪問設定
        'manage_woocommerce' => true,  // 允許管理 WooCommerce
    ));
});
```

---

## 📱 響應式設計

客戶模式界面採用響應式設計，支援：
- 💻 桌面電腦 (1920px+)
- 💻 筆記型電腦 (1366px+)
- 📱 平板電腦 (768px+)
- 📱 手機 (320px+)

---

## 🎨 自訂樣式

如需自訂界面顏色和樣式，可編輯：
- `admin/css/client-dashboard.css` (需新建)

或在主題中覆蓋樣式：

```css
/* 在主題 style.css 或 custom.css 中 */
.QiMall-client-wrap {
    --primary-color: #your-color;
    --secondary-color: #your-color;
}
```

---

## ⚠️ 注意事項

1. **備份重要**: 啟用前請備份網站
2. **測試環境**: 建議先在測試環境試用
3. **權限管理**: 確保只給信任的用戶管理員權限
4. **停用方式**: 刪除 `wp-config.php` 中的 `QiMall_CLIENT_MODE` 定義即可恢復原樣

---

## 🆘 故障排除

### 問題：啟用後看不到新選單
**解決**:
1. 確認 `wp-config.php` 中已添加常數
2. 清除瀏覽器緩存
3. 登出後重新登入

### 問題：某些選單仍然顯示
**解決**:
編輯 `includes/class-client-dashboard.php` 的 `remove_unnecessary_menus()` 方法

### 問題：想恢復原始介面
**解決**:
刪除或註解掉 `wp-config.php` 中的這行：
```php
// define('QiMall_CLIENT_MODE', true);  // 註解掉即可停用
```

---

## 📧 技術支援

如有問題，請參考：
- [完整文件](README.md)
- [常見問題](FAQ.md)
- [開發者文件](IA.md)

