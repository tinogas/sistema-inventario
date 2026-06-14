<?php
require_once BASE_PATH . '/core/Model.php';

class EntradaModel extends Model
{
    public function listar(?int $sucursal_id, string $buscar, int $pagina): array
    {
        $where  = "WHERE m.tipo = 'entrada'";
        $params = [];

        if ($sucursal_id) {
            $where .= ' AND m.sucursal_id = :sid';
            $params[':sid'] = $sucursal_id;
        }
        if ($buscar !== '') {
            $where .= " AND (m.folio LIKE :q OR p.razon_social LIKE :q2 OR m.referencia_factura LIKE :q3)";
            $like = "%{$buscar}%";
            $params[':q']  = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        $sql = "SELECT m.id, m.folio, m.estado, m.created_at, m.referencia_factura,
                       su.nombre AS sucursal,
                       u.nombre  AS usuario,
                       COALESCE(p.razon_social,'—') AS proveedor,
                       (SELECT COUNT(*) FROM movimientos_detalle WHERE movimiento_id = m.id) AS num_partidas
                FROM movimientos m
                INNER JOIN sucursales su ON su.id = m.sucursal_id
                INNER JOIN usuarios u    ON u.id  = m.usuario_id
                LEFT  JOIN proveedores p ON p.id  = m.proveedor_id
                {$where}
                ORDER BY m.created_at DESC";

        return $this->paginar($sql, $params, $pagina);
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT m.*, su.nombre AS sucursal_nombre, u.nombre AS usuario_nombre,
                    COALESCE(p.razon_social,'—') AS proveedor_nombre
             FROM movimientos m
             INNER JOIN sucursales su ON su.id = m.sucursal_id
             INNER JOIN usuarios u    ON u.id  = m.usuario_id
             LEFT  JOIN proveedores p ON p.id  = m.proveedor_id
             WHERE m.id = :id AND m.tipo = 'entrada'",
            [':id' => $id]
        );
    }

    public function getDetalle(int $movimiento_id): array
    {
        return $this->fetchAll(
            "SELECT d.*, p.codigo, p.nombre AS producto_nombre,
                    COALESCE(u.clave,'PZA') AS unidad
             FROM movimientos_detalle d
             INNER JOIN productos p ON p.id = d.producto_id
             LEFT  JOIN unidades  u ON u.id = p.unidad_id
             WHERE d.movimiento_id = :mid",
            [':mid' => $movimiento_id]
        );
    }

    /**
     * Crea la entrada y actualiza stock en una transacción atómica.
     * $datos = [sucursal_id, proveedor_id, referencia_factura, uuid_cfdi, notas, usuario_id]
     * $partidas = [[producto_id, cantidad, precio_unitario], ...]
     */
    public function confirmar(array $datos, array $partidas): int
    {
        if (empty($partidas)) {
            throw new RuntimeException('La entrada debe tener al menos una partida.');
        }

        $this->beginTransaction();
        try {
            // Generar folio dentro de la transacción para reducir la ventana de race condition
            $this->execute('SELECT GET_LOCK(:lk, 5)', [':lk' => 'folio_entrada']);
            $folio = $this->generarFolio(MOV_ENTRADA);

            // Insertar cabecera
            $this->execute(
                "INSERT INTO movimientos
                    (tipo, folio, sucursal_id, proveedor_id, referencia_factura, uuid_cfdi, notas, estado, usuario_id)
                 VALUES
                    ('entrada', :folio, :sid, :prov, :ref, :uuid, :notas, 'confirmado', :uid)",
                [
                    ':folio' => $folio,
                    ':sid'   => $datos['sucursal_id'],
                    ':prov'  => $datos['proveedor_id'] ?: null,
                    ':ref'   => $datos['referencia_factura'] ?: null,
                    ':uuid'  => $datos['uuid_cfdi'] ?: null,
                    ':notas' => $datos['notas'] ?: null,
                    ':uid'   => $datos['usuario_id'],
                ]
            );
            $movId = $this->lastInsertId();

            foreach ($partidas as $p) {
                // Insertar detalle
                $this->execute(
                    "INSERT INTO movimientos_detalle (movimiento_id, producto_id, cantidad, precio_unitario)
                     VALUES (:mid, :pid, :qty, :precio)",
                    [
                        ':mid'    => $movId,
                        ':pid'    => $p['producto_id'],
                        ':qty'    => $p['cantidad'],
                        ':precio' => $p['precio_unitario'],
                    ]
                );

                // Actualizar stock (INSERT o UPDATE)
                $this->execute(
                    "INSERT INTO stock_sucursal (producto_id, sucursal_id, cantidad)
                     VALUES (:pid, :sid, :qty)
                     ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)",
                    [
                        ':pid' => $p['producto_id'],
                        ':sid' => $datos['sucursal_id'],
                        ':qty' => $p['cantidad'],
                    ]
                );
            }

            $this->execute('SELECT RELEASE_LOCK(:lk)', [':lk' => 'folio_entrada']);
            $this->commit();
            return $movId;

        } catch (Exception $e) {
            $this->execute('SELECT RELEASE_LOCK(:lk)', [':lk' => 'folio_entrada']);
            $this->rollback();
            throw $e;
        }
    }

    public function cancelar(int $id): void
    {
        $mov = $this->getById($id);
        if (!$mov) throw new RuntimeException('Entrada no encontrada.');
        if ($mov['estado'] === 'cancelado') throw new RuntimeException('Ya está cancelada.');
        if ($mov['estado'] !== 'confirmado') throw new RuntimeException('Solo se pueden cancelar entradas en estado confirmado.');

        $partidas = $this->getDetalle($id);

        $this->beginTransaction();
        try {
            $this->execute(
                "UPDATE movimientos SET estado = 'cancelado' WHERE id = :id",
                [':id' => $id]
            );

            // Revertir stock
            foreach ($partidas as $p) {
                $afectadas = $this->execute(
                    "UPDATE stock_sucursal SET cantidad = cantidad - :qty
                     WHERE producto_id = :pid AND sucursal_id = :sid AND cantidad >= :qty2",
                    [':qty' => $p['cantidad'], ':qty2' => $p['cantidad'], ':pid' => $p['producto_id'], ':sid' => $mov['sucursal_id']]
                );
                if ($afectadas === 0) {
                    throw new RuntimeException(
                        'Stock insuficiente para revertir el producto ID ' . $p['producto_id'] . '. Cancele manualmente con ajuste de inventario.'
                    );
                }
            }

            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
