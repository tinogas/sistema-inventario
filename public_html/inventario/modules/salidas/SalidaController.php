<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/salidas/SalidaModel.php';

class SalidaController extends Controller
{
    private SalidaModel $model;

    public function __construct()
    {
        $this->model = new SalidaModel();
    }

    public function index(): void
    {
        $this->requirePermiso('salidas.ver');

        $sucursal_id = Auth::sucursalFiltro();
        $buscar      = $this->getStr('buscar');
        $pagina      = max(1, $this->getInt('pagina', 1));

        $resultado = $this->model->listar($sucursal_id, $buscar, $pagina);

        $titulo    = 'Salidas de inventario';
        $vistaPath = BASE_PATH . '/modules/salidas/views/lista.php';
        $this->render('salidas/lista', compact('titulo','resultado','buscar','vistaPath'));
    }

    public function nueva(): void
    {
        $this->requirePermiso('salidas.crear');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $datos = [
                'sucursal_id'        => $this->postInt('sucursal_id') ?: (int) Auth::sucursalActual(),
                'mecanico_id'        => $this->postInt('mecanico_id') ?: null,
                'servicio_id'        => $this->postInt('servicio_id') ?: null,
                'referencia_factura' => $this->postStr('referencia_factura'),
                'notas'              => $this->postStr('notas'),
                'usuario_id'         => Auth::usuario()['id'],
            ];

            $forzar     = isset($_POST['forzar_stock']);
            $productoIds = $_POST['producto_id']     ?? [];
            $cantidades  = $_POST['cantidad']         ?? [];
            $precios     = $_POST['precio_unitario']  ?? [];

            $partidas = [];
            foreach ($productoIds as $i => $pid) {
                $pid = (int) $pid;
                $qty = (float) str_replace(',','.', $cantidades[$i] ?? 0);
                $prc = (float) str_replace(',','.', $precios[$i]    ?? 0);
                if ($pid > 0 && $qty > 0) {
                    $partidas[] = ['producto_id' => $pid, 'cantidad' => $qty, 'precio_unitario' => $prc];
                }
            }

            try {
                $id = $this->model->confirmar($datos, $partidas, $forzar);
                $nota = $forzar ? ' (stock forzado)' : '';
                $this->auditoria('confirmar_salida' . ($forzar?'_forzada':''), 'movimientos', $id);
                Session::flash('success', 'Salida registrada correctamente.' . $nota);
                $this->redirect('/?modulo=salidas&accion=detalle&id=' . $id);
            } catch (RuntimeException $e) {
                Session::flash('error', $e->getMessage());
            }
        }

        $db        = Database::getInstance();
        $sucId     = Auth::sucursalActual();
        $sucursales = $db->query('SELECT id, nombre FROM sucursales WHERE activa=1 ORDER BY nombre')->fetchAll();
        $mecanicos  = $db->prepare(
            'SELECT id, nombre FROM mecanicos WHERE activo=1' .
            ($sucId ? ' AND sucursal_id = ?' : '') . ' ORDER BY nombre'
        );
        $sucId ? $mecanicos->execute([$sucId]) : $mecanicos->execute([]);
        $mecanicos = $mecanicos->fetchAll();

        $servicios = $db->query('SELECT id, nombre FROM servicios WHERE activo=1 ORDER BY nombre')->fetchAll();

        $titulo    = 'Nueva salida';
        $vistaPath = BASE_PATH . '/modules/salidas/views/nueva.php';
        $this->render('salidas/nueva', compact('titulo','sucursales','mecanicos','servicios','vistaPath'));
    }

    public function detalle(): void
    {
        $this->requirePermiso('salidas.ver');
        $id = $this->getInt('id');

        $salida = $this->model->getById($id);
        if (!$salida) {
            Session::flash('error', 'Salida no encontrada.');
            $this->redirect('/?modulo=salidas');
        }

        $partidas  = $this->model->getDetalle($id);
        $titulo    = 'Detalle salida ' . $salida['folio'];
        $vistaPath = BASE_PATH . '/modules/salidas/views/detalle.php';
        $this->render('salidas/detalle', compact('titulo','salida','partidas','vistaPath'));
    }
}
