<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/traspasos/TraspasoModel.php';

class TraspasoController extends Controller
{
    private TraspasoModel $model;

    public function __construct()
    {
        $this->model = new TraspasoModel();
    }

    public function index(): void
    {
        $this->requirePermiso('traspasos.ver');
        $sucursal_id = Auth::sucursalFiltro();
        $buscar      = $this->getStr('buscar');
        $pagina      = max(1, $this->getInt('pagina', 1));

        $resultado = $this->model->listar($sucursal_id, $buscar, $pagina);
        $titulo    = 'Traspasos entre sucursales';
        $vistaPath = BASE_PATH . '/modules/traspasos/views/lista.php';
        $this->render('traspasos/lista', compact('titulo','resultado','buscar','vistaPath'));
    }

    public function nuevo(): void
    {
        $this->requirePermiso('traspasos.crear');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $datos = [
                'sucursal_origen_id' => $this->postInt('sucursal_origen_id') ?: (int) Auth::sucursalActual(),
                'sucursal_dest_id'   => $this->postInt('sucursal_dest_id'),
                'notas'              => $this->postStr('notas'),
                'usuario_id'         => Auth::usuario()['id'],
            ];

            $productoIds = $_POST['producto_id'] ?? [];
            $cantidades  = $_POST['cantidad']     ?? [];
            $partidas    = [];
            foreach ($productoIds as $i => $pid) {
                $pid = (int) $pid;
                $qty = (float) str_replace(',','.', $cantidades[$i] ?? 0);
                if ($pid > 0 && $qty > 0) {
                    $partidas[] = ['producto_id' => $pid, 'cantidad' => $qty];
                }
            }

            try {
                $id = $this->model->crear($datos, $partidas);
                $this->auditoria('crear_traspaso', 'traspasos', $id);
                Session::flash('success', 'Traspaso enviado. La sucursal destino debe confirmar la recepción.');
                $this->redirect('/?modulo=traspasos&accion=detalle&id=' . $id);
            } catch (RuntimeException $e) {
                Session::flash('error', $e->getMessage());
            }
        }

        $db         = Database::getInstance();
        $sucursales = $db->query('SELECT id, nombre FROM sucursales WHERE activa=1 ORDER BY nombre')->fetchAll();

        $titulo    = 'Nuevo traspaso';
        $vistaPath = BASE_PATH . '/modules/traspasos/views/nuevo.php';
        $this->render('traspasos/nuevo', compact('titulo','sucursales','vistaPath'));
    }

    public function detalle(): void
    {
        $this->requirePermiso('traspasos.ver');
        $id = $this->getInt('id');

        $traspaso = $this->model->getById($id);
        if (!$traspaso) {
            Session::flash('error', 'Traspaso no encontrado.');
            $this->redirect('/?modulo=traspasos');
        }

        $partidas  = $this->model->getPartidas($traspaso['movimiento_salida_id']);
        $titulo    = 'Detalle traspaso';
        $vistaPath = BASE_PATH . '/modules/traspasos/views/detalle.php';
        $this->render('traspasos/detalle', compact('titulo','traspaso','partidas','vistaPath'));
    }

    public function confirmarRecepcion(): void
    {
        $this->requirePermiso('traspasos.confirmar');
        $this->validarCsrf();

        $id        = $this->postInt('traspaso_id');
        $recibidas = $_POST['recibido'] ?? [];

        try {
            $this->model->confirmarRecepcion($id, $recibidas, Auth::usuario()['id']);
            $this->auditoria('confirmar_recepcion_traspaso', 'traspasos', $id);
            Session::flash('success', 'Recepción confirmada. Stock actualizado en tu sucursal.');
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/?modulo=traspasos&accion=detalle&id=' . $id);
    }

    public function cancelar(): void
    {
        $this->requirePermiso('traspasos.crear');
        $this->validarCsrf();
        $id = $this->postInt('traspaso_id');

        try {
            $this->model->cancelar($id);
            $this->auditoria('cancelar_traspaso', 'traspasos', $id);
            Session::flash('success', 'Traspaso cancelado y stock revertido en origen.');
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/?modulo=traspasos');
    }
}
