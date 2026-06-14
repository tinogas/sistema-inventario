<?php
require_once BASE_PATH . '/core/Model.php';

/**
 * BackupModel — Respaldos de la base de datos en PHP puro (portable, sin
 * depender de mysqldump). Genera un .sql con la estructura y los datos de
 * todas las tablas, y registra cada respaldo en backups_log.
 */
class BackupModel extends Model
{
    /** Carpeta donde se guardan los .sql (relativa al raíz del sistema) */
    public const DIR_REL = 'backups';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTabla();
    }

    private function ensureTabla(): void
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS backups_log (
                id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
                archivo        VARCHAR(255)  NOT NULL,
                tamano_bytes   BIGINT UNSIGNED NOT NULL DEFAULT 0,
                num_tablas     INT UNSIGNED  NOT NULL DEFAULT 0,
                num_registros  INT UNSIGNED  NOT NULL DEFAULT 0,
                usuario_id     INT UNSIGNED  NULL,
                usuario_nombre VARCHAR(120)  NULL,
                estado         ENUM('completado','error') NOT NULL DEFAULT 'completado',
                notas          TEXT          NULL,
                created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_bk_fecha (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function dirAbs(): string
    {
        return rtrim(BASE_PATH, '/\\') . '/' . self::DIR_REL;
    }

    /**
     * Genera un respaldo completo y lo registra en el log.
     * @return array ['archivo'=>..., 'tamano'=>..., 'num_tablas'=>..., 'num_registros'=>...]
     */
    public function crear(array $usuario): array
    {
        $dir = $this->dirAbs();
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            $this->registrar('', 0, 0, 0, $usuario, 'error', 'No se pudo crear la carpeta de respaldos.');
            throw new RuntimeException('No se pudo crear la carpeta de respaldos.');
        }

        $nombre  = 'backup_' . DB_NAME . '_' . date('Ymd_His') . '.sql';
        $destino = $dir . '/' . $nombre;

        try {
            [$numTablas, $numRegistros] = $this->generarDump($destino);
        } catch (Throwable $e) {
            if (is_file($destino)) { @unlink($destino); }
            $this->registrar($nombre, 0, 0, 0, $usuario, 'error', $e->getMessage());
            throw new RuntimeException('Error al generar el respaldo: ' . $e->getMessage());
        }

        $tamano = is_file($destino) ? (int) filesize($destino) : 0;
        $this->registrar($nombre, $tamano, $numTablas, $numRegistros, $usuario, 'completado', null);

        return ['archivo' => $nombre, 'tamano' => $tamano, 'num_tablas' => $numTablas, 'num_registros' => $numRegistros];
    }

    /**
     * Escribe el dump SQL completo en $destino. Devuelve [numTablas, numRegistros].
     */
    private function generarDump(string $destino): array
    {
        $fh = fopen($destino, 'w');
        if (!$fh) {
            throw new RuntimeException('No se pudo abrir el archivo de respaldo para escritura.');
        }

        $cab  = "-- ============================================================\n";
        $cab .= "-- Respaldo de la base de datos: " . DB_NAME . "\n";
        $cab .= "-- Generado: " . date('Y-m-d H:i:s') . "\n";
        $cab .= "-- Sistema: " . APP_NAME . "\n";
        $cab .= "-- ============================================================\n\n";
        $cab .= "SET NAMES utf8mb4;\n";
        $cab .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        fwrite($fh, $cab);

        $tablas = $this->db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $numTablas = 0;
        $numRegistros = 0;

        foreach ($tablas as $tabla) {
            $numTablas++;
            // Estructura
            $createRow = $this->db->query("SHOW CREATE TABLE `{$tabla}`")->fetch(PDO::FETCH_NUM);
            $create    = $createRow[1] ?? '';
            fwrite($fh, "-- ------------------------------------------------------------\n");
            fwrite($fh, "-- Tabla: {$tabla}\n");
            fwrite($fh, "-- ------------------------------------------------------------\n");
            fwrite($fh, "DROP TABLE IF EXISTS `{$tabla}`;\n");
            fwrite($fh, $create . ";\n\n");

            // Datos
            $stmt = $this->db->query("SELECT * FROM `{$tabla}`");
            $columnas = null;
            $buffer   = [];
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($columnas === null) {
                    $columnas = '`' . implode('`, `', array_keys($fila)) . '`';
                }
                $vals = [];
                foreach ($fila as $v) {
                    $vals[] = ($v === null) ? 'NULL' : $this->db->quote((string) $v);
                }
                $buffer[] = '(' . implode(', ', $vals) . ')';
                $numRegistros++;

                if (count($buffer) >= 200) {
                    fwrite($fh, "INSERT INTO `{$tabla}` ({$columnas}) VALUES\n" . implode(",\n", $buffer) . ";\n");
                    $buffer = [];
                }
            }
            if ($buffer) {
                fwrite($fh, "INSERT INTO `{$tabla}` ({$columnas}) VALUES\n" . implode(",\n", $buffer) . ";\n");
            }
            fwrite($fh, "\n");
        }

        fwrite($fh, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($fh);

        return [$numTablas, $numRegistros];
    }

    private function registrar(string $archivo, int $tamano, int $numTablas, int $numReg, array $usuario, string $estado, ?string $notas): void
    {
        $this->execute(
            'INSERT INTO backups_log (archivo, tamano_bytes, num_tablas, num_registros, usuario_id, usuario_nombre, estado, notas)
             VALUES (:a, :t, :nt, :nr, :uid, :un, :e, :n)',
            [
                ':a' => $archivo, ':t' => $tamano, ':nt' => $numTablas, ':nr' => $numReg,
                ':uid' => $usuario['id'] ?? null, ':un' => $usuario['nombre'] ?? null,
                ':e' => $estado, ':n' => $notas,
            ]
        );
    }

    public function listar(): array
    {
        $filas = $this->fetchAll('SELECT * FROM backups_log ORDER BY created_at DESC');
        // Marcar si el archivo aún existe en disco
        foreach ($filas as &$f) {
            $f['existe'] = $f['archivo'] && is_file($this->dirAbs() . '/' . $f['archivo']);
        }
        return $filas;
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM backups_log WHERE id = :id', [':id' => $id]);
    }

    /** Ruta absoluta del archivo de un respaldo, validando que esté dentro de la carpeta. */
    public function rutaArchivo(string $archivo): ?string
    {
        $base = $archivo === '' ? '' : basename($archivo); // evita path traversal
        if ($base === '' || $base !== $archivo) {
            return null;
        }
        $ruta = $this->dirAbs() . '/' . $base;
        return is_file($ruta) ? $ruta : null;
    }

    public function eliminar(int $id): void
    {
        $reg = $this->getById($id);
        if (!$reg) {
            return;
        }
        if (!empty($reg['archivo'])) {
            $ruta = $this->rutaArchivo($reg['archivo']);
            if ($ruta) { @unlink($ruta); }
        }
        $this->execute('DELETE FROM backups_log WHERE id = :id', [':id' => $id]);
    }
}
