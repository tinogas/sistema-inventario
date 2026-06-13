<?php
require_once BASE_PATH . '/core/Model.php';

class UnidadModel extends Model
{
    public function listar(): array
    {
        return $this->fetchAll(
            'SELECT id, clave, nombre
               FROM unidades
           ORDER BY nombre ASC'
        );
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, clave, nombre
               FROM unidades
              WHERE id = :id',
            [':id' => $id]
        );
    }

    public function crear(array $datos): int
    {
        $this->execute(
            'INSERT INTO unidades (clave, nombre)
             VALUES (:clave, :nombre)',
            [
                ':clave'  => strtoupper($datos['clave']),
                ':nombre' => $datos['nombre'],
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $datos): void
    {
        $this->execute(
            'UPDATE unidades
                SET clave  = :clave,
                    nombre = :nombre
              WHERE id = :id',
            [
                ':clave'  => strtoupper($datos['clave']),
                ':nombre' => $datos['nombre'],
                ':id'     => $id,
            ]
        );
    }

    public function eliminar(int $id): bool
    {
        $tieneProductos = (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM productos WHERE unidad_id = :id',
            [':id' => $id]
        );
        if ($tieneProductos > 0) {
            return false;
        }
        $this->execute(
            'DELETE FROM unidades WHERE id = :id',
            [':id' => $id]
        );
        return true;
    }
}
