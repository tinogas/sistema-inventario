<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/proveedores/ProveedorModel.php';

class ProveedorController extends Controller
{
    private ProveedorModel $model;

    public function __construct()
    {
        $this->model = new ProveedorModel();
    }

    public function index(): void
    {
        $this->requirePermiso('proveedores.ver');

        $buscar  = $this->getStr('buscar');
        $pagina  = max(1, $this->getInt('pagina', 1));
        $result  = $this->model->listar($buscar, $pagina);

        $titulo    = 'Proveedores';
        $vistaPath = BASE_PATH . '/modules/proveedores/views/lista.php';

        $this->render('proveedores/lista', compact(
            'titulo', 'vistaPath', 'buscar',
            'result'
        ));
    }

    public function nuevo(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $razon_social = $this->postStr('razon_social');

            if (empty($razon_social)) {
                Session::flash('error', 'La razón social es obligatoria.');
                $this->redirect('/?modulo=proveedores&accion=nuevo');
                return;
            }

            $datos = [
                'razon_social' => $razon_social,
                'rfc'          => $this->postStr('rfc'),
                'contacto'     => $this->postStr('contacto'),
                'telefono'     => $this->postStr('telefono'),
                'email'        => $this->postStr('email'),
                'notas'        => $this->postStr('notas'),
            ];

            try {
                $id = $this->model->crear($datos);
                $this->auditoria('crear', 'proveedores', $id, "Proveedor: {$razon_social}");
                Session::flash('success', 'Proveedor creado correctamente.');
            } catch (Exception $e) {
                Session::flash('error', 'No se pudo crear el proveedor.');
            }

            $this->redirect('/?modulo=proveedores');
            return;
        }

        $proveedor = null;
        $titulo    = 'Nuevo proveedor';
        $vistaPath = BASE_PATH . '/modules/proveedores/views/form.php';

        $this->render('proveedores/form', compact('titulo', 'proveedor', 'vistaPath'));
    }

    public function editar(): void
    {
        $this->requireAdmin();

        $id        = $this->getInt('id');
        $proveedor = $this->model->getById($id);

        if (!$proveedor) {
            Session::flash('error', 'Proveedor no encontrado.');
            $this->redirect('/?modulo=proveedores');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $razon_social = $this->postStr('razon_social');

            if (empty($razon_social)) {
                Session::flash('error', 'La razón social es obligatoria.');
                $this->redirect("/?modulo=proveedores&accion=editar&id={$id}");
                return;
            }

            $datos = [
                'razon_social' => $razon_social,
                'rfc'          => $this->postStr('rfc'),
                'contacto'     => $this->postStr('contacto'),
                'telefono'     => $this->postStr('telefono'),
                'email'        => $this->postStr('email'),
                'notas'        => $this->postStr('notas'),
            ];

            try {
                $this->model->actualizar($id, $datos);
                $this->auditoria('editar', 'proveedores', $id, "Proveedor: {$razon_social}");
                Session::flash('success', 'Proveedor actualizado correctamente.');
            } catch (Exception $e) {
                Session::flash('error', 'No se pudo actualizar el proveedor.');
            }

            $this->redirect('/?modulo=proveedores');
            return;
        }

        $titulo    = 'Editar proveedor';
        $vistaPath = BASE_PATH . '/modules/proveedores/views/form.php';

        $this->render('proveedores/form', compact('titulo', 'proveedor', 'vistaPath'));
    }

    public function eliminar(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=proveedores');
            return;
        }

        $this->validarCsrf();

        $id        = $this->postInt('id');
        $proveedor = $this->model->getById($id);

        if (!$proveedor) {
            Session::flash('error', 'Proveedor no encontrado.');
            $this->redirect('/?modulo=proveedores');
            return;
        }

        $this->model->eliminar($id);
        $this->auditoria('eliminar', 'proveedores', $id, "Proveedor: {$proveedor['razon_social']}");
        Session::flash('success', 'Proveedor desactivado correctamente.');

        $this->redirect('/?modulo=proveedores');
    }
}
