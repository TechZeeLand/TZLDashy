<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
Auth::requireAuth();
require_once ROOT_DIR . '/lib/TOTP.php';

$user    = Auth::user();
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Update profile info ──────────────────────────
    if ($action === 'update_profile') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if (!$name || !$email) { $error = 'Name and email are required.'; }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Invalid email.'; }
        else {
            // Handle avatar upload
            if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $fname = uploadFile($_FILES['avatar'], AVATAR_DIR);
                if ($fname) {
                    if ($user['avatar']) @unlink(AVATAR_DIR . '/' . $user['avatar']);
                    Database::query("UPDATE users SET avatar=? WHERE id=?", [$fname, $user['id']]);
                }
            }
            Database::query("UPDATE users SET name=?,email=? WHERE id=?", [$name, $email, $user['id']]);
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated.';
            $user = Auth::user();
        }
    }

    // ── Change password ──────────────────────────────
    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $user['password'])) { $error = 'Current password is wrong.'; }
        elseif (strlen($new) < 8) { $error = 'New password must be at least 8 characters.'; }
        elseif ($new !== $confirm) { $error = 'New passwords do not match.'; }
        else {
            $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
            Database::query("UPDATE users SET password=? WHERE id=?", [$hash, $user['id']]);
            $success = 'Password changed successfully.';
        }
    }

    // ── Enable TOTP ──────────────────────────────────
    if ($action === 'enable_totp') {
        $code   = trim($_POST['totp_code'] ?? '');
        $secret = $_POST['totp_secret'] ?? '';
        if (TOTP::verify($secret, $code)) {
            Database::query("UPDATE users SET totp_secret=?,totp_enabled=1 WHERE id=?", [$secret, $user['id']]);
            $success = 'Authenticator 2FA enabled.';
            $user = Auth::user();
        } else { $error = 'Invalid code. Try again.'; }
    }

    // ── Disable TOTP ─────────────────────────────────
    if ($action === 'disable_totp') {
        Database::query("UPDATE users SET totp_secret=NULL,totp_enabled=0 WHERE id=?", [$user['id']]);
        $success = 'Authenticator 2FA disabled.'; $user = Auth::user();
    }

    // ── Toggle email 2FA ─────────────────────────────
    if ($action === 'toggle_email_2fa') {
        $val = $user['email_2fa'] ? 0 : 1;
        Database::query("UPDATE users SET email_2fa=? WHERE id=?", [$val, $user['id']]);
        $success = $val ? 'Email 2FA enabled.' : 'Email 2FA disabled.';
        $user = Auth::user();
    }
}

// Generate TOTP setup secret if not enabled
$totpSetupSecret = $user['totp_enabled'] ? '' : TOTP::generateSecret();
$totpQRUrl       = $user['totp_enabled'] ? '' : TOTP::getQRUrl($totpSetupSecret, $user['email'], APP_NAME);

$pageTitle = 'My Profile';
require_once __DIR__ . '/../partials/header.php';
?>

<main>
<div class="settings-layout">

  <!-- SIDEBAR -->
  <nav class="settings-sidebar">
    <button class="settings-nav-item active" id="nav-profile" onclick="switchTab('profile',this)">
      <span class="nav-icon">👤</span> Profile Info
    </button>
    <button class="settings-nav-item" id="nav-security" onclick="switchTab('security',this)">
      <span class="nav-icon">🔒</span> Security
    </button>
    <button class="settings-nav-item" id="nav-2fa" onclick="switchTab('2fa',this)">
      <span class="nav-icon">🔐</span> Two-Factor Auth
    </button>
    <hr style="border:none;border-top:1px solid var(--border);margin:8px 4px;">
    <a class="settings-nav-item" href="/pages/settings.php"><span class="nav-icon">⚙️</span> Settings</a>
    <a class="settings-nav-item" href="/auth/logout.php"><span class="nav-icon">🚪</span> Sign Out</a>
  </nav>

  <!-- PANEL -->
  <div class="settings-panel">
    <?php if ($success): ?>
    <div class="alert alert-success">✅ <?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= e($error) ?></div>
    <?php endif; ?>

    <!-- PROFILE INFO -->
    <div id="tab-profile" class="settings-section active">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_profile">
        <div class="settings-card">
          <div class="settings-card-title">👤 Profile Information</div>

          <div class="profile-header">
            <div class="profile-avatar-wrap">
              <?php if ($user['avatar']): ?>
              <img src="/uploads/avatars/<?= e($user['avatar']) ?>" class="profile-avatar" alt="Avatar">
              <?php else: ?>
              <div class="profile-avatar-placeholder"><?= e(strtoupper(substr($user['name'],0,1))) ?></div>
              <?php endif; ?>
              <label for="avatarFile" class="avatar-change-btn" title="Change photo">📷</label>
              <input type="file" id="avatarFile" name="avatar" accept="image/*" style="display:none"
                     onchange="previewAvatar(this)">
            </div>
            <div>
              <div style="font-size:20px;font-weight:700"><?= e($user['name']) ?></div>
              <div style="color:var(--muted);font-size:14px"><?= e($user['email']) ?></div>
              <span class="badge badge-<?= $user['role'] ?>" style="margin-top:6px"><?= strtoupper($user['role']) ?></span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Full Name</label>
              <input class="form-input" type="text" name="name" value="<?= e($user['name']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input class="form-input" type="email" name="email" value="<?= e($user['email']) ?>" required>
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Profile</button>
      </form>
    </div>

    <!-- SECURITY / PASSWORD -->
    <div id="tab-security" class="settings-section">
      <form method="POST">
        <input type="hidden" name="action" value="change_password">
        <div class="settings-card">
          <div class="settings-card-title">🔑 Change Password</div>
          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input class="form-input" type="password" name="current_password" required>
          </div>
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input class="form-input" type="password" name="new_password" minlength="8" required>
            <div class="form-hint">Minimum 8 characters.</div>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input class="form-input" type="password" name="confirm_password" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Change Password</button>
      </form>
    </div>

    <!-- 2FA -->
    <div id="tab-2fa" class="settings-section">
      <div class="settings-card">
        <div class="settings-card-title">🔐 Two-Factor Authentication</div>

        <!-- Email 2FA -->
        <div class="two-fa-row">
          <div>
            <div style="font-weight:700">📧 Email OTP</div>
            <div style="color:var(--muted);font-size:13px">Receive a one-time code via email when you log in.</div>
          </div>
          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="toggle_email_2fa">
            <label class="toggle-switch">
              <input type="checkbox" <?= $user['email_2fa']?'checked':'' ?> onchange="this.form.submit()">
              <span class="toggle-slider"></span>
            </label>
          </form>
        </div>

        <!-- Authenticator 2FA -->
        <div class="two-fa-row" style="flex-direction:column;align-items:flex-start;gap:16px">
          <div style="display:flex;justify-content:space-between;width:100%;align-items:center">
            <div>
              <div style="font-weight:700">📱 Authenticator App</div>
              <div style="color:var(--muted);font-size:13px">
                Use Google Authenticator, Aegis, or similar.
                Status: <strong style="color:<?= $user['totp_enabled']?'var(--success)':'var(--muted)' ?>"><?= $user['totp_enabled']?'Enabled':'Disabled' ?></strong>
              </div>
            </div>
            <?php if ($user['totp_enabled']): ?>
            <form method="POST">
              <input type="hidden" name="action" value="disable_totp">
              <button type="submit" class="btn btn-sm btn-danger">Disable</button>
            </form>
            <?php else: ?>
            <button class="btn btn-sm btn-primary" onclick="document.getElementById('totpSetup').style.display='block';this.style.display='none'">Enable</button>
            <?php endif; ?>
          </div>

          <?php if (!$user['totp_enabled']): ?>
          <div id="totpSetup" style="display:none;width:100%">
            <div class="alert alert-info" style="margin-bottom:16px">
              📱 Scan this QR code with your authenticator app, then enter the 6-digit code to confirm.
            </div>
            <div class="qr-wrap">
              <img src="<?= e($totpQRUrl) ?>" width="200" height="200" alt="QR Code">
              <div style="margin-top:10px;font-size:12px;color:var(--muted)">
                Manual key: <code style="background:var(--card2);padding:3px 8px;border-radius:5px;font-size:13px"><?= e($totpSetupSecret) ?></code>
              </div>
            </div>
            <form method="POST">
              <input type="hidden" name="action" value="enable_totp">
              <input type="hidden" name="totp_secret" value="<?= e($totpSetupSecret) ?>">
              <div class="form-group">
                <label class="form-label">Verification Code</label>
                <input class="form-input" type="text" name="totp_code" maxlength="6"
                       placeholder="123456" inputmode="numeric" style="letter-spacing:8px;font-size:22px;text-align:center">
              </div>
              <button type="submit" class="btn btn-primary">Verify & Enable</button>
            </form>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>
</main>

<script src="/assets/js/app.js" defer></script>
<script>
function switchTab(id, btn) {
  document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + id)?.classList.add('active');
  btn.classList.add('active');
}

function previewAvatar(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.querySelector('.profile-avatar');
    const ph  = document.querySelector('.profile-avatar-placeholder');
    if (img) img.src = e.target.result;
    if (ph) {
      const newImg = document.createElement('img');
      newImg.src = e.target.result;
      newImg.className = 'profile-avatar';
      ph.replaceWith(newImg);
    }
  };
  reader.readAsDataURL(file);
}

// Hash navigation
const hash = location.hash.replace('#','');
if (hash) {
  const btn = document.getElementById('nav-' + hash);
  if (btn) switchTab(hash, btn);
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
