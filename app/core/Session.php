<?php
/**
 * Session & CSRF Security Wrapper
 */

class Session {
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            session_start();
        }

        // Inactivity timeout check
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            self::destroy();
            header("Location: " . BASE_URL . "/login?msg=timeout");
            exit;
        }
        $_SESSION['last_activity'] = time();

        // Generate CSRF token if not set
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function setFlash(string $type, string $message): void {
        $_SESSION['flash_msg'] = [
            'type' => $type, // success, danger, warning, info
            'message' => $message
        ];
    }

    public static function getFlash(): ?array {
        if (isset($_SESSION['flash_msg'])) {
            $flash = $_SESSION['flash_msg'];
            unset($_SESSION['flash_msg']);
            return $flash;
        }
        return null;
    }

    public static function csrfToken(): string {
        return $_SESSION['csrf_token'] ?? '';
    }

    public static function verifyCsrf(): bool {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    public static function destroy(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
