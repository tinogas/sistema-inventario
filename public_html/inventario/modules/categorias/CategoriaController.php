<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/categorias/CategoriaModel.php';

class CategoriaController extends Controller
{
    private CategoriaModel $model;

    public function __construct()
    {
        $this->model = new CategoriaModel();
    }

    public function index(): void
    {
        $this->requirePermiso('categorias.ver');

        $categorias = $this->model->listarTodas();
        $titulo     = 'Categorías';
        $vistaPath  = BASE_PATH . '/modules/categorias/views/lista.php';

        $this->render('categorias/lista', compact('titulo', 'categorias', 'vistaPath'));
    }

    public function exportarCsv(): void
    {
        $this->requirePermiso('categorias.ver');

        $filas = $this->model->listarTodas();

        $datos = array_map(function (array $c): array {
            return [
                'ID'          => $c['id'],
                'Nombre'      => $c['nombre'],
                'Descripcion' => $c['descripcion'] ?? '',
                'Activa'      => $c['activa'] ? 'Sí' : 'No',
            ];
        }, $filas);

        if (empty($datos)) {
            Session::flash('warning', 'No hay categorías para exportar.');
            $this->redirect('/?modulo=categorias');
        }

        $filename = 'categorias_' . date('Y-m-d') . '.csv';
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

    public function nuevo(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $nombre      = $this->postStr('nombre');
            $descripcion = $this->postStr('descripcion');

            if (empty($nombre)) {
                Session::flash('error', 'El nombre de la categoría es obligatorio.');
                $this->redirect('/?modulo=categorias&accion=nuevo');
                return;
            }

            try {
                $nuevoId = $this->model->crear([
                    'nombre'      => $nombre,
                    'descripcion' => $descripcion,
                ]);
                $this->auditoria('crear', 'categorias', $nuevoId, "Categoría: {$nombre}");
                Session::flash('success', 'Categoría creada correctamente.');
            } catch (Exception $e) {
                Session::flash('error', 'No se pudo crear la categoría. Verifica que el nombre no esté duplicado.');
            }

            $this->redirect('/?modulo=categorias');
            return;
        }

        $categoria = null;
        $titulo    = 'Nueva categoría';
        $vistaPath = BASE_PATH . '/modules/categorias/views/form.php';

        $this->render('categorias/form', compact('titulo', 'categoria', 'vistaPath'));
    }

    public function editar(): void
    {
        $this->requireAdmin();

        $id = $this->getInt('id');
        $categoria = $this->model->getById($id);

        if (!$categoria) {
            Session::flash('error', 'Categoría no encontrada.');
            $this->redirect('/?modulo=categorias');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $nombre      = $this->postStr('nombre');
            $descripcion = $this->postStr('descripcion');

            if (empty($nombre)) {
                Session::flash('error', 'El nombre de la categoría es obligatorio.');
                $this->redirect("/?modulo=categorias&accion=editar&id={$id}");
                return;
            }

            try {
                $this->model->actualizar($id, [
                    'nombre'      => $nombre,
                    'descripcion' => $descripcion,
                ]);
                $this->auditoria('editar', 'categorias', $id, "Categoría: {$nombre}");
                Session::flash('success', 'Categoría actualizada correctamente.');
            } catch (Exception $e) {
                Session::flash('error', 'No se pudo actualizar la categoría. Verifica que el nombre no esté duplicado.');
            }

            $this->redirect('/?modulo=categorias');
            return;
        }

        $titulo    = 'Editar categoría';
        $vistaPath = BASE_PATH . '/modules/categorias/views/form.php';

        $this->render('categorias/form', compact('titulo', 'categoria', 'vistaPath'));
    }

    public function eliminar(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=categorias');
            return;
        }

        $this->validarCsrf();

        $id = $this->postInt('id');
        $categoria = $this->model->getById($id);

        if (!$categoria) {
            Session::flash('error', 'Categoría no encontrada.');
            $this->redirect('/?modulo=categorias');
            return;
        }

        $eliminada = $this->model->eliminar($id);

        if ($eliminada) {
            $this->auditoria('eliminar', 'categorias', $id, "Categoría: {$categoria['nombre']}");
            Session::flash('success', 'Categoría desactivada correctamente.');
        } else {
            Session::flash('error', 'No se puede desactivar la categoría porque tiene productos asociados.');
        }

        $this->redirect('/?modulo=categorias');
    }
}
