<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Upload.php';
require_once BASE_PATH . '/modules/usuarios/UsuarioModel.php';

class UsuarioController extends Controller
{
    private UsuarioModel $model;

    public function __construct()
    {
        $this->model = new UsuarioModel();
    }

    // ---------------------------------------------------------------
    // GET /?modulo=usuarios
    // ---------------------------------------------------------------
    public function index(): void
    {
        $this->requireAdmin();

        $usuarios  = $this->model->listar();
        $titulo    = 'Usuarios';
        $vistaPath = BASE_PATH . '/modules/usuarios/views/lista.php';

        $this->render('usuarios/lista', compact('titulo', 'vistaPath', 'usuarios'));
    }

    // ---------------------------------------------------------------
    // GET/POST /?modulo=usuarios&accion=nuevo
    // ---------------------------------------------------------------
    public function nuevo(): void
    {
        $this->requireAdmin();

        $sucursales = $this->model->getSucursales();
        $errores    = [];
        $datos      = [
            'nombre'      => '',
            'email'       => '',
            'rol'         => ROL_CONSULTA,
            'sucursal_id' => '',
            'password'    => '',
            'foto'        => null,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $datos['nombre']      = $this->postStr('nombre');
            $datos['email']       = $this->postStr('email');
            $datos['rol']         = $this->postStr('rol');
            $datos['sucursal_id'] = $this->postInt('sucursal_id');
            $datos['password']    = $_POST['password'] ?? '';

            $errores = $this->validarDatos($datos, true);
            try {
                $datos['foto'] = Upload::imagen('foto', 'usuario');
            } catch (RuntimeException $e) {
                $errores[] = $e->getMessage();
            }

            if (empty($errores)) {
                $id = $this->model->crear($datos);
                $this->auditoria('crear', 'usuarios', $id, "Usuario: {$datos['email']} rol: {$datos['rol']}");
                Session::flash('success', 'Usuario creado correctamente.');
                $this->redirect('/?modulo=usuarios');
            }
        }

        $titulo    = 'Nuevo usuario';
        $vistaPath = BASE_PATH . '/modules/usuarios/views/form.php';
        $this->render('usuarios/form', compact('titulo', 'vistaPath', 'sucursales', 'datos', 'errores'));
    }

    // ---------------------------------------------------------------
    // GET/POST /?modulo=usuarios&accion=editar&id=N
    // ---------------------------------------------------------------
    public function editar(): void
    {
        $this->requireAdmin();

        $id      = $this->getInt('id');
        $usuario = $this->model->getById($id);

        if (!$usuario) {
            Session::flash('error', 'Usuario no encontrado.');
            $this->redirect('/?modulo=usuarios');
        }

        $sucursales = $this->model->getSucursales();
        $errores    = [];
        $datos      = [
            'nombre'      => $usuario['nombre'],
            'email'       => $usuario['email'],
            'rol'         => $usuario['rol'],
            'sucursal_id' => $usuario['sucursal_id'] ?? '',
            'password'    => '',
            'foto'        => $usuario['foto'] ?? null,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $datos['nombre']      = $this->postStr('nombre');
            $datos['email']       = $this->postStr('email');
            $datos['rol']         = $this->postStr('rol');
            $datos['sucursal_id'] = $this->postInt('sucursal_id');
            $datos['password']    = $_POST['password'] ?? '';

            $errores = $this->validarDatos($datos, false, $id);
            try {
                $datos['foto'] = Upload::imagen('foto', 'usuario', $usuario['foto'] ?? null);
            } catch (RuntimeException $e) {
                $errores[] = $e->getMessage();
            }

            if (empty($errores)) {
                $this->model->actualizar($id, $datos);
                // Si el admin editó su propia foto, refrescar la sesión para el navbar
                if ($id === (int) Auth::usuario()['id']) {
                    Session::set('usuario_foto', $datos['foto']);
                }
                $this->auditoria('editar', 'usuarios', $id, "Usuario: {$datos['email']} rol: {$datos['rol']}");
                Session::flash('success', 'Usuario actualizado correctamente.');
                $this->redirect('/?modulo=usuarios');
            }
        }

        $titulo    = 'Editar usuario';
        $vistaPath = BASE_PATH . '/modules/usuarios/views/form.php';
        $this->render('usuarios/form', compact('titulo', 'vistaPath', 'sucursales', 'datos', 'errores', 'usuario', 'id'));
    }

    // ---------------------------------------------------------------
    // POST /?modulo=usuarios&accion=eliminar
    // ---------------------------------------------------------------
    public function eliminar(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=usuarios');
        }

        $this->validarCsrf();

        $id          = $this->postInt('id');
        $usuarioActualId = (int) Auth::usuario()['id'];

        if ($id === $usuarioActualId) {
            Session::flash('error', 'No puedes darte de baja a ti mismo.');
            $this->redirect('/?modulo=usuarios');
        }

        $usuario = $this->model->getById($id);
        if (!$usuario) {
            Session::flash('error', 'Usuario no encontrado.');
            $this->redirect('/?modulo=usuarios');
        }

        try {
            $this->model->eliminar($id, $usuarioActualId);
            $this->auditoria('eliminar', 'usuarios', $id, "Usuario: {$usuario['email']}");
            Session::flash('success', 'Usuario dado de baja correctamente.');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect('/?modulo=usuarios');
    }

    // ---------------------------------------------------------------
    // Validación compartida
    // ---------------------------------------------------------------
    private function validarDatos(array $datos, bool $esNuevo, ?int $excluirId = null): array
    {
        $errores = [];
        $rolesValidos = [ROL_ADMIN, ROL_ALMACENISTA, ROL_CONSULTA];

        if ($datos['nombre'] === '') {
            $errores[] = 'El nombre es obligatorio.';
        }

        if ($datos['email'] === '') {
            $errores[] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no es válido.';
        } elseif ($this->model->emailExiste($datos['email'], $excluirId)) {
            $errores[] = 'El correo electrónico ya está en uso por otro usuario.';
        }

        if (!in_array($datos['rol'], $rolesValidos, true)) {
            $errores[] = 'Rol no válido.';
        }

        // Sucursal obligatoria para roles no-admin
        if ($datos['rol'] !== ROL_ADMIN && (int)$datos['sucursal_id'] <= 0) {
            $errores[] = 'Debes asignar una sucursal para este rol.';
        }

        // Contraseña obligatoria solo al crear
        if ($esNuevo && empty($datos['password'])) {
            $errores[] = 'La contraseña es obligatoria.';
        }

        if (!empty($datos['password']) && strlen($datos['password']) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        }

        return $errores;
    }
}
