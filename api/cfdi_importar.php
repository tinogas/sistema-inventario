<?php
declare(strict_types=1);
ob_start();  // Captura cualquier output espurio (notices/warnings) antes del JSON

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/Auth.php';

Session::iniciar();

function responderJson(mixed $data, int $status = 200): never
{
    ob_end_clean();  // Descartar cualquier output previo (notices, warnings)
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!Auth::estaAutenticado() || !Auth::tienePermiso('entradas.crear')) {
    responderJson(['error' => 'Sin permiso'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['archivo'])) {
    responderJson(['error' => 'Se requiere un archivo XML']);
}

$archivo = $_FILES['archivo'];
if ($archivo['error'] !== UPLOAD_ERR_OK) {
    responderJson(['error' => 'Error al subir el archivo']);
}

// Validar que sea XML (content-type o extensión)
$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
if ($extension !== 'xml') {
    responderJson(['error' => 'Solo se aceptan archivos .xml']);
}

// Guardar temporalmente
$cfdiDir = BASE_PATH . '/uploads/cfdi';
if (!is_dir($cfdiDir)) {
    mkdir($cfdiDir, 0750, true);
}
$tmpPath = $cfdiDir . '/' . uniqid('cfdi_', true) . '.xml';
if (!move_uploaded_file($archivo['tmp_name'], $tmpPath)) {
    responderJson(['error' => 'No se pudo guardar el archivo temporal']);
}

try {
    $resultado = parsearCFDI($tmpPath);
    unlink($tmpPath);
    responderJson($resultado);
} catch (Exception $e) {
    @unlink($tmpPath);
    responderJson(['error' => $e->getMessage()]);
}

function parsearCFDI(string $xmlPath): array
{
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string(file_get_contents($xmlPath), 'SimpleXMLElement', LIBXML_NONET);

    if ($xml === false) {
        $errores = array_map(fn($e) => $e->message, libxml_get_errors());
        throw new Exception('XML inválido: ' . implode(', ', $errores));
    }

    // Registrar namespaces CFDI 3.3 y 4.0
    $xml->registerXPathNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
    $xml->registerXPathNamespace('tfd',  'http://www.sat.gob.mx/TimbreFiscalDigital');

    // Intentar CFDI 4.0 primero, luego 3.3
    $tfd = $xml->xpath('//tfd:TimbreFiscalDigital/@UUID');
    $uuid = $tfd ? (string) $tfd[0] : '';

    if (!$uuid) {
        // Intentar con namespace 3.3
        $xml->registerXPathNamespace('cfdi', 'http://www.sat.gob.mx/cfd/3');
        $tfd2 = $xml->xpath('//tfd:TimbreFiscalDigital/@UUID');
        $uuid = $tfd2 ? (string) $tfd2[0] : '';
    }

    // Extraer emisor
    $emisorRfc = '';
    $emisores  = $xml->xpath('//*[local-name()="Emisor"]/@Rfc');
    if ($emisores) $emisorRfc = (string) $emisores[0];

    // Extraer conceptos (partidas)
    $conceptos = $xml->xpath('//*[local-name()="Concepto"]');
    $partidas  = [];

    // Intentar asociar conceptos con productos del catálogo por descripción
    $db = Database::getInstance();

    foreach ($conceptos as $c) {
        $descripcion = (string) ($c['Descripcion'] ?? $c['descripcion'] ?? '');
        $cantidad    = (float)  ($c['Cantidad']    ?? $c['cantidad']    ?? 1);
        $precio      = (float)  ($c['ValorUnitario'] ?? $c['valorUnitario'] ?? 0);
        $claveSat    = (string) ($c['ClaveProdServ']  ?? '');

        // Intentar match con producto del catálogo por nombre similar
        $productoId    = 0;
        $codigo        = '';
        $matchAprox    = false;
        if ($descripcion) {
            $stmt = $db->prepare(
                "SELECT id, codigo FROM productos WHERE activo=1 AND nombre LIKE :q LIMIT 1"
            );
            $stmt->execute([':q' => '%' . substr($descripcion, 0, 20) . '%']);
            $match = $stmt->fetch();
            if ($match) {
                $productoId = $match['id'];
                $codigo     = $match['codigo'];
                $matchAprox = true; // match por nombre, no confirmado
            }
        }

        $partidas[] = [
            'producto_id'    => $productoId,
            'codigo'         => $codigo,
            'clave_sat'      => $claveSat,
            'descripcion'    => $descripcion,
            'cantidad'       => $cantidad,
            'precio'         => $precio,
            'match_aproximado' => $matchAprox,
        ];
    }

    return [
        'uuid'       => $uuid,
        'emisor_rfc' => $emisorRfc,
        'partidas'   => $partidas,
    ];
}
