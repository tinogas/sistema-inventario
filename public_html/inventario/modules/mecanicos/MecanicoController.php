<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Upload.php';
require_once BASE_PATH . '/modules/mecanicos/MecanicoModel.php';

class MecanicoController extends Controller
{
    private MecanicoModel $model;

    public function __construct()
    {
        $this->model = new MecanicoModel();
    }

    // ---------------------------------------------------------------
    // GET /?modulo=mecanicos
    // ---------------------------------------------------------------
    public function index(): void
    {
        $this->requirePermiso('mecanicos.ver');

        $sucursal_id = Auth::sucursalFiltro();
        $mecanicos   = $this->model->listar($sucursal_id);
        $titulo      = 'Mecánicos';
        $vistaPath   = BASE_PATH . '/modules/mecanicos/views/lista.php';

        $this->render('mecanicos/lista', compact('titulo', 'vistaPath', 'mecanicos'));
    }

    // ---------------------------------------------------------------
    // GET /?modulo=mecanicos&accion=exportar_csv
    // ---------------------------------------------------------------
    public function exportarCsv(): void
    {
        $this->requirePermiso('mecanicos.ver');

        $sucursal_id = Auth::sucursalFiltro();
        $filas       = $this->model->listar($sucursal_id);

        $datos = array_map(function (array $m): array {
            return [
                'ID'        => $m['id'],
                'Nombre'    => $m['nombre'],
                'Sucursal'  => $m['sucursal_nombre'],
                'Telefono'  => $m['telefono'] ?? '',
                'Activo'    => $m['activo'] ? 'Sí' : 'No',
            ];
        }, $filas);

        if (empty($datos)) {
            Session::flash('warning', 'No hay mecánicos para exportar.');
            $this->redirect('/?modulo=mecanicos');
        }

        $filename = 'mecanicos_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, array_keys($datos[0]), ';');
        foreach ($datos as $fila) {
            fputcsv($out, $fila, ';');
        }
        fclose($out);
        exit;
    }

    // ---------------------------------------------------------------
    // GET/POST /?modulo=mecanicos&accion=nuevo
    // ---------------------------------------------------------------
    public function nuevo(): void
    {
        $this->requirePermiso('mecanicos.editar');

        $sucursales = $this->model->getSucursales();
        $errores    = [];
        $datos      = ['nombre' => '', 'sucursal_id' => '', 'telefono' => '', 'foto' => null];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $datos['nombre']      = $this->postStr('nombre');
            $datos['sucursal_id'] = $this->postInt('sucursal_id');
            $datos['telefono']    = $this->postStr('telefono');

            if ($datos['nombre'] === '') {
                $errores[] = 'El nombre es obligatorio.';
            }
            if ($datos['sucursal_id'] <= 0) {
                $errores[] = 'Debes seleccionar una sucursal.';
            }
            try {
                $datos['foto'] = Upload::imagen('foto', 'mecanico');
            } catch (RuntimeException $e) {
                $errores[] = $e->getMessage();
            }

            if (empty($errores)) {
                $id = $this->model->crear($datos);
                $this->auditoria('crear', 'mecanicos', $id, "Mecánico: {$datos['nombre']}");
                Session::flash('success', 'Mecánico creado correctamente.');
                $this->redirect('/?modulo=mecanicos');
            }
        }

        $titulo    = 'Nuevo mecánico';
        $vistaPath = BASE_PATH . '/modules/mecanicos/views/form.php';
        $this->render('mecanicos/form', compact('titulo', 'vistaPath', 'sucursales', 'datos', 'errores'));
    }

    // ---------------------------------------------------------------
    // GET/POST /?modulo=mecanicos&accion=editar&id=N
    // ---------------------------------------------------------------
    public function editar(): void
    {
        $this->requirePermiso('mecanicos.editar');

        $id       = $this->getInt('id');
        $mecanico = $this->model->getById($id);

        if (!$mecanico) {
            Session::flash('error', 'Mecánico no encontrado.');
            $this->redirect('/?modulo=mecanicos');
        }

        $sucursales = $this->model->getSucursales();
        $errores    = [];
        $datos      = [
            'nombre'      => $mecanico['nombre'],
            'sucursal_id' => $mecanico['sucursal_id'],
            'telefono'    => $mecanico['telefono'] ?? '',
            'foto'        => $mecanico['foto'] ?? null,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $datos['nombre']      = $this->postStr('nombre');
            $datos['sucursal_id'] = $this->postInt('sucursal_id');
            $datos['telefono']    = $this->postStr('telefono');

            if ($datos['nombre'] === '') {
                $errores[] = 'El nombre es obligatorio.';
            }
            if ($datos['sucursal_id'] <= 0) {
                $errores[] = 'Debes seleccionar una sucursal.';
            }
            try {
                $datos['foto'] = Upload::imagen('foto', 'mecanico', $mecanico['foto'] ?? null);
            } catch (RuntimeException $e) {
                $errores[] = $e->getMessage();
            }

            if (empty($errores)) {
                $this->model->actualizar($id, $datos);
                $this->auditoria('editar', 'mecanicos', $id, "Mecánico: {$datos['nombre']}");
                Session::flash('success', 'Mecánico actualizado correctamente.');
                $this->redirect('/?modulo=mecanicos');
            }
        }

        $titulo    = 'Editar mecánico';
        $vistaPath = BASE_PATH . '/modules/mecanicos/views/form.php';
        $this->render('mecanicos/form', compact('titulo', 'vistaPath', 'sucursales', 'datos', 'errores', 'mecanico', 'id'));
    }

    // ---------------------------------------------------------------
    // POST /?modulo=mecanicos&accion=eliminar
    // ---------------------------------------------------------------
    public function eliminar(): void
    {
        $this->requirePermiso('mecanicos.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=mecanicos');
        }

        $this->validarCsrf();

        $id       = $this->postInt('id');
        $mecanico = $this->model->getById($id);

        if (!$mecanico) {
            Session::flash('error', 'Mecánico no encontrado.');
            $this->redirect('/?modulo=mecanicos');
        }

        $this->model->eliminar($id);
        $this->auditoria('eliminar', 'mecanicos', $id, "Mecánico: {$mecanico['nombre']}");
        Session::flash('success', 'Mecánico dado de baja correctamente.');
        $this->redirect('/?modulo=mecanicos');
    }
}
