<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Upload.php';
require_once BASE_PATH . '/modules/sucursales/SucursalModel.php';

class SucursalController extends Controller
{
    private SucursalModel $model;

    public function __construct()
    {
        $this->model = new SucursalModel();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $sucursales = $this->model->listar();
        $titulo     = 'Sucursales';
        $this->render('sucursales/lista', compact('titulo', 'sucursales'));
    }

    public function nuevo(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();
            $datos = [
                'nombre'    => $this->postStr('nombre'),
                'ciudad'    => $this->postStr('ciudad'),
                'direccion' => $this->postStr('direccion'),
                'telefono'  => $this->postStr('telefono'),
                'latitud'   => $this->postStr('latitud'),
                'longitud'  => $this->postStr('longitud'),
                'foto'      => null,
            ];
            $errFoto = '';
            try { $datos['foto'] = Upload::imagen('foto', 'sucursal'); }
            catch (RuntimeException $e) { $errFoto = $e->getMessage(); }

            if (!$datos['nombre'] || !$datos['ciudad'] || $errFoto) {
                Session::flash('error', $errFoto ?: 'Nombre y ciudad son obligatorios.');
                $titulo = 'Nueva sucursal';
                $this->render('sucursales/form', compact('titulo', 'datos'));
                return;
            } else {
                $id = $this->model->crear($datos);
                $this->auditoria('crear_sucursal', 'sucursales', $id);
                Session::flash('success', 'Sucursal creada correctamente.');
                $this->redirect('/?modulo=sucursales');
            }
        }

        $titulo = 'Nueva sucursal';
        $this->render('sucursales/form', compact('titulo'));
    }

    public function editar(): void
    {
        $this->requireAdmin();
        $id        = $this->getInt('id');
        $sucursal  = $this->model->getById($id);

        if (!$sucursal) {
            Session::flash('error', 'Sucursal no encontrada.');
            $this->redirect('/?modulo=sucursales');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();
            $datos = [
                'nombre'    => $this->postStr('nombre'),
                'ciudad'    => $this->postStr('ciudad'),
                'direccion' => $this->postStr('direccion'),
                'telefono'  => $this->postStr('telefono'),
                'latitud'   => $this->postStr('latitud'),
                'longitud'  => $this->postStr('longitud'),
                'activa'    => isset($_POST['activa']),
                'foto'      => $sucursal['foto'] ?? null,
            ];
            $errFoto = '';
            try { $datos['foto'] = Upload::imagen('foto', 'sucursal', $sucursal['foto'] ?? null); }
            catch (RuntimeException $e) { $errFoto = $e->getMessage(); }

            if (!$datos['nombre'] || !$datos['ciudad'] || $errFoto) {
                Session::flash('error', $errFoto ?: 'Nombre y ciudad son obligatorios.');
            } else {
                $this->model->actualizar($id, $datos);
                $this->auditoria('editar_sucursal', 'sucursales', $id);
                Session::flash('success', 'Sucursal actualizada.');
                $this->redirect('/?modulo=sucursales');
            }
        }

        $titulo = 'Editar sucursal';
        $this->render('sucursales/form', compact('titulo', 'sucursal'));
    }
}
