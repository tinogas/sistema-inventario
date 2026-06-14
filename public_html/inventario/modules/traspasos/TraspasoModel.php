<?php
require_once BASE_PATH . '/core/Model.php';

class TraspasoModel extends Model
{
    public function listar(?int $sucursal_id, string $buscar, int $pagina): array
    {
        $where  = "WHERE (m.tipo = 'traspaso_salida')";
        $params = [];

        if ($sucursal_id) {
            $where .= ' AND (m.sucursal_id = :sid OR m.sucursal_dest_id = :sid2)';
            $params[':sid']  = $sucursal_id;
            $params[':sid2'] = $sucursal_id;
        }
        if ($buscar !== '') {
            $where .= ' AND (m.folio LIKE :q)';
            $params[':q'] = "%{$buscar}%";
        }

        $sql = "SELECT t.id AS traspaso_id, t.estado AS traspaso_estado, t.fecha_envio, t.fecha_recepcion,
                       m.folio AS folio_salida, su_o.nombre AS sucursal_origen, su_d.nombre AS sucursal_destino,
                       u.nombre AS usuario,
                       (SELECT COUNT(*) FROM movimientos_detalle WHERE movimiento_id = m.id) AS num_partidas
                FROM traspasos t
                INNER JOIN movimientos m ON m.id = t.movimiento_salida_id
                INNER JOIN sucursales su_o ON su_o.id = m.sucursal_id
                INNER JOIN sucursales su_d ON su_d.id = m.sucursal_dest_id
                INNER JOIN usuarios u      ON u.id    = m.usuario_id
                {$where}
                ORDER BY t.fecha_envio DESC";

        return $this->paginar($sql, $params, $pagina);
    }

    public function getById(int $traspaso_id): ?array
    {
        return $this->fetchOne(
            "SELECT t.*, t.estado AS traspaso_estado, m.folio AS folio_salida, m.notas, m.sucursal_id, m.sucursal_dest_id,
                    su_o.nombre AS sucursal_origen, su_d.nombre AS sucursal_destino,
                    u.nombre AS usuario
             FROM traspasos t
             INNER JOIN movimientos m ON m.id = t.movimiento_salida_id
             INNER JOIN sucursales su_o ON su_o.id = m.sucursal_id
             INNER JOIN sucursales su_d ON su_d.id = m.sucursal_dest_id
             INNER JOIN usuarios u      ON u.id    = m.usuario_id
             WHERE t.id = :id",
            [':id' => $traspaso_id]
        );
    }

    public function getPartidas(int $movimiento_id): array
    {
        return $this->fetchAll(
            "SELECT d.*, p.codigo, p.nombre AS producto_nombre, COALESCE(u.clave,'PZA') AS unidad
             FROM movimientos_detalle d
             INNER JOIN productos p ON p.id = d.producto_id
             LEFT  JOIN unidades  u ON u.id = p.unidad_id
             WHERE d.movimiento_id = :mid",
            [':mid' => $movimiento_id]
        );
    }

    /**
     * Crea el traspaso: descuenta stock en origen, estado "en_transito".
     */
    public function crear(array $datos, array $partidas): int
    {
        if (empty($partidas)) {
            throw new RuntimeException('El traspaso debe tener al menos una partida.');
        }
        if ($datos['sucursal_origen_id'] === $datos['sucursal_dest_id']) {
            throw new RuntimeException('La sucursal origen y destino no pueden ser la misma.');
        }

        $this->beginTransaction();
        try {
            // Verificar stock en origen dentro de la TX (FOR UPDATE bloquea la fila contra concurrencia)
            foreach ($partidas as $p) {
                $stock = (float) $this->fetchColumn(
                    'SELECT COALESCE(cantidad,0) FROM stock_sucursal WHERE producto_id=:pid AND sucursal_id=:sid FOR UPDATE',
                    [':pid' => $p['producto_id'], ':sid' => $datos['sucursal_origen_id']]
                );
                if ($stock < $p['cantidad']) {
                    $nombre = $this->fetchColumn('SELECT nombre FROM productos WHERE id=:pid', [':pid' => $p['producto_id']]);
                    throw new RuntimeException("Stock insuficiente para \"{$nombre}\" en la sucursal origen.");
                }
            }

            $folio = $this->generarFolio(MOV_TRASPASO_SALIDA);

            $this->execute(
                "INSERT INTO movimientos
                    (tipo, folio, sucursal_id, sucursal_dest_id, notas, estado, usuario_id)
                 VALUES ('traspaso_salida', :folio, :soid, :sdid, :notas, 'confirmado', :uid)",
                [
                    ':folio' => $folio,
                    ':soid'  => $datos['sucursal_origen_id'],
                    ':sdid'  => $datos['sucursal_dest_id'],
                    ':notas' => $datos['notas'] ?: null,
                    ':uid'   => $datos['usuario_id'],
                ]
            );
            $movSalidaId = $this->lastInsertId();

            foreach ($partidas as $p) {
                $this->execute(
                    "INSERT INTO movimientos_detalle (movimiento_id, producto_id, cantidad, precio_unitario)
                     VALUES (:mid, :pid, :qty, 0)",
                    [':mid' => $movSalidaId, ':pid' => $p['producto_id'], ':qty' => $p['cantidad']]
                );
                // Descontar en origen
                $this->execute(
                    "UPDATE stock_sucursal SET cantidad = cantidad - :qty
                     WHERE producto_id = :pid AND sucursal_id = :sid",
                    [':qty' => $p['cantidad'], ':pid' => $p['producto_id'], ':sid' => $datos['sucursal_origen_id']]
                );
            }

            // Registrar traspaso
            $this->execute(
                "INSERT INTO traspasos (movimiento_salida_id, estado) VALUES (:msid, 'en_transito')",
                [':msid' => $movSalidaId]
            );
            $traspasoId = $this->lastInsertId();

            $this->commit();
            return $traspasoId;

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Sucursal destino confirma la recepción.
     */
    public function confirmarRecepcion(int $traspaso_id, array $cantidadesRecibidas, int $usuario_id): void
    {
        $this->beginTransaction();
        try {
        // Re-leer con FOR UPDATE dentro de la TX para evitar doble confirmación concurrente
        $traspaso = $this->fetchOne(
            'SELECT t.*, t.estado AS traspaso_estado, m.sucursal_id, m.sucursal_dest_id, m.id AS movimiento_salida_id_lock
             FROM traspasos t
             INNER JOIN movimientos m ON m.id = t.movimiento_salida_id
             WHERE t.id = :id FOR UPDATE',
            [':id' => $traspaso_id]
        );
        if (!$traspaso) throw new RuntimeException('Traspaso no encontrado.');
        if ($traspaso['traspaso_estado'] !== 'en_transito') throw new RuntimeException('El traspaso no está en tránsito.');

        $partidas = $this->getPartidas($traspaso['movimiento_salida_id']);
            // Movimiento de entrada en destino
            $folioEntrada = $this->generarFolio(MOV_TRASPASO_ENTRADA);
            $this->execute(
                "INSERT INTO movimientos
                    (tipo, folio, sucursal_id, notas, estado, usuario_id)
                 VALUES ('traspaso_entrada', :folio, :sid, 'Recepción de traspaso', 'confirmado', :uid)",
                [':folio' => $folioEntrada, ':sid' => $traspaso['sucursal_dest_id'], ':uid' => $usuario_id]
            );
            $movEntradaId = $this->lastInsertId();

            foreach ($partidas as $p) {
                $cantRecibida = (float) ($cantidadesRecibidas[$p['producto_id']] ?? $p['cantidad']);
                if ($cantRecibida <= 0) continue;

                $this->execute(
                    "INSERT INTO movimientos_detalle (movimiento_id, producto_id, cantidad, precio_unitario)
                     VALUES (:mid, :pid, :qty, 0)",
                    [':mid' => $movEntradaId, ':pid' => $p['producto_id'], ':qty' => $cantRecibida]
                );
                // Acreditar en destino
                $this->execute(
                    "INSERT INTO stock_sucursal (producto_id, sucursal_id, cantidad)
                     VALUES (:pid, :sid, :qty)
                     ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)",
                    [':pid' => $p['producto_id'], ':sid' => $traspaso['sucursal_dest_id'], ':qty' => $cantRecibida]
                );
            }

            // Actualizar traspaso
            $this->execute(
                "UPDATE traspasos SET estado='recibido', movimiento_entrada_id=:meid, fecha_recepcion=NOW()
                 WHERE id=:id",
                [':meid' => $movEntradaId, ':id' => $traspaso_id]
            );

            $this->commit();

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function cancelar(int $traspaso_id): void
    {
        $this->beginTransaction();
        try {
        // Re-leer con FOR UPDATE dentro de la TX para serializar contra confirmación concurrente
        $traspaso = $this->fetchOne(
            'SELECT t.*, t.estado AS traspaso_estado, m.sucursal_id, m.sucursal_dest_id, m.id AS movimiento_salida_id_lock
             FROM traspasos t
             INNER JOIN movimientos m ON m.id = t.movimiento_salida_id
             WHERE t.id = :id FOR UPDATE',
            [':id' => $traspaso_id]
        );
        if (!$traspaso) throw new RuntimeException('Traspaso no encontrado.');
        if ($traspaso['traspaso_estado'] !== 'en_transito') throw new RuntimeException('Solo se pueden cancelar traspasos en tránsito.');

        $partidas = $this->getPartidas($traspaso['movimiento_salida_id']);
            // Revertir stock en origen
            foreach ($partidas as $p) {
                $this->execute(
                    "UPDATE stock_sucursal SET cantidad = cantidad + :qty
                     WHERE producto_id = :pid AND sucursal_id = :sid",
                    [':qty' => $p['cantidad'], ':pid' => $p['producto_id'], ':sid' => $traspaso['sucursal_id']]
                );
            }
            $this->execute(
                "UPDATE traspasos SET estado='cancelado' WHERE id=:id",
                [':id' => $traspaso_id]
            );
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
