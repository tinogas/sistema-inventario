<?php
require_once BASE_PATH . '/core/Model.php';

class SalidaModel extends Model
{
    public function listar(?int $sucursal_id, string $buscar, int $pagina): array
    {
        $where  = "WHERE m.tipo = 'salida'";
        $params = [];

        if ($sucursal_id) {
            $where .= ' AND m.sucursal_id = :sid';
            $params[':sid'] = $sucursal_id;
        }
        if ($buscar !== '') {
            $where .= " AND (m.folio LIKE :q OR m.referencia_factura LIKE :q2 OR mec.nombre LIKE :q3)";
            $like = "%{$buscar}%";
            $params[':q']  = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        $sql = "SELECT m.id, m.folio, m.estado, m.created_at, m.referencia_factura,
                       su.nombre AS sucursal, u.nombre AS usuario,
                       COALESCE(mec.nombre,'—') AS mecanico,
                       COALESCE(ser.nombre,'—') AS servicio,
                       (SELECT COUNT(*) FROM movimientos_detalle WHERE movimiento_id = m.id) AS num_partidas
                FROM movimientos m
                INNER JOIN sucursales su ON su.id = m.sucursal_id
                INNER JOIN usuarios u    ON u.id  = m.usuario_id
                LEFT  JOIN mecanicos mec ON mec.id = m.mecanico_id
                LEFT  JOIN servicios ser ON ser.id = m.servicio_id
                {$where}
                ORDER BY m.created_at DESC";

        return $this->paginar($sql, $params, $pagina);
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT m.*, su.nombre AS sucursal_nombre, u.nombre AS usuario_nombre,
                    COALESCE(mec.nombre,'—') AS mecanico_nombre,
                    COALESCE(ser.nombre,'—') AS servicio_nombre
             FROM movimientos m
             INNER JOIN sucursales su ON su.id = m.sucursal_id
             INNER JOIN usuarios u    ON u.id  = m.usuario_id
             LEFT  JOIN mecanicos mec ON mec.id = m.mecanico_id
             LEFT  JOIN servicios ser ON ser.id = m.servicio_id
             WHERE m.id = :id AND m.tipo = 'salida'",
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
     * Verifica si hay stock suficiente para cada partida.
     * Devuelve array de problemas; vacío si todo OK.
     */
    public function verificarStock(int $sucursal_id, array $partidas): array
    {
        $problemas = [];
        foreach ($partidas as $p) {
            $stock = (float) $this->fetchColumn(
                "SELECT COALESCE(cantidad, 0) FROM stock_sucursal
                 WHERE producto_id = :pid AND sucursal_id = :sid",
                [':pid' => $p['producto_id'], ':sid' => $sucursal_id]
            );
            if ($stock < $p['cantidad']) {
                $nombre = $this->fetchColumn(
                    'SELECT nombre FROM productos WHERE id = :pid',
                    [':pid' => $p['producto_id']]
                );
                $problemas[] = "Stock insuficiente para \"{$nombre}\": disponible {$stock}, requerido {$p['cantidad']}.";
            }
        }
        return $problemas;
    }

    /**
     * Confirma la salida. Si $forzar=true descuenta aunque el stock quede negativo.
     */
    public function confirmar(array $datos, array $partidas, bool $forzar = false): int
    {
        if (empty($partidas)) {
            throw new RuntimeException('La salida debe tener al menos una partida.');
        }

        if (!$forzar) {
            $problemas = $this->verificarStock($datos['sucursal_id'], $partidas);
            if ($problemas) {
                throw new RuntimeException(implode(' | ', $problemas));
            }
        }

        $this->beginTransaction();
        try {
            $folio = $this->generarFolio(MOV_SALIDA);

            $this->execute(
                "INSERT INTO movimientos
                    (tipo, folio, sucursal_id, mecanico_id, servicio_id, referencia_factura, notas, estado, usuario_id)
                 VALUES
                    ('salida', :folio, :sid, :mec, :ser, :ref, :notas, 'confirmado', :uid)",
                [
                    ':folio' => $folio,
                    ':sid'   => $datos['sucursal_id'],
                    ':mec'   => $datos['mecanico_id']        ?: null,
                    ':ser'   => $datos['servicio_id']        ?: null,
                    ':ref'   => $datos['referencia_factura'] ?: null,
                    ':notas' => $datos['notas']              ?: null,
                    ':uid'   => $datos['usuario_id'],
                ]
            );
            $movId = $this->lastInsertId();

            foreach ($partidas as $p) {
                $this->execute(
                    "INSERT INTO movimientos_detalle (movimiento_id, producto_id, cantidad, precio_unitario)
                     VALUES (:mid, :pid, :qty, :precio)",
                    [':mid'=>$movId, ':pid'=>$p['producto_id'], ':qty'=>$p['cantidad'], ':precio'=>$p['precio_unitario']]
                );

                // Descontar stock (puede quedar negativo si forzar=true)
                $this->execute(
                    "INSERT INTO stock_sucursal (producto_id, sucursal_id, cantidad)
                     VALUES (:pid, :sid, :neg)
                     ON DUPLICATE KEY UPDATE cantidad = cantidad - :neg2",
                    [
                        ':pid'  => $p['producto_id'],
                        ':sid'  => $datos['sucursal_id'],
                        ':neg'  => $p['cantidad'],
                        ':neg2' => $p['cantidad'],
                    ]
                );
            }

            $this->commit();
            return $movId;

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
