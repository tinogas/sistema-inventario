<?php
require_once BASE_PATH . '/core/Model.php';

class EmpresaModel extends Model
{
    private static string $ddl = "
        CREATE TABLE IF NOT EXISTS empresa (
            id    INT AUTO_INCREMENT,
            clave VARCHAR(60) NOT NULL,
            valor TEXT,
            PRIMARY KEY (id),
            UNIQUE KEY uq_empresa_clave (clave)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";

    private static array $defaults = [
        'nombre'      => 'Taller Muelles Sonora',
        'rfc'         => '',
        'direccion'   => '',
        'ciudad'      => 'Hermosillo',
        'cp'          => '',
        'telefono'    => '',
        'email'       => '',
        'logo_path'   => '',
        'pie_factura' => '',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $this->db->exec(self::$ddl);

        // Insertar claves por defecto si no existen
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO empresa (clave, valor) VALUES (:clave, :valor)'
        );
        foreach (self::$defaults as $clave => $valor) {
            $stmt->execute([':clave' => $clave, ':valor' => $valor]);
        }
    }

    /**
     * Retorna todos los datos de la empresa como array asociativo ['clave' => 'valor'].
     */
    public function get(): array
    {
        $filas = $this->fetchAll('SELECT clave, valor FROM empresa');
        $datos = [];
        foreach ($filas as $fila) {
            $datos[$fila['clave']] = $fila['valor'];
        }
        return $datos;
    }

    /**
     * Alias estático para uso en vistas de facturas/impresión.
     * Crea una instancia temporal para obtener los datos.
     */
    public static function getAll(): array
    {
        $instancia = new self();
        return $instancia->get();
    }

    /**
     * Guarda (INSERT o UPDATE) los datos de la empresa.
     *
     * @param array $datos Array asociativo ['clave' => 'valor']
     */
    public function guardar(array $datos): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO empresa (clave, valor)
             VALUES (:clave, :valor)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)'
        );
        foreach ($datos as $clave => $valor) {
            // Solo guardar claves conocidas para evitar inyección de datos
            if (array_key_exists($clave, self::$defaults)) {
                $stmt->execute([':clave' => $clave, ':valor' => (string) $valor]);
            }
        }
    }
}
