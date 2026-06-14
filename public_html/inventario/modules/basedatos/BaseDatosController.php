<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/basedatos/BaseDatosModel.php';

class BaseDatosController extends Controller
{
    private BaseDatosModel $model;

    public function __construct()
    {
        $this->model = new BaseDatosModel();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $conteos   = $this->model->conteos();
        $seed      = $this->model->seedInfo();
        $titulo    = 'Base de datos';
        $vistaPath = BASE_PATH . '/modules/basedatos/views/lista.php';
        $this->render('basedatos/lista', compact('titulo', 'conteos', 'seed', 'vistaPath'));
    }

    /** Guarda/actualiza el seed de ejemplo con los datos actuales. */
    public function guardarSeed(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/?modulo=basedatos'); }
        $this->validarCsrf();
        try {
            $r = $this->model->guardarSeed();
            $this->auditoria('guardar_seed', 'basedatos', 0, "Seed: {$r['tablas']} tablas, {$r['registros']} registros");
            Session::flash('success', "Seed de ejemplo guardado con los datos actuales ({$r['tablas']} tablas, {$r['registros']} registros).");
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/?modulo=basedatos');
    }

    /** Carga los datos de ejemplo (restaura el seed). DESTRUCTIVO. */
    public function cargarEjemplo(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/?modulo=basedatos'); }
        $this->validarCsrf();
        if ($this->postStr('confirmar') !== 'CARGAR') {
            Session::flash('error', 'Debes escribir CARGAR para confirmar.');
            $this->redirect('/?modulo=basedatos');
        }
        try {
            $this->model->cargarEjemplo();
            $this->auditoria('cargar_ejemplo', 'basedatos', 0, 'Datos de ejemplo cargados');
            Session::flash('success', 'Datos de ejemplo cargados correctamente.');
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/?modulo=basedatos');
    }

    /** Vacía la base para empezar de cero (conserva admin actual y catálogos base). DESTRUCTIVO. */
    public function vaciar(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/?modulo=basedatos'); }
        $this->validarCsrf();
        if ($this->postStr('confirmar') !== 'VACIAR') {
            Session::flash('error', 'Debes escribir VACIAR para confirmar.');
            $this->redirect('/?modulo=basedatos');
        }
        try {
            $this->model->vaciar((int) Auth::usuario()['id']);
            $this->auditoria('vaciar_bd', 'basedatos', 0, 'Base de datos vaciada (inicio desde cero)');
            Session::flash('success', 'Base de datos vaciada. Se conservó tu usuario y los catálogos base (sucursales, categorías, unidades).');
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/?modulo=basedatos');
    }
}
