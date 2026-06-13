<?php
require_once BASE_PATH . '/core/Model.php';

class SucursalModel extends Model
{
    public function listar(): array
    {
        return $this->fetchAll(
            'SELECT * FROM sucursales ORDER BY id ASC'
        );
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM sucursales WHERE id = :id',
            [':id' => $id]
        );
    }

    public function crear(array $d): int
    {
        $this->execute(
            'INSERT INTO sucursales (nombre, ciudad, direccion, telefono, activa)
             VALUES (:nombre, :ciudad, :dir, :tel, 1)',
            [':nombre' => $d['nombre'], ':ciudad' => $d['ciudad'],
             ':dir' => $d['direccion'] ?: null, ':tel' => $d['telefono'] ?: null]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $d): void
    {
        $this->execute(
            'UPDATE sucursales SET nombre=:nombre, ciudad=:ciudad,
             direccion=:dir, telefono=:tel, activa=:activa WHERE id=:id',
            [':nombre' => $d['nombre'], ':ciudad' => $d['ciudad'],
             ':dir' => $d['direccion'] ?: null, ':tel' => $d['telefono'] ?: null,
             ':activa' => $d['activa'] ? 1 : 0, ':id' => $id]
        );
    }
}
