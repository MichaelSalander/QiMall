<?php
if (!defined('ABSPATH')) { exit; }
$current = wp_get_current_user();
$rest_nonce = wp_create_nonce('wp_rest');
?>
<div class="qimall-client-admin">
  <div class="header">
    <div class="brand">
      <span class="logo">🅒</span>
      <strong>Co-Share 客戶中心</strong>
    </div>
    <div class="user">您好，<?php echo esc_html($current->display_name ?: $current->user_login); ?> | <a href="<?php echo esc_url(wp_logout_url(home_url('/client-login'))); ?>">登出</a></div>
  </div>
  <div class="nav">
    <a href="#" class="active">總覽</a>
    <a href="#members">會員</a>
    <a href="#rewards">收益</a>
    <a href="#orders">訂單</a>
    <a href="#settings">設定</a>
  </div>
  <div class="body">
    <div class="card-grid" id="qimall-kpi">
      <div class="card" data-key="total_clients"><div class="kpi">👥</div><div class="title">會員數</div><div class="num">—</div></div>
      <div class="card" data-key="total_orders"><div class="kpi">📦</div><div class="title">訂單數</div><div class="num">—</div></div>
      <div class="card" data-key="paid_rewards"><div class="kpi">✅</div><div class="title">已發放收益</div><div class="num">—</div></div>
      <div class="card" data-key="pending_rewards"><div class="kpi">⏳</div><div class="title">待發放收益</div><div class="num">—</div></div>
    </div>
    <p id="qimall-kpi-status">載入中...</p>
    <div id="members" class="members-section" style="margin-top:40px;">
      <h2 style="margin:0 0 16px;">會員列表</h2>
      <div class="filters" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
        <input type="text" id="member-search" placeholder="搜尋名稱/Email" style="padding:6px 10px;" />
        <select id="member-role" style="padding:6px 10px;">
          <option value="">全部角色</option>
          <option value="QiMall_client_admin">Admin</option>
          <option value="QiMall_client_manager">Manager</option>
          <option value="QiMall_client_viewer">Viewer</option>
        </select>
        <button id="member-reload" style="padding:6px 14px;background:var(--primary);color:#fff;border:0;border-radius:6px;cursor:pointer;">重新載入</button>
      </div>
      <div class="table-wrap" style="overflow:auto;border:1px solid #eee;background:#fff;border-radius:8px;">
        <table class="qimall-table" style="width:100%;border-collapse:collapse;min-width:780px;">
          <thead style="background:#fafafa;">
            <tr>
              <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #eee;">ID</th>
              <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #eee;">名稱</th>
              <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #eee;">Email</th>
              <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #eee;">角色</th>
              <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #eee;">註冊時間</th>
              <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #eee;">推薦人</th>
            </tr>
          </thead>
          <tbody id="members-tbody"></tbody>
        </table>
      </div>
      <div class="pager" style="display:flex;align-items:center;gap:10px;margin-top:12px;">
        <button id="page-prev" disabled style="padding:6px 12px;border:1px solid #ccc;background:#fff;border-radius:6px;cursor:pointer;">上一頁</button>
        <span id="page-info">Page 1 / 1</span>
        <button id="page-next" disabled style="padding:6px 12px;border:1px solid #ccc;background:#fff;border-radius:6px;cursor:pointer;">下一頁</button>
      </div>
      <p id="members-status" style="margin-top:8px;color:#666;font-size:12px;">尚未載入</p>
    </div>
  </div>
</div>
<style>
.qimall-client-admin{--primary:#4f46e5;background:#f5f6fb;min-height:70vh;font:14px/1.6 system-ui,Segoe UI,Arial}
.qimall-client-admin .header{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border-bottom:1px solid #eee}
.qimall-client-admin .brand{display:flex;align-items:center;gap:8px}
.qimall-client-admin .logo{display:inline-flex;width:28px;height:28px;border-radius:8px;background:var(--primary);color:#fff;align-items:center;justify-content:center;font-weight:bold}
.qimall-client-admin .nav{display:flex;gap:10px;padding:10px 20px;background:#fff;border-bottom:1px solid #eee}
.qimall-client-admin .nav a{padding:8px 12px;border-radius:6px;text-decoration:none;color:#333}
.qimall-client-admin .nav a.active,.qimall-client-admin .nav a:hover{background:rgba(79,70,229,.1);color:var(--primary)}
.qimall-client-admin .body{padding:20px}
.qimall-client-admin .card-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}
.qimall-client-admin .card{background:#fff;border:1px solid #eee;border-radius:10px;padding:18px}
.qimall-client-admin .card .kpi{font-size:24px}
.qimall-client-admin .card .title{color:#666}
.qimall-client-admin .card .num{font-size:22px;font-weight:700}
</style>
<script>
(function(){
  const nonce = '<?php echo esc_js($rest_nonce); ?>';
  const statsStatusEl = document.getElementById('qimall-kpi-status');
  // 載入 KPI
  fetch('<?php echo esc_url( rest_url('qimall/v1/stats') ); ?>', { headers: { 'X-WP-Nonce': nonce } })
    .then(r => r.json())
    .then(json => {
      if(!json || !json.success){ throw new Error('資料載入失敗'); }
      const data = json.data || {};
      document.querySelectorAll('#qimall-kpi .card').forEach(card => {
        const key = card.getAttribute('data-key');
        if(key && (key in data)){
          const numEl = card.querySelector('.num');
          if(numEl){ numEl.textContent = data[key]; }
        }
      });
      statsStatusEl.textContent = '已更新';
    })
    .catch(err => { statsStatusEl.textContent = '載入失敗'; console.error('[QiMall stats error]', err); });

  // 會員列表邏輯
  const tbody = document.getElementById('members-tbody');
  const membersStatus = document.getElementById('members-status');
  const pagePrev = document.getElementById('page-prev');
  const pageNext = document.getElementById('page-next');
  const pageInfo = document.getElementById('page-info');
  const searchInput = document.getElementById('member-search');
  const roleSelect = document.getElementById('member-role');
  const reloadBtn = document.getElementById('member-reload');
  let currentPage = 1; let totalPages = 1; let loading = false;

  function fetchMembers(page=1){
    if(loading) return; loading = true; membersStatus.textContent = '載入中...';
    const params = new URLSearchParams();
    params.set('page', page);
    params.set('per_page', 20);
    if(searchInput.value.trim()) params.set('search', searchInput.value.trim());
    if(roleSelect.value) params.set('role', roleSelect.value);
    const url = '<?php echo esc_url( rest_url('qimall/v1/members') ); ?>?' + params.toString();
    fetch(url, { headers: { 'X-WP-Nonce': nonce } })
      .then(r => r.json())
      .then(json => {
        if(!json.success) throw new Error('載入失敗');
        const data = json.data; tbody.innerHTML='';
        (data.items||[]).forEach(item => {
          const tr = document.createElement('tr');
          tr.innerHTML = '<td style="padding:6px 10px;border-bottom:1px solid #eee;">'+item.id+'</td>'+
                        '<td style="padding:6px 10px;border-bottom:1px solid #eee;">'+(item.display_name||item.login)+'</td>'+
                        '<td style="padding:6px 10px;border-bottom:1px solid #eee;">'+item.email+'</td>'+
                        '<td style="padding:6px 10px;border-bottom:1px solid #eee;">'+(item.roles||[]).join(', ')+'</td>'+
                        '<td style="padding:6px 10px;border-bottom:1px solid #eee;">'+item.registered.substring(0,10)+'</td>'+
                        '<td style="padding:6px 10px;border-bottom:1px solid #eee;">'+(item.referrer_name||'—')+'</td>';
          tbody.appendChild(tr);
        });
        currentPage = data.page; totalPages = data.total_pages; updatePager();
        membersStatus.textContent = '已載入，共 '+data.total+' 筆';
      })
      .catch(err => { membersStatus.textContent = '載入失敗'; console.error('[QiMall members error]', err); })
      .finally(()=>{ loading=false; });
  }

  function updatePager(){
    pageInfo.textContent = 'Page '+currentPage+' / '+totalPages;
    pagePrev.disabled = currentPage <= 1;
    pageNext.disabled = currentPage >= totalPages;
  }

  pagePrev.addEventListener('click', ()=>{ if(currentPage>1) fetchMembers(currentPage-1); });
  pageNext.addEventListener('click', ()=>{ if(currentPage<totalPages) fetchMembers(currentPage+1); });
  reloadBtn.addEventListener('click', ()=>{ fetchMembers(1); });
  searchInput.addEventListener('keydown', e=>{ if(e.key==='Enter'){ fetchMembers(1); } });
  roleSelect.addEventListener('change', ()=>{ fetchMembers(1); });

  // 點選導覽「會員」才載入，或 URL hash 已是 #members
  document.querySelectorAll('.nav a[href="#members"]').forEach(a => {
    a.addEventListener('click', (e)=>{ e.preventDefault(); setActiveNav(a); fetchMembers(1); document.getElementById('members').scrollIntoView({behavior:'smooth'}); });
  });

  function setActiveNav(active){
    document.querySelectorAll('.nav a').forEach(a=>a.classList.remove('active'));
    active.classList.add('active');
  }

  if(location.hash==='#members'){ fetchMembers(1); }
})();
</script>


