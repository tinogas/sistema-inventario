<?php
class Controller
{
    protected function requireAuth(): void
    {
        if (!Auth::estaAutenticado()) {
            $this->redirect('/?modulo=auth&accion=login');
        }
    }

    protected function requirePermiso(string $permiso): void
    {
        $this->requireAuth();
        if (!Auth::tienePermiso($permiso)) {
            Session::flash('error', 'No tienes permiso para realizar esta acción.');
            $this->redirect('/?modulo=dashboard');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!Auth::esAdmin()) {
            Session::flash('error', 'Acceso restringido a administradores.');
            $this->redirect('/?modulo=dashboard');
        }
    }

    protected function validarCsrf(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!Session::validarCsrf($token)) {
            http_response_code(403);
            die('Token de seguridad inválido. Por favor recarga la página e intenta de nuevo.');
        }
        Session::renovarCsrf();
    }

    protected function render(string $vista, array $datos = []): void
    {
        // Derivar vistaPath automáticamente: "modulo/nombre" → modules/modulo/views/nombre.php
        $partes    = explode('/', $vista);
        $vistaPath = count($partes) === 2
            ? BASE_PATH . '/modules/' . $partes[0] . '/views/' . $partes[1] . '.php'
            : BASE_PATH . '/modules/' . $vista . '.php';

        // Eliminar claves reservadas del layout antes de extraer para evitar colisiones
        unset($datos['flash'], $datos['usuario'], $datos['csrf'], $datos['appName'], $datos['appUrl']);
        extract($datos);

        $flash   = Session::getFlash();
        $usuario = Auth::usuario();
        $csrf    = Session::getCsrfToken();
        $appName = APP_NAME;
        $appUrl  = APP_URL;

        require_once BASE_PATH . '/shared/views/layout.php';
    }

    protected function renderSinLayout(string $archivo, array $datos = []): void
    {
        extract($datos);
        require_once $archivo;
    }

    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . APP_URL . str_replace(APP_URL, '', $url));
        exit;
    }

    protected function redirectBack(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        // Solo permitir Referers internos para evitar open redirect
        if (empty($ref) || strpos($ref, APP_URL) === false) {
            $ref = APP_URL . '/?modulo=dashboard';
        }
        header('Location: ' . $ref);
        exit;
    }

    protected function postInt(string $key, int $default = 0): int
    {
        return (int) ($_POST[$key] ?? $default);
    }

    protected function postStr(string $key, string $default = ''): string
    {
        return trim(strip_tags($_POST[$key] ?? $default));
    }

    protected function postFloat(string $key, float $default = 0.0): float
    {
        return (float) str_replace(',', '.', $_POST[$key] ?? $default);
    }

    protected function getInt(string $key, int $default = 0): int
    {
        return (int) ($_GET[$key] ?? $default);
    }

    protected function getStr(string $key, string $default = ''): string
    {
        return trim(strip_tags($_GET[$key] ?? $default));
    }

    protected function auditoria(string $accion, string $tabla = '', int $id = 0, string $desc = ''): void
    {
        try {
            $db = Database::getInstance();
            $db->prepare(
                'INSERT INTO auditoria (usuario_id, accion, tabla_ref, registro_id, ip, descripcion)
                 VALUES (:uid, :accion, :tabla, :rid, :ip, :desc)'
            )->execute([
                ':uid'    => Auth::usuario()['id'],
                ':accion' => $accion,
                ':tabla'  => $tabla ?: null,
                ':rid'    => $id ?: null,
                ':ip'     => $_SERVER['REMOTE_ADDR'] ?? null,
                ':desc'   => $desc ?: null,
            ]);
        } catch (Exception $e) {
            // No interrumpir el flujo por falla en auditoría
        }
    }
}
