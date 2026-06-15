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

$db = Database::getInstance();

// sucursal_id explícito vía GET (salida/factura/traspaso indican la sucursal del formulario).
// Para NO-admin se ignora el GET y se fuerza su propia sucursal, evitando que un
// almacenista consulte el stock de otra sucursal manipulando la URL.
$explicit_sid = isset($_GET['sucursal_id']) ? (int)$_GET['sucursal_id'] : 0;
if (Auth::esAdmin()) {
    $sucursal_id = $explicit_sid > 0 ? $explicit_sid : Auth::sucursalFiltro();
} else {
    $sucursal_id = Auth::sucursalFiltro();
}

// Subconsulta para stock en tránsito (traspasos enviados desde esta sucursal aún sin recibir)
function stockEnTransitoSQL(?int $sid): array {
    if (!$sid) return ['expr' => '0', 'params' => []];
    return [
        'expr'   => '(SELECT COALESCE(SUM(md2.cantidad),0) FROM movimientos_detalle md2
                       INNER JOIN movimientos m2 ON m2.id = md2.movimiento_id
                       INNER JOIN traspasos t2   ON t2.movimiento_salida_id = m2.id
                       WHERE md2.producto_id = p.id AND m2.sucursal_id = :sid_tr AND m2.tipo = \'traspaso_salida\' AND t2.estado = \'en_transito\')',
        'params' => [':sid_tr' => $sid],
    ];
}

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
    $transit = stockEnTransitoSQL($sucursal_id);
    $params  = array_merge($params, $transit['params']);
    $stmt = $db->prepare(
        "SELECT p.id, p.codigo, p.nombre, p.precio_costo, p.precio_venta,
                COALESCE(u.clave,'PZA') AS unidad,
                {$stockExpr} AS stock_actual,
                ({$transit['expr']}) AS stock_en_transito
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
        $producto['stock_disponible'] = max(0, (float)$producto['stock_actual'] - (float)$producto['stock_en_transito']);
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
    $transitQ = stockEnTransitoSQL($sucursal_id);
    $paramsQ  = array_merge($paramsQ, $transitQ['params']);
    $stmt = $db->prepare(
        "SELECT p.id, p.codigo, p.nombre,
                {$stockExprQ} AS stock_actual,
                ({$transitQ['expr']}) AS stock_en_transito
         FROM productos p
         {$stockJoinQ}
         WHERE p.activo = 1
           AND (p.codigo LIKE :q1 OR p.codigo_alterno LIKE :q2 OR p.nombre LIKE :q3)
         ORDER BY p.nombre ASC
         LIMIT 10"
    );
    $stmt->execute($paramsQ);
    $sug = $stmt->fetchAll();
    foreach ($sug as &$s) {
        $s['stock_disponible'] = max(0, (float)$s['stock_actual'] - (float)$s['stock_en_transito']);
    }
    unset($s);
    echo json_encode(['sugerencias' => $sug], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

echo json_encode(['error' => 'Parámetro requerido: codigo o q'], JSON_UNESCAPED_UNICODE);
