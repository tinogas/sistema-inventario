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
$q  = trim($_GET['q'] ?? '');

if ($q === '') {
    echo json_encode(['sugerencias' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

$like = '%' . $q . '%';
$stmt = $db->prepare(
    'SELECT id, nombre, rfc, telefono
       FROM clientes
      WHERE activo = 1
        AND (nombre LIKE :q1 OR rfc LIKE :q2 OR telefono LIKE :q3)
      ORDER BY nombre ASC
      LIMIT 8'
);
$stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
$sugerencias = $stmt->fetchAll();

echo json_encode(['sugerencias' => $sugerencias], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
