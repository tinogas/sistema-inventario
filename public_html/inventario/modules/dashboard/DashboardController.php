<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/dashboard/DashboardModel.php';

class DashboardController extends Controller
{
    private DashboardModel $model;

    public function __construct()
    {
        $this->model = new DashboardModel();
    }

    public function index(): void
    {
        $this->requirePermiso('dashboard.ver');
        $sucursal_id = Auth::sucursalFiltro();

        $kpis         = $this->model->getKpis($sucursal_id);
        $alertas      = $this->model->getAlertasStock($sucursal_id, 10);
        $movimientos7 = $this->model->getMovimientos7Dias($sucursal_id);
        $ultimas      = $this->model->getUltimasActividades($sucursal_id, 8);

        $titulo    = 'Dashboard';
        $vistaPath = BASE_PATH . '/modules/dashboard/views/index.php';
        $this->render('dashboard/index', compact(
            'titulo','kpis','alertas','movimientos7','ultimas'
        ));
    }
}
