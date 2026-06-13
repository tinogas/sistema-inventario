<?php
require_once BASE_PATH . '/core/Model.php';

class MecanicoModel extends Model
{
    /**
     * Lista mecánicos activos. Si se pasa sucursal_id filtra por ella.
     */
    public function listar(?int $sucursal_id = null): array
    {
        $sql = 'SELECT m.id, m.nombre, m.telefono, m.activo,
                       s.nombre AS sucursal_nombre, s.id AS sucursal_id
                FROM mecanicos m
                INNER JOIN sucursales s ON s.id = m.sucursal_id
                WHERE m.activo = 1';

        $params = [];
        if ($sucursal_id !== null) {
            $sql .= ' AND m.sucursal_id = :sucursal_id';
            $params[':sucursal_id'] = $sucursal_id;
        }
        $sql .= ' ORDER BY s.nombre, m.nombre';

        return $this->fetchAll($sql, $params);
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT m.*, s.nombre AS sucursal_nombre
             FROM mecanicos m
             INNER JOIN sucursales s ON s.id = m.sucursal_id
             WHERE m.id = :id',
            [':id' => $id]
        );
    }

    public function crear(array $datos): int
    {
        $this->execute(
            'INSERT INTO mecanicos (nombre, sucursal_id, telefono)
             VALUES (:nombre, :sucursal_id, :telefono)',
            [
                ':nombre'      => $datos['nombre'],
                ':sucursal_id' => (int) $datos['sucursal_id'],
                ':telefono'    => $datos['telefono'] ?: null,
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $datos): void
    {
        $this->execute(
            'UPDATE mecanicos
             SET nombre = :nombre, sucursal_id = :sucursal_id, telefono = :telefono
             WHERE id = :id',
            [
                ':nombre'      => $datos['nombre'],
                ':sucursal_id' => (int) $datos['sucursal_id'],
                ':telefono'    => $datos['telefono'] ?: null,
                ':id'          => $id,
            ]
        );
    }

    /**
     * Baja lógica.
     */
    public function eliminar(int $id): void
    {
        $this->execute(
            'UPDATE mecanicos SET activo = 0 WHERE id = :id',
            [':id' => $id]
        );
    }

    /**
     * Devuelve todas las sucursales activas para el select del formulario.
     */
    public function getSucursales(): array
    {
        return $this->fetchAll(
            'SELECT id, nombre FROM sucursales WHERE activa = 1 ORDER BY nombre'
        );
    }
}
