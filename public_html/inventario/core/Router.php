<?php
class Router
{
    private static array $rutas = [
        'auth'       => ['clase' => 'AuthController',       'archivo' => 'modules/auth/AuthController.php'],
        'dashboard'  => ['clase' => 'DashboardController',  'archivo' => 'modules/dashboard/DashboardController.php'],
        'productos'  => ['clase' => 'ProductoController',   'archivo' => 'modules/productos/ProductoController.php'],
        'categorias' => ['clase' => 'CategoriaController',  'archivo' => 'modules/categorias/CategoriaController.php'],
        'unidades'   => ['clase' => 'UnidadController',     'archivo' => 'modules/unidades/UnidadController.php'],
        'proveedores'=> ['clase' => 'ProveedorController',  'archivo' => 'modules/proveedores/ProveedorController.php'],
        'mecanicos'  => ['clase' => 'MecanicoController',   'archivo' => 'modules/mecanicos/MecanicoController.php'],
        'servicios'  => ['clase' => 'ServicioController',   'archivo' => 'modules/servicios/ServicioController.php'],
        'entradas'   => ['clase' => 'EntradaController',    'archivo' => 'modules/entradas/EntradaController.php'],
        'salidas'    => ['clase' => 'SalidaController',     'archivo' => 'modules/salidas/SalidaController.php'],
        'traspasos'  => ['clase' => 'TraspasoController',   'archivo' => 'modules/traspasos/TraspasoController.php'],
        'reportes'   => ['clase' => 'ReporteController',    'archivo' => 'modules/reportes/ReporteController.php'],
        'usuarios'   => ['clase' => 'UsuarioController',    'archivo' => 'modules/usuarios/UsuarioController.php'],
        'sucursales' => ['clase' => 'SucursalController',  'archivo' => 'modules/sucursales/SucursalController.php'],
        'facturas'   => ['clase' => 'FacturaController',   'archivo' => 'modules/facturas/FacturaController.php'],
    ];

    public static function dispatch(): void
    {
        $modulo = filter_input(INPUT_GET, 'modulo', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'dashboard';
        $accion = filter_input(INPUT_GET, 'accion', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'index';

        // Módulo por defecto si no está autenticado
        if (!Auth::estaAutenticado() && $modulo !== 'auth') {
            header('Location: ' . APP_URL . '/?modulo=auth&accion=login');
            exit;
        }

        if (!isset(self::$rutas[$modulo])) {
            self::error404();
            return;
        }

        $ruta = self::$rutas[$modulo];
        $archivo = BASE_PATH . '/' . $ruta['archivo'];

        if (!file_exists($archivo)) {
            self::error404();
            return;
        }

        require_once $archivo;
        $clase = $ruta['clase'];

        if (!class_exists($clase)) {
            self::error404();
            return;
        }

        $controller = new $clase();
        $metodo = self::sanitizarMetodo($accion);

        if (!method_exists($controller, $metodo)) {
            self::error404();
            return;
        }

        $controller->$metodo();
    }

    private static function sanitizarMetodo(string $accion): string
    {
        // Convierte "crear_entrada" → "crearEntrada" (camelCase)
        $partes = explode('_', preg_replace('/[^a-z0-9_]/', '', strtolower($accion)));
        $metodo = array_shift($partes);
        foreach ($partes as $parte) {
            $metodo .= ucfirst($parte);
        }
        return $metodo;
    }

    private static function error404(): void
    {
        http_response_code(404);
        echo '<h1>404 - Página no encontrada</h1>';
        exit;
    }
}
