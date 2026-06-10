<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
Auth::requireAdmin();

$action = $_POST['action'] ?? '';

function interimPage(string $title, string $msg, bool $refresh = false): void {
    $accent = '#00ffbf';
    echo "<!DOCTYPE html><html><head><title>{$title}</title>
    <meta charset='UTF-8'>
    " . ($refresh ? "<meta http-equiv='refresh' content='60;url=/'>" : '') . "
    <link rel='stylesheet' href='/assets/css/app.css'>
    <style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;}
    .card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:48px;text-align:center;max-width:420px;}
    h1{color:var(--accent);margin-bottom:12px;} p{color:var(--muted);}
    .spin{border:4px solid var(--border);border-top-color:{$accent};border-radius:50%;width:36px;height:36px;animation:spin 1s linear infinite;margin:24px auto;}
    @keyframes spin{to{transform:rotate(360deg);}}</style>
    </head><body><div class='card'><h1>{$title}</h1><p>{$msg}</p>
    " . ($refresh ? "<div class='spin'></div>" : '') . "
    <a href='/' style='display:inline-block;margin-top:20px;padding:12px 24px;background:var(--accent);color:#111;border-radius:12px;font-weight:700;text-decoration:none;'>← Dashboard</a>
    </div></body></html>";
}

if ($action === 'system_shutdown') {
    shell_exec('sudo /sbin/shutdown -h now 2>/dev/null');
    interimPage('Shutting Down…', 'The server is powering off.');
    exit;
}

if ($action === 'system_reboot') {
    shell_exec('sudo /sbin/reboot 2>/dev/null');
    interimPage('Restarting…', 'The server is rebooting. Page will redirect in 60 seconds.', true);
    exit;
}

redirect('/');
