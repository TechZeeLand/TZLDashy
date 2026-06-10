<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

Auth::startSession();
if (Auth::check()) redirect('/');

$pending = Auth::getPending2FAUser();
if (!$pending) redirect('/auth/login.php');

$method = $_GET['method'] ?? 'totp';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if ($method === 'email') {
        $row = Database::fetchOne(
            "SELECT * FROM two_factor_codes WHERE user_id=? AND code=? AND used=0 AND expires_at > NOW()",
            [$pending['id'], $code]
        );
        if ($row) {
            Database::query("UPDATE two_factor_codes SET used=1 WHERE id=?", [$row['id']]);
            Auth::complete2FALogin($pending);
            redirect('/');
        } else {
            $error = 'Invalid or expired code.';
        }
    } else {
        require_once ROOT_DIR . '/lib/TOTP.php';
        if (TOTP::verify($pending['totp_secret'], $code)) {
            Auth::complete2FALogin($pending);
            redirect('/');
        } else {
            $error = 'Invalid code. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Two-Factor Auth – TZLDashy</title>
  <link rel="icon" href="/favicon.png" type="image/png">
  <link rel="stylesheet" href="/assets/css/app.css">
  <style>
    body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg);}
    .card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:48px 40px;width:420px;max-width:95vw;text-align:center;}
    .icon{font-size:52px;margin-bottom:16px;}
    h1{font-size:22px;color:var(--accent);margin-bottom:8px;}
    p{color:var(--muted);font-size:14px;margin-bottom:28px;}
    .code-input{width:100%;background:var(--bg);border:2px solid var(--border);border-radius:12px;
                padding:16px;font-size:28px;letter-spacing:10px;text-align:center;color:var(--text);outline:none;}
    .code-input:focus{border-color:var(--accent);}
    .btn{width:100%;padding:14px;background:var(--accent);color:#111;border:none;border-radius:12px;
         font-size:15px;font-weight:700;cursor:pointer;margin-top:16px;}
    .btn:hover{opacity:.88;}
    .back{display:inline-block;margin-top:20px;color:var(--muted);font-size:13px;text-decoration:none;}
    .back:hover{color:var(--accent);}
    .error-box{background:rgba(255,80,80,.12);border:1px solid rgba(255,80,80,.4);border-radius:10px;
               padding:12px 16px;color:#ff6b6b;font-size:14px;margin-bottom:18px;}
  </style>
</head>
<body>
<div class="card">
  <div class="icon"><?= $method === 'email' ? '📧' : '🔐' ?></div>
  <h1>Two-Factor Authentication</h1>
  <p><?= $method === 'email'
    ? 'We sent a 6-digit code to <strong>' . e($pending['email']) . '</strong>. Enter it below.'
    : 'Enter the 6-digit code from your authenticator app.' ?></p>

  <?php if ($error): ?><div class="error-box">⚠️ <?= e($error) ?></div><?php endif; ?>

  <form method="POST">
    <input class="code-input" type="text" name="code" maxlength="6" pattern="\d{6}"
           placeholder="000000" inputmode="numeric" autofocus required>
    <button type="submit" class="btn">Verify →</button>
  </form>
  <a class="back" href="/auth/login.php">← Back to Login</a>
</div>
</body>
</html>
