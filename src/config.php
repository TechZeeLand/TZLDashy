<?php
declare(strict_types=1);

if (defined('CONFIG_LOADED')) return;
define('CONFIG_LOADED', true);

// ── Environment helpers ──────────────────────────────────────────────────────
function env(string $key, mixed $default = null): mixed {
    $val = getenv($key);
    return ($val !== false) ? $val : $default;
}

// ── Application ──────────────────────────────────────────────────────────────
define('APP_NAME',    env('APP_NAME', 'tzldashy'));
define('APP_ENV',     env('APP_ENV', 'production'));
define('APP_URL',     env('APP_URL', 'http://localhost:1011'));
define('APP_KEY',     env('APP_KEY', 'changeme32characterssecretkey!!'));
define('BASE_URL',    '/');
define('ROOT_DIR',    __DIR__);
define('PUBLIC_DIR',  ROOT_DIR . '/public');
define('UPLOAD_DIR',  PUBLIC_DIR . '/uploads');
define('AVATAR_DIR',  UPLOAD_DIR . '/avatars');
define('LOGOS_DIR',   PUBLIC_DIR . '/Logos');

// ── Database ─────────────────────────────────────────────────────────────────
define('DB_HOST',     env('DB_HOST', 'db'));
define('DB_PORT',     env('DB_PORT', '3306'));
define('DB_NAME',     env('DB_NAME', 'tzldashy'));
define('DB_USER',     env('DB_USER', 'tzldashy'));
define('DB_PASS',     env('DB_PASS', ''));

// ── SMTP ─────────────────────────────────────────────────────────────────────
define('SMTP_HOST',       env('SMTP_HOST', 'smtp.zoho.com'));
define('SMTP_PORT',       (int)env('SMTP_PORT', '465'));
define('SMTP_USER',       env('SMTP_USER', 'yourmail@rayaz.org'));
define('SMTP_PASS',       env('SMTP_PASS', ''));
define('SMTP_FROM',       env('SMTP_FROM', 'yourmail@rayaz.org'));
define('SMTP_FROM_NAME',  env('SMTP_FROM_NAME', 'TZLDashy'));
define('SMTP_ENCRYPTION', env('SMTP_ENCRYPTION', 'ssl'));

// ── Session ───────────────────────────────────────────────────────────────────
define('SESSION_NAME',    env('SESSION_NAME', 'tzldashy_session'));
define('SESSION_LIFETIME', (int)env('SESSION_LIFETIME', '86400')); // 24 h

// ── Error reporting ───────────────────────────────────────────────────────────
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// ── Bootstrap ─────────────────────────────────────────────────────────────────
require_once ROOT_DIR . '/lib/Database.php';
require_once ROOT_DIR . '/lib/Auth.php';
require_once ROOT_DIR . '/lib/Helpers.php';
