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
$cliente_id  = (int)($_GET['cliente_id'] ?? 0);

if (!$cliente_id) {
    echo json_encode(['unidades' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $db->prepare(
    'SELECT id, marca, modelo, anio, placas, numero_serie, color
       FROM clientes_unidades
      WHERE cliente_id = :cid
        AND activo = 1
      ORDER BY marca, modelo'
);
$stmt->execute([':cid' => $cliente_id]);
$unidades = $stmt->fetchAll();

echo json_encode(['unidades' => $unidades], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
