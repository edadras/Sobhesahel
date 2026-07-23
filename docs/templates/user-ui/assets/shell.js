/* ===========================================================
   صبح ساحل — Shared shell  (header, sidebar, theme, helpers)
   Usage:  SS.mount({ active: 'dashboard' })   // sidebar pages
           SS.mountBare()                       // login etc (no sidebar)
   =========================================================== */
(function () {
  const ICONS = {
    menu: '<path d="M3 6h18M3 12h18M3 18h18"/>',
    sun: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
    moon: '<path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z"/>',
    globe: '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18Z"/>',
    bell: '<path d="M18 8.5C18 5.2 15.3 2.5 12 2.5S6 5.2 6 8.5c0 4.6-1.2 6.6-2.2 7.7-.5.5-.1 1.3.6 1.3h15.2c.7 0 1.1-.8.6-1.3-1-1.1-2.2-3.1-2.2-7.7Z"/><path d="M10 20.5a2 2 0 0 0 4 0"/>',
    grid: '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    crown: '<path d="M3 7l4 4 5-7 5 7 4-4-2 12H5L3 7Z"/>',
    coins: '<ellipse cx="9" cy="6" rx="6" ry="3"/><path d="M3 6v5c0 1.7 2.7 3 6 3s6-1.3 6-3V6"/><path d="M15 11.5c2.5.4 6 .2 6-2.5"/><path d="M9 14v3c0 1.7 2.7 3 6 3s6-1.3 6-3v-8"/>',
    gift: '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M5 12v9h14v-9M12 8S11 3 8.5 3 5 6 5 6s2 2 7 2c5 0 7-2 7-2s-1-3-3.5-3S12 8 12 8Z"/>',
    bag: '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/>',
    book: '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
    award: '<circle cx="12" cy="8" r="6"/><path d="M8.2 13.4 7 22l5-3 5 3-1.2-8.6"/>',
    pin: '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    news: '<path d="M4 4h13a1 1 0 0 1 1 1v14a2 2 0 0 0 2-2V8h-2"/><path d="M4 4a1 1 0 0 0-1 1v13a2 2 0 0 0 2 2h12V5a1 1 0 0 0-1-1Z"/><path d="M7 8h7M7 12h7M7 16h5"/>',
    settings: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 0 1-4 0v-.1A1.6 1.6 0 0 0 7 19.4a1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.6 1.6 0 0 0 3 12a1.6 1.6 0 0 0-1.1-.6H1a2 2 0 0 1 0-4h.1A1.6 1.6 0 0 0 2.6 7Z"/>',
    logout: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/>',
    bookmark: '<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2Z"/>',
    star: '<path d="M12 2l3 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.9 21l1.2-6.8-5-4.9 6.9-1Z"/>',
    check: '<path d="M20 6 9 17l-5-5"/>',
    chevL: '<path d="M15 18l-6-6 6-6"/>',
    chevR: '<path d="M9 18l6-6-6-6"/>',
    chevD: '<path d="M6 9l6 6 6-6"/>',
    search: '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/>',
    plus: '<path d="M12 5v14M5 12h14"/>',
    user: '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
    mail: '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6"/>',
    phone: '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/>',
    heart: '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/>',
    fire: '<path d="M12 2c1 4-2 5-2 8a4 4 0 0 0 8 0c0-3-2-4-2-6 3 2 5 5 5 9a9 9 0 0 1-18 0c0-4 3-7 6-11 0 0 4 1 5 0Z"/>',
    download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5M12 15V3"/>',
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    eye: '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
    play: '<path d="M6 4l14 8-14 8Z"/>',
    ticket: '<path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2 2 2 0 0 0 0 4 2 2 0 0 1-2 2v0a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2 2 2 0 0 0 0-4Z"/>',
    target: '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
    share: '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>',
    x: '<path d="M18 6 6 18M6 6l12 12"/>',
    edit: '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
  };

  function icon(name, cls) {
    return `<svg class="${cls||''}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICONS[name]||''}</svg>`;
  }

  const fa = '۰۱۲۳۴۵۶۷۸۹';
  function toFa(n) { return String(n).replace(/[0-9]/g, d => fa[d]); }
  function num(n) { return toFa(String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '٬')); }

  // ---- user (persisted demo state) ----
  const user = {
    name: 'سحر موسوی', initials: 'س‌م',
    tier: 'gold', tierFa: 'طلایی',
    points: 7240, pointsToNext: 10000, prevTier: 5000,
    plan: 'طلایی', planActive: true,
  };

  const NAV = [
    { id:'dashboard', label:'داشبورد', icon:'grid', href:'dashboard.html' },
    { id:'subscription', label:'اشتراک من', icon:'crown', href:'subscription.html' },
    { id:'points', label:'امتیازها و سطوح', icon:'coins', href:'points.html' },
    { id:'club', label:'باشگاه مشتریان', icon:'gift', href:'club.html' },
    { id:'shop', label:'فروشگاه دیجیتال', icon:'bag', href:'shop.html' },
    { id:'library', label:'مطالعات من', icon:'book', href:'library.html' },
    { id:'badges', label:'نشان‌ها', icon:'award', href:'badges.html' },
    { id:'notifications', label:'اعلان‌ها', icon:'bell', href:'notifications.html', n:3 },
    { id:'tourism', label:'گردشگری هرمزگان', icon:'pin', href:'tourism.html' },
    { id:'archive', label:'نسخه دیجیتال روزنامه', icon:'news', href:'archive.html' },
    { sep:true },
    { id:'settings', label:'تنظیمات', icon:'settings', href:'settings.html' },
    { id:'logout', label:'خروج از حساب', icon:'logout', href:'login.html' },
  ];

  const TOPNAV = [
    { label:'خانه', href:'#' }, { label:'اخبار', href:'#' }, { label:'یادداشت‌ها', href:'#' },
    { label:'آرشیو روزنامه‌ها', href:'archive.html' }, { label:'پادکست', href:'#' },
    { label:'عکس', href:'#' }, { label:'ویدئو', href:'#' },
  ];

  function tierIcon(t){ return t==='platinum'||t==='gold' ? 'crown' : 'star'; }

  function header(opts) {
    const top = TOPNAV.map(i => `<a href="${i.href}" ${i.active?'class="active"':''}>${i.label}</a>`).join('');
    return `
    <header class="topbar">
      <div class="topbar-inner">
        <button class="icon-btn hamburger" id="ss-burger" aria-label="منو">${icon('menu')}</button>
        <a class="brand-mark" href="dashboard.html">
          <div>
            <div class="brand-word">صبح ساحل</div>
            <div class="brand-tag">پنل اعضا و باشگاه مشتریان</div>
          </div>
        </a>
        <nav class="topnav">${top}</nav>
        <div class="topbar-spacer"></div>
        <div class="topbar-actions">
          <button class="icon-btn" id="ss-theme" aria-label="تغییر تم">${icon('sun')}</button>
          <a class="icon-btn" href="notifications.html" aria-label="اعلان‌ها">${icon('bell')}<span class="dot"></span></a>
          <a class="user-chip" href="points.html">
            <span class="pts">${num(user.points)} <span>امتیاز</span></span>
            <span class="uname">${user.name}</span>
            <span class="avatar">${user.initials}</span>
          </a>
        </div>
      </div>
    </header>`;
  }

  function sidebar(active) {
    const pct = Math.round(((user.points - user.prevTier) / (user.pointsToNext - user.prevTier)) * 100);
    const links = NAV.map(i => {
      if (i.sep) return '<div class="sep"></div>';
      const badge = i.n ? `<span class="badge-n">${toFa(i.n)}</span>` : '';
      const at = i.id === active ? 'active' : '';
      const data = i.toast ? `data-toast="${i.toast}"` : '';
      return `<a href="${i.href}" class="${at}" ${data}>${icon(i.icon)}<span>${i.label}</span>${badge}</a>`;
    }).join('');
    return `
    <aside class="sidebar" id="ss-side">
      <div class="side-card side-userbox">
        <span class="avatar xl" style="margin:0 auto">${user.initials}</span>
        <div class="name">${user.name}</div>
        <div class="meta">عضو از مهر ۱۴۰۳</div>
        <div style="margin-top:10px"><span class="tier-badge tier-${user.tier}">${icon(tierIcon(user.tier))} سطح ${user.tierFa}</span></div>
        <div class="side-progress">
          <div class="progress"><i style="width:${pct}%"></i></div>
          <div class="lbl"><span>${num(user.points)}</span><span>تا پلاتینیوم: ${num(user.pointsToNext)}</span></div>
        </div>
      </div>
      <nav class="side-card side-nav">${links}</nav>
    </aside>`;
  }

  function footer() {
    return `
    <footer class="foot">
      <div class="foot-inner">
        <div class="brand-word">صبح ساحل</div>
        <span>پایگاه خبری استان هرمزگان — بندرعباس</span>
        <span style="margin-inline-start:auto">© ۱۴۰۵ تمامی حقوق محفوظ است</span>
      </div>
    </footer>`;
  }

  // ---- theme ----
  function applyTheme(t) {
    document.documentElement.setAttribute('data-theme', t);
    localStorage.setItem('ss-theme', t);
    const b = document.getElementById('ss-theme');
    if (b) b.innerHTML = icon(t === 'dark' ? 'moon' : 'sun');
  }
  function initTheme() { applyTheme(localStorage.getItem('ss-theme') || 'light'); }

  // ---- toast ----
  function toast(msg) {
    let wrap = document.querySelector('.toast-wrap');
    if (!wrap) { wrap = document.createElement('div'); wrap.className = 'toast-wrap'; document.body.appendChild(wrap); }
    const t = document.createElement('div'); t.className = 'toast';
    t.innerHTML = icon('check') + '<span>' + msg + '</span>';
    wrap.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateY(8px)'; t.style.transition='.3s'; }, 2400);
    setTimeout(() => t.remove(), 2750);
  }

  function wire() {
    initTheme();
    const tbtn = document.getElementById('ss-theme');
    if (tbtn) tbtn.onclick = () => applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    const burger = document.getElementById('ss-burger');
    const side = document.getElementById('ss-side');
    if (burger && side) {
      let bd = document.querySelector('.backdrop');
      if (!bd) { bd = document.createElement('div'); bd.className = 'backdrop'; document.body.appendChild(bd); }
      const open = () => { side.classList.add('open'); bd.classList.add('show'); };
      const close = () => { side.classList.remove('open'); bd.classList.remove('show'); };
      burger.onclick = open; bd.onclick = close;
    }
    document.querySelectorAll('[data-toast]').forEach(el => {
      el.addEventListener('click', e => { e.preventDefault(); toast(el.getAttribute('data-toast')); });
    });
  }

  function mount(opts) {
    opts = opts || {};
    const root = document.getElementById('app') || document.body;
    document.body.insertAdjacentHTML('afterbegin', header(opts));
    const wrap = document.createElement('div');
    wrap.className = 'app';
    wrap.insertAdjacentHTML('afterbegin', sidebar(opts.active || ''));
    const main = document.createElement('div');
    main.className = 'main';
    while (root.firstChild) main.appendChild(root.firstChild); // MOVE nodes (keeps event listeners)
    wrap.appendChild(main);
    root.parentNode.insertBefore(wrap, root);
    root.remove();
    document.body.insertAdjacentHTML('beforeend', footer());
    wire();
  }

  function mountBare() { document.body.insertAdjacentHTML('afterbegin', ''); initTheme();
    const tbtn = document.getElementById('ss-theme');
    if (tbtn) tbtn.onclick = () => applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
  }

  window.SS = { icon, toFa, num, user, mount, mountBare, toast, applyTheme, initTheme, wire };
})();
