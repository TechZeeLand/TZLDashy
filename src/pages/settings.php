<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
if (Auth::needsSetup()) redirect('/auth/setup.php');
Auth::requireAuth();

$user     = Auth::user();
$isAdmin  = Auth::isAdmin();
$uSettings = getUserSettings((int)$user['id']);
$success  = '';
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Save appearance ─────────────────────────────
    if ($action === 'save_appearance') {
        setSetting('theme',           $_POST['theme']         ?? 'dark', $user['id']);
        setSetting('accent_color',    $_POST['accent_color']  ?? '#00ffbf', $user['id']);
        setSetting('font',            $_POST['font']          ?? 'Alata', $user['id']);
        setSetting('primary_color',   $_POST['primary_color'] ?? '', $user['id']);
        setSetting('secondary_color', $_POST['secondary_color'] ?? '', $user['id']);
        $success = 'Appearance saved.';
        $uSettings = getUserSettings((int)$user['id']);
    }

    // ── Save general ────────────────────────────────
    if ($action === 'save_general' && $isAdmin) {
        setSetting('app_name',      trim($_POST['app_name'] ?? 'TZLDashy'));
        setSetting('weather_city',  trim($_POST['weather_city'] ?? 'Dhaka'));
        setSetting('terminal_url',  trim($_POST['terminal_url'] ?? ''));
        $success = 'General settings saved.';
    }

    // ── Save language/font ──────────────────────────
    if ($action === 'save_language') {
        setSetting('language', $_POST['language'] ?? 'en', $user['id']);
        $success = 'Language saved.';
    }

    redirect('/pages/settings.php?section=' . ($_POST['section'] ?? 'appearance') . '&saved=1');
}

$section   = $_GET['section'] ?? 'appearance';
if ($_GET['saved'] ?? false) $success = 'Settings saved successfully.';

$globalSettings = [
    'app_name'      => getSetting('app_name', null, 'TZLDashy'),
    'weather_city'  => getSetting('weather_city', null, 'Dhaka'),
    'terminal_url'  => getSetting('terminal_url', null, 'http://localhost:7681'),
];

$pageTitle = 'Settings';
require_once __DIR__ . '/../partials/header.php';
?>

<main>
<div class="settings-layout">

  <!-- SIDEBAR NAV -->
  <nav class="settings-sidebar">
    <button class="settings-nav-item <?= $section==='appearance'?'active':'' ?>" onclick="switchSection('appearance',this)">
      <span class="nav-icon">🎨</span> Appearance
    </button>
    <button class="settings-nav-item <?= $section==='language'?'active':'' ?>" onclick="switchSection('language',this)">
      <span class="nav-icon">🌐</span> Language & Font
    </button>
    <?php if ($isAdmin): ?>
    <button class="settings-nav-item <?= $section==='general'?'active':'' ?>" onclick="switchSection('general',this)">
      <span class="nav-icon">⚙️</span> General
    </button>
    <button class="settings-nav-item <?= $section==='users'?'active':'' ?>" onclick="switchSection('users',this)">
      <span class="nav-icon">👥</span> User Management
    </button>
    <?php endif; ?>
    <hr style="border:none;border-top:1px solid var(--border);margin:8px 4px;">
    <a class="settings-nav-item" href="/pages/profile.php">
      <span class="nav-icon">👤</span> Profile
    </a>
    <a class="settings-nav-item" href="/pages/about.php">
      <span class="nav-icon">ℹ️</span> About
    </a>
  </nav>

  <!-- PANEL -->
  <div class="settings-panel">
    <?php if ($success): ?>
    <div class="alert alert-success">✅ <?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= e($error) ?></div>
    <?php endif; ?>

    <!-- ── APPEARANCE ── -->
    <div class="settings-section <?= $section==='appearance'?'active':'' ?>" id="sec-appearance">
      <form method="POST">
        <input type="hidden" name="action" value="save_appearance">
        <input type="hidden" name="section" value="appearance">

        <div class="settings-card">
          <div class="settings-card-title">🎨 Theme</div>
          <div class="form-group">
            <label class="form-label">Mode</label>
            <select class="form-select" name="theme" onchange="previewTheme(this.value)">
              <option value="dark"  <?= ($uSettings['theme']??'dark')==='dark'?'selected':'' ?>>🌙 Dark</option>
              <option value="light" <?= ($uSettings['theme']??'dark')==='light'?'selected':'' ?>>☀️ Light</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Accent Colour</label>
            <div class="color-grid" id="accentSwatches">
              <?php
              $swatches = ['#00ffbf','#6c63ff','#ff6b6b','#ffa500','#4fc3f7','#f06292','#69f0ae','#fff176','#e040fb','#40c4ff'];
              $cur = $uSettings['accent_color'] ?? '#00ffbf';
              foreach ($swatches as $sw): ?>
              <div class="color-swatch <?= $sw===$cur?'selected':'' ?>"
                   style="background:<?= e($sw) ?>"
                   onclick="selectAccent('<?= e($sw) ?>')"></div>
              <?php endforeach; ?>
            </div>
            <input type="color" class="form-input" name="accent_color" id="accentInput"
                   value="<?= e($cur) ?>" style="height:44px;padding:4px 8px;cursor:pointer;"
                   oninput="document.querySelectorAll('.color-swatch').forEach(s=>s.classList.remove('selected'))">
            <div class="form-hint">Pick a preset or choose a custom colour.</div>
          </div>
        </div>

        <div class="settings-card">
          <div class="settings-card-title">🎨 Custom Colours</div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Primary Override</label>
              <input type="color" class="form-input" name="primary_color"
                     value="<?= e($uSettings['primary_color']??'#00ffbf') ?>" style="height:44px;padding:4px 8px;cursor:pointer;">
            </div>
            <div class="form-group">
              <label class="form-label">Secondary Override</label>
              <input type="color" class="form-input" name="secondary_color"
                     value="<?= e($uSettings['secondary_color']??'#1a1a1a') ?>" style="height:44px;padding:4px 8px;cursor:pointer;">
            </div>
          </div>
          <div class="form-hint">These override the accent. Leave as-is to use the accent colour.</div>
        </div>

        <button type="submit" class="btn btn-primary">Save Appearance</button>
      </form>
    </div>

    <!-- ── LANGUAGE & FONT ── -->
    <div class="settings-section <?= $section==='language'?'active':'' ?>" id="sec-language">
      <form method="POST">
        <input type="hidden" name="action" value="save_language">
        <input type="hidden" name="section" value="language">

        <div class="settings-card">
          <div class="settings-card-title">🌐 Language</div>
          <div class="form-group">
            <label class="form-label">Interface Language</label>
            <select class="form-select" name="language">
              <?php
              $langs = ['en'=>'English','bn'=>'Bengali (বাংলা)','fr'=>'French (Français)',
                        'de'=>'German (Deutsch)','es'=>'Spanish (Español)','ar'=>'Arabic (عربي)','zh'=>'Chinese (中文)'];
              $curLang = $uSettings['language'] ?? 'en';
              foreach ($langs as $code => $label):
              ?>
              <option value="<?= $code ?>" <?= $code===$curLang?'selected':'' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-hint">UI language support is expanding. Most labels are currently in English.</div>
          </div>
        </div>

        <div class="settings-card">
          <div class="settings-card-title">🔤 Font</div>
          <div class="form-group">
            <label class="form-label">Font Family</label>
            <select class="form-select" name="font" id="fontSelect" onchange="previewFont(this.value)">
              <?php
              $fonts = ['Alata','Inter','Roboto','Poppins','Ubuntu','Montserrat','Nunito','Source Code Pro','JetBrains Mono'];
              $curFont = $uSettings['font'] ?? 'Alata';
              foreach ($fonts as $f):
              ?>
              <option value="<?= $f ?>" <?= $f===$curFont?'selected':'' ?>><?= $f ?></option>
              <?php endforeach; ?>
            </select>
            <div id="fontPreview" style="margin-top:12px;padding:14px;background:var(--card2);border-radius:10px;font-family:<?= e($curFont) ?>,sans-serif;">
              The quick brown fox jumps over the lazy dog. 0123456789
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Language & Font</button>
      </form>
    </div>

    <!-- ── GENERAL (admin only) ── -->
    <?php if ($isAdmin): ?>
    <div class="settings-section <?= $section==='general'?'active':'' ?>" id="sec-general">
      <form method="POST">
        <input type="hidden" name="action" value="save_general">
        <input type="hidden" name="section" value="general">
        <div class="settings-card">
          <div class="settings-card-title">⚙️ General Settings</div>
          <div class="form-group">
            <label class="form-label">App Name</label>
            <input class="form-input" type="text" name="app_name" value="<?= e($globalSettings['app_name']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Weather City</label>
            <input class="form-input" type="text" name="weather_city" value="<?= e($globalSettings['weather_city']) ?>" placeholder="Dhaka">
          </div>
          <div class="form-group">
            <label class="form-label">Terminal URL (ttyd/wetty)</label>
            <input class="form-input" type="url" name="terminal_url" value="<?= e($globalSettings['terminal_url']) ?>" placeholder="http://localhost:7681">
            <div class="form-hint">URL to your terminal-in-browser service (ttyd recommended).</div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Save General Settings</button>
      </form>
    </div>

    <!-- ── USER MANAGEMENT (admin only) ── -->
    <div class="settings-section <?= $section==='users'?'active':'' ?>" id="sec-users">
      <div class="settings-card">
        <div class="settings-card-title" style="justify-content:space-between;">
          <span>👥 Users</span>
          <button class="btn btn-sm btn-primary" onclick="openModal('addUserModal')">+ Add User</button>
        </div>
        <?php
        $allUsers = Database::fetchAll("SELECT id,name,email,role,created_at FROM users ORDER BY role DESC,name");
        ?>
        <table class="data-table">
          <thead>
            <tr>
              <th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($allUsers as $u): ?>
          <tr>
            <td><?= e($u['name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><span class="badge badge-<?= $u['role'] ?>"><?= e(strtoupper($u['role'])) ?></span></td>
            <td style="color:var(--muted);font-size:13px"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td>
              <?php if ($u['id'] != $user['id']): ?>
              <button class="btn btn-sm" onclick='openEditUser(<?= json_encode($u) ?>)'>Edit</button>
              <?php if ($u['role'] !== 'admin'): ?>
              <button class="btn btn-sm btn-danger" style="margin-left:6px" onclick="deleteUser(<?= $u['id'] ?>)">Delete</button>
              <?php endif; ?>
              <?php else: ?>
              <span style="color:var(--muted);font-size:12px">You</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div><!-- .settings-panel -->
</div><!-- .settings-layout -->
</main>

<!-- ADD USER MODAL -->
<div class="modal" id="addUserModal">
  <div class="modal-box" style="max-width:460px">
    <button class="modal-close" onclick="closeModal('addUserModal')">&times;</button>
    <h2 class="modal-title">➕ Add User</h2>
    <form method="POST" action="/api/users.php">
      <input type="hidden" name="action" value="add_user">
      <div class="form-group"><label class="form-label">Name</label>
        <input class="form-input" type="text" name="name" required></div>
      <div class="form-group"><label class="form-label">Email</label>
        <input class="form-input" type="email" name="email" required></div>
      <div class="form-group"><label class="form-label">Password</label>
        <input class="form-input" type="password" name="password" required minlength="8"></div>
      <div class="form-group"><label class="form-label">Role</label>
        <select class="form-select" name="role">
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select></div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-full">Create User</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT USER MODAL -->
<div class="modal" id="editUserModal">
  <div class="modal-box" style="max-width:460px">
    <button class="modal-close" onclick="closeModal('editUserModal')">&times;</button>
    <h2 class="modal-title">✏️ Edit User</h2>
    <form method="POST" action="/api/users.php">
      <input type="hidden" name="action" value="edit_user">
      <input type="hidden" name="id" id="editUserId">
      <div class="form-group"><label class="form-label">Name</label>
        <input class="form-input" type="text" name="name" id="editUserName" required></div>
      <div class="form-group"><label class="form-label">Email</label>
        <input class="form-input" type="email" name="email" id="editUserEmail" required></div>
      <div class="form-group"><label class="form-label">New Password (leave blank to keep)</label>
        <input class="form-input" type="password" name="password" minlength="8"></div>
      <div class="form-group"><label class="form-label">Role</label>
        <select class="form-select" name="role" id="editUserRole">
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select></div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-full">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<form id="deleteUserForm" method="POST" action="/api/users.php" style="display:none">
  <input type="hidden" name="action" value="delete_user">
  <input type="hidden" name="id" id="deleteUserId">
</form>

<script src="/assets/js/app.js" defer></script>
<script>
function switchSection(id, btn) {
  document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
  document.getElementById('sec-' + id)?.classList.add('active');
  btn.classList.add('active');
}

function selectAccent(color) {
  document.getElementById('accentInput').value = color;
  document.querySelectorAll('.color-swatch').forEach(s => {
    s.classList.toggle('selected', s.style.background === color);
  });
}

function previewTheme(val) {
  document.documentElement.classList.toggle('light', val === 'light');
  document.body.classList.toggle('light', val === 'light');
}

function previewFont(fontName) {
  const prev = document.getElementById('fontPreview');
  if (!fontName || fontName === 'Alata') {
    prev.style.fontFamily = 'Alata, sans-serif';
    return;
  }
  if (!document.querySelector(`link[data-font="${fontName}"]`)) {
    const link = document.createElement('link');
    link.rel  = 'stylesheet';
    link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(fontName)}:wght@400;700&display=swap`;
    link.dataset.font = fontName;
    document.head.appendChild(link);
  }
  prev.style.fontFamily = `'${fontName}', sans-serif`;
}

function openEditUser(u) {
  document.getElementById('editUserId').value    = u.id;
  document.getElementById('editUserName').value  = u.name;
  document.getElementById('editUserEmail').value = u.email;
  document.getElementById('editUserRole').value  = u.role;
  openModal('editUserModal');
}

function deleteUser(id) {
  if (!confirm('Delete this user? This cannot be undone.')) return;
  document.getElementById('deleteUserId').value = id;
  document.getElementById('deleteUserForm').submit();
}

// Auto-show correct section from URL
const sec = new URLSearchParams(location.search).get('section') || 'appearance';
document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
document.getElementById('sec-' + sec)?.classList.add('active');
document.querySelectorAll('.settings-nav-item').forEach(b => {
  if (b.getAttribute('onclick')?.includes(`'${sec}'`)) b.classList.add('active');
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
