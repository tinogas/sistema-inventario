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
        Auth::logout();
        $this->redirect('/?modulo=auth&accion=login');
    }

    private function renderLogin(): void
    {
        $flash   = Session::getFlash();
        $appName = APP_NAME;
        require_once BASE_PATH . '/modules/auth/views/login.php';
    }
}
