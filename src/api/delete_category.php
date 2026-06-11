<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
Auth::requireAuth();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    // Move apps in this category to the Apps slide rather than deleting them
    Database::query("UPDATE apps SET category_id=NULL,location='apps' WHERE category_id=?", [$id]);
    Database::query("DELETE FROM categories WHERE id=?", [$id]);
}
redirect('/');
