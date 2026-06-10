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
        // Check 2FA
        if ($user['totp_enabled']) {
            Auth::setPending2FA((int)$user['id']);
            redirect('/auth/2fa.php');
        } elseif ($user['email_2fa']) {
            Auth::setPending2FA((int)$user['id']);
            // Generate + send OTP
            $code    = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', time() + 600);
            Database::query("DELETE FROM two_factor_codes WHERE user_id=?", [$user['id']]);
            Database::query("INSERT INTO two_factor_codes (user_id, code, expires_at) VALUES (?,?,?)",
                [$user['id'], $code, $expires]);
            sendEmail($user['email'], $user['name'], APP_NAME . ' – Verification Code',
                Mailer::emailTemplate('Your Login Code',
                    "<p>Hi {$user['name']},</p><p>Your verification code is:</p><div class='code'>{$code}</div><p>Expires in 10 minutes.</p>"
                )
            );
            redirect('/auth/2fa.php?method=email');
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
  <link rel="icon" href="/favicon.png" type="image/png">
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--bg); }
    .login-card { background:var(--card); border:1px solid var(--border); border-radius:20px;
                  padding:48px 40px; width:440px; max-width:95vw; }
    .logo-area { text-align:center; margin-bottom:36px; }
    .logo-area .gear { font-size:48px; }
    .logo-area h1 { font-size:26px; color:var(--accent); margin:8px 0 4px; }
    .logo-area p { color:var(--muted); font-size:13px; }
    .form-group { margin-bottom:18px; }
    .form-group label { display:block; font-size:13px; color:var(--muted); margin-bottom:6px; }
    .form-group input { width:100%; background:var(--bg); border:2px solid var(--border);
                        border-radius:12px; padding:13px 16px; color:var(--text);
                        font-size:15px; outline:none; transition:border-color .2s; }
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
                 background:none; border:none; color:var(--muted); cursor:pointer; font-size:18px; }
  </style>
</head>
<body>
<div class="login-card">
  <div class="logo-area">
    <div class="gear">⚙️</div>
    <h1>TZLDashy</h1>
    <p>Sign in to your server dashboard</p>
  </div>

  <?php if ($error): ?>
    <div class="error-box">⚠️ <?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" autocomplete="on">
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" placeholder="you@example.com" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
    </div>
    <div class="form-group">
      <label>Password</label>
      <div class="input-pw-wrap">
        <input type="password" name="password" id="pw" placeholder="Your password" required>
        <button type="button" class="toggle-pw" onclick="togglePw()">👁</button>
      </div>
    </div>
    <button type="submit" class="btn-primary">Sign In →</button>
  </form>

  <div class="footer-note">TZLDashy · rayaz.org · Built by TechZeeLand</div>
</div>
<script>
function togglePw(){
  const i = document.getElementById('pw');
  i.type = i.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
