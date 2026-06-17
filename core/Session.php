<?php
class Session
{
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => APP_URL,
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destruir(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function regenerar(): void
    {
        session_regenerate_id(true);
    }

    // Flash messages: guardar y recuperar una sola vez
    public static function flash(string $tipo, string $mensaje): void
    {
        $_SESSION['_flash'][$tipo][] = $mensaje;
    }

    public static function getFlash(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }

    // Token CSRF
    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function validarCsrf(string $token): bool
    {
        return hash_equals($_SESSION['_csrf'] ?? '', $token);
    }

    public static function renovarCsrf(): void
    {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
}
