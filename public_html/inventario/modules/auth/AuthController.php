<?php
require_once BASE_PATH . '/core/Controller.php';

class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::estaAutenticado()) {
            $this->redirect('/?modulo=dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();
            $email    = $this->postStr('email');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                Session::flash('error', 'Ingresa tu correo y contraseña.');
                $this->renderLogin();
                return;
            }

            if (Auth::intentarLogin($email, $password)) {
                $this->redirect('/?modulo=dashboard');
            } else {
                Session::flash('error', 'Correo o contraseña incorrectos.');
                $this->renderLogin();
            }
            return;
        }

        $this->renderLogin();
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=dashboard');
            return;
        }
        $this->validarCsrf();
        Auth::logout();
        $this->redirect('/?modulo=auth&accion=login');
    }

    // ---------------------------------------------------------------
    // POST /?modulo=auth&accion=impersonar
    // Solo admin real (no durante impersonación activa)
    // ---------------------------------------------------------------
    public function impersonar(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=dashboard');
            return;
        }

        // El rol en sesión debe ser admin Y no estar ya impersonando
        if (Session::get('usuario_rol') !== ROL_ADMIN || Auth::estaImpersonando()) {
            Session::flash('error', 'Acción no permitida.');
            $this->redirect('/?modulo=dashboard');
            return;
        }

        $this->validarCsrf();
        $targetId = $this->postInt('usuario_id');

        $db   = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT id, nombre, email, rol, sucursal_id, foto
             FROM usuarios WHERE id = :id AND activo = 1'
        );
        $stmt->execute([':id' => $targetId]);
        $target = $stmt->fetch();

        if (!$target || $target['rol'] === ROL_ADMIN) {
            Session::flash('error', 'Usuario no válido para esta acción.');
            $this->redirect('/?modulo=dashboard');
            return;
        }

        Auth::iniciarImpersonacion($target);
        Session::flash('success', 'Actuando como "' . $target['nombre'] . '".');
        $this->redirect('/?modulo=dashboard');
    }

    // ---------------------------------------------------------------
    // POST /?modulo=auth&accion=terminar_impersonacion
    // ---------------------------------------------------------------
    public function terminarImpersonacion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=dashboard');
            return;
        }
        $this->validarCsrf();

        if (!Auth::estaImpersonando()) {
            $this->redirect('/?modulo=dashboard');
            return;
        }

        Auth::terminarImpersonacion();
        Session::flash('success', 'Has vuelto a tu sesión de administrador.');
        $this->redirect('/?modulo=dashboard');
    }

    private function renderLogin(): void
    {
        $flash   = Session::getFlash();
        $appName = APP_NAME;
        require_once BASE_PATH . '/modules/auth/views/login.php';
    }
}
