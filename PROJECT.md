# Co-Share System - 專案結構

## 📁 檔案結構

```
QiMall/
├── QiMall.php           # 主外掛檔案
├── README.md                   # 專案說明文件
├── INSTALL.md                  # 安裝使用指南
├── 消費股東.docx               # 原始需求文件
│
├── includes/                   # 核心功能類別
│   ├── class-database.php            # 資料庫管理
│   ├── class-group.php               # 制度組管理
│   ├── class-user-role.php           # 用戶角色管理
│   ├── class-product.php             # 商品管理
│   ├── class-reward.php              # 獎勵管理
│   ├── class-transaction.php         # 交易記錄
│   ├── class-reward-calculator.php   # 獎勵計算引擎
│   ├── class-distribution-calculator.php  # 分銷計算
│   ├── class-cron.php                # 定時任務
│   └── ajax-handlers.php             # AJAX處理
│
├── admin/                      # 後台管理介面
│   ├── class-admin.php               # 管理介面主類別
│   ├── views/                        # 後台視圖
│   │   ├── dashboard.php            # 儀表板
│   │   ├── groups.php               # 制度組管理
│   │   ├── products.php             # 商品管理
│   │   ├── members.php              # 會員管理
│   │   ├── rewards.php              # 獎勵記錄
│   │   ├── points.php               # 積分管理
│   │   └── settings.php             # 系統設定
│   ├── css/
│   │   └── admin.css                # 後台樣式
│   └── js/
│       └── admin.js                 # 後台腳本
│
└── public/                     # 前台公開介面
    ├── class-public.php              # 前台主類別
    ├── views/                        # 前台視圖
    │   ├── member-dashboard.php     # 會員中心
    │   └── earnings.php             # 收益查詢
    ├── css/
    │   └── public.css               # 前台樣式
    └── js/
        └── public.js                # 前台腳本
```

## 🗄️ 資料庫結構

### 1. wp_QiMall_groups（制度組表）
- **用途**：存儲制度組配置
- **關鍵欄位**：
  - `group_code` - 制度組代號（唯一）
  - `shareholder_profit_rate` - 股東分潤比例
  - `shareholder_dividend_rate` - 股東分紅比例
  - `shareholder_referral_count` - 股東回本人數
  - `partner_referral_count` - 合夥人回本人數

### 2. wp_QiMall_products（商品表）
- **用途**：WooCommerce商品與制度組綁定
- **關鍵欄位**：
  - `product_id` - WooCommerce商品ID
  - `group_code` - 所屬制度組
  - `plan_type` - 商品類型（shareholder/partner/common）

### 3. wp_QiMall_user_roles（用戶角色表）
- **用途**：記錄用戶在各制度組的角色
- **關鍵欄位**：
  - `user_id` + `group_code` + `role_type` - 組合唯一鍵
  - `total_earned` - 累計收益
  - `referral_count` - 已推薦人數
  - `status` - 狀態（active/completed/cancelled）

### 4. wp_QiMall_rewards（獎勵記錄表）
- **用途**：記錄所有獎勵明細
- **關鍵欄位**：
  - `reward_type` - 獎勵類型（profit/dividend/referral/distribution）
  - `amount` - 獎勵金額
  - `status` - 狀態（pending/calculated/paid）

### 5. wp_QiMall_transactions（交易記錄表）
- **用途**：記錄所有積分交易
- **關鍵欄位**：
  - `transaction_type` - 交易類型
  - `balance_before` - 交易前餘額
  - `balance_after` - 交易後餘額

### 6. wp_QiMall_referrals（推薦關係表）
- **用途**：記錄推薦關係
- **關鍵欄位**：
  - `referrer_id` - 推薦人
  - `referee_id` - 被推薦人
  - `is_counted` - 是否已計入回本

### 7. wp_QiMall_points（積分帳戶表）
- **用途**：用戶積分帳戶
- **關鍵欄位**：
  - `total_points` - 總積分
  - `available_points` - 可用積分
  - `pending_points` - 待結算積分

### 8. wp_QiMall_distribution（分銷關係表）
- **用途**：二級分銷網路
- **關鍵欄位**：
  - `parent_id` - 一級推薦人
  - `grandparent_id` - 二級推薦人
  - `level_1_rate` / `level_2_rate` - 分銷比例

## 🔧 核心功能模組

### 1. 制度組管理（Group Management）
- **檔案**：`includes/class-group.php`
- **功能**：
  - 建立/編輯/刪除制度組
  - 設定各項收益比例
  - 管理制度組狀態

### 2. 用戶角色管理（User Role Management）
- **檔案**：`includes/class-user-role.php`
- **功能**：
  - 建立用戶角色（股東/合夥人）
  - 檢查組內互斥規則
  - 追蹤收益和回本進度

### 3. 商品管理（Product Management）
- **檔案**：`includes/class-product.php`
- **功能**：
  - 綁定WooCommerce商品到制度組
  - 設定商品類型和屬性
  - 管理商品狀態

### 4. 獎勵計算引擎（Reward Calculator）
- **檔案**：`includes/class-reward-calculator.php`
- **功能**：
  - 商城分潤計算（股東專屬）
  - 團隊分紅計算（股東專屬）
  - 推薦回本計算（股東/合夥人）
  - 二級分銷計算（通用）

### 5. 分銷計算器（Distribution Calculator）
- **檔案**：`includes/class-distribution-calculator.php`
- **功能**：
  - 建立分銷網路
  - 計算一級/二級分銷收益
  - 團隊統計

### 6. 定時任務（Cron Jobs）
- **檔案**：`includes/class-cron.php`
- **功能**：
  - 每日獎勵計算（凌晨3點）
  - 每月積分發放（每月1日凌晨2點）

## 🎯 業務流程

### 流程1: 用戶購買股東/合夥人商品
```
1. 用戶下單 → 2. 訂單完成 
   ↓
3. 系統檢查商品類型 
   ↓
4. 建立用戶角色記錄
   ↓
5. 檢查組內互斥規則
   ↓
6. 記錄推薦關係（如有）
   ↓
7. 建立分銷網路
```

### 流程2: 商城訂單完成後的收益計算
```
1. WooCommerce訂單完成
   ↓
2. 觸發 woocommerce_order_status_completed 鉤子
   ↓
3. QiMall_Reward_Calculator::process_order_rewards()
   ├── 計算商城分潤（給所有活躍股東）
   ├── 計算團隊分紅（給所有活躍股東）
   ├── 檢查推薦回本（給推薦人）
   └── 計算二級分銷（給一級和二級推薦人）
   ↓
4. 建立獎勵記錄（status: pending）
```

### 流程3: 每日獎勵計算
```
每日凌晨3點執行:
1. 獲取所有 pending 狀態的獎勵
   ↓
2. 標記為 calculated
```

### 流程4: 每月積分發放
```
每月1日凌晨2點執行:
1. 獲取所有 calculated 狀態的獎勵
   ↓
2. 按用戶匯總金額
   ↓
3. 更新用戶積分帳戶
   ↓
4. 將獎勵標記為 paid
   ↓
5. 建立交易記錄
   ↓
6. 發送通知（可選）
```

## 🔌 WordPress 整合

### 短代碼（Shortcodes）
```php
[QiMall_member_dashboard]  // 會員中心完整介面
[QiMall_earnings]          // 收益查詢頁面
[QiMall_referral_link]     // 推薦連結
```

### 鉤子（Hooks）

**動作鉤子：**
- `QiMall_init` - 外掛初始化
- `QiMall_points_distributed` - 積分發放後
- `woocommerce_order_status_completed` - 訂單完成（WooCommerce）

**過濾器鉤子：**
- `QiMall_reward_amount` - 修改獎勵金額
- `QiMall_distribution_rate` - 修改分銷比例

### AJAX 端點
- `QiMall_manual_distribute_points` - 手動發放積分
- `QiMall_get_user_earnings` - 獲取用戶收益
- `QiMall_get_team_data` - 獲取團隊數據
- `QiMall_bind_product` - 綁定商品
- `QiMall_unbind_product` - 解除綁定

## 📱 使用者介面

### 後台管理介面
1. **儀表板** - 系統概覽和統計
2. **制度組管理** - 新增/編輯制度組
3. **商品管理** - 綁定WooCommerce商品
4. **會員管理** - 查看所有會員角色
5. **獎勵記錄** - 查看所有獎勵明細
6. **積分管理** - 管理用戶積分帳戶
7. **系統設定** - 全域設定

### 前台用戶介面
1. **會員中心** - 個人資訊和收益總覽
2. **收益查詢** - 詳細收益明細
3. **推薦連結** - 獲取推薦連結

## 🔒 安全性

- ✅ 所有輸入都經過 sanitize 處理
- ✅ 資料庫查詢使用 prepared statements
- ✅ AJAX 請求使用 nonce 驗證
- ✅ 權限檢查（current_user_can）
- ✅ 防止直接檔案訪問（ABSPATH 檢查）

## 🚀 效能優化

- ✅ 資料庫索引優化
- ✅ 批量操作減少查詢次數
- ✅ 使用 WordPress transients 快取
- ✅ 定時任務分散負載

## 📊 報表與統計

系統提供以下統計功能：
- 活躍股東/合夥人數量
- 待結算/已發放獎勵金額
- 各制度組運營狀況
- 用戶收益排行
- 團隊發展統計

---

**版本**: 1.0.0  
**最後更新**: 2025-11-01  
**開發者**: Your Name  
**授權**: GPL v2 or later

