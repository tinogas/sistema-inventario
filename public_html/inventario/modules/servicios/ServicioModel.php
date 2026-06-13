<?php
require_once BASE_PATH . '/core/Model.php';

class ServicioModel extends Model
{
    public function listar(): array
    {
        return $this->fetchAll(
            'SELECT id, nombre, descripcion, precio, activo
             FROM servicios
             WHERE activo = 1
             ORDER BY nombre'
        );
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, nombre, descripcion, precio, activo
             FROM servicios
             WHERE id = :id',
            [':id' => $id]
        );
    }

    public function crear(array $datos): int
    {
        $this->execute(
            'INSERT INTO servicios (nombre, descripcion, precio)
             VALUES (:nombre, :descripcion, :precio)',
            [
                ':nombre'      => $datos['nombre'],
                ':descripcion' => $datos['descripcion'] ?: null,
                ':precio'      => (float) $datos['precio'],
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $datos): void
    {
        $this->execute(
            'UPDATE servicios
             SET nombre = :nombre, descripcion = :descripcion, precio = :precio
             WHERE id = :id',
            [
                ':nombre'      => $datos['nombre'],
                ':descripcion' => $datos['descripcion'] ?: null,
                ':precio'      => (float) $datos['precio'],
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
            'UPDATE servicios SET activo = 0 WHERE id = :id',
            [':id' => $id]
        );
    }

    /**
     * Devuelve los productos asociados al servicio con su cantidad.
     */
    public function getProductosAsociados(int $servicio_id): array
    {
        return $this->fetchAll(
            'SELECT sp.producto_id, sp.cantidad,
                    p.nombre AS producto_nombre, p.codigo AS producto_codigo
             FROM servicios_productos sp
             INNER JOIN productos p ON p.id = sp.producto_id
             WHERE sp.servicio_id = :sid
             ORDER BY p.nombre',
            [':sid' => $servicio_id]
        );
    }

    /**
     * Reemplaza la lista completa de productos del servicio.
     * $items = [ ['producto_id' => N, 'cantidad' => X], ... ]
     */
    public function actualizarProductos(int $servicio_id, array $items): void
    {
        $this->beginTransaction();
        try {
            // Eliminar todos los productos actuales del servicio
            $this->execute(
                'DELETE FROM servicios_productos WHERE servicio_id = :sid',
                [':sid' => $servicio_id]
            );

            // Re-insertar solo los que vienen en $items y tienen cantidad > 0
            if (!empty($items)) {
                $stmt = $this->db->prepare(
                    'INSERT INTO servicios_productos (servicio_id, producto_id, cantidad)
                     VALUES (:sid, :pid, :cantidad)
                     ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad)'
                );
                foreach ($items as $item) {
                    $pid      = (int) ($item['producto_id'] ?? 0);
                    $cantidad = (float) ($item['cantidad'] ?? 0);
                    if ($pid <= 0 || $cantidad <= 0) {
                        continue;
                    }
                    $stmt->execute([
                        ':sid'      => $servicio_id,
                        ':pid'      => $pid,
                        ':cantidad' => $cantidad,
                    ]);
                }
            }
            $this->commit();
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Busca productos activos por nombre o código para el selector AJAX.
     */
    public function buscarProductos(string $q): array
    {
        $like = '%' . $q . '%';
        return $this->fetchAll(
            'SELECT id, codigo, nombre
             FROM productos
             WHERE activo = 1 AND (nombre LIKE :q OR codigo LIKE :q2)
             ORDER BY nombre
             LIMIT 30',
            [':q' => $like, ':q2' => $like]
        );
    }
}
