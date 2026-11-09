<?php
/**
 * Auth — autentikasi, sesi, pembatasan percobaan login.
 */
declare(strict_types=1);

class Auth
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900; // 15 menit

    /** Coba login. Return true bila berhasil. */
    public static function attempt(string $username, string $password): bool
    {
        $username = trim($username);

        if (self::isLockedOut($username)) {
            return false;
        }

        $user = Database::fetch(
            "SELECT * FROM tbl_user WHERE username = :u LIMIT 1",
            [':u' => $username]
        );

        if ($user && $user['status'] === 'aktif' && password_verify($password, $user['password'])) {
            self::resetAttempts($username);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id_user'];
            $_SESSION['user'] = $user;
            return true;
        }

        self::recordFailure($username);
        return false;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }
        session_destroy();
    }

    /** Sisa detik lockout (0 bila tidak terkunci). */
    public static function lockoutRemaining(string $username): int
    {
        $a = $_SESSION['login_attempts'][$username] ?? null;
        if (!$a || $a['count'] < self::MAX_ATTEMPTS) {
            return 0;
        }
        $elapsed = time() - $a['first'];
        if ($elapsed >= self::LOCKOUT_SECONDS) {
            return 0;
        }
        return self::LOCKOUT_SECONDS - $elapsed;
    }

    private static function isLockedOut(string $username): bool
    {
        return self::lockoutRemaining($username) > 0;
    }

    private static function recordFailure(string $username): void
    {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }
        if (!isset($_SESSION['login_attempts'][$username])) {
            $_SESSION['login_attempts'][$username] = ['count' => 0, 'first' => time()];
        }
        $_SESSION['login_attempts'][$username]['count']++;
    }

    private static function resetAttempts(string $username): void
    {
        unset($_SESSION['login_attempts'][$username]);
    }
}
