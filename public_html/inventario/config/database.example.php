<?php
/**
 * Copia este archivo como database.php y configura tus credenciales.
 * NUNCA subas database.php al repositorio (está en .gitignore).
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'nombre_base_datos');   // Nombre de la BD en cPanel
define('DB_USER', 'usuario_bd');          // Usuario MySQL de cPanel
define('DB_PASS', 'contraseña_bd');       // Contraseña MySQL de cPanel
define('DB_CHARSET', 'utf8mb4');

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST, DB_NAME, DB_CHARSET
            );
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }

    private function __construct() {}
    private function __clone() {}
}
