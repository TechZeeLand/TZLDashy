<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
Auth::requireAuth();
header('Content-Type: application/json');

$body = json_decode(file_get_contents('php://input'), true);
$ids  = array_map('intval', $body['ids'] ?? []);

foreach ($ids as $i => $id) {
    Database::query("UPDATE categories SET sort_order=? WHERE id=?", [$i, $id]);
}

echo json_encode(['ok' => true]);
