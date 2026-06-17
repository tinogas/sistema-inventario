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
    echo json_encode(['total' => 0]);
    exit;
}

$db          = Database::getInstance();
$sucursal_id = Auth::sucursalFiltro();

$where  = 'WHERE p.activo = 1 AND ss.cantidad <= p.stock_minimo';
$params = [];

if ($sucursal_id) {
    $where .= ' AND ss.sucursal_id = ?';
    $params[] = $sucursal_id;
}

$stmt = $db->prepare("SELECT COUNT(*) FROM stock_sucursal ss INNER JOIN productos p ON p.id = ss.producto_id {$where}");
$stmt->execute($params);

echo json_encode(['total' => (int) $stmt->fetchColumn()]);
