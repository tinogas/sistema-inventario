<?php
declare(strict_types=1);

define('BASE_PATH', __DIR__);

// Cargar configuración
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';

// Cargar núcleo
require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Router.php';

// Iniciar sesión segura
Session::iniciar();

// Despachar la petición
Router::dispatch();
