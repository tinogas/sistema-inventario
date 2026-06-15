<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/entradas/EntradaModel.php';

class EntradaController extends Controller
{
    private EntradaModel $model;

    public function __construct()
    {
        $this->model = new EntradaModel();
    }

    public function index(): void
    {
        $this->requirePermiso('entradas.ver');

        $sucursal_id = Auth::sucursalFiltro();
        $buscar      = $this->getStr('buscar');
        $pagina      = max(1, $this->getInt('pagina', 1));

        $resultado = $this->model->listar($sucursal_id, $buscar, $pagina);

        $titulo    = 'Entradas de inventario';
        $vistaPath = BASE_PATH . '/modules/entradas/views/lista.php';
        $this->render('entradas/lista', compact('titulo','resultado','buscar','vistaPath'));
    }

    public function nueva(): void
    {
        $this->requirePermiso('entradas.crear');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $datos = [
                'sucursal_id'         => $this->postInt('sucursal_id') ?: (int) Auth::sucursalActual(),
                'proveedor_id'        => $this->postInt('proveedor_id') ?: null,
                'referencia_factura'  => $this->postStr('referencia_factura'),
                'uuid_cfdi'           => $this->postStr('uuid_cfdi'),
                'notas'               => $this->postStr('notas'),
                'usuario_id'          => Auth::usuario()['id'],
            ];

            // Leer partidas del POST
            $productoIds = $_POST['producto_id']    ?? [];
            $cantidades  = $_POST['cantidad']        ?? [];
            $precios     = $_POST['precio_unitario'] ?? [];

            $partidas = [];
            foreach ($productoIds as $i => $pid) {
                $pid = (int) $pid;
                $qty = (float) str_replace(',', '.', $cantidades[$i] ?? 0);
                $prc = (float) str_replace(',', '.', $precios[$i]    ?? 0);
                if ($pid > 0 && $qty > 0 && $prc >= 0) {
                    $partidas[] = ['producto_id' => $pid, 'cantidad' => $qty, 'precio_unitario' => $prc];
                }
            }

            try {
                $id = $this->model->confirmar($datos, $partidas);
                $this->auditoria('confirmar_entrada', 'movimientos', $id);
                Session::flash('success', 'Entrada registrada correctamente.');
                $this->redirect('/?modulo=entradas&accion=detalle&id=' . $id);
            } catch (Exception $e) {
                Session::flash('error', $e->getMessage());
            }
        }

        $db          = Database::getInstance();
        $sucursales  = $db->query('SELECT id, nombre FROM sucursales WHERE activa=1 ORDER BY nombre')->fetchAll();
        $proveedores = $db->query('SELECT id, razon_social FROM proveedores WHERE activo=1 ORDER BY razon_social')->fetchAll();

        // Precarga opcional desde el detalle de un producto (?producto_id=&sucursal_id=)
        $precargaCodigo   = '';
        $precargaSucursal = $this->getInt('sucursal_id');
        $pid = $this->getInt('producto_id');
        if ($pid > 0) {
            $st = $db->prepare('SELECT codigo FROM productos WHERE id = ? AND activo = 1');
            $st->execute([$pid]);
            $precargaCodigo = (string) ($st->fetchColumn() ?: '');
        }

        $titulo    = 'Nueva entrada';
        $vistaPath = BASE_PATH . '/modules/entradas/views/nueva.php';
        $this->render('entradas/nueva', compact('titulo','sucursales','proveedores','vistaPath','precargaCodigo','precargaSucursal'));
    }

    public function detalle(): void
    {
        $this->requirePermiso('entradas.ver');
        $id = $this->getInt('id');

        $entrada = $this->model->getById($id);
        if (!$entrada) {
            Session::flash('error', 'Entrada no encontrada.');
            $this->redirect('/?modulo=entradas');
        }

        $partidas  = $this->model->getDetalle($id);
        $titulo    = 'Detalle entrada ' . $entrada['folio'];
        $vistaPath = BASE_PATH . '/modules/entradas/views/detalle.php';
        $this->render('entradas/detalle', compact('titulo','entrada','partidas','vistaPath'));
    }

    public function cancelar(): void
    {
        $this->requirePermiso('entradas.cancelar');
        $this->validarCsrf();
        $id = $this->postInt('id');

        try {
            $this->model->cancelar($id);
            $this->auditoria('cancelar_entrada', 'movimientos', $id);
            Session::flash('success', 'Entrada cancelada y stock revertido.');
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/?modulo=entradas');
    }
}
