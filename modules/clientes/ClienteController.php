<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/clientes/ClienteModel.php';
require_once BASE_PATH . '/modules/clientes/UnidadClienteModel.php';

class ClienteController extends Controller
{
    private ClienteModel $model;

    public function __construct()
    {
        $this->model = new ClienteModel();
    }

    public function index(): void
    {
        $this->requirePermiso('clientes.ver');

        $buscar = $this->getStr('buscar');
        $pagina = max(1, $this->getInt('pagina', 1));
        $result = $this->model->listar($buscar, $pagina);

        $titulo    = 'Clientes';
        $vistaPath = BASE_PATH . '/modules/clientes/views/lista.php';

        $this->render('clientes/lista', compact('titulo', 'vistaPath', 'buscar', 'result'));
    }

    public function nuevo(): void
    {
        $this->requirePermiso('clientes.crear');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $nombre = $this->postStr('nombre');
            if (empty($nombre)) {
                Session::flash('error', 'El nombre del cliente es obligatorio.');
                $this->redirect('/?modulo=clientes&accion=nuevo');
                return;
            }

            $datos = [
                'nombre'    => $nombre,
                'rfc'       => $this->postStr('rfc'),
                'telefono'  => $this->postStr('telefono'),
                'email'     => $this->postStr('email'),
                'direccion' => $this->postStr('direccion'),
                'notas'     => $this->postStr('notas'),
            ];

            try {
                $id = $this->model->crear($datos);
                $this->auditoria('crear', 'clientes', $id, "Cliente: {$nombre}");
                Session::flash('success', 'Cliente creado correctamente.');
                $this->redirect("/?modulo=clientes&accion=detalle&id={$id}");
            } catch (Exception $e) {
                Session::flash('error', 'No se pudo crear el cliente.');
                $this->redirect('/?modulo=clientes&accion=nuevo');
            }
            return;
        }

        $cliente   = null;
        $titulo    = 'Nuevo cliente';
        $vistaPath = BASE_PATH . '/modules/clientes/views/form.php';
        $this->render('clientes/form', compact('titulo', 'cliente', 'vistaPath'));
    }

    public function editar(): void
    {
        $this->requirePermiso('clientes.editar');

        $id      = $this->getInt('id');
        $cliente = $this->model->getById($id);

        if (!$cliente) {
            Session::flash('error', 'Cliente no encontrado.');
            $this->redirect('/?modulo=clientes');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $nombre = $this->postStr('nombre');
            if (empty($nombre)) {
                Session::flash('error', 'El nombre del cliente es obligatorio.');
                $this->redirect("/?modulo=clientes&accion=editar&id={$id}");
                return;
            }

            $datos = [
                'nombre'    => $nombre,
                'rfc'       => $this->postStr('rfc'),
                'telefono'  => $this->postStr('telefono'),
                'email'     => $this->postStr('email'),
                'direccion' => $this->postStr('direccion'),
                'notas'     => $this->postStr('notas'),
            ];

            try {
                $this->model->actualizar($id, $datos);
                $this->auditoria('editar', 'clientes', $id, "Cliente: {$nombre}");
                Session::flash('success', 'Cliente actualizado correctamente.');
                $this->redirect("/?modulo=clientes&accion=detalle&id={$id}");
            } catch (Exception $e) {
                Session::flash('error', 'No se pudo actualizar el cliente.');
                $this->redirect("/?modulo=clientes&accion=editar&id={$id}");
            }
            return;
        }

        $titulo    = 'Editar cliente';
        $vistaPath = BASE_PATH . '/modules/clientes/views/form.php';
        $this->render('clientes/form', compact('titulo', 'cliente', 'vistaPath'));
    }

    public function detalle(): void
    {
        $this->requirePermiso('clientes.ver');

        $id      = $this->getInt('id');
        $cliente = $this->model->getById($id);

        if (!$cliente) {
            Session::flash('error', 'Cliente no encontrado.');
            $this->redirect('/?modulo=clientes');
            return;
        }

        $unidades  = $this->model->getUnidades($id);
        $titulo    = htmlspecialchars($cliente['nombre']);
        $vistaPath = BASE_PATH . '/modules/clientes/views/detalle.php';

        $this->render('clientes/detalle', compact('titulo', 'cliente', 'unidades', 'vistaPath'));
    }

    public function toggleActivo(): void
    {
        $this->requirePermiso('clientes.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=clientes');
            return;
        }

        $this->validarCsrf();
        $id      = $this->postInt('id');
        $cliente = $this->model->getById($id);

        if (!$cliente) {
            Session::flash('error', 'Cliente no encontrado.');
            $this->redirect('/?modulo=clientes');
            return;
        }

        $this->model->toggleActivo($id);
        $nuevoEstado = $cliente['activo'] ? 'desactivado' : 'activado';
        $this->auditoria('toggle_activo', 'clientes', $id, "Cliente {$nuevoEstado}: {$cliente['nombre']}");
        Session::flash('success', "Cliente {$nuevoEstado} correctamente.");

        $this->redirect("/?modulo=clientes&accion=detalle&id={$id}");
    }
}
