<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';

Auth::startSession();
Auth::requireAdmin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'system_reboot') {
    // Flush output first so browser gets a response
    if (ob_get_level()) ob_end_clean();
    header('Location: /?reboot=1');
    flush();
    // Give nginx/php-fpm a moment to send the response
    sleep(1);
    // Try multiple reboot approaches in sequence
    @exec('sudo /sbin/reboot 2>&1');
    @exec('sudo reboot 2>&1');
    @exec('sudo /bin/systemctl reboot 2>&1');
    // SYS_BOOT capability alternative: write to sysrq
    @file_put_contents('/proc/sysrq-trigger', 'b');
    exit;
}

if ($action === 'system_shutdown') {
    if (ob_get_level()) ob_end_clean();
    header('Location: /?shutdown=1');
    flush();
    sleep(1);
    @exec('sudo /sbin/poweroff 2>&1');
    @exec('sudo /sbin/shutdown -h now 2>&1');
    @exec('sudo poweroff 2>&1');
    @exec('sudo /bin/systemctl poweroff 2>&1');
    @file_put_contents('/proc/sysrq-trigger', 'o');
    exit;
}

if ($action === 'update_sort' && !empty($_POST['ids'])) {
    $ids = array_map('intval', (array)$_POST['ids']);
    foreach ($ids as $i => $id) {
        Database::query("UPDATE apps SET sort_order=? WHERE id=?", [$i, $id]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'update_cat_sort' && !empty($_POST['ids'])) {
    $ids = array_map('intval', (array)$_POST['ids']);
    foreach ($ids as $i => $id) {
        Database::query("UPDATE categories SET sort_order=? WHERE id=?", [$i, $id]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'delete_category') {
    $id = (int)($_POST['id'] ?? 0);
    // Move orphaned apps out of the category rather than deleting them
    Database::query("UPDATE apps SET category_id=NULL,location='apps' WHERE category_id=?", [$id]);
    Database::query("DELETE FROM categories WHERE id=?", [$id]);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
