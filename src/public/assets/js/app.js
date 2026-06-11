// ─────────────────────────────────────────────────────────────────────────────
//  TZLDashy – Main Application JavaScript
// ─────────────────────────────────────────────────────────────────────────────

'use strict';

// ── Tab / slider navigation ──────────────────────────────────────────────────
let currentSlide = 0;

function showTab(index, btn) {
    currentSlide = index;
    const slider = document.getElementById('mainSlider');
    if (slider) slider.style.transform = `translateX(-${index * 100}vw)`;
    document.querySelectorAll('.tab-btn').forEach((b, i) => {
        b.classList.toggle('active', i === index);
    });
    if (index === 3) {
        // Re-load terminal iframe if needed
        const iframe = document.getElementById('termIframe');
        if (iframe && !iframe.dataset.loaded) {
            iframe.dataset.loaded = '1';
        }
    }
}

// ── Modal helpers ────────────────────────────────────────────────────────────
function openModal(id) {
    const m = document.getElementById(id);
    if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (m) { m.style.display = 'none'; document.body.style.overflow = ''; }
}

document.addEventListener('click', e => {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
        document.body.style.overflow = '';
    }
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(m => { m.style.display = 'none'; });
        document.body.style.overflow = '';
    }
});

// ── App modals ───────────────────────────────────────────────────────────────
function openAddApp(location) {
    const sel = document.getElementById('addLocation');
    if (sel) {
        sel.value = location || 'apps';
        toggleCatField('addCat', 'addLocation');
    }
    openModal('addAppModal');
}

function openAddForCat(catId) {
    const sel = document.getElementById('addLocation');
    const csel = document.getElementById('addCatSelect');
    if (sel) { sel.value = 'home'; toggleCatField('addCat', 'addLocation'); }
    if (csel) csel.value = catId;
    openModal('addAppModal');
}

function openEditApp(app) {
    document.getElementById('editId').value    = app.id    || '';
    document.getElementById('editTitle').value = app.title || '';
    document.getElementById('editDesc').value  = app.description || '';
    document.getElementById('editLink').value  = app.link  || '';
    const locSel = document.getElementById('editLocation');
    if (locSel) {
        locSel.value = app.location || 'apps';
        toggleCatField('editCat', 'editLocation');
    }
    const catSel = document.getElementById('editCatSelect');
    if (catSel && app.category_id) catSel.value = app.category_id;
    openModal('editAppModal');
}

function toggleCatField(catDivId, selectId) {
    const div = document.getElementById(catDivId);
    const sel = document.getElementById(selectId);
    if (div && sel) div.style.display = sel.value === 'home' ? 'block' : 'none';
}

// ── Card context menus ───────────────────────────────────────────────────────
function toggleCardMenu(e, btn) {
    e.stopPropagation();
    const card   = btn.closest('.app-card');
    const menu   = card.querySelector('.card-actions');
    const isOpen = menu.classList.contains('open');
    // Close all menus first
    document.querySelectorAll('.card-actions.open').forEach(m => m.classList.remove('open'));
    if (!isOpen) menu.classList.add('open');
}

document.addEventListener('click', () => {
    document.querySelectorAll('.card-actions.open').forEach(m => m.classList.remove('open'));
});

// ── Delete helpers ───────────────────────────────────────────────────────────
function deleteApp(id) {
    if (!confirm('Delete this app? This cannot be undone.')) return;
    document.getElementById('deleteAppId').value = id;
    document.getElementById('deleteAppForm').submit();
}

function deleteCat(id) {
    if (!confirm('Delete this category? Apps inside will be moved to the Apps slide.')) return;
    fetch('/api/system.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete_category&id=${id}`
    }).then(() => location.reload());
}

// ── Category edit ────────────────────────────────────────────────────────────
function openEditCat(id, name) {
    document.getElementById('editCatId').value   = id;
    document.getElementById('editCatName').value = name;
    openModal('editCatModal');
}

// ── Expand/collapse long category ────────────────────────────────────────────
function toggleExpand(btn, grid) {
    const extras = grid.querySelectorAll('.extra-card');
    const hidden = extras[0]?.style.display === 'none' || getComputedStyle(extras[0]).display === 'none';
    extras.forEach(c => c.style.display = hidden ? 'block' : 'none');
    btn.textContent = hidden ? 'Show less' : `Show all (${grid.querySelectorAll('.app-card').length})`;
}

// ── Drag-and-drop (SortableJS) ───────────────────────────────────────────────
function initSortable() {
    // Categories
    const catContainer = document.getElementById('homeCategories');
    if (catContainer && window.Sortable) {
        Sortable.create(catContainer, {
            handle: '.cat-drag',
            animation: 150,
            onEnd: function (evt) {
                const ids = [...catContainer.querySelectorAll('.category-block')].map(b => b.dataset.catId);
                fetch('/api/system.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=update_cat_sort&' + ids.map((id,i)=>`ids[${i}]=${id}`).join('&')
                });
            }
        });
        // Apps within each category
        document.querySelectorAll('.cat-grid').forEach(grid => {
            Sortable.create(grid, {
                animation: 150,
                onEnd: function () {
                    const ids = [...grid.querySelectorAll('.app-card')].map(c => c.dataset.id);
                    fetch('/api/system.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=update_sort&' + ids.map((id,i)=>`ids[${i}]=${id}`).join('&')
                    });
                }
            });
        });
    }
    // Apps grid
    const appsGrid = document.getElementById('appsGrid');
    if (appsGrid && window.Sortable) {
        Sortable.create(appsGrid, {
            animation: 150,
            onEnd: function () {
                const ids = [...appsGrid.querySelectorAll('.app-card')].map(c => c.dataset.id);
                fetch('/api/system.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=update_sort&' + ids.map((id,i)=>`ids[${i}]=${id}`).join('&')
                });
            }
        });
    }
}

// ── Clock & date ─────────────────────────────────────────────────────────────
function updateClock() {
    const now  = new Date();
    const timeEl = document.getElementById('time');
    const dateEl = document.getElementById('date');
    if (timeEl) timeEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    if (dateEl) dateEl.textContent = now.toLocaleDateString([], { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
}

// ── Weather ──────────────────────────────────────────────────────────────────
async function fetchWeather() {
    const city = (typeof WEATHER_CITY !== 'undefined') ? WEATHER_CITY : 'Dhaka';
    const el   = document.getElementById('weather-val');
    const icon = document.getElementById('weather-icon');
    if (!el) return;
    try {
        const r = await fetch(`https://wttr.in/${encodeURIComponent(city)}?format=%t+%C`);
        if (!r.ok) throw new Error('fetch failed');
        const t = (await r.text()).trim();
        el.textContent = t;
        // Update icon based on condition text
        const lower = t.toLowerCase();
        if (icon) {
            if (lower.includes('rain') || lower.includes('drizzle')) icon.className = 'fa-solid fa-cloud-rain';
            else if (lower.includes('snow')) icon.className = 'fa-solid fa-snowflake';
            else if (lower.includes('cloud')) icon.className = 'fa-solid fa-cloud';
            else if (lower.includes('sun') || lower.includes('clear')) icon.className = 'fa-solid fa-sun';
            else if (lower.includes('storm') || lower.includes('thunder')) icon.className = 'fa-solid fa-bolt';
            else if (lower.includes('fog') || lower.includes('mist')) icon.className = 'fa-solid fa-smog';
            else icon.className = 'fa-solid fa-temperature-half';
        }
    } catch {
        if (el) el.textContent = 'N/A';
    }
}

// ── System stats (with proper network delta) ──────────────────────────────────
let prevNet = null;
let prevNetTs = null;

function set(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

function setBar(id, pct) {
    const el = document.getElementById(id);
    if (el) el.style.width = Math.min(100, Math.max(0, pct)) + '%';
}

async function fetchStats() {
    try {
        const r = await fetch('/api/stats.php');
        if (!r.ok) return;
        const d = await r.json();

        const now = Date.now();

        // CPU
        set('cpu-val',  d.cpu ?? '–');
        set('temp-val', d.temp > 0 ? d.temp : '–');
        setBar('cpu-bar', d.cpu ?? 0);

        // RAM
        set('ram-used',  d.ram_u ?? '–');
        set('ram-total', d.ram_t ?? '–');
        setBar('ram-bar', d.ram_t > 0 ? (d.ram_u / d.ram_t * 100) : 0);

        // System disk
        set('ssd-used',  d.ssd_u ?? '–');
        set('ssd-total', d.ssd_t ?? '–');
        setBar('ssd-bar', d.ssd_p ?? 0);

        // Main storage
        set('storage-used',  d.storage_u ?? '–');
        set('storage-total', d.storage_t ?? '–');
        setBar('storage-bar', d.storage_p ?? 0);

        // Network: calculate per-second rates from cumulative counters
        if (prevNet !== null && prevNetTs !== null) {
            const dt = (now - prevNetTs) / 1000; // seconds
            if (dt > 0) {
                const rxDelta = Math.max(0, (d.net_rx - prevNet.rx));
                const txDelta = Math.max(0, (d.net_tx - prevNet.tx));
                // bytes/sec → Mbps
                const rxMbps = ((rxDelta / dt) * 8 / 1e6).toFixed(2);
                const txMbps = ((txDelta / dt) * 8 / 1e6).toFixed(2);
                set('net-down', rxMbps);
                set('net-up',   txMbps);
            }
        } else {
            set('net-down', '…');
            set('net-up',   '…');
        }
        prevNet   = { rx: d.net_rx, tx: d.net_tx };
        prevNetTs = now;

    } catch { /* silently ignore */ }
}

// ── Toast notifications ──────────────────────────────────────────────────────
function toast(msg, type = 'info') {
    const c = document.getElementById('toast-container');
    if (!c) return;
    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.innerHTML = (type === 'success'
        ? '<i class="fa-solid fa-circle-check"></i> '
        : type === 'error'
        ? '<i class="fa-solid fa-circle-xmark"></i> '
        : '<i class="fa-solid fa-circle-info"></i> '
    ) + msg;
    c.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 3000);
}

// ── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    updateClock();
    setInterval(updateClock, 1000);
    fetchWeather();
    setInterval(fetchWeather, 10 * 60 * 1000); // refresh weather every 10 min

    // Stats: start after 1 second so first delta is meaningful
    setTimeout(() => {
        fetchStats();
        setInterval(fetchStats, 3000);
    }, 1000);

    initSortable();

    // Init category field visibility
    toggleCatField('addCat', 'addLocation');
    toggleCatField('editCat', 'editLocation');

    // Show reboot/shutdown feedback
    const sp = new URLSearchParams(location.search);
    if (sp.get('reboot'))   toast('Server is rebooting…', 'info');
    if (sp.get('shutdown')) toast('Server is shutting down…', 'info');
});
