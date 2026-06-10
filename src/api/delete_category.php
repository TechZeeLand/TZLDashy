<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
Auth::requireAuth();

$id = (int)$_POST['id'];
if ($id) {
    // Delete logos
    $apps = Database::fetchAll("SELECT image FROM apps WHERE category_id=?", [$id]);
    foreach ($apps as $a) @unlink(LOGOS_DIR . '/' . $a['image']);
    Database::query("DELETE FROM categories WHERE id=?", [$id]);
}
redirect('/');
