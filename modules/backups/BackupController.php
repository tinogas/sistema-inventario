<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/backups/BackupModel.php';

class BackupController extends Controller
{
    private BackupModel $model;

    public function __construct()
    {
        $this->model = new BackupModel();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $backups   = $this->model->listar();
        $titulo    = 'Respaldos de la base de datos';
        $vistaPath = BASE_PATH . '/modules/backups/views/lista.php';
        $this->render('backups/lista', compact('titulo', 'backups', 'vistaPath'));
    }

    public function crear(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=backups');
        }
        $this->validarCsrf();

        try {
            $r = $this->model->crear(Auth::usuario());
            $this->auditoria('crear_backup', 'backups_log', 0, 'Respaldo: ' . $r['archivo']);
            Session::flash('success',
                'Respaldo generado: ' . $r['archivo'] . ' (' .
                $r['num_tablas'] . ' tablas, ' . $r['num_registros'] . ' registros).');
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/?modulo=backups');
    }

    public function descargar(): void
    {
        $this->requireAdmin();
        $id  = $this->getInt('id');
        $reg = $this->model->getById($id);
        if (!$reg || empty($reg['archivo'])) {
            Session::flash('error', 'Respaldo no encontrado.');
            $this->redirect('/?modulo=backups');
        }
        $ruta = $this->model->rutaArchivo($reg['archivo']);
        if (!$ruta) {
            Session::flash('error', 'El archivo del respaldo ya no existe en el servidor.');
            $this->redirect('/?modulo=backups');
        }

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . basename($ruta) . '"');
        header('Content-Length: ' . filesize($ruta));
        header('Cache-Control: no-store');
        readfile($ruta);
        exit;
    }

    public function eliminar(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=backups');
        }
        $this->validarCsrf();

        $id = $this->postInt('id');
        $this->model->eliminar($id);
        $this->auditoria('eliminar_backup', 'backups_log', $id);
        Session::flash('success', 'Respaldo eliminado.');
        $this->redirect('/?modulo=backups');
    }
}
