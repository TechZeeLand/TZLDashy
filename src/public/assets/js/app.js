/* ═══════════════════════════════════════════════════
   TZLDashy — app.js
   Owner: rayaz.org | Creator: TechZeeLand
   ═══════════════════════════════════════════════════ */

'use strict';

/* ── CLOCK & DATE ───────────────────────────────── */
function updateClock() {
  const now = new Date();
  const t = document.getElementById('time');
  const d = document.getElementById('date');
  if (t) t.textContent = now.toLocaleTimeString();
  if (d) d.textContent = now.toLocaleDateString(undefined, {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
  });
}
updateClock();
setInterval(updateClock, 1000);

/* ── WEATHER ────────────────────────────────────── */
const WEATHER_KEY = 'a438c54a6af54d25983183310251512';
const city = typeof WEATHER_CITY !== 'undefined' ? WEATHER_CITY : 'Dhaka';

fetch(`https://api.weatherapi.com/v1/current.json?key=${WEATHER_KEY}&q=${encodeURIComponent(city)}&aqi=no`)
  .then(r => r.ok ? r.json() : null)
  .then(data => {
    if (!data) return;
    const el = document.getElementById('weather-val');
    const ic = document.getElementById('weather-icon');
    if (el) el.textContent = `${data.current.temp_c}°C  ${data.current.condition.text}`;
    if (ic) ic.textContent = '';
  })
  .catch(() => {});

/* ── MODAL HELPERS ──────────────────────────────── */
function openModal(id) {
  document.getElementById(id)?.classList.add('open');
}

function closeModal(id) {
  document.getElementById(id)?.classList.remove('open');
}

// Close on backdrop click
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal')) {
    e.target.classList.remove('open');
  }
});

// Close on Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal.open').forEach(m => m.classList.remove('open'));
    document.querySelectorAll('.card-actions.open').forEach(m => m.classList.remove('open'));
  }
});

/* ── CARD MENU ──────────────────────────────────── */
function toggleCardMenu(e, btn) {
  e.stopPropagation();
  e.preventDefault();
  const menu = btn.nextElementSibling;
  // close others
  document.querySelectorAll('.card-actions.open').forEach(m => {
    if (m !== menu) m.classList.remove('open');
  });
  menu.classList.toggle('open');
}

document.addEventListener('click', () => {
  document.querySelectorAll('.card-actions.open').forEach(m => m.classList.remove('open'));
});

/* ── ADD APP ────────────────────────────────────── */
function openAddApp(location = 'home', catId = null) {
  document.getElementById('addAppForm')?.reset();
  const loc = document.getElementById('addLocation');
  if (loc) loc.value = location;
  if (catId) {
    const sel = document.getElementById('addCatSelect');
    if (sel) sel.value = catId;
  }
  toggleCatField('addCat', 'addLocation');
  openModal('addAppModal');
}

function openAddForCat(catId) {
  openAddApp('home', catId);
}

/* ── EDIT APP ───────────────────────────────────── */
function openEditApp(app) {
  document.getElementById('editId').value      = app.id;
  document.getElementById('editTitle').value   = app.title;
  document.getElementById('editDesc').value    = app.description || '';
  document.getElementById('editLink').value    = app.link;
  document.getElementById('editLocation').value = app.location || 'apps';
  const cat = document.getElementById('editCatSelect');
  if (cat && app.category_id) cat.value = app.category_id;
  toggleCatField('editCat', 'editLocation');
  openModal('editAppModal');
}

/* ── TOGGLE CATEGORY FIELD ──────────────────────── */
function toggleCatField(fieldId, selectId) {
  const field = document.getElementById(fieldId);
  const sel   = document.getElementById(selectId);
  if (!field || !sel) return;
  const show = sel.value === 'home';
  field.style.display = show ? '' : 'none';
  const inner = field.querySelector('select');
  if (inner) inner.required = show;
}

/* ── EDIT CATEGORY ──────────────────────────────── */
function openEditCat(id, name) {
  document.getElementById('editCatId').value   = id;
  document.getElementById('editCatName').value = name;
  openModal('editCatModal');
}

/* ── DELETE APP ─────────────────────────────────── */
function deleteApp(id) {
  if (!confirm('Delete this app?')) return;
  document.getElementById('deleteAppId').value = id;
  document.getElementById('deleteAppForm').submit();
}

/* ── DELETE CATEGORY ────────────────────────────── */
function deleteCat(id) {
  if (!confirm('Delete this category and all its apps?')) return;
  document.getElementById('deleteCatId').value = id;
  document.getElementById('deleteCatForm').submit();
}

/* ── TAB SLIDER ─────────────────────────────────── */
let currentSlide = 0;

function showTab(index, btn) {
  currentSlide = index;
  const slider = document.getElementById('mainSlider');
  if (slider) slider.style.transform = `translateX(-${index * 100}vw)`;
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  // lazy-load stats
  if (index === 2) fetchStats();
}

/* ── EXPAND/COLLAPSE CATEGORY ───────────────────── */
function toggleExpand(btn, grid) {
  grid.classList.toggle('expanded');
  const count = grid.querySelectorAll('.app-card').length;
  btn.textContent = grid.classList.contains('expanded')
    ? 'Show less'
    : `Show all (${count})`;
}

/* ── STATS POLLING ──────────────────────────────── */
let statsLoaded = false;
let statsInterval = null;

function fetchStats() {
  if (!statsInterval) {
    statsInterval = setInterval(fetchStats, 3000);
  }
  fetch('/api/stats.php')
    .then(r => r.json())
    .then(d => {
      set('cpu-val', d.cpu);
      set('temp-val', d.temp);
      set('ram-used', d.ram_u);
      set('ram-total', d.ram_t);
      set('ssd-used', d.ssd_u);
      set('ssd-total', d.ssd_t);
      set('raid-used', d.raid_u);
      set('raid-total', d.raid_t);
      bar('cpu-bar',  d.cpu);
      bar('ram-bar',  d.ram_p);
      bar('ssd-bar',  d.ssd_p);
      bar('raid-bar', d.raid_p);
      // Network calc
      if (statsLoaded && d._prev) {
        const rx = ((d.net_rx - d._prev.net_rx) * 8 / 3 / 1e6).toFixed(2);
        const tx = ((d.net_tx - d._prev.net_tx) * 8 / 3 / 1e6).toFixed(2);
        set('net-down', rx);
        set('net-up', tx);
      }
      statsLoaded = true;
    })
    .catch(() => {});
}

function set(id, val) {
  const el = document.getElementById(id);
  if (el) el.textContent = val ?? '–';
}

function bar(id, pct) {
  const el = document.getElementById(id);
  if (el) el.style.width = Math.min(100, Math.max(0, pct || 0)) + '%';
}

/* ── SORTABLE DRAG & DROP ───────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  // Sort app cards in each category
  document.querySelectorAll('.cat-grid').forEach(grid => {
    Sortable.create(grid, {
      animation: 150,
      handle: '.app-card',
      ghostClass: 'dragging',
      onEnd(evt) {
        const catId = grid.dataset.catId;
        const ids = [...grid.querySelectorAll('.app-card')].map(c => c.dataset.id);
        saveSortOrder('/api/save_order.php', { cat_id: catId, ids });
      }
    });
  });

  // Sort apps in apps grid
  const appsGrid = document.getElementById('appsGrid');
  if (appsGrid) {
    Sortable.create(appsGrid, {
      animation: 150,
      ghostClass: 'dragging',
      onEnd() {
        const ids = [...appsGrid.querySelectorAll('.app-card')].map(c => c.dataset.id);
        saveSortOrder('/api/save_order.php', { location: 'apps', ids });
      }
    });
  }

  // Sort categories
  const catContainer = document.getElementById('homeCategories');
  if (catContainer) {
    Sortable.create(catContainer, {
      animation: 150,
      handle: '.cat-drag',
      onEnd() {
        const ids = [...catContainer.querySelectorAll('.category-block')].map(b => b.dataset.catId);
        saveSortOrder('/api/save_category_order.php', { ids });
      }
    });
  }
});

function saveSortOrder(url, data) {
  fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  }).catch(() => {});
}

/* ── TOAST ──────────────────────────────────────── */
function toast(msg, type = 'success') {
  const c = document.getElementById('toast-container');
  if (!c) return;
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.textContent = (type === 'success' ? '✅ ' : '❌ ') + msg;
  c.appendChild(el);
  setTimeout(() => el.remove(), 3500);
}

/* ── URL params for flash messages ─────────────── */
const params = new URLSearchParams(location.search);
if (params.get('saved')) toast('Changes saved successfully.');
if (params.get('error')) toast(params.get('error'), 'error');
