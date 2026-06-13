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

    protected function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    protected function commit(): void
    {
        $this->db->commit();
    }

    protected function rollback(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
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

        $count = (int) $this->fetchColumn(
            "SELECT COUNT(*) FROM movimientos
             WHERE tipo = :tipo AND YEAR(created_at) = :anno",
            [':tipo' => $tipo, ':anno' => $anno]
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
