<?php
require_once BASE_PATH . '/core/Model.php';

class CategoriaModel extends Model
{
    public function listar(): array
    {
        return $this->fetchAll(
            'SELECT id, nombre, descripcion, activa
               FROM categorias
              WHERE activa = 1
           ORDER BY nombre ASC'
        );
    }

    public function listarTodas(): array
    {
        return $this->fetchAll(
            'SELECT id, nombre, descripcion, activa
               FROM categorias
           ORDER BY nombre ASC'
        );
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, nombre, descripcion, activa
               FROM categorias
              WHERE id = :id',
            [':id' => $id]
        );
    }

    public function crear(array $datos): int
    {
        $this->execute(
            'INSERT INTO categorias (nombre, descripcion, activa)
             VALUES (:nombre, :descripcion, 1)',
            [
                ':nombre'      => $datos['nombre'],
                ':descripcion' => $datos['descripcion'] ?: null,
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $datos): void
    {
        $this->execute(
            'UPDATE categorias
                SET nombre      = :nombre,
                    descripcion = :descripcion
              WHERE id = :id',
            [
                ':nombre'      => $datos['nombre'],
                ':descripcion' => $datos['descripcion'] ?: null,
                ':id'          => $id,
            ]
        );
    }

    public function eliminar(int $id): bool
    {
        $tieneProductos = (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM productos WHERE categoria_id = :id',
            [':id' => $id]
        );
        if ($tieneProductos > 0) {
            return false;
        }
        $this->execute(
            'UPDATE categorias SET activa = 0 WHERE id = :id',
            [':id' => $id]
        );
        return true;
    }
}
