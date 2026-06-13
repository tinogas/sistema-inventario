<?php
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/modules/productos/ProductoModel.php';

class ProductoController extends Controller
{
    private ProductoModel $model;

    public function __construct()
    {
        $this->model = new ProductoModel();
    }

    // -------------------------------------------------------
    // index — Lista paginada
    // -------------------------------------------------------
    public function index(): void
    {
        $this->requirePermiso('productos.ver');

        $sucursal_id = Auth::sucursalFiltro();
        $buscar      = $this->getStr('buscar');
        $pagina      = max(1, $this->getInt('pagina', 1));

        $paginacion = $this->model->listar($sucursal_id, $buscar, $pagina);

        $titulo    = 'Productos';
        $vistaPath = BASE_PATH . '/modules/productos/views/lista.php';
        $this->render('productos/lista', compact(
            'titulo', 'paginacion', 'buscar', 'sucursal_id'
        ));
    }

    // -------------------------------------------------------
    // nuevo — GET muestra formulario / POST guarda
    // -------------------------------------------------------
    public function nuevo(): void
    {
        $this->requirePermiso('productos.editar');

        $categorias = $this->model->getCategorias();
        $unidades   = $this->model->getUnidades();
        $proveedores = $this->model->getProveedores();
        $producto   = null; // sin datos previos

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $datos = $this->recogerFormulario();
            $errores = $this->validarDatos($datos);

            // Verificar código único
            if (empty($errores)) {
                $existente = $this->model->buscarPorCodigo($datos['codigo']);
                if ($existente !== null) {
                    $errores[] = 'Ya existe un producto con ese código.';
                }
            }

            if (empty($errores)) {
                $id = $this->model->crear($datos);
                $this->auditoria('crear', 'productos', $id, "Código: {$datos['codigo']} — {$datos['nombre']}");
                Session::flash('success', 'Producto creado correctamente.');
                $this->redirect('/?modulo=productos&accion=detalle&id=' . $id);
            } else {
                // Re-poblar el formulario con los datos ingresados
                $producto = $datos;
                Session::flash('error', implode(' ', $errores));
            }
        }

        $titulo    = 'Nuevo producto';
        $vistaPath = BASE_PATH . '/modules/productos/views/form.php';
        $this->render('productos/form', compact(
            'titulo', 'producto', 'categorias', 'unidades', 'proveedores'
        ));
    }

    // -------------------------------------------------------
    // editar — GET muestra formulario / POST guarda
    // -------------------------------------------------------
    public function editar(): void
    {
        $this->requirePermiso('productos.editar');

        $id = $this->getInt('id');
        $producto = $this->model->getById($id);

        if ($producto === null) {
            Session::flash('error', 'Producto no encontrado.');
            $this->redirect('/?modulo=productos');
        }

        $categorias  = $this->model->getCategorias();
        $unidades    = $this->model->getUnidades();
        $proveedores = $this->model->getProveedores();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarCsrf();

            $datos  = $this->recogerFormulario();
            $errores = $this->validarDatos($datos);

            // Verificar código único (excluyendo el propio producto)
            if (empty($errores)) {
                $existente = $this->model->buscarPorCodigo($datos['codigo']);
                if ($existente !== null && (int) $existente['id'] !== $id) {
                    $errores[] = 'Ya existe otro producto con ese código.';
                }
            }

            if (empty($errores)) {
                $this->model->actualizar($id, $datos);
                $this->auditoria('editar', 'productos', $id, "Código: {$datos['codigo']} — {$datos['nombre']}");
                Session::flash('success', 'Producto actualizado correctamente.');
                $this->redirect('/?modulo=productos&accion=detalle&id=' . $id);
            } else {
                $producto = array_merge($producto, $datos);
                Session::flash('error', implode(' ', $errores));
            }
        }

        $titulo    = 'Editar producto';
        $vistaPath = BASE_PATH . '/modules/productos/views/form.php';
        $this->render('productos/form', compact(
            'titulo', 'producto', 'categorias', 'unidades', 'proveedores'
        ));
    }

    // -------------------------------------------------------
    // detalle — Stock por sucursal + últimos movimientos
    // -------------------------------------------------------
    public function detalle(): void
    {
        $this->requirePermiso('productos.ver');

        $id = $this->getInt('id');
        $producto = $this->model->getById($id);

        if ($producto === null) {
            Session::flash('error', 'Producto no encontrado.');
            $this->redirect('/?modulo=productos');
        }

        $stockSucursales = $this->model->getStockPorSucursal($id);
        $movimientos     = $this->model->getUltimosMovimientos($id, 15);

        $titulo    = 'Detalle: ' . $producto['nombre'];
        $vistaPath = BASE_PATH . '/modules/productos/views/detalle.php';
        $this->render('productos/detalle', compact(
            'titulo', 'producto', 'stockSucursales', 'movimientos'
        ));
    }

    // -------------------------------------------------------
    // eliminar — POST con CSRF, solo admin
    // -------------------------------------------------------
    public function eliminar(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/?modulo=productos');
        }

        $this->validarCsrf();

        $id = $this->postInt('id');
        $producto = $this->model->getById($id);

        if ($producto === null) {
            Session::flash('error', 'Producto no encontrado.');
            $this->redirect('/?modulo=productos');
        }

        try {
            $this->model->eliminar($id);
            $this->auditoria('eliminar', 'productos', $id, "Código: {$producto['codigo']} — {$producto['nombre']}");
            Session::flash('success', "Producto \"{$producto['nombre']}\" eliminado.");
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect('/?modulo=productos');
    }

    // -------------------------------------------------------
    // buscarAjax — Endpoint JSON para autocomplete
    // -------------------------------------------------------
    public function buscarAjax(): void
    {
        $this->requirePermiso('productos.ver');

        $q = $this->getStr('q');
        if (strlen($q) < 2) {
            $this->json([]);
        }

        $this->json($this->model->buscarSugerencias($q));
    }

    // -------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------

    private function recogerFormulario(): array
    {
        return [
            'codigo'         => strtoupper($this->postStr('codigo')),
            'codigo_alterno' => strtoupper($this->postStr('codigo_alterno')),
            'nombre'         => $this->postStr('nombre'),
            'descripcion'    => $this->postStr('descripcion'),
            'categoria_id'   => $this->postInt('categoria_id') ?: null,
            'unidad_id'      => $this->postInt('unidad_id'),
            'proveedor_id'   => $this->postInt('proveedor_id') ?: null,
            'precio_costo'   => $this->postFloat('precio_costo'),
            'precio_venta'   => $this->postFloat('precio_venta'),
            'stock_minimo'   => $this->postFloat('stock_minimo'),
        ];
    }

    private function validarDatos(array $d): array
    {
        $errores = [];

        if ($d['codigo'] === '') {
            $errores[] = 'El código es obligatorio.';
        }
        if ($d['nombre'] === '') {
            $errores[] = 'El nombre es obligatorio.';
        }
        if ($d['unidad_id'] <= 0) {
            $errores[] = 'Debe seleccionar una unidad de medida.';
        }
        if ($d['precio_venta'] < 0) {
            $errores[] = 'El precio de venta no puede ser negativo.';
        }
        if ($d['stock_minimo'] < 0) {
            $errores[] = 'El stock mínimo no puede ser negativo.';
        }

        return $errores;
    }
}
