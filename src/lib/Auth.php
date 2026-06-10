<?php
declare(strict_types=1);

class Auth {
    // ── Bootstrap session ──────────────────────────────────────────────────────
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    // ── Check if first run (no admin exists) ──────────────────────────────────
    public static function needsSetup(): bool {
        try {
            $setup = Database::fetchOne(
                "SELECT setting_value FROM settings WHERE user_id IS NULL AND setting_key='setup_done'"
            );
            if ($setup && $setup['setting_value'] === '1') return false;
            $count = Database::fetchOne("SELECT COUNT(*) AS c FROM users WHERE role='admin'");
            return !$count || (int)$count['c'] === 0;
        } catch (\Throwable) {
            return true;
        }
    }

    // ── Create the first admin account ────────────────────────────────────────
    public static function createAdmin(string $name, string $email, string $password): int {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        Database::query(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')",
            [$name, $email, $hash]
        );
        $id = (int)Database::lastInsertId();
        Database::query(
            "UPDATE settings SET setting_value='1' WHERE user_id IS NULL AND setting_key='setup_done'"
        );
        return $id;
    }

    // ── Attempt login, returns user array or false ─────────────────────────────
    public static function attempt(string $email, string $password): array|false {
        $user = Database::fetchOne(
            "SELECT * FROM users WHERE email = ? LIMIT 1", [$email]
        );
        if (!$user) return false;
        if (!password_verify($password, $user['password'])) return false;
        return $user;
    }

    // ── Start a session for a user ────────────────────────────────────────────
    public static function login(array $user): void {
        self::startSession();
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        // store pending 2fa user before full login
    }

    // ── Set pending 2FA state ─────────────────────────────────────────────────
    public static function setPending2FA(int $userId): void {
        self::startSession();
        $_SESSION['2fa_pending_user'] = $userId;
        unset($_SESSION['user_id']);
    }

    // ── Get pending 2FA user ──────────────────────────────────────────────────
    public static function getPending2FAUser(): ?array {
        if (empty($_SESSION['2fa_pending_user'])) return null;
        return Database::fetchOne("SELECT * FROM users WHERE id=?", [$_SESSION['2fa_pending_user']]) ?: null;
    }

    // ── Complete 2FA login ────────────────────────────────────────────────────
    public static function complete2FALogin(array $user): void {
        unset($_SESSION['2fa_pending_user']);
        self::login($user);
    }

    // ── Logout ────────────────────────────────────────────────────────────────
    public static function logout(): void {
        self::startSession();
        $_SESSION = [];
        session_destroy();
    }

    // ── Check if user is logged in ────────────────────────────────────────────
    public static function check(): bool {
        self::startSession();
        return !empty($_SESSION['user_id']);
    }

    // ── Require login (redirect if not) ──────────────────────────────────────
    public static function requireAuth(): void {
        if (!self::check()) {
            header('Location: /auth/login.php');
            exit;
        }
    }

    // ── Require admin role ────────────────────────────────────────────────────
    public static function requireAdmin(): void {
        self::requireAuth();
        if ($_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            die('Access denied.');
        }
    }

    // ── Get current user ──────────────────────────────────────────────────────
    public static function user(): ?array {
        if (!self::check()) return null;
        return Database::fetchOne("SELECT * FROM users WHERE id=?", [$_SESSION['user_id']]) ?: null;
    }

    // ── Get current user id ───────────────────────────────────────────────────
    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    // ── Check if admin ────────────────────────────────────────────────────────
    public static function isAdmin(): bool {
        return ($_SESSION['user_role'] ?? '') === 'admin';
    }
}
