<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/unidades/UnidadModel.php';

class UnidadController extends Controller
{
    private UnidadModel $model;

    public function __construct()
    {
        $this->model = new UnidadModel();
    }

    public function index(): void
    {
        $this->requirePermiso('unidades.ver');

        $unidades  = $this->model->listar();
        $titulo    = 'Unidades de medida';
        $vistaPath = BASE_PATH . '/modules/unidades/views/lista.php';

        $this->render('unidades/lista', compact('titulo', 'unidades', 'vistaPath'));
    }

    public function nuevo(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $clave  = $this->postStr('clave');
            $nombre = $this->postStr('nombre');

            if (empty($clave) || empty($nombre)) {
                Session::flash('error', 'La clave y el nombre son obligatorios.');
                $this->redirect('/?modulo=unidades&accion=nuevo');
                return;
            }

            if (strlen($clave) > 10) {
                Session::flash('error', 'La clave no puede tener más de 10 caracteres.');
                $this->redirect('/?modulo=unidades&accion=nuevo');
                return;
            }

            try {
                $this->model->crear([
                    'clave'  => $clave,
                    'nombre' => $nombre,
                ]);
                $this->auditoria('crear', 'unidades', 0, "Unidad: {$clave} - {$nombre}");
                Session::flash('success', 'Unidad creada correctamente.');
            } catch (Exception $e) {
                Session::flash('error', 'No se pudo crear la unidad. Verifica que la clave no esté duplicada.');
            }

            $this->redirect('/?modulo=unidades');
            return;
        }

        $unidad    = null;
        $titulo    = 'Nueva unidad';
        $vistaPath = BASE_PATH . '/modules/unidades/views/form.php';

        $this->render('unidades/form', compact('titulo', 'unidad', 'vistaPath'));
    }

    public function editar(): void
    {
        $this->requireAdmin();

        $id     = $this->getInt('id');
        $unidad = $this->model->getById($id);

        if (!$unidad) {
            Session::flash('error', 'Unidad no encontrada.');
            $this->redirect('/?modulo=unidades');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $clave  = $this->postStr('clave');
            $nombre = $this->postStr('nombre');

            if (empty($clave) || empty($nombre)) {
                Session::flash('error', 'La clave y el nombre son obligatorios.');
                $this->redirect("/?modulo=unidades&accion=editar&id={$id}");
                return;
            }

            if (strlen($clave) > 10) {
                Session::flash('error', 'La clave no puede tener más de 10 caracteres.');
                $this->redirect("/?modulo=unidades&accion=editar&id={$id}");
                return;
            }

            try {
                $this->model->actualizar($id, [
                    'clave'  => $clave,
                    'nombre' => $nombre,
                ]);
                $this->auditoria('editar', 'unidades', $id, "Unidad: {$clave} - {$nombre}");
                Session::flash('success', 'Unidad actualizada correctamente.');
            } catch (Exception $e) {
                Session::flash('error', 'No se pudo actualizar la unidad. Verifica que la clave no esté duplicada.');
            }

            $this->redirect('/?modulo=unidades');
            return;
        }

        $titulo    = 'Editar unidad';
        $vistaPath = BASE_PATH . '/modules/unidades/views/form.php';

        $this->render('unidades/form', compact('titulo', 'unidad', 'vistaPath'));
    }

    public function eliminar(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=unidades');
            return;
        }

        $this->validarCsrf();

        $id     = $this->postInt('id');
        $unidad = $this->model->getById($id);

        if (!$unidad) {
            Session::flash('error', 'Unidad no encontrada.');
            $this->redirect('/?modulo=unidades');
            return;
        }

        $eliminada = $this->model->eliminar($id);

        if ($eliminada) {
            $this->auditoria('eliminar', 'unidades', $id, "Unidad: {$unidad['clave']} - {$unidad['nombre']}");
            Session::flash('success', 'Unidad eliminada correctamente.');
        } else {
            Session::flash('error', 'No se puede eliminar la unidad porque tiene productos asociados.');
        }

        $this->redirect('/?modulo=unidades');
    }
}
