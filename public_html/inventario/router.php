<?php
/**
 * router.php — Para el servidor built-in de PHP
 * Uso: php -S localhost:8080 router.php
 *      (desde la carpeta public_html/inventario/)
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Servir archivos estáticos directamente (CSS, JS, imágenes)
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Todo lo demás va a index.php
require_once __DIR__ . '/index.php';
