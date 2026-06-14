<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/servicios/ServicioModel.php';

class ServicioController extends Controller
{
    private ServicioModel $model;

    public function __construct()
    {
        $this->model = new ServicioModel();
    }

    // ---------------------------------------------------------------
    // GET /?modulo=servicios
    // ---------------------------------------------------------------
    public function index(): void
    {
        $this->requirePermiso('servicios.ver');

        $servicios = $this->model->listar();
        $titulo    = 'Servicios';
        $vistaPath = BASE_PATH . '/modules/servicios/views/lista.php';

        $this->render('servicios/lista', compact('titulo', 'vistaPath', 'servicios'));
    }

    // ---------------------------------------------------------------
    // GET/POST /?modulo=servicios&accion=nuevo
    // ---------------------------------------------------------------
    public function nuevo(): void
    {
        $this->requirePermiso('servicios.editar');

        $errores = [];
        $datos   = ['nombre' => '', 'descripcion' => '', 'precio' => ''];
        $items   = [];   // productos asociados (vacío en creación)

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $datos['nombre']      = $this->postStr('nombre');
            $datos['descripcion'] = $this->postStr('descripcion');
            $datos['precio']      = $this->postFloat('precio');

            if ($datos['nombre'] === '') {
                $errores[] = 'El nombre es obligatorio.';
            }
            if ($datos['precio'] < 0) {
                $errores[] = 'El precio no puede ser negativo.';
            }

            if (empty($errores)) {
                $id    = $this->model->crear($datos);
                $items = $this->parseItems();
                if (!empty($items)) {
                    $this->model->actualizarProductos($id, $items);
                }
                $this->auditoria('crear', 'servicios', $id, "Servicio: {$datos['nombre']}");
                Session::flash('success', 'Servicio creado correctamente.');
                $this->redirect('/?modulo=servicios');
            } else {
                $items = $this->parseItems();
            }
        }

        $titulo    = 'Nuevo servicio';
        $vistaPath = BASE_PATH . '/modules/servicios/views/form.php';
        $this->render('servicios/form', compact('titulo', 'vistaPath', 'datos', 'errores', 'items'));
    }

    // ---------------------------------------------------------------
    // GET/POST /?modulo=servicios&accion=editar&id=N
    // ---------------------------------------------------------------
    public function editar(): void
    {
        $this->requirePermiso('servicios.editar');

        $id      = $this->getInt('id');
        $servicio = $this->model->getById($id);

        if (!$servicio) {
            Session::flash('error', 'Servicio no encontrado.');
            $this->redirect('/?modulo=servicios');
        }

        $errores = [];
        $datos   = [
            'nombre'      => $servicio['nombre'],
            'descripcion' => $servicio['descripcion'] ?? '',
            'precio'      => $servicio['precio'],
        ];
        $items = $this->model->getProductosAsociados($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $datos['nombre']      = $this->postStr('nombre');
            $datos['descripcion'] = $this->postStr('descripcion');
            $datos['precio']      = $this->postFloat('precio');

            if ($datos['nombre'] === '') {
                $errores[] = 'El nombre es obligatorio.';
            }
            if ($datos['precio'] < 0) {
                $errores[] = 'El precio no puede ser negativo.';
            }

            if (empty($errores)) {
                $this->model->actualizar($id, $datos);
                $items = $this->parseItems();
                $this->model->actualizarProductos($id, $items);
                $this->auditoria('editar', 'servicios', $id, "Servicio: {$datos['nombre']}");
                Session::flash('success', 'Servicio actualizado correctamente.');
                $this->redirect('/?modulo=servicios');
            } else {
                $items = $this->parseItems();
            }
        }

        $titulo    = 'Editar servicio';
        $vistaPath = BASE_PATH . '/modules/servicios/views/form.php';
        $this->render('servicios/form', compact('titulo', 'vistaPath', 'datos', 'errores', 'items', 'servicio', 'id'));
    }

    // ---------------------------------------------------------------
    // POST /?modulo=servicios&accion=eliminar
    // ---------------------------------------------------------------
    public function eliminar(): void
    {
        $this->requirePermiso('servicios.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=servicios');
        }

        $this->validarCsrf();

        $id      = $this->postInt('id');
        $servicio = $this->model->getById($id);

        if (!$servicio) {
            Session::flash('error', 'Servicio no encontrado.');
            $this->redirect('/?modulo=servicios');
        }

        $this->model->eliminar($id);
        $this->auditoria('eliminar', 'servicios', $id, "Servicio: {$servicio['nombre']}");
        Session::flash('success', 'Servicio dado de baja correctamente.');
        $this->redirect('/?modulo=servicios');
    }

    // ---------------------------------------------------------------
    // API JSON: GET /?modulo=servicios&accion=buscar_productos&q=...
    // ---------------------------------------------------------------
    public function buscarProductos(): void
    {
        $this->requirePermiso('servicios.ver');
        $q = $this->getStr('q');
        if (strlen($q) < 2) {
            $this->json([]);
            return;
        }
        $this->json($this->model->buscarProductos($q));
    }

    // ---------------------------------------------------------------
    // Helpers privados
    // ---------------------------------------------------------------

    /**
     * Lee los arrays producto_id[] y cantidad[] del POST y construye
     * la lista de items para actualizarProductos().
     */
    private function parseItems(): array
    {
        $pids      = $_POST['producto_id'] ?? [];
        $cantidades = $_POST['cantidad']    ?? [];
        $items     = [];

        if (!is_array($pids)) {
            return [];
        }

        foreach ($pids as $i => $pid) {
            $pid      = (int) $pid;
            $cantidad = (float) str_replace(',', '.', $cantidades[$i] ?? 0);
            if ($pid > 0 && $cantidad > 0) {
                $items[] = ['producto_id' => $pid, 'cantidad' => $cantidad];
            }
        }
        return $items;
    }
}
