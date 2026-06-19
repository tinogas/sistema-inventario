<?php
require_once BASE_PATH . '/core/Model.php';

class FacturaModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        // Migración: agrega descuento_pct si no existe (compatible con MySQL y MariaDB)
        try {
            $this->db->exec(
                "ALTER TABLE facturas ADD COLUMN descuento_pct DECIMAL(5,2) NOT NULL DEFAULT 0.00"
            );
        } catch (PDOException $e) {
            // Columna ya existe — ignorar
        }
    }

    // ---- Listar ----

    public function listar(?int $sucursal_id, string $estado, string $buscar, int $pagina): array
    {
        $where  = 'WHERE 1=1';
        $params = [];

        if ($sucursal_id) {
            $where .= ' AND f.sucursal_id = :sid';
            $params[':sid'] = $sucursal_id;
        }
        if ($estado) {
            $where .= ' AND f.estado = :estado';
            $params[':estado'] = $estado;
        }
        if ($buscar !== '') {
            $where .= ' AND (f.folio LIKE :q OR f.cliente_nombre LIKE :q2 OR f.vh_placas LIKE :q3)';
            $like = "%{$buscar}%";
            $params[':q'] = $like; $params[':q2'] = $like; $params[':q3'] = $like;
        }

        $sql = "SELECT f.id, f.folio, f.estado, f.cliente_nombre, f.cliente_tel,
                       f.vh_marca, f.vh_modelo, f.vh_anio, f.vh_placas,
                       f.total, f.created_at, f.fecha_emision,
                       su.nombre AS sucursal,
                       COALESCE(m.nombre,'—') AS mecanico,
                       u.nombre AS usuario
                FROM facturas f
                INNER JOIN sucursales su ON su.id = f.sucursal_id
                INNER JOIN usuarios   u  ON u.id  = f.usuario_id
                LEFT  JOIN mecanicos  m  ON m.id  = f.mecanico_id
                {$where}
                ORDER BY f.created_at DESC";

        return $this->paginar($sql, $params, $pagina, 25);
    }

    // ---- Obtener una factura completa ----

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT f.*, su.nombre AS sucursal_nombre, su.ciudad, su.direccion AS sucursal_dir,
                    su.telefono AS sucursal_tel,
                    u.nombre  AS usuario_nombre,
                    COALESCE(m.nombre,'—') AS mecanico_nombre,
                    COALESCE(s.nombre,'—') AS servicio_nombre
             FROM facturas f
             INNER JOIN sucursales su ON su.id = f.sucursal_id
             INNER JOIN usuarios   u  ON u.id  = f.usuario_id
             LEFT  JOIN mecanicos  m  ON m.id  = f.mecanico_id
             LEFT  JOIN servicios  s  ON s.id  = f.servicio_id
             WHERE f.id = :id",
            [':id' => $id]
        );
    }

    public function getDetalle(int $factura_id): array
    {
        return $this->fetchAll(
            "SELECT d.*, p.codigo, p.nombre AS producto_nombre,
                    COALESCE(u.clave,'PZA') AS unidad,
                    (d.cantidad * d.precio_unitario) AS importe
             FROM facturas_detalle d
             INNER JOIN productos p ON p.id = d.producto_id
             LEFT  JOIN unidades  u ON u.id = p.unidad_id
             WHERE d.factura_id = :fid
             ORDER BY d.id ASC",
            [':fid' => $factura_id]
        );
    }

    // ---- Guardar borrador ----

    public function guardar(array $cab, array $partidas, ?int $id = null): int
    {
        $this->beginTransaction();
        try {
            $descuento_pct = max(0, min(100, (float)($cab['descuento_pct'] ?? 0)));
            $cliente_id    = !empty($cab['cliente_id']) ? (int)$cab['cliente_id'] : null;
            $unidad_id     = !empty($cab['unidad_id'])  ? (int)$cab['unidad_id']  : null;

            // Si hay unidad, auto-rellenar datos de vehículo desde el catálogo
            if ($unidad_id) {
                $u = $this->fetchOne(
                    'SELECT cu.marca, cu.modelo, cu.anio, cu.placas,
                            c.nombre AS cli_nombre, c.telefono AS cli_tel
                       FROM clientes_unidades cu
                       INNER JOIN clientes c ON c.id = cu.cliente_id
                      WHERE cu.id = :id',
                    [':id' => $unidad_id]
                );
                if ($u) {
                    $cab['vh_marca']        = $u['marca'];
                    $cab['vh_modelo']       = $u['modelo'];
                    $cab['vh_anio']         = $u['anio'];
                    $cab['vh_placas']       = $u['placas'];
                    $cab['cliente_nombre']  = $u['cli_nombre'];
                    $cab['cliente_tel']     = $u['cli_tel'];
                }
            }

        $campos = [
                ':sid'      => $cab['sucursal_id'],
                ':cli'      => $cab['cliente_nombre'],
                ':tel'      => $cab['cliente_tel']       ?: null,
                ':marca'    => $cab['vh_marca'],
                ':modelo'   => $cab['vh_modelo'],
                ':anio'     => $cab['vh_anio'],
                ':placas'   => $cab['vh_placas']          ?: null,
                ':mec'      => $cab['mecanico_id']        ?: null,
                ':ser'      => $cab['servicio_id']        ?: null,
                ':mo'       => $cab['mano_obra']          ?: 0,
                ':modesc'   => $cab['mano_obra_desc']     ?: null,
                ':proneg'   => $cab['referencia_proneg']  ?: null,
                ':notas'    => $cab['notas']              ?: null,
                ':desc_pct' => $descuento_pct,
                ':cid'      => $cliente_id,
                ':uid2'     => $unidad_id,
            ];

            if ($id) {
                $this->execute(
                    "UPDATE facturas SET
                        sucursal_id=:sid, cliente_nombre=:cli, cliente_tel=:tel,
                        vh_marca=:marca, vh_modelo=:modelo, vh_anio=:anio, vh_placas=:placas,
                        mecanico_id=:mec, servicio_id=:ser, mano_obra=:mo, mano_obra_desc=:modesc,
                        referencia_proneg=:proneg, notas=:notas, descuento_pct=:desc_pct,
                        cliente_id=:cid, unidad_id=:uid2
                     WHERE id=:fid AND estado='borrador'",
                    array_merge($campos, [':fid' => $id])
                );
                $this->execute('DELETE FROM facturas_detalle WHERE factura_id=:fid', [':fid' => $id]);
            } else {
                $folio = $this->generarFolioFactura((int)$cab['sucursal_id']);
                $campos[':folio'] = $folio;
                $campos[':uid']   = $cab['usuario_id'];
                $this->execute(
                    "INSERT INTO facturas
                        (folio, sucursal_id, cliente_nombre, cliente_tel,
                         vh_marca, vh_modelo, vh_anio, vh_placas,
                         mecanico_id, servicio_id, mano_obra, mano_obra_desc,
                         referencia_proneg, notas, descuento_pct, estado, usuario_id,
                         cliente_id, unidad_id)
                     VALUES
                        (:folio, :sid, :cli, :tel,
                         :marca, :modelo, :anio, :placas,
                         :mec, :ser, :mo, :modesc,
                         :proneg, :notas, :desc_pct, 'borrador', :uid,
                         :cid, :uid2)",
                    $campos
                );
                $id = $this->lastInsertId();
            }

            // Calcular totales con descuento
            $subtotal    = 0.0;
            foreach ($partidas as $p) {
                $this->execute(
                    'INSERT INTO facturas_detalle (factura_id, producto_id, cantidad, precio_unitario)
                     VALUES (:fid, :pid, :qty, :precio)',
                    [':fid'=>$id, ':pid'=>$p['producto_id'], ':qty'=>$p['cantidad'], ':precio'=>$p['precio_unitario']]
                );
                $subtotal += $p['cantidad'] * $p['precio_unitario'];
            }
            $bruto = $subtotal + (float)($cab['mano_obra'] ?? 0);
            $total = $descuento_pct > 0 ? round($bruto * (1 - $descuento_pct / 100), 2) : $bruto;
            $this->execute(
                'UPDATE facturas SET subtotal=:sub, total=:tot WHERE id=:id',
                [':sub'=>$subtotal, ':tot'=>$total, ':id'=>$id]
            );

            $this->commit();
            return $id;

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    // ---- Emitir (cambia estado y crea la salida de inventario) ----

    public function emitir(int $id, int $usuario_id): void
    {
        require_once BASE_PATH . '/modules/salidas/SalidaModel.php';

        $factura  = $this->getById($id);
        if (!$factura) throw new RuntimeException('Factura no encontrada.');
        if ($factura['estado'] !== 'borrador') throw new RuntimeException('Solo se pueden emitir facturas en borrador.');

        $detalle = $this->getDetalle($id);
        if (empty($detalle)) throw new RuntimeException('La factura no tiene partidas de productos.');

        $this->beginTransaction();
        try {
            $salidaModel = new SalidaModel();
            $datos = [
                'sucursal_id'        => $factura['sucursal_id'],
                'mecanico_id'        => $factura['mecanico_id'],
                'servicio_id'        => $factura['servicio_id'],
                'referencia_factura' => $factura['folio'],
                'notas'              => 'Factura: ' . $factura['folio'],
                'usuario_id'         => $usuario_id,
            ];
            $partidas = array_map(fn($d) => [
                'producto_id'    => $d['producto_id'],
                'cantidad'       => $d['cantidad'],
                'precio_unitario'=> $d['precio_unitario'],
            ], $detalle);

            $movId = $salidaModel->confirmar($datos, $partidas);

            $this->execute(
                "UPDATE facturas SET estado='emitida', movimiento_id=:mid, fecha_emision=NOW() WHERE id=:id",
                [':mid' => $movId, ':id' => $id]
            );

            // Si la factura tiene unidad del catálogo, crear entrada en bitácora
            if ($factura['unidad_id']) {
                // Recargar factura para obtener totales actualizados
                $factura = $this->getById($id);
                $this->crearBitacora($factura, $detalle);
            }

            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    private function crearBitacora(array $factura, array $detalle): void
    {
        require_once BASE_PATH . '/modules/bitacoras/BitacoraModel.php';

        $snapshot = json_encode(array_map(fn($d) => [
            'nombre'          => $d['producto_nombre'],
            'cantidad'        => (float)$d['cantidad'],
            'precio_unitario' => (float)$d['precio_unitario'],
        ], $detalle), JSON_UNESCAPED_UNICODE);

        $trabajos = $factura['mano_obra_desc'] ?: ($factura['servicio_nombre'] !== '—' ? $factura['servicio_nombre'] : null);

        $bm = new BitacoraModel();
        $bm->crear([
            'unidad_id'            => $factura['unidad_id'],
            'factura_id'           => $factura['id'],
            'fecha_servicio'       => date('Y-m-d', strtotime($factura['fecha_emision'])),
            'mecanico_id'          => $factura['mecanico_id'] ?: null,
            'descripcion'          => $factura['notas'] ?: null,
            'trabajos_realizados'  => $trabajos,
            'productos_snapshot'   => $snapshot,
            'mano_obra'            => $factura['mano_obra'] ?? 0,
            'subtotal'             => $factura['subtotal']  ?? 0,
            'total'                => $factura['total']     ?? 0,
        ]);
    }

    // ---- Marcar pagada ----

    public function marcarPagada(int $id): void
    {
        $factura = $this->getById($id);
        if (!$factura) throw new RuntimeException('Factura no encontrada.');
        if ($factura['estado'] !== 'emitida') throw new RuntimeException('Solo se pueden marcar como pagadas las facturas emitidas.');

        $this->execute(
            "UPDATE facturas SET estado='pagada', fecha_pago=NOW() WHERE id=:id",
            [':id' => $id]
        );
    }

    // ---- Cancelar ----

    public function cancelar(int $id): void
    {
        $factura = $this->getById($id);
        if (!$factura) throw new RuntimeException('Factura no encontrada.');
        if ($factura['estado'] === 'cancelada') throw new RuntimeException('Ya está cancelada.');
        if ($factura['estado'] === 'borrador') {
            $this->execute("UPDATE facturas SET estado='cancelada' WHERE id=:id", [':id'=>$id]);
            return;
        }

        // Si tiene movimiento de salida, revertir el stock
        if ($factura['movimiento_id']) {
            // Revertir la salida: sumar de vuelta el stock
            $detalle = $this->getDetalle($id);
            $this->beginTransaction();
            try {
                foreach ($detalle as $d) {
                    $this->execute(
                        "INSERT INTO stock_sucursal (producto_id, sucursal_id, cantidad)
                         VALUES (:pid, :sid, :qty)
                         ON DUPLICATE KEY UPDATE cantidad = cantidad + :qty2",
                        [':pid'=>$d['producto_id'], ':sid'=>$factura['sucursal_id'], ':qty'=>$d['cantidad'], ':qty2'=>$d['cantidad']]
                    );
                }
                $this->execute(
                    "UPDATE movimientos SET estado='cancelado' WHERE id=:id",
                    [':id' => $factura['movimiento_id']]
                );
                $this->execute("UPDATE facturas SET estado='cancelada' WHERE id=:id", [':id'=>$id]);
                $this->commit();
            } catch (Exception $e) {
                $this->rollback();
                throw $e;
            }
        } else {
            $this->execute("UPDATE facturas SET estado='cancelada' WHERE id=:id", [':id'=>$id]);
        }
    }

    // ---- Generar folio por sucursal+año ----

    private function generarFolioFactura(int $sucursal_id): string
    {
        $anio = (int)date('Y');
        $this->execute(
            "INSERT INTO facturas_folios (sucursal_id, anio, ultimo)
             VALUES (:sid, :anio, 1)
             ON DUPLICATE KEY UPDATE ultimo = LAST_INSERT_ID(ultimo + 1)",
            [':sid' => $sucursal_id, ':anio' => $anio]
        );
        $ultimo = (int)$this->fetchColumn('SELECT LAST_INSERT_ID()');
        // Incluir la sucursal en el folio: el contador es por sucursal+año, así que
        // sin la sucursal dos sucursales generarían el mismo folio (colisión UNIQUE).
        return sprintf('FAC-%d-%d-%05d', $sucursal_id, $anio, $ultimo);
    }
}
