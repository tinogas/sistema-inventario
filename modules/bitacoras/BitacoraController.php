<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/bitacoras/BitacoraModel.php';

class BitacoraController extends Controller
{
    private BitacoraModel $model;

    public function __construct()
    {
        $this->model = new BitacoraModel();
    }

    public function index(): void
    {
        $this->requirePermiso('bitacoras.ver');

        $filtros = [
            'cliente_id'  => $this->getInt('cliente_id')  ?: null,
            'unidad_id'   => $this->getInt('unidad_id')   ?: null,
            'fecha_desde' => $this->getStr('fecha_desde'),
            'fecha_hasta' => $this->getStr('fecha_hasta'),
        ];
        $pagina = max(1, $this->getInt('pagina', 1));
        $result = $this->model->listar($filtros, $pagina);

        // Si viene filtrado por cliente, cargamos su nombre para mostrarlo
        $clienteNombre = '';
        if ($filtros['cliente_id']) {
            require_once BASE_PATH . '/modules/clientes/ClienteModel.php';
            $cm = new ClienteModel();
            $cl = $cm->getById($filtros['cliente_id']);
            $clienteNombre = $cl['nombre'] ?? '';
        }

        $titulo    = 'Bitácora de servicio';
        $vistaPath = BASE_PATH . '/modules/bitacoras/views/lista.php';

        $this->render('bitacoras/lista', compact(
            'titulo', 'vistaPath', 'result', 'filtros', 'clienteNombre'
        ));
    }

    public function ver(): void
    {
        $this->requirePermiso('bitacoras.ver');

        $id       = $this->getInt('id');
        $bitacora = $this->model->getById($id);

        if (!$bitacora) {
            Session::flash('error', 'Registro de bitácora no encontrado.');
            $this->redirect('/?modulo=bitacoras');
            return;
        }

        $productos = json_decode($bitacora['productos_snapshot'] ?? '[]', true) ?: [];

        $titulo    = 'Bitácora — ' . $bitacora['folio'];
        $vistaPath = BASE_PATH . '/modules/bitacoras/views/ver.php';

        $this->render('bitacoras/ver', compact('titulo', 'bitacora', 'productos', 'vistaPath'));
    }

    public function imprimir(): void
    {
        $this->requirePermiso('bitacoras.imprimir');

        $id       = $this->getInt('id');
        $bitacora = $this->model->getById($id);

        if (!$bitacora) {
            http_response_code(404);
            echo 'Bitácora no encontrada.';
            exit;
        }

        $productos = json_decode($bitacora['productos_snapshot'] ?? '[]', true) ?: [];

        // Cargar datos de empresa
        $db      = \Database::getInstance();
        $empresa = $db->query('SELECT * FROM empresa LIMIT 1')->fetch() ?: [];

        require BASE_PATH . '/modules/bitacoras/views/imprimir.php';
        exit;
    }
}
