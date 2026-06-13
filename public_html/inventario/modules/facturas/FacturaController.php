<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/facturas/FacturaModel.php';

class FacturaController extends Controller
{
    private FacturaModel $model;

    public function __construct()
    {
        $this->model = new FacturaModel();
    }

    public function index(): void
    {
        $this->requirePermiso('facturas.ver');
        $sucursal_id = Auth::sucursalFiltro();
        $estado      = $this->getStr('estado');
        $buscar      = $this->getStr('buscar');
        $pagina      = max(1, $this->getInt('pagina', 1));

        $resultado = $this->model->listar($sucursal_id, $estado, $buscar, $pagina);
        $titulo    = 'Facturas de servicio';
        $this->render('facturas/lista', compact('titulo','resultado','estado','buscar','vistaPath'));
    }

    public function nueva(): void
    {
        $this->requirePermiso('facturas.crear');
        [$sucursales, $mecanicos, $servicios] = $this->catalogos();

        $titulo = 'Nueva factura';
        $this->render('facturas/nueva', compact('titulo','sucursales','mecanicos','servicios'));
    }

    public function guardar(): void
    {
        $this->requirePermiso('facturas.crear');
        $this->validarCsrf();

        [$cab, $partidas] = $this->leerPost();
        $id = $this->getInt('id') ?: null;

        try {
            $id = $this->model->guardar($cab, $partidas, $id);
            Session::flash('success', 'Borrador guardado.');
            $this->redirect('/?modulo=facturas&accion=detalle&id=' . $id);
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/?modulo=facturas&accion=nueva');
        }
    }

    public function editar(): void
    {
        $this->requirePermiso('facturas.crear');
        $id      = $this->getInt('id');
        $factura = $this->model->getById($id);

        if (!$factura || $factura['estado'] !== 'borrador') {
            Session::flash('error', 'Solo se pueden editar facturas en borrador.');
            $this->redirect('/?modulo=facturas');
        }

        $detalle                  = $this->model->getDetalle($id);
        [$sucursales, $mecanicos, $servicios] = $this->catalogos();

        $titulo = 'Editar factura ' . $factura['folio'];
        $this->render('facturas/nueva', compact('titulo','factura','detalle','sucursales','mecanicos','servicios'));
    }

    public function detalle(): void
    {
        $this->requirePermiso('facturas.ver');
        $id = $this->getInt('id');

        $factura = $this->model->getById($id);
        if (!$factura) {
            Session::flash('error', 'Factura no encontrada.');
            $this->redirect('/?modulo=facturas');
        }

        $detalle = $this->model->getDetalle($id);
        $titulo  = 'Factura ' . $factura['folio'];
        $this->render('facturas/detalle', compact('titulo','factura','detalle'));
    }

    public function emitir(): void
    {
        $this->requirePermiso('facturas.emitir');
        $this->validarCsrf();
        $id = $this->postInt('id');

        try {
            $this->model->emitir($id, Auth::usuario()['id']);
            $this->auditoria('emitir_factura', 'facturas', $id);
            Session::flash('success', 'Factura emitida. Inventario descontado.');
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/?modulo=facturas&accion=detalle&id=' . $id);
    }

    public function pagar(): void
    {
        $this->requirePermiso('facturas.emitir');
        $this->validarCsrf();
        $id = $this->postInt('id');

        try {
            $this->model->marcarPagada($id);
            $this->auditoria('pagar_factura', 'facturas', $id);
            Session::flash('success', 'Factura marcada como pagada.');
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/?modulo=facturas&accion=detalle&id=' . $id);
    }

    public function cancelar(): void
    {
        $this->requirePermiso('facturas.crear');
        $this->validarCsrf();
        $id = $this->postInt('id');

        try {
            $this->model->cancelar($id);
            $this->auditoria('cancelar_factura', 'facturas', $id);
            Session::flash('success', 'Factura cancelada.');
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/?modulo=facturas');
    }

    public function imprimir(): void
    {
        $this->requirePermiso('facturas.ver');
        $id = $this->getInt('id');

        $factura = $this->model->getById($id);
        if (!$factura) {
            die('Factura no encontrada.');
        }

        $detalle = $this->model->getDetalle($id);
        $this->renderSinLayout(BASE_PATH . '/modules/facturas/views/imprimir.php', compact('factura','detalle'));
    }

    // ---- Helpers ----

    private function catalogos(): array
    {
        $db         = Database::getInstance();
        $sucId      = Auth::sucursalActual();
        $sucursales = $db->query('SELECT id, nombre FROM sucursales WHERE activa=1 ORDER BY nombre')->fetchAll();

        $stmt = $db->prepare(
            'SELECT id, nombre FROM mecanicos WHERE activo=1' .
            ($sucId ? ' AND sucursal_id=?' : '') . ' ORDER BY nombre'
        );
        $sucId ? $stmt->execute([$sucId]) : $stmt->execute([]);
        $mecanicos = $stmt->fetchAll();

        $servicios = $db->query('SELECT id, nombre FROM servicios WHERE activo=1 ORDER BY nombre')->fetchAll();
        return [$sucursales, $mecanicos, $servicios];
    }

    private function leerPost(): array
    {
        $cab = [
            'sucursal_id'       => $this->postInt('sucursal_id')   ?: (int)Auth::sucursalActual(),
            'cliente_nombre'    => $this->postStr('cliente_nombre'),
            'cliente_tel'       => $this->postStr('cliente_tel'),
            'vh_marca'          => $this->postStr('vh_marca'),
            'vh_modelo'         => $this->postStr('vh_modelo'),
            'vh_anio'           => $this->postInt('vh_anio')        ?: (int)date('Y'),
            'vh_placas'         => $this->postStr('vh_placas'),
            'mecanico_id'       => $this->postInt('mecanico_id')    ?: null,
            'servicio_id'       => $this->postInt('servicio_id')    ?: null,
            'mano_obra'         => $this->postFloat('mano_obra'),
            'mano_obra_desc'    => $this->postStr('mano_obra_desc'),
            'referencia_proneg' => $this->postStr('referencia_proneg'),
            'notas'             => $this->postStr('notas'),
            'usuario_id'        => Auth::usuario()['id'],
        ];

        $productoIds = $_POST['producto_id']     ?? [];
        $cantidades  = $_POST['cantidad']         ?? [];
        $precios     = $_POST['precio_unitario']  ?? [];

        $partidas = [];
        foreach ($productoIds as $i => $pid) {
            $pid = (int)$pid;
            $qty = (float)str_replace(',','.', $cantidades[$i] ?? 0);
            $prc = (float)str_replace(',','.', $precios[$i]    ?? 0);
            if ($pid > 0 && $qty > 0) {
                $partidas[] = ['producto_id'=>$pid, 'cantidad'=>$qty, 'precio_unitario'=>$prc];
            }
        }
        return [$cab, $partidas];
    }
}
