<?php
require_once BASE_PATH . '/core/Model.php';

/**
 * BaseDatosModel — Mantenimiento de la base de datos:
 *  - Guardar un "seed" de ejemplo con los datos actuales (snapshot .sql).
 *  - Cargar los datos de ejemplo (para presentaciones).
 *  - Vaciar la base para empezar desde cero (conserva el admin y catálogos base).
 */
class BaseDatosModel extends Model
{
    /** Ruta del seed de ejemplo (relativa al raíz del sistema) */
    public const SEED_REL = 'data/seed_ejemplo.sql';

    /** Tablas que se conservan al "vaciar" (catálogos base). usuarios se maneja aparte. */
    private const CONSERVAR = ['sucursales', 'categorias', 'unidades'];

    private function seedAbs(): string
    {
        return rtrim(BASE_PATH, '/\\') . '/' . self::SEED_REL;
    }

    public function seedExiste(): bool
    {
        return is_file($this->seedAbs());
    }

    public function seedInfo(): array
    {
        $abs = $this->seedAbs();
        if (!is_file($abs)) {
            return ['existe' => false, 'tamano' => 0, 'fecha' => null];
        }
        return ['existe' => true, 'tamano' => (int) filesize($abs), 'fecha' => date('Y-m-d H:i:s', filemtime($abs))];
    }

    /** Conteo de registros por tabla (para mostrar el estado actual). */
    public function conteos(): array
    {
        $tablas = $this->db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $out = [];
        foreach ($tablas as $t) {
            $out[$t] = (int) $this->db->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        }
        return $out;
    }

    /**
     * Genera/actualiza el seed de ejemplo con los datos ACTUALES (dump completo).
     */
    public function guardarSeed(): array
    {
        $dir = dirname($this->seedAbs());
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('No se pudo crear la carpeta de datos.');
        }
        [$tablas, $registros] = $this->dump($this->seedAbs());
        return ['tablas' => $tablas, 'registros' => $registros, 'tamano' => (int) filesize($this->seedAbs())];
    }

    /**
     * Carga los datos de ejemplo: ejecuta el seed (DROP/CREATE/INSERT de todo).
     */
    public function cargarEjemplo(): void
    {
        $abs = $this->seedAbs();
        if (!is_file($abs)) {
            throw new RuntimeException('No existe el archivo de datos de ejemplo. Primero genera el seed.');
        }
        $sql = file_get_contents($abs);
        if ($sql === false || trim($sql) === '') {
            throw new RuntimeException('El archivo de datos de ejemplo está vacío.');
        }
        $this->ejecutarSql($sql);
    }

    /**
     * Vacía la base para empezar de cero: borra todos los datos operativos y de
     * catálogo, CONSERVANDO el usuario administrador indicado y los catálogos base
     * (sucursales, categorías, unidades) para que el sistema siga siendo usable.
     */
    public function vaciar(int $adminId): void
    {
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        try {
            $tablas = $this->db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tablas as $t) {
                if (in_array($t, self::CONSERVAR, true)) {
                    continue; // catálogos base se conservan
                }
                if ($t === 'usuarios') {
                    // conservar solo el administrador actual
                    $st = $this->db->prepare('DELETE FROM usuarios WHERE id <> :id');
                    $st->execute([':id' => $adminId]);
                    continue;
                }
                $this->db->exec("TRUNCATE TABLE `{$t}`");
            }
        } finally {
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    // ── Internos ─────────────────────────────────────────────────────

    /** Escribe el dump completo (DROP/CREATE/INSERT) en $destino. Devuelve [tablas, registros]. */
    private function dump(string $destino): array
    {
        $fh = fopen($destino, 'w');
        if (!$fh) {
            throw new RuntimeException('No se pudo abrir el archivo de seed para escritura.');
        }
        fwrite($fh, "-- Seed de datos de ejemplo — " . DB_NAME . "\n");
        fwrite($fh, "-- Generado: " . date('Y-m-d H:i:s') . "\n");
        fwrite($fh, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n");

        $tablas = $this->db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $numT = 0; $numR = 0;
        foreach ($tablas as $t) {
            $numT++;
            $create = $this->db->query("SHOW CREATE TABLE `{$t}`")->fetch(PDO::FETCH_NUM)[1] ?? '';
            fwrite($fh, "DROP TABLE IF EXISTS `{$t}`;\n{$create};\n");

            $stmt = $this->db->query("SELECT * FROM `{$t}`");
            $cols = null; $buf = [];
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($cols === null) {
                    $cols = '`' . implode('`, `', array_keys($fila)) . '`';
                }
                $vals = [];
                foreach ($fila as $v) {
                    $vals[] = ($v === null) ? 'NULL' : $this->db->quote((string) $v);
                }
                $buf[] = '(' . implode(', ', $vals) . ')';
                $numR++;
                if (count($buf) >= 200) {
                    fwrite($fh, "INSERT INTO `{$t}` ({$cols}) VALUES\n" . implode(",\n", $buf) . ";\n");
                    $buf = [];
                }
            }
            if ($buf) {
                fwrite($fh, "INSERT INTO `{$t}` ({$cols}) VALUES\n" . implode(",\n", $buf) . ";\n");
            }
            fwrite($fh, "\n");
        }
        fwrite($fh, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($fh);
        return [$numT, $numR];
    }

    /** Ejecuta un script SQL, separando sentencias de forma segura (respeta comillas). */
    private function ejecutarSql(string $sql): void
    {
        $stmts = $this->dividirSql($sql);
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        try {
            foreach ($stmts as $s) {
                $s = trim($s);
                if ($s === '' || str_starts_with($s, '--')) {
                    continue;
                }
                $this->db->exec($s);
            }
        } finally {
            $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /** Divide un script SQL en sentencias por ';' fuera de comillas/cadenas. */
    private function dividirSql(string $sql): array
    {
        $stmts = [];
        $buffer = '';
        $len = strlen($sql);
        $enComilla = false;   // dentro de '...'
        $i = 0;
        while ($i < $len) {
            $ch = $sql[$i];
            $buffer .= $ch;
            if ($enComilla) {
                if ($ch === '\\') {                 // escape: copiar el siguiente char tal cual
                    if ($i + 1 < $len) { $buffer .= $sql[$i + 1]; $i += 2; continue; }
                } elseif ($ch === "'") {
                    // ¿comilla doble escapada ''?
                    if ($i + 1 < $len && $sql[$i + 1] === "'") { $buffer .= "'"; $i += 2; continue; }
                    $enComilla = false;
                }
            } else {
                if ($ch === "'") {
                    $enComilla = true;
                } elseif ($ch === ';') {
                    $stmts[] = substr($buffer, 0, -1); // sin el ';'
                    $buffer = '';
                }
            }
            $i++;
        }
        if (trim($buffer) !== '') {
            $stmts[] = $buffer;
        }
        return $stmts;
    }
}
