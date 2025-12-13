<?php
// QiMall 管理系統 — 乾淨版客戶後台模板（完全清理版）
if (!defined('ABSPATH')) { exit; }

// 取用品牌設定
$branding = get_option('QiMall_branding', array(
  'portal_title' => 'QiMall管理系統',
  'primary_color' => '#4f46e5',
  'logo_url' => ''
));

// 標題強制為 QiMall（避免舊字樣殘留）
$portal_title = isset($branding['portal_title']) ? trim($branding['portal_title']) : 'QiMall管理系統';
if ($portal_title === '' || $portal_title === 'Co-Share 客戶中心') {
  $portal_title = 'QiMall管理系統';
}

// 頁面上下文（避免未定義 Notice）
if (!isset($is_login_page)) {
  $req = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
  $is_login_page = (function($uri){
    if (function_exists('is_page') && is_page('client-login')) return true;
    return (strpos($uri, 'client-login') !== false);
  })($req);
}
if (!isset($is_admin_page)) {
  $req = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
  $is_admin_page = (function($uri){
    if (function_exists('is_page') && is_page('client-admin')) return true;
    return (strpos($uri, 'client-admin') !== false);
  })($req);
}
if ($is_login_page && $is_admin_page) { $is_admin_page = false; }

// 當前使用者與 REST Nonce（避免未定義）
if (!isset($current_user)) { $current_user = wp_get_current_user(); }
if (!isset($rest_nonce)) { $rest_nonce = wp_create_nonce('wp_rest'); }
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo $is_login_page ? 'QiMall 客戶登入' : 'QiMall管理系統'; ?></title>
  <?php wp_head(); ?>
  <style>
    html,body{margin:0;padding:0;background:#f5f6fb;font:14px/1.6 system-ui,-apple-system,Segoe UI,Arial}
    body.qimall-client-portal{--primary:<?php echo esc_attr($branding['primary_color'] ?: '#4f46e5'); ?>;--bg:#f5f6fb;--card:#ffffff;--border:#e5e7eb;--muted:#6b7280;--text:#111827}
    #wpadminbar{display:none !important}
    .qimall-portal-wrapper{min-height:100vh;display:flex;flex-direction:column;background:var(--bg)}
    .qimall-portal-header{position:sticky;top:0;z-index:10;display:flex;justify-content:space-between;align-items:center;padding:14px 20px;background:var(--card);border-bottom:1px solid var(--border);box-shadow:0 4px 18px -8px rgba(0,0,0,.12)}
    .qimall-portal-brand{display:flex;align-items:center;gap:10px;font-weight:600;color:var(--text)}
    .qimall-portal-layout{display:flex;min-height:0;flex:1}
    /* 深色側欄（Element Admin 風格） */
    .qimall-sidebar{width:240px;background:linear-gradient(180deg,#0b1220 0%,#0e1729 100%);display:flex;flex-direction:column;box-shadow:inset -1px 0 0 rgba(255,255,255,0.04)}
    .qimall-sidebar a{display:flex;align-items:center;gap:12px;padding:12px 16px;color:#cbd5e1;text-decoration:none;transition:background .18s ease,color .18s ease;position:relative}
    .qimall-sidebar a .icon{width:18px;display:inline-flex;justify-content:center;opacity:.95}
    .qimall-sidebar a:hover{background:#131c2f;color:#e5e7eb}
    .qimall-sidebar a.active{color:#fff;box-shadow:inset 3px 0 0 var(--primary);background:#111827}
    /* 側欄收合 */
    body.sidebar-collapsed .qimall-sidebar{width:64px}
    body.sidebar-collapsed .qimall-sidebar a{justify-content:center;padding:12px}
    body.sidebar-collapsed .qimall-sidebar a span:last-child{display:none}
    .qimall-portal-main{flex:1;display:flex;flex-direction:column}
    .qimall-portal-body{flex:1;padding:20px;display:flex;flex-direction:column;gap:16px}
    .el-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:14px}
    .qimall-table-wrap{background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:auto;box-shadow:0 10px 22px -12px rgba(0,0,0,.12)}
    .qimall-table{width:100%;border-collapse:separate;border-spacing:0}
    .qimall-table thead th{position:sticky;top:0;background:#f8fafc;color:#334155;font-weight:600;padding:10px 12px;border-bottom:1px solid var(--border);text-align:left}
    .qimall-table tbody tr{transition:background .15s ease}
    .qimall-table tbody tr:nth-child(odd){background:#fcfdff}
    .qimall-table tbody tr:hover{background:#f1f5f9}
    .qimall-table td{padding:10px 12px;font-size:13px;border-bottom:1px solid #f1f5f9;color:#4b5563}
    .el-button{display:inline-flex;align-items:center;gap:6px;border:1px solid #d1d5db;background:#fff;color:#374151;cursor:pointer;border-radius:6px;padding:7px 14px;font-size:13px;transition:filter .15s ease,background .15s ease,box-shadow .15s ease}
    .el-button:hover{background:#f3f4f6;filter:brightness(.98)}
    .el-button:active{filter:brightness(.95)}
    .el-button:disabled{opacity:.6;cursor:not-allowed}
    .el-button--primary{background:var(--primary);border-color:var(--primary);color:#fff;box-shadow:0 6px 16px -10px var(--primary)}
    .el-button--success{background:#10b981;border-color:#10b981;color:#fff}
    .el-button--warning{background:#f59e0b;border-color:#f59e0b;color:#fff}
    .el-button--danger{background:#ef4444;border-color:#ef4444;color:#fff}
    .el-button--info{background:#0ea5e9;border-color:#0ea5e9;color:#fff}
    .el-button--small{padding:4px 10px;font-size:12px;border-radius:5px}
    .el-input input,.el-select select{width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:6px;background:#fff;color:var(--text);outline:none}
    .el-input input:focus,.el-select select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,70,229,.15)}
    .el-pagination{display:flex;align-items:center;gap:8px;margin-top:14px}
    .qimall-login-center{display:flex;justify-content:center;align-items:center;min-height:80vh;padding:20px}
    .qimall-login-card{background:var(--card);padding:32px 30px;border-radius:14px;box-shadow:0 10px 28px -6px rgba(0,0,0,0.12);width:100%;max-width:380px}
    /* KPI cards */
    .qimall-kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px}
    .qimall-kpi{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:12px;display:flex;gap:10px;align-items:center}
    .qimall-kpi .icon{font-size:18px}
    .qimall-kpi .label{color:#334155}
    .qimall-kpi .num{margin-left:auto;font-weight:700;color:#111827}
  </style>
</head>
<body <?php body_class('qimall-client-portal'); ?>>
  <div class="qimall-portal-wrapper">
    <?php if ($is_login_page): ?>
      <div class="qimall-login-center">
        <div class="qimall-login-card">
          <h2>客戶登入（QiMall）</h2>
          <?php wp_login_form(array(
            'redirect'       => home_url('/client-admin'),
            'label_username' => '帳號',
            'label_password' => '密碼',
            'label_log_in'   => '登入',
            'remember'       => true,
          )); ?>
          <p class="qimall-login-hint">登入後將進入客戶後台。如有問題請聯絡管理員。</p>
        </div>
      </div>
    <?php elseif ($is_admin_page): ?>
      <div class="qimall-portal-header">
        <div class="qimall-portal-brand">
          <button id="sidebar-toggle" class="el-button" title="收合側欄" style="padding:6px 10px;line-height:1;">☰</button>
          <?php if(!empty($branding['logo_url'])): ?>
            <img src="<?php echo esc_url($branding['logo_url']); ?>" alt="Logo" style="height:32px;width:auto;border-radius:6px;" />
          <?php else: ?>
            <div class="qimall-portal-logo">🅠</div>
          <?php endif; ?>
          <?php echo esc_html($portal_title); ?>
        </div>
        <div class="qimall-portal-user">您好，<?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?> | <a href="<?php echo esc_url(wp_logout_url(home_url('/client-login'))); ?>">登出</a></div>
      </div>
      <div class="qimall-portal-layout">
        <aside class="qimall-sidebar">
          <a href="#" class="active" data-tab="overview"><span class="icon">📊</span><span>總覽</span></a>
          <a href="#members" data-tab="members"><span class="icon">👥</span><span>會員</span></a>
          <a href="#rewards" data-tab="rewards"><span class="icon">💰</span><span>收益</span></a>
          <a href="#orders" data-tab="orders"><span class="icon">🧾</span><span>訂單</span></a>
          <a href="#groups" data-tab="groups"><span class="icon">🏷️</span><span>制度組</span></a>
          <a href="#products" data-tab="products"><span class="icon">🛒</span><span>商品</span></a>
          <a href="#settings" data-tab="settings"><span class="icon">⚙️</span><span>設定</span></a>
        </aside>
        <div class="qimall-portal-main">
          <div class="qimall-portal-body" id="qimall-portal-root">
            <div id="tab-overview" class="tab-panel">
              <div class="qimall-kpi-grid" id="qimall-kpi">
                <div class="qimall-kpi" data-key="total_clients"><div class="icon">👥</div><div class="label">會員數</div><div class="num">—</div></div>
                <div class="qimall-kpi" data-key="total_orders"><div class="icon">📦</div><div class="label">訂單數</div><div class="num">—</div></div>
                <div class="qimall-kpi" data-key="paid_rewards"><div class="icon">✅</div><div class="label">已發放收益</div><div class="num">—</div></div>
                <div class="qimall-kpi" data-key="pending_rewards"><div class="icon">⏳</div><div class="label">待發放收益</div><div class="num">—</div></div>
              </div>
              <p id="qimall-kpi-status">載入中...</p>
            </div>
            <div id="tab-members" class="tab-panel" style="display:none;">
              <h2 style="margin:4px 0 16px;">會員列表</h2>
              <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
                <div class="el-input" style="max-width:240px;"><input type="text" id="member-search" placeholder="搜尋名稱/Email" /></div>
                <div class="el-select" style="max-width:180px;"><select id="member-role">
                  <option value="">全部角色</option>
                  <option value="QiMall_client_admin">Admin</option>
                  <option value="QiMall_client_manager">Manager</option>
                  <option value="QiMall_client_viewer">Viewer</option>
                </select></div>
                <button id="member-reload" class="el-button el-button--primary">重新載入</button>
              </div>
              <div class="qimall-table-wrap">
                <table class="qimall-table">
                  <thead><tr>
                    <th>ID</th><th>名稱</th><th>Email</th><th>角色</th><th>註冊時間</th><th>推薦人</th>
                  </tr></thead>
                  <tbody id="members-tbody"></tbody>
                </table>
              </div>
              <div class="el-pagination">
                <button id="page-prev" class="el-button" disabled>上一頁</button>
                <span id="page-info">Page 1 / 1</span>
                <button id="page-next" class="el-button" disabled>下一頁</button>
              </div>
              <p id="members-status" style="font-size:12px;color:#6b7280;margin-top:10px;">尚未載入</p>
            </div>
            <div id="tab-rewards" class="tab-panel" style="display:none;">
              <h2 style="margin:4px 0 16px;">收益記錄</h2>
              <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
                <div class="el-select" style="max-width:160px;"><select id="rewards-status">
                  <option value="">全部狀態</option>
                  <option value="pending">待結算</option>
                  <option value="calculated">已計算</option>
                  <option value="paid">已發放</option>
                </select></div>
                <div class="el-select" style="max-width:160px;"><select id="rewards-type">
                  <option value="">全部類型</option>
                  <option value="profit">利潤</option>
                  <option value="dividend">分紅</option>
                  <option value="referral">推薦</option>
                  <option value="distribution">分銷</option>
                </select></div>
                <button id="rewards-reload" class="el-button el-button--primary">重新載入</button>
              </div>
              <div class="qimall-table-wrap">
                <table class="qimall-table">
                  <thead><tr>
                    <th>ID</th><th>用戶</th><th>類型</th><th>金額</th><th>狀態</th><th>建立時間</th><th>來源訂單</th>
                  </tr></thead>
                  <tbody id="rewards-tbody"></tbody>
                </table>
              </div>
              <div class="el-pagination">
                <button id="rewards-prev" class="el-button" disabled>上一頁</button>
                <span id="rewards-page">Page 1 / 1</span>
                <button id="rewards-next" class="el-button" disabled>下一頁</button>
                <span id="rewards-total" style="margin-left:auto;">合計：—</span>
              </div>
              <p id="rewards-status-text" style="font-size:12px;color:#6b7280;margin-top:10px;">尚未載入</p>
            </div>
            <div id="tab-orders" class="tab-panel" style="display:none;">
              <h2 style="margin:4px 0 16px;">訂單</h2>
              <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
                <div class="el-input" style="max-width:220px;"><input type="text" id="orders-search" placeholder="搜尋訂單/客戶" /></div>
                <div class="el-select" style="max-width:160px;"><select id="orders-status">
                  <option value="">全部</option>
                  <option value="completed">已完成</option>
                  <option value="processing">處理中</option>
                  <option value="on-hold">保留</option>
                  <option value="cancelled">已取消</option>
                  <option value="refunded">已退款</option>
                </select></div>
                <button id="orders-reload" class="el-button el-button--primary">重新載入</button>
              </div>
              <div class="qimall-table-wrap">
                <table class="qimall-table">
                  <thead><tr>
                    <th>ID</th><th>下單時間</th><th>狀態</th><th>總額</th><th>幣別</th><th>客戶</th>
                  </tr></thead>
                  <tbody id="orders-tbody"></tbody>
                </table>
              </div>
              <div class="el-pagination">
                <button id="orders-prev" class="el-button" disabled>上一頁</button>
                <span id="orders-page">Page 1 / 1</span>
                <button id="orders-next" class="el-button" disabled>下一頁</button>
                <span id="orders-status-text" style="margin-left:auto;color:#6b7280;font-size:12px;">尚未載入</span>
              </div>
            </div>
            <div id="tab-groups" class="tab-panel" style="display:none;">
              <h2 style="margin:4px 0 16px;">制度組</h2>
              <div class="qimall-table-wrap">
                <table class="qimall-table">
                  <thead><tr>
                    <th>代碼</th><th>名稱</th><th>股東商品</th><th>合夥商品</th><th>啟用</th><th>操作</th>
                  </tr></thead>
                  <tbody id="groups-tbody"></tbody>
                </table>
              </div>
              <div style="margin-top:12px;display:flex;gap:10px;">
                <input type="text" id="group-code" placeholder="代碼" style="padding:6px 10px;" />
                <input type="text" id="group-name" placeholder="名稱" style="padding:6px 10px;" />
                <input type="text" id="group-shareholder" placeholder="股東商品名" style="padding:6px 10px;" />
                <input type="text" id="group-partner" placeholder="合夥商品名" style="padding:6px 10px;" />
                <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" id="group-active" /> 啟用</label>
                <button id="group-save" class="el-button el-button--primary">新增制度組</button>
                <span id="groups-status" style="margin-left:auto;color:#6b7280;font-size:12px;">尚未載入</span>
              </div>
            </div>
            <div id="tab-products" class="tab-panel" style="display:none;">
              <h2 style="margin:4px 0 16px;">商品</h2>
              <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
                <div class="el-input" style="max-width:220px;"><input type="text" id="products-search" placeholder="搜尋商品" /></div>
                <div class="el-input" style="max-width:180px;"><input type="text" id="products-group" placeholder="制度組代碼" /></div>
                <div class="el-select" style="max-width:140px;"><select id="products-enabled">
                  <option value="">全部</option>
                  <option value="1">已啟用</option>
                  <option value="0">未啟用</option>
                </select></div>
                <button id="products-reload" class="el-button el-button--primary">重新載入</button>
                <button id="products-bulk-enable" class="el-button el-button--success el-button--small" title="批次啟用 Co-Share" disabled>批次啟用</button>
                <button id="products-bulk-disable" class="el-button el-button--warning el-button--small" title="批次停用 Co-Share" disabled>批次停用</button>
                <button id="products-clear-selection" class="el-button el-button--small" disabled>清除選取</button>
                <button id="products-export" class="el-button el-button--info el-button--small" title="匯出目前頁面 CSV">匯出 CSV</button>
              </div>
              <div class="qimall-table-wrap">
                <table class="qimall-table">
                  <thead><tr>
                    <th style="width:40px;"><input type="checkbox" id="products-select-all" title="全選" /></th>
                    <th data-sort="id" class="sortable" title="點擊排序">品號</th>
                    <th data-sort="name" class="sortable" title="點擊排序">品名</th>
                    <th data-sort="enabled" class="sortable" title="點擊排序">啟用</th>
                    <th data-sort="group" class="sortable" title="點擊排序">制度組</th>
                    <th data-sort="ptype" class="sortable" title="點擊排序">商品類型</th>
                    <th data-sort="dist" class="sortable" title="點擊排序">分銷啟用</th>
                    <th>操作</th>
                  </tr></thead>
                  <tbody id="products-tbody"></tbody>
                </table>
              </div>
              <div class="el-pagination">
                <button id="products-prev" class="el-button" disabled>上一頁</button>
                <span id="products-page">Page 1 / 1</span>
                <button id="products-next" class="el-button" disabled>下一頁</button>
                <span id="products-status" style="margin-left:auto;color:#6b7280;font-size:12px;">尚未載入</span>
              </div>
            </div>
            <div id="tab-settings" class="tab-panel" style="display:none;">
              <h2 style="margin:4px 0 16px;">設定</h2>
              <div class="el-card" style="margin-bottom:12px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <div class="el-input" style="max-width:240px;"><input type="text" id="set-title" placeholder="後台標題（預設 QiMall管理系統）" /></div>
                <div class="el-input" style="max-width:360px;"><input type="text" id="set-logo" placeholder="Logo URL" /></div>
                <div class="el-input" style="max-width:160px;"><input type="text" id="set-primary" placeholder="#4f46e5 主色" /></div>
                <button id="set-save" class="el-button el-button--primary">儲存設定</button>
                <span id="set-status" style="margin-left:auto;color:#6b7280;font-size:12px;">尚未載入</span>
              </div>
              <div style="display:flex;gap:16px;align-items:center;">
                <img id="set-preview-logo" src="" alt="Logo" style="height:36px;display:none;border-radius:6px;" />
                <span id="set-preview-title"><?php echo esc_html($portal_title); ?></span>
              </div>
              <div style="margin-top:18px;" class="el-card">
                <h3 style="margin:0 0 8px;">業務參數</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
                  <div class="el-input"><input type="number" id="b-ref-cookie" placeholder="推薦 Cookie 天數 (30)" /></div>
                  <div class="el-input"><input type="number" id="b-ref-default" placeholder="預設推薦人 User ID (0)" /></div>
                  <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" id="b-ref-self" /> 允許自我推薦</label>
                  <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" id="b-t3r1-enabled" /> 啟用推三返一</label>
                  <div class="el-input"><input type="number" id="b-t3r1-count" placeholder="推三返一人數 (3)" /></div>
                  <div class="el-select"><select id="b-t3r1-type"><option value="percentage">百分比</option><option value="fixed">固定額</option></select></div>
                  <div class="el-input"><input type="number" id="b-t3r1-value" placeholder="返還值 (100)" /></div>
                  <div class="el-input"><input type="number" id="b-t3r1-days" placeholder="限期天數 (0=不限)" /></div>
                  <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" id="b-dist-enabled" /> 啟用分銷</label>
                  <div class="el-input"><input type="number" id="b-dist-levels" placeholder="分銷層級 (2)" /></div>
                  <div class="el-input"><input type="number" id="b-dist-l1" placeholder="L1 ％ (5)" /></div>
                  <div class="el-input"><input type="number" id="b-dist-l2" placeholder="L2 ％ (2)" /></div>
                  <div class="el-select"><select id="b-reward-rounding"><option value="round">四捨五入</option><option value="floor">無條件捨去</option><option value="ceil">無條件進位</option></select></div>
                  <div class="el-input"><input type="number" id="b-reward-decimals" placeholder="小數位數 (2)" /></div>
                  <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" id="b-orders-clawback" /> 退款回收收益</label>
                  <div class="el-input"><input type="number" id="b-payout-min" placeholder="最低出款 (100)" /></div>
                </div>
                <div id="b-order-statuses" style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
                  <?php foreach(array('completed','processing','on-hold','pending','refunded','cancelled') as $st): ?>
                    <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" value="<?php echo esc_attr($st); ?>" /> <?php echo esc_html($st); ?></label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <script>
  (function(){
    const nonce = '<?php echo esc_js($rest_nonce); ?>';
    const navLinks = document.querySelectorAll('.qimall-sidebar a');
    const panels = document.querySelectorAll('.tab-panel');
    navLinks.forEach(a=>{
      a.addEventListener('click',e=>{
        e.preventDefault();
        navLinks.forEach(n=>n.classList.remove('active'));
        a.classList.add('active');
        panels.forEach(p=>p.style.display='none');
        const t=document.getElementById('tab-'+a.getAttribute('data-tab'));
        if(t){t.style.display='block';}
        if(a.getAttribute('data-tab')==='members' && !window.__membersLoaded){ fetchMembers(1); }
      });
    });

    // 側欄收合切換
    const toggleBtn = document.getElementById('sidebar-toggle');
    if(toggleBtn){
      toggleBtn.addEventListener('click',()=>{
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('qimallSidebarCollapsed', document.body.classList.contains('sidebar-collapsed')?'1':'0');
      });
      if(localStorage.getItem('qimallSidebarCollapsed')==='1'){
        document.body.classList.add('sidebar-collapsed');
      }
    }

    // KPI
    if(document.getElementById('qimall-kpi')){
      fetch('<?php echo esc_url( rest_url('qimall/v1/stats') ); ?>', { headers: { 'X-WP-Nonce': nonce } })
        .then(r=>r.json()).then(body=>{ const data=body.data||{}; document.querySelectorAll('#qimall-kpi .qimall-kpi').forEach(card=>{ const key=card.getAttribute('data-key'); if(key in data){ card.querySelector('.num').textContent=data[key]; } }); document.getElementById('qimall-kpi-status').textContent='已更新'; })
        .catch(err=>{ console.error(err); document.getElementById('qimall-kpi-status').textContent='載入失敗'; });
    }

    // Members
    const membersTbody=document.getElementById('members-tbody'); const pagePrev=document.getElementById('page-prev'); const pageNext=document.getElementById('page-next'); const pageInfo=document.getElementById('page-info'); const memberSearch=document.getElementById('member-search'); const memberRole=document.getElementById('member-role'); const memberReload=document.getElementById('member-reload'); let currentPage=1,totalPages=1,isLoading=false;
    function fetchMembers(page){ if(isLoading) return; isLoading=true; document.getElementById('members-status').textContent='載入中...'; const params=new URLSearchParams(); params.set('page',page); params.set('per_page',20); if(memberSearch.value.trim()) params.set('search',memberSearch.value.trim()); if(memberRole.value) params.set('role',memberRole.value); fetch('<?php echo esc_url(rest_url('qimall/v1/members')); ?>?'+params.toString(),{headers:{'X-WP-Nonce':nonce}}).then(r=>r.json()).then(json=>{ if(!json.success) throw new Error('members fail'); const d=json.data||{}; membersTbody.innerHTML=''; (d.items||[]).forEach(m=>{ const tr=document.createElement('tr'); tr.innerHTML='<td>'+m.id+'</td>'+'<td>'+m.name+'</td>'+'<td>'+m.email+'</td>'+'<td>'+ (m.role||'—') +'</td>'+'<td>'+ (m.registered_at||'').toString().substring(0,19) +'</td>'+'<td>'+ (m.referrer_id?('User '+m.referrer_id):'—') +'</td>'; membersTbody.appendChild(tr); }); currentPage=d.page||1; totalPages=d.total_pages||1; pageInfo.textContent='Page '+currentPage+' / '+totalPages; pagePrev.disabled=currentPage<=1; pageNext.disabled=currentPage>=totalPages; document.getElementById('members-status').textContent='已載入，共 '+(d.total||0)+' 筆'; window.__membersLoaded=true; }).catch(err=>{ document.getElementById('members-status').textContent='載入失敗'; console.error(err); }).finally(()=>{ isLoading=false; }); }
    if(pagePrev){ pagePrev.addEventListener('click',()=>{ if(currentPage>1) fetchMembers(currentPage-1); }); }
    if(pageNext){ pageNext.addEventListener('click',()=>{ if(currentPage<totalPages) fetchMembers(currentPage+1); }); }
    if(memberReload){ memberReload.addEventListener('click',()=>fetchMembers(1)); }
    if(memberRole){ memberRole.addEventListener('change',()=>fetchMembers(1)); }

    // Rewards
    const rewardsTbody=document.getElementById('rewards-tbody'); const rewardsStatus=document.getElementById('rewards-status-text'); const rewardsPrev=document.getElementById('rewards-prev'); const rewardsNext=document.getElementById('rewards-next'); const rewardsPage=document.getElementById('rewards-page'); const rewardsTotal=document.getElementById('rewards-total'); const rewardsStatusSel=document.getElementById('rewards-status'); const rewardsTypeSel=document.getElementById('rewards-type'); const rewardsReload=document.getElementById('rewards-reload'); let rewardsCurrent=1, rewardsTotalPages=1, rewardsLoading=false;
    function fetchRewards(page){ if(rewardsLoading) return; rewardsLoading=true; rewardsStatus.textContent='載入中...'; const params=new URLSearchParams(); params.set('page',page); params.set('per_page',20); if(rewardsStatusSel.value) params.set('status',rewardsStatusSel.value); if(rewardsTypeSel.value) params.set('reward_type',rewardsTypeSel.value); fetch('<?php echo esc_url(rest_url('qimall/v1/rewards')); ?>?'+params.toString(),{headers:{'X-WP-Nonce':nonce}}).then(r=>r.json()).then(json=>{ if(!json.success) throw new Error('rewards fail'); const d=json.data||{}; rewardsTbody.innerHTML=''; (d.items||[]).forEach(it=>{ const tr=document.createElement('tr'); tr.innerHTML='<td>'+it.id+'</td>'+'<td>'+it.user_id+'</td>'+'<td>'+it.reward_type+'</td>'+'<td>'+Number(it.amount).toFixed(2)+'</td>'+'<td>'+it.status+'</td>'+'<td>'+ (it.created_at||'').toString().substring(0,19) +'</td>'+'<td>'+ (it.source_order_id||'—') +'</td>'; rewardsTbody.appendChild(tr); }); rewardsCurrent=d.page||1; rewardsTotalPages=d.total_pages||1; rewardsPage.textContent='Page '+rewardsCurrent+' / '+rewardsTotalPages; rewardsPrev.disabled=rewardsCurrent<=1; rewardsNext.disabled=rewardsCurrent>=rewardsTotalPages; rewardsTotal.textContent='合計：'+ Number(d.total_amount||0).toFixed(2); rewardsStatus.textContent='已載入，共 '+(d.total||0)+' 筆'; window.__rewardsLoaded=true; }).catch(err=>{ rewardsStatus.textContent='載入失敗'; console.error(err); }).finally(()=>{ rewardsLoading=false; }); }
    if(rewardsPrev){ rewardsPrev.addEventListener('click',()=>{ if(rewardsCurrent>1) fetchRewards(rewardsCurrent-1); }); }
    if(rewardsNext){ rewardsNext.addEventListener('click',()=>{ if(rewardsCurrent<rewardsTotalPages) fetchRewards(rewardsCurrent+1); }); }
    if(rewardsReload){ rewardsReload.addEventListener('click',()=>fetchRewards(1)); }
    if(rewardsStatusSel){ rewardsStatusSel.addEventListener('change',()=>fetchRewards(1)); }
    if(rewardsTypeSel){ rewardsTypeSel.addEventListener('change',()=>fetchRewards(1)); }
    document.querySelectorAll('.qimall-sidebar a[data-tab="rewards"]').forEach(a=>{ a.addEventListener('click',()=>{ if(!window.__rewardsLoaded){ fetchRewards(1); } }); });

    // Orders
    const ordersTbody=document.getElementById('orders-tbody'); const ordersStatus=document.getElementById('orders-status-text'); const ordersPrev=document.getElementById('orders-prev'); const ordersNext=document.getElementById('orders-next'); const ordersPage=document.getElementById('orders-page'); const ordersSearch=document.getElementById('orders-search'); const ordersStatusSel=document.getElementById('orders-status'); const ordersReload=document.getElementById('orders-reload'); let ordersCurrent=1, ordersTotalPages=1, ordersLoading=false;
    function fetchOrders(page){ if(ordersLoading) return; ordersLoading=true; ordersStatus.textContent='載入中...'; const params=new URLSearchParams(); params.set('page',page); params.set('per_page',20); if(ordersSearch.value.trim()) params.set('search',ordersSearch.value.trim()); if(ordersStatusSel.value) params.set('status',ordersStatusSel.value); fetch('<?php echo esc_url(rest_url('qimall/v1/orders')); ?>?'+params.toString(),{headers:{'X-WP-Nonce':nonce}}).then(r=>r.json()).then(json=>{ if(!json.success) throw new Error('orders fail'); const d=json.data||{}; ordersTbody.innerHTML=''; (d.items||[]).forEach(it=>{ const tr=document.createElement('tr'); tr.innerHTML='<td>'+it.id+'</td>'+'<td>'+ (it.date||'').toString().substring(0,19) +'</td>'+'<td>'+it.status+'</td>'+'<td>'+ Number(it.total).toFixed(2) +'</td>'+'<td>'+ (it.currency||'') +'</td>'+'<td>'+ (it.customer_name||('User '+it.customer_id)) +'</td>'; ordersTbody.appendChild(tr); }); ordersCurrent=d.page||1; ordersTotalPages=d.total_pages||1; ordersPage.textContent='Page '+ordersCurrent+' / '+ordersTotalPages; ordersPrev.disabled=ordersCurrent<=1; ordersNext.disabled=ordersCurrent>=ordersTotalPages; ordersStatus.textContent='已載入，共 '+(d.total||0)+' 筆'; window.__ordersLoaded=true; }).catch(err=>{ ordersStatus.textContent='載入失敗'; console.error(err); }).finally(()=>{ ordersLoading=false; }); }
    if(ordersPrev){ ordersPrev.addEventListener('click',()=>{ if(ordersCurrent>1) fetchOrders(ordersCurrent-1); }); }
    if(ordersNext){ ordersNext.addEventListener('click',()=>{ if(ordersCurrent<ordersTotalPages) fetchOrders(ordersCurrent+1); }); }
    if(ordersReload){ ordersReload.addEventListener('click',()=>fetchOrders(1)); }
    if(ordersStatusSel){ ordersStatusSel.addEventListener('change',()=>fetchOrders(1)); }
    document.querySelectorAll('.qimall-sidebar a[data-tab="orders"]').forEach(a=>{ a.addEventListener('click',()=>{ if(!window.__ordersLoaded){ fetchOrders(1); } }); });

    // Groups
    const groupsTbody=document.getElementById('groups-tbody'); const groupsStatus=document.getElementById('groups-status'); const groupCode=document.getElementById('group-code'); const groupName=document.getElementById('group-name'); const groupShare=document.getElementById('group-shareholder'); const groupPartner=document.getElementById('group-partner'); const groupActive=document.getElementById('group-active'); const groupSave=document.getElementById('group-save');
    function loadGroups(){ groupsStatus.textContent='載入中...'; fetch('<?php echo esc_url(rest_url('qimall/v1/groups')); ?>', {headers:{'X-WP-Nonce':nonce}}).then(r=>r.json()).then(json=>{ const items=json.data||[]; groupsTbody.innerHTML=''; items.forEach(g=>{ const tr=document.createElement('tr'); tr.innerHTML='<td>'+g.group_code+'</td>'+'<td>'+g.group_name+'</td>'+'<td>'+g.shareholder_product_name+'</td>'+'<td>'+g.partner_product_name+'</td>'+'<td>'+ (g.is_active? '✅' : '⏸️') +'</td>'+'<td><button class="el-button el-button--danger el-button--small" data-action="delete" data-code="'+g.group_code+'">刪除</button></td>'; groupsTbody.appendChild(tr); }); groupsTbody.querySelectorAll('button[data-action="delete"]').forEach(btn=>{ btn.addEventListener('click',()=>{ const code=btn.getAttribute('data-code'); if(!confirm('確定刪除制度組 '+code+' 嗎？此操作不可逆。')) return; groupsStatus.textContent='刪除中...'; fetch('<?php echo esc_url(rest_url('qimall/v1/groups')); ?>?group_code='+encodeURIComponent(code),{ method:'DELETE', headers:{'X-WP-Nonce':nonce} }).then(r=>r.json()).then(j=>{ if(!j.success) throw new Error('delete fail'); loadGroups(); }).catch(err=>{ groupsStatus.textContent='刪除失敗（可能仍有商品綁定）'; console.error(err); }); }); }); groupsStatus.textContent='已載入，共 '+items.length+' 組'; window.__groupsLoaded=true; }).catch(err=>{ groupsStatus.textContent='載入失敗'; console.error(err); }); }
    if(groupSave){ groupSave.addEventListener('click',()=>{ groupsStatus.textContent='儲存中...'; fetch('<?php echo esc_url(rest_url('qimall/v1/groups')); ?>',{ method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':nonce}, body:JSON.stringify({ group_code:groupCode.value.trim(), group_name:groupName.value.trim(), shareholder_product_name:groupShare.value.trim(), partner_product_name:groupPartner.value.trim(), is_active:groupActive.checked?1:0 }) }).then(r=>r.json()).then(j=>{ if(!j.success) throw new Error('save fail'); loadGroups(); }).catch(err=>{ groupsStatus.textContent='儲存失敗'; console.error(err); }); }); }
    document.querySelectorAll('.qimall-sidebar a[data-tab="groups"]').forEach(a=>{ a.addEventListener('click',()=>{ if(!window.__groupsLoaded){ loadGroups(); } }); });

    // Products
    const productsTbody=document.getElementById('products-tbody'); const productsPrev=document.getElementById('products-prev'); const productsNext=document.getElementById('products-next'); const productsPage=document.getElementById('products-page'); const productsStatus=document.getElementById('products-status'); const productsSearch=document.getElementById('products-search'); const productsGroup=document.getElementById('products-group'); const productsEnabled=document.getElementById('products-enabled'); const productsBulkEnable=document.getElementById('products-bulk-enable'); const productsBulkDisable=document.getElementById('products-bulk-disable'); const productsClearSel=document.getElementById('products-clear-selection'); const productsExport=document.getElementById('products-export'); const productsSelectAll=document.getElementById('products-select-all'); let productsCurrent=1, productsTotalPages=1, productsLoading=false; let productsSelection=new Set(); let productsLastItems=[]; let productsSortField=null, productsSortDir='asc';
    function updateBulkButtons(){ const hasSel=productsSelection.size>0; if(productsBulkEnable) productsBulkEnable.disabled=!hasSel; if(productsBulkDisable) productsBulkDisable.disabled=!hasSel; if(productsClearSel) productsClearSel.disabled=!hasSel; if(productsSelectAll){ const rows=productsTbody.querySelectorAll('input[type="checkbox"][data-product]'); const allChecked=[...rows].length>0 && [...rows].every(cb=>cb.checked); productsSelectAll.checked=allChecked; } }
    function applySorting(arr){ if(!productsSortField) return arr; const f=productsSortField; const dir=productsSortDir==='asc'?1:-1; return arr.slice().sort((a,b)=>{ let va,vb; switch(f){ case 'id': va=a.id; vb=b.id; break; case 'name': va=(a.name||'').toLowerCase(); vb=(b.name||'').toLowerCase(); break; case 'enabled': va=a.QiMall_enabled?1:0; vb=b.QiMall_enabled?1:0; break; case 'group': va=(a.group_code||'').toLowerCase(); vb=(b.group_code||'').toLowerCase(); break; case 'ptype': va=(a.product_type||'').toLowerCase(); vb=(b.product_type||'').toLowerCase(); break; case 'dist': va=a.distribution_enabled?1:0; vb=b.distribution_enabled?1:0; break; default: va=''; vb=''; } if(va<vb) return -1*dir; if(va>vb) return 1*dir; return 0; }); }
    function setSort(field){ if(productsSortField===field){ productsSortDir=productsSortDir==='asc'?'desc':'asc'; } else { productsSortField=field; productsSortDir='asc'; } document.querySelectorAll('#tab-products thead th.sortable').forEach(th=>{ const f=th.getAttribute('data-sort'); const base=th.getAttribute('data-base-label')||th.textContent.replace(/[▲▼]\s*$/,''); th.setAttribute('data-base-label', base); if(f===productsSortField){ th.textContent=base+(productsSortDir==='asc'?' ▲':' ▼'); } else { th.textContent=base; } }); loadProducts(productsCurrent); }
    function loadProducts(page){ if(productsLoading) return; productsLoading=true; productsStatus.textContent='載入中...'; const params=new URLSearchParams(); params.set('page',page); params.set('per_page',20); if(productsSearch.value.trim()) params.set('search',productsSearch.value.trim()); if(productsGroup.value.trim()) params.set('group_code',productsGroup.value.trim()); if(productsEnabled.value!=='') params.set('QiMall_enabled',productsEnabled.value); fetch('<?php echo esc_url(rest_url('qimall/v1/products')); ?>?'+params.toString(),{headers:{'X-WP-Nonce':nonce}}).then(r=>r.json()).then(json=>{ if(!json.success) throw new Error('products fail'); const d=json.data||{}; productsTbody.innerHTML=''; productsLastItems = d.items||[]; const items=applySorting(productsLastItems); items.forEach(it=>{ const tr=document.createElement('tr'); const checked = productsSelection.has(it.id)?' checked':''; tr.innerHTML='<td><input type="checkbox" data-product="'+it.id+'"'+checked+' /></td>'+'<td>'+it.id+'</td>'+'<td>'+it.name+'</td>'+'<td>'+ (it.QiMall_enabled? '✅' : '⏸️') +'</td>'+'<td>'+ (it.group_code||'—') +'</td>'+'<td>'+ (it.product_type||'—') +'</td>'+'<td>'+ (it.distribution_enabled? '✅' : '⏸️') +'</td>'+'<td><button class="el-button el-button--info el-button--small" data-action="edit" data-id="'+it.id+'">快速編輯</button></td>'; productsTbody.appendChild(tr); }); productsTbody.querySelectorAll('input[type="checkbox"][data-product]').forEach(cb=>{ cb.addEventListener('change',()=>{ const pid=parseInt(cb.getAttribute('data-product')); if(cb.checked) productsSelection.add(pid); else productsSelection.delete(pid); updateBulkButtons(); }); }); productsTbody.querySelectorAll('button[data-action="edit"]').forEach(btn=>{ btn.addEventListener('click',()=>{ const id=parseInt(btn.getAttribute('data-id')); const tr=btn.closest('tr'); const name=tr.children[2].textContent; const enabled=tr.children[3].textContent.includes('✅'); const group=tr.children[4].textContent==='—'?'':tr.children[4].textContent; const ptype=tr.children[5].textContent==='—'?'':tr.children[5].textContent; const dist=tr.children[6].textContent.includes('✅'); const editor=document.createElement('tr'); editor.innerHTML='<td colspan="8">'+'<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">'+'<strong>編輯：'+name+'</strong>'+'<label style="display:flex;gap:6px;align-items:center"><input type="checkbox" id="edit-enabled"> 啟用 Co-Share</label>'+'<div class="el-input" style="max-width:180px;"><input type="text" id="edit-group" placeholder="制度組代碼" /></div>'+'<div class="el-input" style="max-width:240px;"><input type="text" id="edit-type" placeholder="商品類型（如 shareholder/partner）" /></div>'+'<label style="display:flex;gap:6px;align-items:center"><input type="checkbox" id="edit-dist"> 分銷啟用</label>'+'<button class="el-button el-button--primary el-button--small" id="edit-save">儲存</button>'+'<button class="el-button el-button--small" id="edit-cancel">取消</button>'+'</div>'+'</td>'; tr.after(editor); editor.querySelector('#edit-enabled').checked=enabled; editor.querySelector('#edit-group').value=group; editor.querySelector('#edit-type').value=ptype; editor.querySelector('#edit-dist').checked=dist; editor.querySelector('#edit-save').addEventListener('click',()=>{ productsStatus.textContent='儲存中...'; const payload={ product_id:id, QiMall_enabled:editor.querySelector('#edit-enabled').checked?1:0, group_code:editor.querySelector('#edit-group').value.trim(), product_type:editor.querySelector('#edit-type').value.trim(), distribution_enabled:editor.querySelector('#edit-dist').checked?1:0 }; fetch('<?php echo esc_url(rest_url('qimall/v1/products/meta')); ?>',{ method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':nonce}, body:JSON.stringify(payload) }).then(r=>r.json()).then(j=>{ if(!j.success) throw new Error('update meta fail'); editor.remove(); loadProducts(productsCurrent); }).catch(err=>{ productsStatus.textContent='儲存失敗（制度組不存在或驗證失敗）'; console.error(err); }); }); editor.querySelector('#edit-cancel').addEventListener('click',()=>{ editor.remove(); }); }); }); productsCurrent=d.page||1; productsTotalPages=d.total_pages||1; productsPage.textContent='Page '+productsCurrent+' / '+productsTotalPages; productsPrev.disabled=productsCurrent<=1; productsNext.disabled=productsCurrent>=productsTotalPages; productsStatus.textContent='已載入，共 '+(d.total||0)+' 筆'; window.__productsLoaded=true; updateBulkButtons(); }).catch(err=>{ productsStatus.textContent='載入失敗'; console.error(err); }).finally(()=>{ productsLoading=false; }); }
    if(productsPrev){ productsPrev.addEventListener('click',()=>{ if(productsCurrent>1) loadProducts(productsCurrent-1); }); }
    if(productsNext){ productsNext.addEventListener('click',()=>{ if(productsCurrent<productsTotalPages) loadProducts(productsCurrent+1); }); }
    if(productsSearch){ productsSearch.addEventListener('keydown',e=>{ if(e.key==='Enter'){ loadProducts(1); } }); }
    if(productsGroup){ productsGroup.addEventListener('keydown',e=>{ if(e.key==='Enter'){ loadProducts(1); } }); }
    if(productsEnabled){ productsEnabled.addEventListener('change',()=>{ loadProducts(1); }); }
    if(productsSelectAll){ productsSelectAll.addEventListener('change',()=>{ const rows=productsTbody.querySelectorAll('input[type="checkbox"][data-product]'); productsSelection.clear(); rows.forEach(cb=>{ cb.checked=productsSelectAll.checked; if(cb.checked) productsSelection.add(parseInt(cb.getAttribute('data-product'))); }); updateBulkButtons(); }); }
    function bulkUpdate(enable){ if(productsSelection.size===0) return; productsStatus.textContent='批次更新中...'; const ids=[...productsSelection]; let ok=0, fail=0; Promise.all(ids.map(id=>{ const payload={ product_id:id, QiMall_enabled:enable?1:0 }; return fetch('<?php echo esc_url(rest_url('qimall/v1/products/meta')); ?>',{ method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':nonce}, body:JSON.stringify(payload) }).then(r=>r.json()).then(j=>{ if(j.success) ok++; else fail++; }).catch(()=>{ fail++; }); })).then(()=>{ productsStatus.textContent='批次完成：成功 '+ok+' / 失敗 '+fail; loadProducts(productsCurrent); }); }
    if(productsBulkEnable){ productsBulkEnable.addEventListener('click',()=>bulkUpdate(true)); }
    if(productsBulkDisable){ productsBulkDisable.addEventListener('click',()=>bulkUpdate(false)); }
    if(productsClearSel){ productsClearSel.addEventListener('click',()=>{ productsSelection.clear(); productsTbody.querySelectorAll('input[type="checkbox"][data-product]').forEach(cb=>cb.checked=false); updateBulkButtons(); }); }
    if(productsExport){ productsExport.addEventListener('click',()=>{ if(!productsLastItems.length){ alert('無資料可匯出'); return; } const rows=['品號,品名,啟用,制度組,商品類型,分銷啟用']; applySorting(productsLastItems).forEach(it=>{ const r=['"'+it.id+'"','"'+(it.name||'')+'"','"'+(it.QiMall_enabled?1:0)+'"','"'+(it.group_code||'')+'"','"'+(it.product_type||'')+'"','"'+(it.distribution_enabled?1:0)+'"']; rows.push(r.join(',')); }); const blob=new Blob([rows.join('\n')],{type:'text/csv;charset=utf-8;'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='products_page_'+productsCurrent+'.csv'; document.body.appendChild(a); a.click(); a.remove(); }); }
    document.querySelectorAll('#tab-products thead th.sortable').forEach(th=>{ th.addEventListener('click',()=>{ setSort(th.getAttribute('data-sort')); }); });
    document.querySelectorAll('.qimall-sidebar a[data-tab="products"]').forEach(a=>{ a.addEventListener('click',()=>{ if(!window.__productsLoaded){ loadProducts(1); } }); });

    // Settings
    const setTitle=document.getElementById('set-title'); const setLogo=document.getElementById('set-logo'); const setPrimary=document.getElementById('set-primary'); const setSaveBtn=document.getElementById('set-save'); const setStatus=document.getElementById('set-status'); const setPreviewTitle=document.getElementById('set-preview-title'); const setPreviewLogo=document.getElementById('set-preview-logo');
    function applyBrandingToUI(data){
      if(data.portal_title){
        setPreviewTitle.textContent=data.portal_title;
        const brand=document.querySelector('.qimall-portal-brand');
        if(brand){
          brand.lastChild && (brand.lastChild.nodeType===3)
            ? (brand.lastChild.nodeValue=' '+data.portal_title)
            : (brand.appendChild(document.createTextNode(' '+data.portal_title)));
        }
      }
      if(typeof data.logo_url !== 'undefined'){
        if(data.logo_url){
          setPreviewLogo.src=data.logo_url; setPreviewLogo.style.display='block';
          const brandLogo = document.querySelector('.qimall-portal-brand img');
          const defaultLogo = document.querySelector('.qimall-portal-logo');
          if(brandLogo){ brandLogo.src=data.logo_url; brandLogo.style.display='inline-block'; }
          if(defaultLogo){ defaultLogo.style.display='none'; }
        } else {
          setPreviewLogo.style.display='none';
          const brandLogo = document.querySelector('.qimall-portal-brand img');
          const defaultLogo = document.querySelector('.qimall-portal-logo');
          if(brandLogo){ brandLogo.style.display='none'; }
          if(defaultLogo){ defaultLogo.style.display='flex'; }
        }
      }
      if(data.primary_color){
        document.body.style.setProperty('--primary', data.primary_color);
        // 強制刷新主要按鈕顏色（避免舊快取）
        document.querySelectorAll('.el-button--primary').forEach(btn=>{
          btn.style.background = 'var(--primary)';
          btn.style.borderColor = 'var(--primary)';
        });
      }
    }
    function loadSettings(){
      setStatus.textContent='載入設定中...';
      fetch('<?php echo esc_url(rest_url('qimall/v1/settings')); ?>', {headers:{'X-WP-Nonce':nonce}})
        .then(r=>r.json())
        .then(json=>{
          const d=json.data||{};
            if(typeof d.portal_title!=='undefined') setTitle.value=d.portal_title;
            if(typeof d.logo_url!=='undefined') setLogo.value=d.logo_url;
            if(typeof d.primary_color!=='undefined') {
              setPrimary.value=d.primary_color;
              document.body.style.setProperty('--primary', d.primary_color);
            }
            applyBrandingToUI(d);
            const b=d.business||{}; const br=b.referral||{}; const bt=b.t3r1||{}; const bd=b.distribution||{}; const rw=b.rewards||{}; const od=b.orders||{}; const po=b.payout||{}; const qs=(id)=>document.getElementById(id);
            if(qs('b-ref-cookie')) qs('b-ref-cookie').value=br.cookie_days??30;
            if(qs('b-ref-default')) qs('b-ref-default').value=br.default_referrer_user_id??0;
            if(qs('b-ref-self')) qs('b-ref-self').checked=!!br.allow_self_referral;
            if(qs('b-t3r1-enabled')) qs('b-t3r1-enabled').checked=!!bt.enabled;
            if(qs('b-t3r1-count')) qs('b-t3r1-count').value=bt.required_count??3;
            if(qs('b-t3r1-type')) qs('b-t3r1-type').value=bt.refund_type||'percentage';
            if(qs('b-t3r1-value')) qs('b-t3r1-value').value=bt.refund_value??100;
            if(qs('b-t3r1-days')) qs('b-t3r1-days').value=bt.time_limit_days??0;
            if(qs('b-dist-enabled')) qs('b-dist-enabled').checked=!!bd.enabled;
            if(qs('b-dist-levels')) qs('b-dist-levels').value=bd.levels??2;
            if(qs('b-dist-l1')) qs('b-dist-l1').value=bd.level1_rate??5;
            if(qs('b-dist-l2')) qs('b-dist-l2').value=bd.level2_rate??2;
            if(qs('b-reward-rounding')) qs('b-reward-rounding').value=rw.rounding_mode||'round';
            if(qs('b-reward-decimals')) qs('b-reward-decimals').value=rw.rounding_decimals??2;
            if(qs('b-orders-clawback')) qs('b-orders-clawback').checked=!!od.clawback_on_refund;
            if(qs('b-payout-min')) qs('b-payout-min').value=po.minimum_payout??100;
            const box=document.getElementById('b-order-statuses');
            if(box){ const set=new Set(od.eligible_statuses||[]); box.querySelectorAll('input[type="checkbox"]').forEach(cb=>{ cb.checked=set.has(cb.value); }); }
            setStatus.textContent='已載入';
        })
        .catch(err=>{ setStatus.textContent='載入失敗'; console.error(err); });
    }
    function saveSettings(){ setStatus.textContent='儲存中...'; const pickStatuses=()=>{ const box=document.getElementById('b-order-statuses'); if(!box) return []; const arr=[]; box.querySelectorAll('input[type="checkbox"]:checked').forEach(cb=>arr.push(cb.value)); return arr; }; const payload={ portal_title:setTitle.value.trim(), primary_color:setPrimary.value.trim(), logo_url:setLogo.value.trim(), business:{ referral:{ cookie_days: Number(document.getElementById('b-ref-cookie').value||0), default_referrer_user_id: Number(document.getElementById('b-ref-default').value||0), allow_self_referral: document.getElementById('b-ref-self').checked }, t3r1:{ enabled: document.getElementById('b-t3r1-enabled').checked, required_count: Number(document.getElementById('b-t3r1-count').value||3), refund_type: document.getElementById('b-t3r1-type').value, refund_value: Number(document.getElementById('b-t3r1-value').value||0), time_limit_days: Number(document.getElementById('b-t3r1-days').value||0) }, distribution:{ enabled: document.getElementById('b-dist-enabled').checked, levels: Number(document.getElementById('b-dist-levels').value||2), level1_rate: Number(document.getElementById('b-dist-l1').value||0), level2_rate: Number(document.getElementById('b-dist-l2').value||0) }, rewards:{ rounding_mode: document.getElementById('b-reward-rounding').value, rounding_decimals: Number(document.getElementById('b-reward-decimals').value||2) }, orders:{ eligible_statuses: pickStatuses(), clawback_on_refund: document.getElementById('b-orders-clawback').checked }, payout:{ minimum_payout: Number(document.getElementById('b-payout-min').value||0) } } }; fetch('<?php echo esc_url(rest_url('qimall/v1/settings')); ?>', { method:'POST', headers:{'Content-Type':'application/json','X-WP-Nonce':nonce}, body:JSON.stringify(payload) }).then(r=>r.json()).then(json=>{ const d=json.data||{}; applyBrandingToUI(d); setStatus.textContent='已儲存'; }).catch(err=>{ setStatus.textContent='儲存失敗'; console.error(err); }); }
    if(setSaveBtn){ setSaveBtn.addEventListener('click', saveSettings); }
    if(setPrimary){
      setPrimary.addEventListener('input',()=>{
        const val=setPrimary.value.trim();
        if(/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(val)){
          document.body.style.setProperty('--primary', val);
          document.querySelectorAll('.el-button--primary').forEach(btn=>{
            btn.style.background='var(--primary)';
            btn.style.borderColor='var(--primary)';
          });
        }
      });
    }
    document.querySelectorAll('.qimall-sidebar a[data-tab="settings"]').forEach(a=>{ a.addEventListener('click',()=>{ if(!window.__settingsLoaded){ loadSettings(); window.__settingsLoaded=true; } }); });
  })();
  </script>
  <?php wp_footer(); ?>
</body>
</html>


