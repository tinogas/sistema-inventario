<?php
// Configuración general de la aplicación
define('APP_NAME',    'Inventario Taller');
define('APP_VERSION', '1.0.0');
define('APP_URL',     '/inventario');  // Ajustar según el dominio en cPanel

// Zona horaria
date_default_timezone_set('America/Hermosillo');

// Mostrar errores solo en desarrollo (cambiar a false en producción)
define('APP_DEBUG', true);

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Roles del sistema
define('ROL_ADMIN',        'admin');
define('ROL_ALMACENISTA',  'almacenista');
define('ROL_CONSULTA',     'consulta');

// Tipos de movimiento
define('MOV_ENTRADA',          'entrada');
define('MOV_SALIDA',           'salida');
define('MOV_TRASPASO_SALIDA',  'traspaso_salida');
define('MOV_TRASPASO_ENTRADA', 'traspaso_entrada');
define('MOV_AJUSTE',           'ajuste');

// Permisos por rol
define('PERMISOS', [
    ROL_ADMIN => ['*'],
    ROL_ALMACENISTA => [
        'dashboard.ver',
        'productos.ver', 'productos.editar',
        'categorias.ver',
        'unidades.ver',
        'proveedores.ver',
        'mecanicos.ver', 'mecanicos.editar',
        'servicios.ver',
        'entradas.ver', 'entradas.crear',
        'salidas.ver', 'salidas.crear',
        'traspasos.ver', 'traspasos.crear', 'traspasos.confirmar',
        'facturas.ver', 'facturas.crear', 'facturas.emitir',
        'reportes.ver',
        'clientes.ver', 'clientes.crear', 'clientes.editar',
        'bitacoras.ver', 'bitacoras.imprimir',
    ],
    ROL_CONSULTA => [
        'dashboard.ver',
        'productos.ver',
        'reportes.ver',
        'clientes.ver',
        'bitacoras.ver',
    ],
]);
