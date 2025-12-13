# Co-Share System - 資訊架構 (Information Architecture)

> 最後更新: 2025-12-02  
> 專案版本: 1.0-dev

---

## 📋 目錄

- [1. 系統總覽](#1-系統總覽)
- [2. 後台管理系統](#2-後台管理系統)
- [3. 前台會員系統](#3-前台會員系統)
- [4. 核心功能模組](#4-核心功能模組)
- [5. 資料庫架構](#5-資料庫架構)
- [6. API 端點](#6-api-端點)
- [7. 短代碼系統](#7-短代碼系統)

---

## 1. 系統總覽

```
Co-Share System (消費股東系統)
│
├─── 後台管理系統 (Admin Panel)
│    ├─── 儀表板
│    ├─── 制度組管理
│    ├─── 商品管理
│    ├─── 會員管理
│    ├─── 獎勵記錄
│    ├─── 積分管理
│    └─── 系統設定
│
├─── 前台會員系統 (Member Portal)
│    ├─── 登入/註冊
│    ├─── 會員中心
│    ├─── 收益查詢
│    ├─── 我的團隊
│    └─── 推薦管理
│
├─── 核心功能模組 (Core Modules)
│    ├─── 制度組管理
│    ├─── 用戶角色管理
│    ├─── 商品管理
│    ├─── 獎勵計算引擎
│    ├─── 分銷計算引擎
│    ├─── 推薦系統
│    ├─── 交易記錄
│    ├─── 積分系統
│    └─── 定時任務
│
└─── WooCommerce 整合 (E-commerce Integration)
     ├─── 訂單處理 Hook
     ├─── 結帳頁擴充
     ├─── 產品 Meta 管理
     └─── 用戶註冊 Hook
```

---

## 2. 後台管理系統

### 2.1 導航結構

```
WordPress 後台選單
│
└─── 消費股東 (QiMall System)
     │
     ├─── 儀表板 (Dashboard)                          ✅ 完成
     │    ├─── 統計卡片
     │    │    ├─── 活躍股東數量                       ✅ 完成
     │    │    ├─── 活躍合夥人數量                     ✅ 完成
     │    │    ├─── 待結算獎勵                         ✅ 完成
     │    │    └─── 已發放獎勵                         ✅ 完成
     │    │
     │    ├─── 制度組狀態總覽                          ✅ 完成
     │    ├─── 近期獎勵記錄                            ✅ 完成
     │    └─── 系統公告                                ✅ 完成
     │
     ├─── 制度組管理 (Groups)                          ✅ 完成
     │    ├─── 制度組列表                              ✅ 完成
     │    ├─── 新增制度組                              ✅ 完成
     │    │    ├─── 基本資訊
     │    │    │    ├─── 制度組代號                    ✅ 完成
     │    │    │    ├─── 制度組名稱                    ✅ 完成
     │    │    │    ├─── 股東商品名稱                  ✅ 完成
     │    │    │    └─── 合夥人商品名稱                ✅ 完成
     │    │    │
     │    │    ├─── 收益設定
     │    │    │    ├─── 股東商城分潤比例              ✅ 完成
     │    │    │    ├─── 股東團隊分紅比例              ✅ 完成
     │    │    │    ├─── 股東推薦回本人數              ✅ 完成 ⚠️ 需改名為「推薦獎勵人數」
     │    │    │    └─── 合夥人推薦回本人數            ✅ 完成 ⚠️ 需改名為「推薦獎勵人數」
     │    │    │
     │    │    └─── 其他設定
     │    │         ├─── 角色互斥設定                  ✅ 完成
     │    │         └─── 獎金池設定                    ✅ 完成
     │    │
     │    ├─── 編輯制度組                              ✅ 完成
     │    └─── 刪除制度組                              ✅ 完成
     │
     ├─── 商品管理 (Products)                          ✅ 完成
     │    ├─── 商品列表                                ✅ 完成
     │    │    ├─── WooCommerce 商品選擇               ✅ 完成
     │    │    ├─── 制度組綁定                         ✅ 完成
     │    │    ├─── 商品類型設定                       ✅ 完成
     │    │    │    ├─── 股東商品 (shareholder)        ✅ 完成
     │    │    │    ├─── 合夥人商品 (partner)          ✅ 完成
     │    │    │    └─── 庫存商品 (stock)              ✅ 完成
     │    │    │
     │    │    └─── 分銷設定                           ✅ 完成
     │    │         └─── 啟用二級分銷                  ✅ 完成
     │    │
     │    ├─── 批次綁定                                ✅ 完成
     │    ├─── 解除綁定                                ✅ 完成
     │    └─── 重複綁定驗證                            ✅ 完成 (JavaScript 彈窗)
     │
     ├─── 會員管理 (Members)                           ✅ 完成
     │    ├─── 會員列表                                ✅ 完成
     │    │    ├─── 用戶資訊                           ✅ 完成
     │    │    ├─── 角色類型 (股東/合夥人)             ✅ 完成
     │    │    ├─── 所屬制度組                         ✅ 完成
     │    │    ├─── 推薦人數                           ✅ 完成
     │    │    ├─── 累計收益                           ✅ 完成
     │    │    └─── 狀態                               ✅ 完成
     │    │
     │    ├─── 篩選與搜尋                              ✅ 完成
     │    │    ├─── 按制度組篩選                       ✅ 完成
     │    │    ├─── 按角色類型篩選                     ✅ 完成
     │    │    └─── 關鍵字搜尋                         ✅ 完成
     │    │
     │    ├─── 會員詳情                                ✅ 完成
     │    │    ├─── 基本資訊                           ✅ 完成
     │    │    ├─── 推薦記錄                           ✅ 完成
     │    │    ├─── 獎勵記錄                           ✅ 完成
     │    │    └─── 團隊統計                           ✅ 完成
     │    │
     │    └─── 手動調整角色                            ❌ 未開發
     │
     ├─── 獎勵記錄 (Rewards)                           ✅ 完成
     │    ├─── 獎勵列表                                ✅ 完成
     │    │    ├─── 獎勵類型                           ✅ 完成
     │    │    │    ├─── 商城分潤 (profit)             ✅ 完成
     │    │    │    ├─── 團隊分紅 (dividend)           ✅ 完成
     │    │    │    ├─── 推薦回本 (referral)           ✅ 完成 ⚠️ 需改名為「推薦獎勵」
     │    │    │    └─── 二級分銷 (distribution)       ✅ 完成
     │    │    │
     │    │    ├─── 金額                               ✅ 完成
     │    │    ├─── 狀態 (待發放/已發放)               ✅ 完成
     │    │    ├─── 關聯訂單                           ✅ 完成
     │    │    └─── 時間                               ✅ 完成
     │    │
     │    ├─── 篩選功能                                ✅ 完成
     │    │    ├─── 按用戶篩選                         ✅ 完成
     │    │    ├─── 按獎勵類型篩選                     ✅ 完成
     │    │    ├─── 按狀態篩選                         ✅ 完成
     │    │    └─── 按時間範圍篩選                     ✅ 完成
     │    │
     │    ├─── 批次發放                                ❌ 未開發
     │    └─── 匯出報表                                ❌ 未開發
     │
     ├─── 積分管理 (Points)                            ✅ 完成
     │    ├─── 積分帳戶列表                            ✅ 完成
     │    │    ├─── 用戶資訊                           ✅ 完成
     │    │    ├─── 可用積分                           ✅ 完成
     │    │    ├─── 待結算積分                         ✅ 完成
     │    │    ├─── 已提領積分                         ✅ 完成
     │    │    └─── 最後更新時間                       ✅ 完成
     │    │
     │    ├─── 手動調整積分                            ❌ 未開發
     │    ├─── 積分流水記錄                            ❌ 未開發
     │    └─── 提領申請審核                            ❌ 未開發
     │
     └─── 系統設定 (Settings)                          ✅ 部分完成
          ├─── 分銷設定                                ✅ 完成
          │    ├─── 一級分銷比例                       ✅ 完成
          │    └─── 二級分銷比例                       ✅ 完成
          │
          ├─── 推薦系統設定                            ❌ 未開發
          │    ├─── 預設推薦人                         ❌ 未開發
          │    ├─── 推薦人顯示名稱 (如: "總店")        ❌ 未開發
          │    └─── Cookie 有效期                      ❌ 未開發
          │
          ├─── 積分系統設定                            ❌ 未開發
          │    ├─── 積分名稱                           ❌ 未開發
          │    ├─── 最低提領金額                       ❌ 未開發
          │    └─── 提領手續費                         ❌ 未開發
          │
          └─── 通知設定                                ❌ 未開發
               ├─── Email 通知                         ❌ 未開發
               └─── 站內通知                           ❌ 未開發
```

---

## 3. 前台會員系統

### 3.1 頁面結構

```
前台會員區
│
├─── 登入/註冊 (Login/Register)                       ✅ 完成
│    │
│    ├─── 登入表單                                    ✅ 完成
│    │    ├─── 用戶名/Email                          ✅ 完成
│    │    ├─── 密碼                                  ✅ 完成
│    │    ├─── 記住我                                ✅ 完成
│    │    └─── 忘記密碼                              ✅ 完成
│    │
│    └─── 註冊表單                                    ✅ 完成
│         ├─── 用戶名                                ✅ 完成
│         ├─── Email                                 ✅ 完成
│         ├─── 密碼                                  ✅ 完成
│         └─── 推薦代碼欄位                          ✅ 完成
│              ├─── 自動填充 (URL/Cookie)            ✅ 完成
│              ├─── 即時驗證 (AJAX)                  ✅ 完成
│              ├─── 顯示推薦人姓名                   ✅ 完成
│              └─── 無推薦人顯示「總店」             ✅ 完成
│
├─── 會員中心 (Member Dashboard)                      ✅ 完成
│    │    短代碼: [QiMall_member_dashboard]
│    │
│    ├─── 會員資訊區                                  ✅ 完成
│    │    ├─── 歡迎訊息                              ✅ 完成
│    │    └─── 用戶顯示名稱                          ✅ 完成
│    │
│    ├─── 積分餘額卡片                                ✅ 完成
│    │    ├─── 可用積分                              ✅ 完成
│    │    ├─── 待結算積分                            ✅ 完成
│    │    └─── 已提領積分                            ✅ 完成
│    │
│    ├─── 收益統計卡片                                ✅ 完成
│    │    ├─── 本月收益                              ✅ 完成
│    │    ├─── 累計收益                              ✅ 完成
│    │    └─── 待發放收益                            ✅ 完成
│    │
│    ├─── 團隊統計卡片                                ✅ 完成
│    │    ├─── 一級團隊人數                          ✅ 完成
│    │    └─── 二級團隊人數                          ✅ 完成
│    │
│    ├─── 我的角色                                    ✅ 完成
│    │    ├─── 股東身份卡片                          ✅ 完成
│    │    │    ├─── 制度組名稱                       ✅ 完成
│    │    │    ├─── 推薦進度                         ✅ 完成
│    │    │    ├─── 回本狀態                         ✅ 完成 ⚠️ 需改為「獎勵狀態」
│    │    │    └─── 累計收益                         ✅ 完成
│    │    │
│    │    └─── 合夥人身份卡片                        ✅ 完成
│    │         ├─── 制度組名稱                       ✅ 完成
│    │         ├─── 推薦進度                         ✅ 完成
│    │         ├─── 回本狀態                         ✅ 完成 ⚠️ 需改為「獎勵狀態」
│    │         └─── 累計收益                         ✅ 完成
│    │
│    └─── 推薦管理區                                  ❌ 未開發
│         ├─── 我的推薦碼                            ❌ 未開發
│         ├─── 推薦連結                              ❌ 未開發
│         ├─── 修改推薦碼                            ❌ 未開發
│         │    └─── 剩餘修改次數顯示                 ❌ 未開發
│         │
│         └─── 推薦統計                              ❌ 未開發
│              ├─── 總推薦人數                       ❌ 未開發
│              └─── 本月新增推薦                     ❌ 未開發
│
├─── 收益查詢 (Earnings)                              ✅ 完成
│    │    短代碼: [QiMall_earnings]
│    │
│    ├─── 收益總覽                                    ✅ 完成
│    │    ├─── 本月收益                              ✅ 完成
│    │    ├─── 累計收益                              ✅ 完成
│    │    └─── 待發放收益                            ✅ 完成
│    │
│    ├─── 收益明細列表                                ✅ 完成
│    │    ├─── 獎勵類型                              ✅ 完成
│    │    ├─── 金額                                  ✅ 完成
│    │    ├─── 狀態                                  ✅ 完成
│    │    ├─── 時間                                  ✅ 完成
│    │    └─── 來源訂單                              ✅ 完成
│    │
│    ├─── 篩選功能                                    ✅ 完成
│    │    ├─── 按獎勵類型篩選                        ✅ 完成
│    │    ├─── 按時間範圍篩選                        ✅ 完成
│    │    └─── 按狀態篩選                            ✅ 完成
│    │
│    └─── 匯出報表                                    ❌ 未開發
│
├─── 我的團隊 (My Team)                               ❌ 未開發
│    │    短代碼: [QiMall_my_team]
│    │
│    ├─── 團隊統計                                    ❌ 未開發
│    │    ├─── 一級團隊人數                          ❌ 未開發
│    │    ├─── 二級團隊人數                          ❌ 未開發
│    │    └─── 團隊總消費金額                        ❌ 未開發
│    │
│    ├─── 一級團隊列表                                ❌ 未開發
│    │    ├─── 成員姓名                              ❌ 未開發
│    │    ├─── 身份 (股東/合夥人/普通)               ❌ 未開發
│    │    ├─── 註冊日期                              ❌ 未開發
│    │    └─── 購買商品                              ❌ 未開發
│    │
│    ├─── 二級團隊列表                                ❌ 未開發
│    │    ├─── 成員姓名                              ❌ 未開發
│    │    ├─── 身份                                  ❌ 未開發
│    │    └─── 註冊日期                              ❌ 未開發
│    │
│    └─── 樹狀圖顯示 (可選)                          ❌ 未開發
│
└─── 推薦連結 (Referral Link)                         ⏳ 部分完成
     │    短代碼: [QiMall_referral_link]
     │
     ├─── 推薦碼顯示                                  ⏳ 邏輯完成,UI 未開發
     ├─── 推薦連結生成                                ⏳ 邏輯完成,UI 未開發
     ├─── 一鍵複製功能                                ❌ 未開發
     └─── 社群分享按鈕                                ❌ 未開發
```

---

## 4. 核心功能模組

### 4.1 功能模組架構

```
includes/ (核心類別目錄)
│
├─── class-database.php                               ✅ 完成
│    └─── 資料庫初始化
│         ├─── 建立 8 張資料表                       ✅ 完成
│         ├─── 版本檢查與更新                        ✅ 完成
│         └─── 示範資料插入                          ✅ 完成
│
├─── class-group.php                                  ✅ 完成
│    └─── 制度組管理
│         ├─── create() - 建立制度組                 ✅ 完成
│         ├─── update() - 更新制度組                 ✅ 完成
│         ├─── delete() - 刪除制度組                 ✅ 完成
│         ├─── get() - 取得單一制度組                ✅ 完成
│         ├─── get_all() - 取得所有制度組            ✅ 完成
│         └─── get_by_code() - 用代號查詢            ✅ 完成
│
├─── class-user-role.php                              ✅ 完成
│    └─── 用戶角色管理
│         ├─── assign_role() - 分配角色              ✅ 完成
│         ├─── get_user_roles() - 取得用戶角色       ✅ 完成
│         ├─── check_conflict() - 檢查角色衝突       ✅ 完成
│         ├─── increment_referral_count() - 增加推薦數 ✅ 完成
│         ├─── check_rebate_completed() - 檢查回本    ✅ 完成 ⚠️ 邏輯需完善
│         └─── get_role_statistics() - 角色統計      ✅ 完成
│
├─── class-product.php                                ✅ 完成
│    └─── 商品管理 (WooCommerce Post Meta)
│         ├─── bind() - 綁定商品到制度組             ✅ 完成
│         ├─── unbind() - 解除綁定                   ✅ 完成
│         ├─── get() - 取得商品配置                  ✅ 完成
│         ├─── get_all() - 取得所有綁定商品          ✅ 完成
│         ├─── check_duplicate() - 檢查重複綁定      ✅ 完成
│         └─── update_meta() - 更新商品 Meta         ✅ 完成
│
├─── class-reward-calculator.php                      ✅ 完成 ⚠️ 有邏輯錯誤
│    └─── 獎勵計算引擎
│         ├─── process_order_rewards() - 處理訂單獎勵  ✅ 完成
│         ├─── calculate_profit_share() - 商城分潤     ✅ 完成
│         ├─── calculate_team_dividend() - 團隊分紅    ✅ 完成
│         ├─── calculate_referral_rebate() - 推薦回本  ✅ 完成 ⚠️ 計數邏輯錯誤
│         └─── calculate_distribution() - 二級分銷     ✅ 完成
│
├─── class-distribution-calculator.php                ✅ 完成
│    └─── 分銷計算引擎
│         ├─── process_distribution() - 處理分銷      ✅ 完成
│         ├─── get_parent() - 取得一級推薦人          ✅ 完成
│         ├─── get_grandparent() - 取得二級推薦人     ✅ 完成
│         ├─── calculate_commission() - 計算佣金      ✅ 完成
│         └─── get_team_statistics() - 團隊統計       ✅ 完成
│
├─── class-referral.php                               ✅ 完成
│    └─── 推薦系統
│         ├─── init() - 初始化 Hook                  ✅ 完成
│         ├─── capture_referral_code() - 捕捉推薦碼   ✅ 完成
│         ├─── save_referral_on_register() - 註冊時記錄 ✅ 完成
│         ├─── generate_referral_code() - 生成推薦碼  ✅ 完成
│         ├─── create_distribution_relationship() - 建立分銷樹 ✅ 完成
│         ├─── get_user_id_by_code() - 用推薦碼查用戶 ✅ 完成
│         ├─── get_referrer_info() - 取得推薦人資訊   ✅ 完成
│         ├─── get_default_referrer_id() - 取得預設推薦人 ✅ 完成 ⚠️ 寫死在程式碼
│         ├─── ajax_verify_referral_code() - AJAX 驗證 ✅ 完成
│         ├─── ajax_update_referral_code() - AJAX 修改 ✅ 完成
│         ├─── validate_referral_code() - 驗證推薦碼  ✅ 完成
│         └─── get_referral_url() - 生成推薦連結      ✅ 完成
│
├─── class-reward.php                                 ✅ 完成
│    └─── 獎勵記錄管理
│         ├─── create() - 建立獎勵記錄               ✅ 完成
│         ├─── update_status() - 更新狀態            ✅ 完成
│         ├─── get_by_user() - 取得用戶獎勵          ✅ 完成
│         ├─── get_statistics() - 獎勵統計           ✅ 完成
│         └─── get_pending_rewards() - 待發放獎勵    ✅ 完成
│
├─── class-transaction.php                            ✅ 完成
│    └─── 交易記錄管理
│         ├─── record() - 記錄交易                   ✅ 完成
│         ├─── get_by_user() - 取得用戶交易          ✅ 完成
│         └─── get_by_order() - 取得訂單交易         ✅ 完成
│
├─── class-cron.php                                   ⏳ 部分完成
│    └─── 定時任務
│         ├─── init() - 初始化排程                   ✅ 完成
│         ├─── monthly_settlement() - 月結算         ⏳ 框架完成,邏輯待補充
│         ├─── settle_rewards() - 結算獎勵           ⏳ 框架完成,邏輯待補充
│         └─── convert_pending_to_available() - 轉換積分 ⏳ 框架完成,邏輯待補充
│
├─── class-demo-data.php                              ✅ 完成
│    └─── 示範資料產生器
│         └─── generate() - 生成測試資料             ✅ 完成
│
└─── ajax-handlers.php                                ✅ 完成
     └─── AJAX 處理函數
          ├─── delete_group() - 刪除制度組           ✅ 完成
          ├─── delete_product() - 刪除商品綁定       ✅ 完成
          └─── update_reward_status() - 更新獎勵狀態 ✅ 完成
```

---

## 5. 資料庫架構

### 5.1 資料表結構

```
WordPress Database
│
├─── wp_QiMall_groups                                ✅ 完成
│    └─── 制度組配置表
│         ├─── id (PK)                                ✅ 完成
│         ├─── group_code (UK) - 制度組代號           ✅ 完成
│         ├─── group_name - 制度組名稱                ✅ 完成
│         ├─── shareholder_product_name - 股東商品名稱 ✅ 完成
│         ├─── partner_product_name - 合夥人商品名稱   ✅ 完成
│         ├─── shareholder_profit_rate - 股東分潤比例  ✅ 完成
│         ├─── shareholder_dividend_rate - 股東分紅比例 ✅ 完成
│         ├─── shareholder_referral_count - 股東推薦人數 ✅ 完成
│         ├─── partner_referral_count - 合夥人推薦人數  ✅ 完成
│         ├─── is_active - 啟用狀態                   ✅ 完成
│         ├─── created_at                             ✅ 完成
│         └─── updated_at                             ✅ 完成
│
├─── wp_QiMall_user_roles                            ✅ 完成
│    └─── 用戶角色表
│         ├─── id (PK)                                ✅ 完成
│         ├─── user_id (FK) - WordPress 用戶 ID       ✅ 完成
│         ├─── group_code (FK) - 制度組代號           ✅ 完成
│         ├─── role_type - 角色類型 (shareholder/partner) ✅ 完成
│         ├─── referral_count - 已推薦人數            ✅ 完成
│         ├─── total_earned - 累計收益                ✅ 完成
│         ├─── status - 狀態 (active/completed/cancelled) ✅ 完成
│         ├─── assigned_at - 分配時間                 ✅ 完成
│         ├─── completed_at - 完成時間                ✅ 完成
│         └─── UK: user_id + group_code + role_type   ✅ 完成
│
├─── wp_QiMall_rewards                               ✅ 完成
│    └─── 獎勵記錄表
│         ├─── id (PK)                                ✅ 完成
│         ├─── user_id (FK) - 用戶 ID                 ✅ 完成
│         ├─── order_id (FK) - 訂單 ID                ✅ 完成
│         ├─── reward_type - 獎勵類型                 ✅ 完成
│         │    ├─── profit - 商城分潤                 ✅ 完成
│         │    ├─── dividend - 團隊分紅               ✅ 完成
│         │    ├─── referral - 推薦回本               ✅ 完成 ⚠️ 需改名
│         │    └─── distribution - 二級分銷           ✅ 完成
│         ├─── amount - 獎勵金額                      ✅ 完成
│         ├─── status - 狀態 (pending/paid/cancelled) ✅ 完成
│         ├─── group_code - 制度組                    ✅ 完成
│         ├─── description - 說明                     ✅ 完成
│         ├─── created_at                             ✅ 完成
│         └─── paid_at                                ✅ 完成
│
├─── wp_QiMall_transactions                          ✅ 完成
│    └─── 交易記錄表
│         ├─── id (PK)                                ✅ 完成
│         ├─── user_id (FK) - 用戶 ID                 ✅ 完成
│         ├─── order_id (FK) - 訂單 ID                ✅ 完成
│         ├─── product_id (FK) - 商品 ID              ✅ 完成
│         ├─── transaction_type - 交易類型            ✅ 完成
│         ├─── amount - 金額                          ✅ 完成
│         ├─── group_code - 制度組                    ✅ 完成
│         └─── created_at                             ✅ 完成
│
├─── wp_QiMall_referrals                             ✅ 完成
│    └─── 推薦關係表
│         ├─── id (PK)                                ✅ 完成
│         ├─── referrer_id (FK) - 推薦人 ID           ✅ 完成
│         ├─── referee_id (FK) - 被推薦人 ID          ✅ 完成
│         ├─── order_id (FK) - 訂單 ID                ✅ 完成
│         ├─── product_id (FK) - 商品 ID              ✅ 完成
│         ├─── plan_type - 商品類型                   ✅ 完成
│         ├─── group_code - 制度組                    ✅ 完成
│         └─── created_at                             ✅ 完成
│
├─── wp_QiMall_distribution                          ✅ 完成
│    └─── 分銷關係表 (二級樹狀結構)
│         ├─── id (PK)                                ✅ 完成
│         ├─── user_id (FK, UK) - 用戶 ID             ✅ 完成
│         ├─── parent_id (FK) - 一級推薦人 ID         ✅ 完成
│         ├─── grandparent_id (FK) - 二級推薦人 ID    ✅ 完成
│         ├─── level - 層級 (1/2)                     ✅ 完成
│         ├─── created_at                             ✅ 完成
│         └─── updated_at                             ✅ 完成
│
├─── wp_QiMall_points                                ✅ 完成
│    └─── 積分帳戶表
│         ├─── id (PK)                                ✅ 完成
│         ├─── user_id (FK, UK) - 用戶 ID             ✅ 完成
│         ├─── available_points - 可用積分            ✅ 完成
│         ├─── pending_points - 待結算積分            ✅ 完成
│         ├─── withdrawn_points - 已提領積分          ✅ 完成
│         ├─── total_earned - 累計賺取                ✅ 完成
│         ├─── last_settlement_date - 最後結算日期    ✅ 完成
│         └─── updated_at                             ✅ 完成
│
└─── wp_usermeta (WordPress 原生表擴充)               ✅ 完成
     └─── 推薦系統 Meta Keys
          ├─── QiMall_referral_code - 用戶推薦碼     ✅ 完成
          ├─── QiMall_referral_code_changes_left - 剩餘修改次數 ✅ 完成
          └─── QiMall_referrer - 推薦人 ID           ✅ 完成
```

---

## 6. API 端點

### 6.1 AJAX 端點

```
AJAX Endpoints
│
├─── 推薦系統                                         ✅ 完成
│    ├─── QiMall_verify_referral_code               ✅ 完成
│    │    ├─── 驗證推薦碼有效性                      ✅ 完成
│    │    └─── 返回推薦人姓名                        ✅ 完成
│    │
│    └─── QiMall_update_referral_code               ✅ 完成
│         ├─── 修改用戶推薦碼                        ✅ 完成
│         ├─── 檢查修改次數限制                      ✅ 完成
│         └─── 唯一性驗證                            ✅ 完成
│
├─── 管理介面                                         ✅ 完成
│    ├─── QiMall_delete_group                       ✅ 完成
│    ├─── QiMall_delete_product                     ✅ 完成
│    └─── QiMall_update_reward_status               ✅ 完成
│
└─── 前台功能                                         ❌ 未開發
     ├─── QiMall_get_my_referral_code               ❌ 未開發
     ├─── QiMall_get_team_data                      ❌ 未開發
     └─── QiMall_export_earnings                    ❌ 未開發
```

---

## 7. 短代碼系統

### 7.1 可用短代碼

```
WordPress Shortcodes
│
├─── [QiMall_member_dashboard]                       ✅ 完成
│    └─── 會員中心頁面
│         ├─── 登入檢查                              ✅ 完成
│         ├─── 積分餘額顯示                          ✅ 完成
│         ├─── 收益統計                              ✅ 完成
│         ├─── 角色卡片                              ✅ 完成
│         └─── 團隊統計                              ✅ 完成
│
├─── [QiMall_earnings]                               ✅ 完成
│    └─── 收益查詢頁面
│         ├─── 收益總覽                              ✅ 完成
│         ├─── 收益明細列表                          ✅ 完成
│         └─── 篩選功能                              ✅ 完成
│
├─── [QiMall_referral_link]                          ⏳ 邏輯完成,UI 未開發
│    └─── 推薦連結頁面
│         ├─── 推薦碼顯示                            ⏳ 邏輯完成
│         ├─── 推薦連結生成                          ⏳ 邏輯完成
│         ├─── 複製功能                              ❌ 未開發
│         └─── 社群分享                              ❌ 未開發
│
└─── [QiMall_my_team]                                ❌ 未開發
     └─── 我的團隊頁面
          ├─── 團隊統計                              ❌ 未開發
          ├─── 一級團隊列表                          ❌ 未開發
          └─── 二級團隊列表                          ❌ 未開發
```

---

## 8. WooCommerce 整合

### 8.1 整合點

```
WooCommerce Integration
│
├─── 訂單處理 Hook                                    ✅ 完成
│    └─── woocommerce_order_status_completed
│         └─── QiMall_Reward_Calculator::process_order_rewards() ✅ 完成
│
├─── 用戶註冊 Hook                                    ✅ 完成
│    ├─── user_register                              ✅ 完成
│    └─── woocommerce_created_customer               ✅ 完成
│         └─── QiMall_Referral::save_referral_on_register() ✅ 完成
│
├─── 產品 Meta 管理                                   ✅ 完成
│    ├─── _QiMall_enabled - 是否啟用                ✅ 完成
│    ├─── _QiMall_group_code - 制度組代號           ✅ 完成
│    ├─── _QiMall_product_type - 商品類型           ✅ 完成
│    └─── _QiMall_distribution_enabled - 分銷啟用   ✅ 完成
│
└─── 結帳頁擴充                                       ❌ 未開發
     ├─── 推薦碼欄位                                  ❌ 未開發
     ├─── 自動填充 Cookie                            ❌ 未開發
     ├─── 即時驗證                                    ❌ 未開發
     └─── 手動輸入優先權                              ❌ 未開發
```

---

## 9. 系統流程圖

### 9.1 推薦註冊流程

```
推薦註冊流程
│
1. 訪客點擊推薦連結
   └─── URL: /register?ref=USER1A2B                  ✅ 完成
        │
        ↓
2. 捕捉推薦參數
   └─── QiMall_Referral::capture_referral_code()   ✅ 完成
        └─── 保存到 Cookie (30天)                    ✅ 完成
        │
        ↓
3. 顯示註冊表單
   └─── register-form.php                            ✅ 完成
        ├─── 自動填充推薦碼 (Cookie/URL)             ✅ 完成
        ├─── AJAX 即時驗證                           ✅ 完成
        └─── 顯示推薦人姓名                          ✅ 完成
        │
        ↓
4. 用戶提交註冊
   └─── WordPress 原生註冊                           ✅ 完成
        │
        ↓
5. 觸發 Hook: user_register
   └─── QiMall_Referral::save_referral_on_register() ✅ 完成
        ├─── 生成用戶推薦碼 (USER+4char)             ✅ 完成
        ├─── 記錄推薦人到 user_meta                  ✅ 完成
        └─── 建立二級分銷關係                        ✅ 完成
             ├─── parent_id (一級)                    ✅ 完成
             └─── grandparent_id (二級)               ✅ 完成
        │
        ↓
6. 註冊完成
   └─── 自動登入並重定向                             ✅ 完成
```

### 9.2 訂單獎勵計算流程

```
訂單獎勵計算流程
│
1. WooCommerce 訂單完成
   └─── Hook: woocommerce_order_status_completed    ✅ 完成
        │
        ↓
2. 觸發獎勵計算
   └─── QiMall_Reward_Calculator::process_order_rewards() ✅ 完成
        │
        ├──→ 檢查商品綁定
        │    └─── QiMall_Product::get($product_id)  ✅ 完成
        │         │
        │         ├─── 有綁定制度組 (股東/合夥人商品)
        │         │    │
        │         │    ├──→ 3A. 計算商城分潤          ✅ 完成
        │         │    │    └─── calculate_profit_share()
        │         │    │
        │         │    ├──→ 3B. 計算團隊分紅          ✅ 完成
        │         │    │    └─── calculate_team_dividend()
        │         │    │
        │         │    └──→ 3C. 計算推薦回本          ✅ 完成 ⚠️ 邏輯錯誤
        │         │         └─── calculate_referral_rebate()
        │         │
        │         └─── 檢查分銷設定
        │              └─── _QiMall_distribution_enabled = 'yes'
        │                   │
        │                   └──→ 3D. 計算二級分銷      ✅ 完成
        │                        └─── calculate_distribution()
        │
        ↓
4. 記錄獎勵到資料庫
   └─── QiMall_Reward::create()                     ✅ 完成
        └─── status = 'pending'                       ✅ 完成
        │
        ↓
5. 更新用戶積分
   └─── pending_points += reward_amount              ✅ 完成
        │
        ↓
6. 月結算 (定時任務)
   └─── QiMall_Cron::monthly_settlement()           ⏳ 框架完成
        ├─── pending → available                      ⏳ 邏輯待補充
        └─── status: pending → paid                   ⏳ 邏輯待補充
```

---

## 10. 狀態圖例說明

```
✅ 完成        - 功能已開發並正常運作
⏳ 部分完成    - 核心邏輯已完成,但 UI 或細節待補充
⚠️ 需修正      - 功能已完成但有邏輯錯誤或需改進
❌ 未開發      - 尚未開始開發
```

---

## 11. 優先開發項目 (依 TODO.md)

### 11.1 P0 - 緊急

1. ❌ WooCommerce 結帳頁推薦碼欄位整合
2. ❌ 會員中心推薦碼管理 UI
3. ❌ 系統設定 - 預設推薦人配置

### 11.2 P1 - 重要

4. ⚠️ 修正推薦計數邏輯 (只計算合夥人商品)
5. ⚠️ 用詞修正: "回本" → "推薦獎勵"

### 11.3 P2 - 建議

6. ❌ 我的團隊頁面完整開發
7. ❌ 推薦進度視覺化增強
8. ⏳ 月結算邏輯完善
9. ❌ 回本狀態自動判斷
10. ❌ AJAX 即時通知系統

---

**文件結束**

