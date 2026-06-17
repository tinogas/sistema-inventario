<?php
class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row ?: null;
    }

    protected function fetchColumn(string $sql, array $params = []): mixed
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    protected function execute(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    protected function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    // ── Transacciones anidadas ──────────────────────────────────────────
    // PDO no soporta transacciones anidadas y todos los modelos comparten la
    // misma conexión singleton. Cuando un método transaccional (p.ej.
    // FacturaModel::emitir) invoca a otro (SalidaModel::confirmar), ambos llaman
    // beginTransaction sobre la misma conexión. Usamos un contador ESTÁTICO
    // (compartido entre todas las instancias) para abrir/cerrar la transacción
    // real solo en el nivel más externo. Si una operación interna hace rollback,
    // se marca toda la transacción como "rollback-only" y el commit externo la
    // revierte, evitando confirmaciones parciales.
    private static int  $txLevel        = 0;
    private static bool $txRollbackOnly = false;

    protected function beginTransaction(): void
    {
        if (self::$txLevel === 0) {
            $this->db->beginTransaction();
            self::$txRollbackOnly = false;
        }
        self::$txLevel++;
    }

    protected function commit(): void
    {
        if (self::$txLevel === 0) {
            return;
        }
        self::$txLevel--;
        if (self::$txLevel === 0) {
            if (self::$txRollbackOnly) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                self::$txRollbackOnly = false;
                throw new RuntimeException('Transacción revertida: una operación interna falló.');
            }
            $this->db->commit();
        }
    }

    protected function rollback(): void
    {
        if (self::$txLevel === 0) {
            return;
        }
        self::$txLevel--;
        if (self::$txLevel === 0) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            self::$txRollbackOnly = false;
        } else {
            // Nivel interno: aplazar el rollback real al nivel externo.
            self::$txRollbackOnly = true;
        }
    }

    // Genera el próximo folio atómico para un tipo de movimiento
    // Formato: ENT-2025-00001
    public function generarFolio(string $tipo): string
    {
        $prefijos = [
            MOV_ENTRADA          => 'ENT',
            MOV_SALIDA           => 'SAL',
            MOV_TRASPASO_SALIDA  => 'TRP',
            MOV_TRASPASO_ENTRADA => 'TRP',
            MOV_AJUSTE           => 'AJU',
        ];
        $prefijo = $prefijos[$tipo] ?? 'MOV';
        $anno    = date('Y');

        // Los traspasos (salida y entrada) comparten el prefijo TRP, por lo que
        // deben contarse juntos: de lo contrario el primer traspaso_salida y el
        // primer traspaso_entrada generarían ambos TRP-AAAA-00001 y colisionarían
        // en el índice UNIQUE de folio (impidiendo confirmar la recepción).
        if ($tipo === MOV_TRASPASO_SALIDA || $tipo === MOV_TRASPASO_ENTRADA) {
            $tiposFolio = [MOV_TRASPASO_SALIDA, MOV_TRASPASO_ENTRADA];
        } else {
            $tiposFolio = [$tipo];
        }
        $placeholders = [];
        $params       = [':anno' => $anno];
        foreach ($tiposFolio as $i => $t) {
            $placeholders[]      = ":tipo{$i}";
            $params[":tipo{$i}"] = $t;
        }
        $inSql = implode(',', $placeholders);

        // El campo folio tiene índice UNIQUE: si dos transacciones concurrentes
        // generan el mismo número, el segundo INSERT falla y hace rollback.
        // NO usar LOCK TABLES aquí: provoca commit implícito y rompe la transacción
        // activa de entradas/salidas/traspasos. La serialización fina se maneja con
        // GET_LOCK() advisory en los modelos que lo requieren.
        $count = (int) $this->fetchColumn(
            "SELECT COUNT(*) FROM movimientos
             WHERE tipo IN ({$inSql}) AND YEAR(created_at) = :anno",
            $params
        );

        return sprintf('%s-%s-%05d', $prefijo, $anno, $count + 1);
    }

    // Paginación simple
    public function paginar(string $sql, array $params, int $pagina, int $porPagina = 20): array
    {
        $total = (int) $this->fetchColumn(
            "SELECT COUNT(*) FROM ({$sql}) AS _t",
            $params
        );
        $offset = ($pagina - 1) * $porPagina;
        $filas  = $this->fetchAll(
            "{$sql} LIMIT {$porPagina} OFFSET {$offset}",
            $params
        );
        return [
            'filas'       => $filas,
            'total'       => $total,
            'pagina'      => $pagina,
            'por_pagina'  => $porPagina,
            'total_paginas' => max(1, (int) ceil($total / $porPagina)),
        ];
    }
}
