<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/clientes/ClienteModel.php';
require_once BASE_PATH . '/modules/clientes/UnidadClienteModel.php';

class UnidadClienteController extends Controller
{
    private UnidadClienteModel $model;
    private ClienteModel $clienteModel;

    public function __construct()
    {
        $this->model        = new UnidadClienteModel();
        $this->clienteModel = new ClienteModel();
    }

    public function nueva(): void
    {
        $this->requirePermiso('clientes.editar');

        $cliente_id = $this->getInt('cliente_id') ?: $this->postInt('cliente_id');
        $cliente    = $this->clienteModel->getById($cliente_id);

        if (!$cliente) {
            Session::flash('error', 'Cliente no encontrado.');
            $this->redirect('/?modulo=clientes');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $marca  = $this->postStr('marca');
            $modelo = $this->postStr('modelo');

            if (empty($marca) || empty($modelo)) {
                Session::flash('error', 'Marca y modelo son obligatorios.');
                $this->redirect("/?modulo=unidad_cliente&accion=nueva&cliente_id={$cliente_id}");
                return;
            }

            $datos = [
                'cliente_id'    => $cliente_id,
                'marca'         => $marca,
                'modelo'        => $modelo,
                'anio'          => $this->postStr('anio'),
                'placas'        => $this->postStr('placas'),
                'numero_serie'  => $this->postStr('numero_serie'),
                'color'         => $this->postStr('color'),
                'notas'         => $this->postStr('notas'),
            ];

            try {
                $id = $this->model->crear($datos);
                $this->auditoria('crear', 'clientes_unidades', $id, "Unidad: {$marca} {$modelo} para cliente #{$cliente_id}");
                Session::flash('success', 'Unidad agregada correctamente.');
            } catch (Exception $e) {
                Session::flash('error', 'No se pudo agregar la unidad.');
            }

            $this->redirect("/?modulo=clientes&accion=detalle&id={$cliente_id}");
            return;
        }

        $unidad    = null;
        $titulo    = 'Nueva unidad — ' . htmlspecialchars($cliente['nombre']);
        $vistaPath = BASE_PATH . '/modules/clientes/views/unidad_form.php';

        $this->render('clientes/unidad_form', compact('titulo', 'unidad', 'cliente', 'vistaPath'));
    }

    public function editar(): void
    {
        $this->requirePermiso('clientes.editar');

        $id     = $this->getInt('id');
        $unidad = $this->model->getById($id);

        if (!$unidad) {
            Session::flash('error', 'Unidad no encontrada.');
            $this->redirect('/?modulo=clientes');
            return;
        }

        $cliente = $this->clienteModel->getById($unidad['cliente_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $marca  = $this->postStr('marca');
            $modelo = $this->postStr('modelo');

            if (empty($marca) || empty($modelo)) {
                Session::flash('error', 'Marca y modelo son obligatorios.');
                $this->redirect("/?modulo=unidad_cliente&accion=editar&id={$id}");
                return;
            }

            $datos = [
                'marca'        => $marca,
                'modelo'       => $modelo,
                'anio'         => $this->postStr('anio'),
                'placas'       => $this->postStr('placas'),
                'numero_serie' => $this->postStr('numero_serie'),
                'color'        => $this->postStr('color'),
                'notas'        => $this->postStr('notas'),
            ];

            try {
                $this->model->actualizar($id, $datos);
                $this->auditoria('editar', 'clientes_unidades', $id, "Unidad: {$marca} {$modelo}");
                Session::flash('success', 'Unidad actualizada correctamente.');
            } catch (Exception $e) {
                Session::flash('error', 'No se pudo actualizar la unidad.');
            }

            $this->redirect("/?modulo=clientes&accion=detalle&id={$unidad['cliente_id']}");
            return;
        }

        $titulo    = 'Editar unidad — ' . htmlspecialchars($cliente['nombre'] ?? '');
        $vistaPath = BASE_PATH . '/modules/clientes/views/unidad_form.php';

        $this->render('clientes/unidad_form', compact('titulo', 'unidad', 'cliente', 'vistaPath'));
    }

    public function toggleActivo(): void
    {
        $this->requirePermiso('clientes.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=clientes');
            return;
        }

        $this->validarCsrf();
        $id     = $this->postInt('id');
        $unidad = $this->model->getById($id);

        if (!$unidad) {
            Session::flash('error', 'Unidad no encontrada.');
            $this->redirect('/?modulo=clientes');
            return;
        }

        $this->model->toggleActivo($id);
        Session::flash('success', 'Estado de la unidad actualizado.');
        $this->redirect("/?modulo=clientes&accion=detalle&id={$unidad['cliente_id']}");
    }
}
