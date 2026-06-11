<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

Auth::startSession();
if (Auth::check()) redirect('/');
if (Auth::needsSetup()) redirect('/auth/setup.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user     = Auth::attempt($email, $password);

    if (!$user) {
        $error = 'Invalid email or password.';
    } else {
        // Check TOTP 2FA (authenticator app)
        if ($user['totp_enabled']) {
            Auth::setPending2FA((int)$user['id']);
            redirect('/auth/2fa.php');
        } else {
            Auth::login($user);
            redirect('/');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – TZLDashy</title>
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="/assets/css/app.css">
  <script src="https://kit.fontawesome.com/86c0c1c09a.js" crossorigin="anonymous" defer></script>
  <style>
    body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--bg); }
    .login-card { background:var(--card); border:1px solid var(--border); border-radius:20px;
                  padding:48px 40px; width:440px; max-width:95vw; }
    .logo-area { text-align:center; margin-bottom:36px; }
    .logo-area .logo-icon { font-size:48px; color:var(--accent); }
    .logo-area h1 { font-size:26px; color:var(--accent); margin:8px 0 4px; }
    .logo-area p { color:var(--muted); font-size:13px; }
    .form-group { margin-bottom:18px; }
    .form-group label { display:block; font-size:13px; color:var(--muted); margin-bottom:6px; }
    .form-group input { width:100%; background:var(--bg); border:2px solid var(--border);
                        border-radius:12px; padding:13px 16px; color:var(--text);
                        font-size:15px; outline:none; transition:border-color .2s; box-sizing:border-box; }
    .form-group input:focus { border-color:var(--accent); }
    .btn-primary { width:100%; padding:14px; background:var(--accent); color:#111;
                   border:none; border-radius:12px; font-size:15px; font-weight:700;
                   cursor:pointer; transition:opacity .2s; }
    .btn-primary:hover { opacity:.88; }
    .error-box { background:rgba(255,80,80,.12); border:1px solid rgba(255,80,80,.4);
                 border-radius:10px; padding:12px 16px; color:#ff6b6b; font-size:14px; margin-bottom:18px; }
    .footer-note { text-align:center; color:var(--muted); font-size:12px; margin-top:28px; }
    .input-pw-wrap { position:relative; }
    .input-pw-wrap input { padding-right:48px; }
    .toggle-pw { position:absolute; right:14px; top:50%; transform:translateY(-50%);
                 background:none; border:none; color:var(--muted); cursor:pointer; font-size:16px; }
  </style>
</head>
<body>
<div class="login-card">
  <div class="logo-area">
    <div class="logo-icon"><i class="fa-solid fa-server"></i></div>
    <h1>TZLDashy</h1>
    <p>Sign in to your server dashboard</p>
  </div>

  <?php if ($error): ?>
    <div class="error-box"><i class="fa-solid fa-triangle-exclamation"></i> <?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" autocomplete="on">
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" placeholder="you@example.com"
             value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
    </div>
    <div class="form-group">
      <label>Password</label>
      <div class="input-pw-wrap">
        <input type="password" name="password" id="pw" placeholder="Your password" required>
        <button type="button" class="toggle-pw" onclick="togglePw()">
          <i class="fa-solid fa-eye" id="pw-eye"></i>
        </button>
      </div>
    </div>
    <button type="submit" class="btn-primary">Sign In <i class="fa-solid fa-arrow-right"></i></button>
  </form>

  <div class="footer-note">TZLDashy &middot; rayaz.org &middot; TechZeeLand</div>
</div>
<script>
function togglePw() {
  const i = document.getElementById('pw');
  const eye = document.getElementById('pw-eye');
  i.type = i.type === 'password' ? 'text' : 'password';
  eye.className = i.type === 'text' ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
}
</script>
</body>
</html>
