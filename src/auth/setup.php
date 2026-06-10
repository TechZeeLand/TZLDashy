<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

Auth::startSession();

// If already set up, redirect
if (!Auth::needsSetup()) {
    redirect('/auth/login.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $id = Auth::createAdmin($name, $email, $password);
        Auth::login(['id' => $id, 'role' => 'admin', 'name' => $name]);
        redirect('/');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Setup – TZLDashy</title>
  <link rel="icon" href="/favicon.png" type="image/png">
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--bg); }
    .setup-card { background: var(--card); border: 1px solid var(--border); border-radius: 20px;
                  padding: 48px 40px; width: 460px; max-width: 95vw; }
    .setup-logo { text-align: center; margin-bottom: 32px; }
    .setup-logo .gear { font-size: 56px; }
    .setup-logo h1 { font-size: 28px; margin: 8px 0 4px; color: var(--accent); }
    .setup-logo p { color: var(--muted); font-size: 14px; }
    .badge { display: inline-block; background: var(--accent); color: #111; font-size: 11px;
             font-weight: 700; padding: 3px 10px; border-radius: 20px; margin-bottom: 24px; }
    .step-title { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
    .step-sub { color: var(--muted); font-size: 14px; margin-bottom: 28px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px; }
    .form-group input { width: 100%; background: var(--bg); border: 2px solid var(--border);
                        border-radius: 12px; padding: 12px 16px; color: var(--text);
                        font-size: 15px; outline: none; transition: border-color .2s; }
    .form-group input:focus { border-color: var(--accent); }
    .btn-primary { width: 100%; padding: 14px; background: var(--accent); color: #111;
                   border: none; border-radius: 12px; font-size: 15px; font-weight: 700;
                   cursor: pointer; margin-top: 8px; transition: opacity .2s; }
    .btn-primary:hover { opacity: .88; }
    .error-box { background: rgba(255,80,80,.12); border: 1px solid rgba(255,80,80,.4);
                 border-radius: 10px; padding: 12px 16px; color: #ff6b6b; font-size: 14px; margin-bottom: 18px; }
    .hint { font-size: 12px; color: var(--muted); margin-top: 4px; }
    .divider { border: none; border-top: 1px solid var(--border); margin: 28px 0; }
    .footer-note { text-align: center; color: var(--muted); font-size: 12px; }
  </style>
</head>
<body>
<div class="setup-card">
  <div class="setup-logo">
    <div class="gear">⚙️</div>
    <h1>TZLDashy</h1>
    <p>Server Dashboard by TechZeeLand</p>
  </div>

  <div style="text-align:center"><span class="badge">FIRST RUN SETUP</span></div>
  <div class="step-title">Create Admin Account</div>
  <div class="step-sub">Set up your administrator account to get started. This can only be done once.</div>

  <?php if ($error): ?>
    <div class="error-box">⚠️ <?= e($error) ?></div>
  <?php endif; ?>

  <form method="POST" autocomplete="off">
    <div class="form-group">
      <label>Full Name</label>
      <input type="text" name="name" placeholder="Your Name" value="<?= e($_POST['name'] ?? '') ?>" required autofocus>
    </div>
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" placeholder="admin@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Minimum 8 characters" required>
      <div class="hint">Use a strong password with letters, numbers and symbols.</div>
    </div>
    <div class="form-group">
      <label>Confirm Password</label>
      <input type="password" name="confirm" placeholder="Repeat your password" required>
    </div>
    <button type="submit" class="btn-primary">Create Admin &amp; Launch TZLDashy →</button>
  </form>

  <hr class="divider">
  <div class="footer-note">TZLDashy · rayaz.org · Built by TechZeeLand</div>
</div>
</body>
</html>
