<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
Auth::requireAdmin();

$action = $_POST['action'] ?? '';

if ($action === 'add_user') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = in_array($_POST['role'] ?? '', ['admin','user']) ? $_POST['role'] : 'user';

    if (!$name || !$email || !$password) {
        redirect('/pages/settings.php?section=users&error=All+fields+required');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect('/pages/settings.php?section=users&error=Invalid+email');
    }
    $exists = Database::fetchOne("SELECT id FROM users WHERE email=?", [$email]);
    if ($exists) {
        redirect('/pages/settings.php?section=users&error=Email+already+in+use');
    }
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    Database::query("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)", [$name,$email,$hash,$role]);
    redirect('/pages/settings.php?section=users&saved=1');
}

if ($action === 'edit_user') {
    $id    = (int)$_POST['id'];
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role  = in_array($_POST['role'] ?? '', ['admin','user']) ? $_POST['role'] : 'user';
    $pw    = $_POST['password'] ?? '';

    // Cannot edit yourself via this route (use profile page)
    if ($id === Auth::id()) redirect('/pages/settings.php?section=users');

    // Cannot demote an admin if they're the only one
    $target = Database::fetchOne("SELECT * FROM users WHERE id=?", [$id]);
    if ($target && $target['role'] === 'admin' && $role === 'user') {
        $adminCount = Database::fetchOne("SELECT COUNT(*) AS c FROM users WHERE role='admin'");
        if ((int)$adminCount['c'] <= 1) {
            redirect('/pages/settings.php?section=users&error=Cannot+demote+the+only+admin');
        }
    }

    if ($pw && strlen($pw) >= 8) {
        $hash = password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12]);
        Database::query("UPDATE users SET name=?,email=?,role=?,password=? WHERE id=?", [$name,$email,$role,$hash,$id]);
    } else {
        Database::query("UPDATE users SET name=?,email=?,role=? WHERE id=?", [$name,$email,$role,$id]);
    }
    redirect('/pages/settings.php?section=users&saved=1');
}

if ($action === 'delete_user') {
    $id = (int)$_POST['id'];
    if ($id === Auth::id()) redirect('/pages/settings.php?section=users&error=Cannot+delete+yourself');
    $target = Database::fetchOne("SELECT role FROM users WHERE id=?", [$id]);
    if ($target && $target['role'] === 'admin') {
        redirect('/pages/settings.php?section=users&error=Cannot+delete+admin+accounts');
    }
    // Remove avatar
    $u = Database::fetchOne("SELECT avatar FROM users WHERE id=?", [$id]);
    if ($u && $u['avatar']) @unlink(AVATAR_DIR . '/' . $u['avatar']);
    Database::query("DELETE FROM users WHERE id=?", [$id]);
    redirect('/pages/settings.php?section=users&saved=1');
}

redirect('/pages/settings.php?section=users');
