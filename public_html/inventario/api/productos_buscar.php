<?php
declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/Auth.php';

header('Content-Type: application/json; charset=utf-8');

Session::iniciar();
if (!Auth::estaAutenticado()) {
    echo json_encode(['error' => 'No autenticado'], JSON_UNESCAPED_UNICODE);
    exit;
}

$db          = Database::getInstance();
$sucursal_id = Auth::sucursalFiltro();

// ---- Búsqueda exacta por código (escáner) ----
if (isset($_GET['codigo'])) {
    $codigo = trim($_GET['codigo']);
    if ($codigo === '') {
        echo json_encode(['encontrado' => false], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($sucursal_id !== null) {
        $stockExpr = 'COALESCE(ss.cantidad, 0)';
        $stockJoin = 'LEFT JOIN stock_sucursal ss ON ss.producto_id = p.id AND ss.sucursal_id = :sid';
        $params    = [':codigo' => $codigo, ':codigo2' => $codigo, ':sid' => $sucursal_id];
    } else {
        $stockExpr = 'COALESCE(stot.total_stock, 0)';
        $stockJoin = 'LEFT JOIN (SELECT producto_id, SUM(cantidad) AS total_stock FROM stock_sucursal GROUP BY producto_id) stot ON stot.producto_id = p.id';
        $params    = [':codigo' => $codigo, ':codigo2' => $codigo];
    }
    $stmt = $db->prepare(
        "SELECT p.id, p.codigo, p.nombre, p.precio_costo, p.precio_venta,
                COALESCE(u.clave,'PZA') AS unidad,
                {$stockExpr} AS stock_actual
         FROM productos p
         LEFT JOIN unidades u ON u.id = p.unidad_id
         {$stockJoin}
         WHERE p.activo = 1
           AND (p.codigo = :codigo OR p.codigo_alterno = :codigo2)
         LIMIT 1"
    );
    $stmt->execute($params);
    $producto = $stmt->fetch();

    if ($producto) {
        echo json_encode(['encontrado' => true, 'producto' => $producto], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    } else {
        echo json_encode(['encontrado' => false], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
    exit;
}

// ---- Sugerencias por nombre/código para autocomplete ----
if (isset($_GET['q'])) {
    $q    = trim($_GET['q']);
    $like = "%{$q}%";
    if ($sucursal_id !== null) {
        $stockExprQ = 'COALESCE(ss.cantidad, 0)';
        $stockJoinQ = 'LEFT JOIN stock_sucursal ss ON ss.producto_id = p.id AND ss.sucursal_id = :sid';
        $paramsQ    = [':q1' => $like, ':q2' => $like, ':q3' => $like, ':sid' => $sucursal_id];
    } else {
        $stockExprQ = 'COALESCE(stot.total_stock, 0)';
        $stockJoinQ = 'LEFT JOIN (SELECT producto_id, SUM(cantidad) AS total_stock FROM stock_sucursal GROUP BY producto_id) stot ON stot.producto_id = p.id';
        $paramsQ    = [':q1' => $like, ':q2' => $like, ':q3' => $like];
    }
    $stmt = $db->prepare(
        "SELECT p.id, p.codigo, p.nombre,
                {$stockExprQ} AS stock_actual
         FROM productos p
         {$stockJoinQ}
         WHERE p.activo = 1
           AND (p.codigo LIKE :q1 OR p.codigo_alterno LIKE :q2 OR p.nombre LIKE :q3)
         ORDER BY p.nombre ASC
         LIMIT 10"
    );
    $stmt->execute($paramsQ);
    echo json_encode(['sugerencias' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

echo json_encode(['error' => 'Parámetro requerido: codigo o q'], JSON_UNESCAPED_UNICODE);
