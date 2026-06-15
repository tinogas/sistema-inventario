<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/empresa/EmpresaModel.php';

class EmpresaController extends Controller
{
    private EmpresaModel $model;

    public function __construct()
    {
        $this->model = new EmpresaModel();
    }

    /**
     * Muestra el formulario con los datos actuales de la empresa.
     */
    public function index(): void
    {
        $this->requireAdmin();

        $empresa = $this->model->get();
        $titulo  = 'Datos de la empresa';
        $this->render('empresa/form', compact('titulo', 'empresa'));
    }

    /**
     * Procesa el POST del formulario y guarda los datos.
     */
    public function guardar(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=empresa');
        }

        $this->validarCsrf();

        $datos = [
            'nombre'      => $this->postStr('nombre'),
            'rfc'         => $this->postStr('rfc'),
            'direccion'   => $this->postStr('direccion'),
            'ciudad'      => $this->postStr('ciudad'),
            'cp'          => $this->postStr('cp'),
            'telefono'    => $this->postStr('telefono'),
            'email'       => $this->postStr('email'),
            'logo_path'   => $this->postStr('logo_path'),
            'pie_factura' => trim($_POST['pie_factura'] ?? ''),
        ];

        if (!$datos['nombre']) {
            Session::flash('error', 'El nombre de la empresa es obligatorio.');
            $this->redirect('/?modulo=empresa');
        }

        $this->model->guardar($datos);
        $this->auditoria('editar_empresa', 'empresa', 0);
        Session::flash('success', 'Datos de la empresa guardados correctamente.');
        $this->redirect('/?modulo=empresa');
    }
}
