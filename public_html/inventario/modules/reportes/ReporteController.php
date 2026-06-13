<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/reportes/ReporteModel.php';

class ReporteController extends Controller
{
    private ReporteModel $model;

    public function __construct()
    {
        $this->model = new ReporteModel();
    }

    public function index(): void
    {
        $this->redirect('/?modulo=reportes&accion=stock');
    }

    public function stock(): void
    {
        $this->requirePermiso('reportes.ver');

        $sucursal_id = Auth::sucursalFiltro();
        $categoria   = $this->getInt('categoria_id');
        $buscar      = $this->getStr('buscar');

        $datos      = $this->model->getStock($sucursal_id, $categoria ?: null, $buscar);
        $categorias = $this->model->getCategorias();

        if (isset($_GET['exportar'])) {
            $this->exportarCsv($datos, 'stock_actual');
        }

        $titulo    = 'Reporte: Stock actual';
        $vistaPath = BASE_PATH . '/modules/reportes/views/stock.php';
        $this->render('reportes/stock', compact('titulo','datos','categorias','sucursal_id','categoria','buscar','vistaPath'));
    }

    public function movimientos(): void
    {
        $this->requirePermiso('reportes.ver');

        $sucursal_id = Auth::sucursalFiltro();
        $tipo        = $this->getStr('tipo');
        $desde       = $this->getStr('desde') ?: date('Y-m-01');
        $hasta       = $this->getStr('hasta') ?: date('Y-m-d');
        $pagina      = max(1, $this->getInt('pagina', 1));

        $resultado = $this->model->getMovimientos($sucursal_id, $tipo, $desde, $hasta, $pagina);

        if (isset($_GET['exportar'])) {
            $this->exportarCsv($resultado['filas'], 'movimientos');
        }

        $titulo    = 'Reporte: Movimientos';
        $vistaPath = BASE_PATH . '/modules/reportes/views/movimientos.php';
        $this->render('reportes/movimientos', compact('titulo','resultado','sucursal_id','tipo','desde','hasta','vistaPath'));
    }

    public function alertas(): void
    {
        $this->requirePermiso('reportes.ver');

        $sucursal_id = Auth::sucursalFiltro();
        $datos       = $this->model->getAlertasStock($sucursal_id);

        $titulo    = 'Alertas: Stock bajo mínimo';
        $vistaPath = BASE_PATH . '/modules/reportes/views/alertas.php';
        $this->render('reportes/alertas', compact('titulo','datos','vistaPath'));
    }

    public function kardex(): void
    {
        $this->requirePermiso('reportes.ver');

        $producto_id = $this->getInt('producto_id');
        $sucursal_id = Auth::sucursalFiltro();
        $desde       = $this->getStr('desde') ?: date('Y-m-01');
        $hasta       = $this->getStr('hasta') ?: date('Y-m-d');

        $producto  = null;
        $kardex    = [];

        if ($producto_id) {
            $db = Database::getInstance();
            $stmt = $db->prepare('SELECT id, codigo, nombre FROM productos WHERE id=? AND activo=1');
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch();
            if ($producto) {
                $kardex = $this->model->getKardex($producto_id, $sucursal_id, $desde, $hasta);
            }
        }

        $titulo    = 'Kardex de producto';
        $vistaPath = BASE_PATH . '/modules/reportes/views/kardex.php';
        $this->render('reportes/kardex', compact('titulo','producto','kardex','sucursal_id','producto_id','desde','hasta','vistaPath'));
    }

    private function exportarCsv(array $datos, string $nombre): void
    {
        if (empty($datos)) {
            Session::flash('warning', 'No hay datos para exportar.');
            return;
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '_' . date('Ymd_His') . '.csv"');
        $out = fopen('php://output', 'w');
        // BOM para Excel en español
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, array_keys($datos[0]), ';');
        foreach ($datos as $fila) {
            fputcsv($out, $fila, ';');
        }
        fclose($out);
        exit;
    }
}
