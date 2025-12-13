# QiMall 管理系統

> **WooCommerce 客戶管理系統 - 獨立後台解決方案**  
> 版本: 1.0-dev | 最後更新: 2025-12-02

[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net/)

---

## 📖 文件導覽

| 文件 | 說明 | 適合對象 |
|------|------|---------|
| [README.md](README.md) | 專案介紹與功能概覽 | 👥 所有人 |
| [QUICKSTART.md](QUICKSTART.md) | 10秒快速啟動指南 | 🚀 新手快速上手 |
| [INSTALL.md](INSTALL.md) | 完整安裝與設定教學 | 📝 管理員 |
| [IA.md](IA.md) | 資訊架構與開發狀態 | 💻 開發者 |
| [TODO.md](TODO.md) | 待辦事項與錯誤追蹤 | 🔧 開發者 |
| [PROJECT.md](PROJECT.md) | 專案結構與技術細節 | 💻 開發者 |
| [REFACTOR-REPORT.md](REFACTOR-REPORT.md) | 架構重構報告 | 📊 開發者 |

---

## 🎯 功能特色

### 核心功能
- ✅ **多制度組架構** - 可建立多個獨立的股東/合夥人組別
- ✅ **組內互斥，組間共存** - 同一組的股東與合夥人互斥，不同組可共存
- ✅ **獨立獎金池管理** - 每個制度組擁有獨立的獎勵池
- ✅ **即時收益計算** - 訂單完成立即計算獎勵
- ✅ **月結積分發放** - 自動排程月度結算
- ✅ **WooCommerce 深度整合** - 原生產品系統，無縫整合
- ✅ **推薦註冊系統** - URL/Cookie 追蹤，自動建立推薦關係
- ✅ **二級分銷樹** - 自動維護推薦網路結構

### 四種收益模式
1. **商城分潤**（股東專屬）- 按比例分享商城營業額
2. **團隊分紅**（股東專屬）- 團隊業績獎勵
3. **推薦獎勵**（股東/合夥人）- 推薦達標人數獲得積分獎勵
4. **二級分銷**（通用）- 一級/二級推薦佣金

### 推薦系統特色 ⭐ NEW
- 🔗 **推薦碼系統** - 自動生成唯一推薦碼 (格式: USER+4位)
- 🍪 **Cookie 追蹤** - 30天自動追蹤推薦來源
- 🔄 **一次修改** - 用戶可修改推薦碼一次
- 🏪 **預設推薦人** - 無推薦人時指派為「總店」
- ✅ **即時驗證** - AJAX 即時驗證推薦碼有效性

---

## 💻 系統需求

| 需求 | 版本 |
|------|------|
| WordPress | 5.8+ |
| PHP | 7.4+ |
| WooCommerce | 5.0+ |
| MySQL | 5.7+ |

---

## 🚀 快速開始

### 方式一：10秒快速啟動（推薦）

1. **啟用外掛**
   ```
   WordPress 後台 → 外掛 → 啟用 "QiMall System"
   ```

2. **一鍵生成測試資料**
   ```
   消費股東 → 🧪 測試資料 → 🚀 一鍵生成所有測試資料
   ```
   
3. **完成！** 
   - 自動建立 2 個制度組
   - 自動建立 5 個測試商品和用戶
   - 自動生成推薦關係和訂單
   - 自動計算所有獎勵

詳細教學請看 → [QUICKSTART.md](QUICKSTART.md)

### 方式二：手動設定

詳細安裝步驟請參考 → [INSTALL.md](INSTALL.md)

---

## 📊 資料庫結構

外掛會自動建立 8 張資料表：

| 資料表 | 說明 | 狀態 |
|--------|------|------|
| `wp_QiMall_groups` | 制度組配置 | ✅ |
| `wp_QiMall_user_roles` | 用戶角色記錄 | ✅ |
| `wp_QiMall_rewards` | 獎勵記錄 | ✅ |
| `wp_QiMall_transactions` | 交易記錄 | ✅ |
| `wp_QiMall_referrals` | 推薦關係 | ✅ |
| `wp_QiMall_distribution` | 二級分銷樹 | ✅ |
| `wp_QiMall_points` | 積分帳戶 | ✅ |
| `wp_usermeta` | 推薦碼擴充 | ✅ |

**注意**: 已移除 `wp_QiMall_products` 表，改用 WooCommerce 原生 Post Meta。詳見 [REFACTOR-REPORT.md](REFACTOR-REPORT.md)

---

## 🏗️ 架構概覽

```
後台管理系統
├─ 儀表板 (統計總覽)
├─ 制度組管理 (CRUD)
├─ 商品管理 (WooCommerce 整合)
├─ 會員管理 (角色查詢)
├─ 獎勵記錄 (收益明細)
├─ 積分管理 (帳戶管理)
└─ 系統設定 (分銷比例)

前台會員系統
├─ 登入/註冊 (推薦碼整合) ✅
├─ 會員中心 (積分/收益)
├─ 收益查詢 (明細列表)
├─ 我的團隊 (一級/二級) ❌ 未開發
└─ 推薦管理 (推薦碼/連結) ⏳ 部分完成
```

完整架構請參考 → [IA.md](IA.md)

---

## 📋 使用說明

### 1. 建立制度組

前往 `消費股東 > 制度組管理` 建立新的制度組：

```yaml
制度組代號: group_01
制度組名稱: 制度組①
股東商品名稱: 消費股東A
合夥人商品名稱: 消費合夥人B

收益設定:
  股東商城分潤比例: 5%
  股東團隊分紅比例: 3%
  股東推薦獎勵人數: 10人
  合夥人推薦獎勵人數: 3人
```

### 2. 綁定商品

**方式 A: WooCommerce 商品頁面**
```
商品 → 編輯商品 → 「消費股東設定」標籤
→ ✅ 啟用
→ 選擇制度組: group_01
→ 商品類型: 股東商品/合夥人商品/庫存商品
```

**方式 B: 快速綁定**
```
消費股東 → 商品管理 → 快速綁定
→ 選擇制度組和類型 → 儲存
```

### 3. 設定分銷比例

前往 `消費股東 > 系統設定`:

```yaml
一級分銷比例: 10%
二級分銷比例: 5%
```

### 4. 前台短代碼

在頁面或文章中使用以下短代碼：

| 短代碼 | 功能 | 狀態 |
|--------|------|------|
| `[QiMall_member_dashboard]` | 會員中心 | ✅ |
| `[QiMall_earnings]` | 收益查詢 | ✅ |
| `[QiMall_referral_link]` | 推薦連結 | ⏳ |
| `[QiMall_my_team]` | 我的團隊 | ❌ |

---

## 🔌 WooCommerce 整合

### 商品 Meta 欄位

系統使用以下 Post Meta 儲存商品配置：

| Meta Key | 值 | 說明 |
|----------|-----|------|
| `_QiMall_enabled` | yes/no | 是否啟用 |
| `_QiMall_group_code` | group_01 | 制度組代號 |
| `_QiMall_product_type` | shareholder/partner/stock | 商品類型 |
| `_QiMall_distribution_enabled` | yes/no | 是否參與分銷 |

### Hook 整合點

| Hook | 用途 | 處理類別 |
|------|------|---------|
| `woocommerce_order_status_completed` | 訂單完成計算獎勵 | `QiMall_Reward_Calculator` |
| `user_register` | 用戶註冊記錄推薦 | `QiMall_Referral` |
| `woocommerce_created_customer` | WC 結帳註冊 | `QiMall_Referral` |

---

## 🎨 推薦系統使用

### 推薦流程

1. **生成推薦連結**
   ```
   用戶註冊後自動生成推薦碼: USER1A2B
   推薦連結: https://your-site.com/?ref=USER1A2B
   ```

2. **訪客點擊連結**
   ```
   系統自動捕捉 ?ref= 參數
   保存到 Cookie (30天有效)
   ```

3. **訪客註冊**
   ```
   註冊表單自動填充推薦碼
   即時驗證並顯示推薦人姓名
   無推薦碼時顯示「總店」
   ```

4. **建立推薦關係**
   ```
   記錄到 user_meta: QiMall_referrer
   建立二級分銷樹: parent_id + grandparent_id
   ```

### 推薦碼規則

- **格式**: `USER` + 4位隨機字母/數字
- **大小寫敏感**: `USER1A2B` ≠ `user1a2b`
- **唯一性**: 系統自動檢查重複
- **修改次數**: 每人可修改一次

---

## 📈 開發狀態

### ✅ 已完成功能 (90%)

- ✅ 後台管理系統 (7個模組)
- ✅ 獎勵計算引擎 (4種收益模式)
- ✅ 推薦註冊系統 (Cookie/AJAX)
- ✅ 會員中心基礎功能
- ✅ WooCommerce 深度整合
- ✅ 防重複綁定驗證
- ✅ 分銷產品分離邏輯

### ⚠️ 需修正 (P1)

- ⚠️ 推薦計數邏輯錯誤 (應只計算合夥人商品)
- ⚠️ 用詞修正: "回本" → "推薦獎勵"

### ❌ 待開發功能 (P0)

- ❌ WooCommerce 結帳頁推薦碼欄位
- ❌ 會員中心推薦碼管理 UI
- ❌ 系統設定 - 預設推薦人配置
- ❌ 我的團隊頁面完整開發

詳細開發狀態請參考 → [TODO.md](TODO.md) 和 [IA.md](IA.md)

---

## 🤝 技術支援

### 常見問題

**Q: 為什麼移除了 `wp_QiMall_products` 表？**  
A: 為了更好地整合 WooCommerce，改用原生 Post Meta 儲存。詳見 [REFACTOR-REPORT.md](REFACTOR-REPORT.md)

**Q: 如何測試推薦系統？**  
A: 使用隱私模式瀏覽器訪問 `/?ref=推薦碼`，Cookie 會自動保存30天。

**Q: 積分如何發放？**  
A: 訂單完成時獎勵為 `pending` 狀態，月結算時轉為 `available` 可用積分。

### 測試資料

快速生成測試資料：
```
消費股東 → 🧪 測試資料 → 🚀 一鍵生成所有測試資料
```

生成內容:
- 5個測試用戶 (密碼: test123456)
- 2個制度組
- 5個商品 (已綁定)
- 推薦關係網路
- 5個訂單 (已完成並計算獎勵)

---

## 📝 授權與版權

QiMall System  
Copyright © 2024-2025  
WordPress Plugin

---

## 🔗 相關連結

- [WordPress 外掛開發文件](https://developer.wordpress.org/plugins/)
- [WooCommerce 開發文件](https://woocommerce.github.io/code-reference/)
- [專案架構文件](PROJECT.md)
- [完整 API 文件](IA.md)

---

**📦 版本**: 1.0-dev  
**👨‍💻 狀態**: 開發中 (90% 完成)  
**🔄 最後更新**: 2025-12-02


