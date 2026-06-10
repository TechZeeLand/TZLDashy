<?php
declare(strict_types=1);

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function redirect(string $url): never {
    header("Location: $url");
    exit;
}

function jsonResponse(mixed $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getSetting(string $key, ?int $userId = null, mixed $default = null): mixed {
    $row = Database::fetchOne(
        "SELECT setting_value FROM settings WHERE user_id " .
        ($userId ? "= ?" : "IS NULL") . " AND setting_key = ?",
        $userId ? [$userId, $key] : [$key]
    );
    return $row ? $row['setting_value'] : $default;
}

function setSetting(string $key, mixed $value, ?int $userId = null): void {
    Database::query(
        "INSERT INTO settings (user_id, setting_key, setting_value) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
        [$userId, $key, $value]
    );
}

function getUserSettings(int $userId): array {
    $rows = Database::fetchAll(
        "SELECT setting_key, setting_value FROM settings WHERE user_id = ?", [$userId]
    );
    $out = [];
    foreach ($rows as $r) $out[$r['setting_key']] = $r['setting_value'];
    return $out;
}

function generateToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

function uploadFile(array $file, string $dir, array $allowed = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml']): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if (!in_array($file['type'], $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;

    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $name = uniqid('', true) . '.' . $ext;
    $dest = rtrim($dir, '/') . '/' . $name;

    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;

    return $name;
}

function sendEmail(string $to, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool {
    require_once ROOT_DIR . '/mail/Mailer.php';
    return Mailer::send($to, $toName, $subject, $htmlBody, $textBody);
}

function getThemeVars(?int $userId = null): array {
    $uid = $userId ?? Auth::id();
    $settings = $uid ? getUserSettings($uid) : [];
    return [
        'theme'       => $settings['theme'] ?? getSetting('theme', null, 'dark'),
        'accent'      => $settings['accent_color'] ?? getSetting('accent_color', null, '#00ffbf'),
        'font'        => $settings['font'] ?? getSetting('font', null, 'Alata'),
        'primary'     => $settings['primary_color'] ?? '',
        'secondary'   => $settings['secondary_color'] ?? '',
    ];
}
