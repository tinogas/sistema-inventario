<?php
require_once BASE_PATH . '/core/Model.php';

class BitacoraModel extends Model
{
    public function listar(array $filtros = [], int $pagina = 1): array
    {
        $where  = 'WHERE 1=1';
        $params = [];

        if (!empty($filtros['cliente_id'])) {
            $where .= ' AND cu.cliente_id = :cliente_id';
            $params[':cliente_id'] = $filtros['cliente_id'];
        }
        if (!empty($filtros['unidad_id'])) {
            $where .= ' AND b.unidad_id = :unidad_id';
            $params[':unidad_id'] = $filtros['unidad_id'];
        }
        if (!empty($filtros['fecha_desde'])) {
            $where .= ' AND b.fecha_servicio >= :fecha_desde';
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where .= ' AND b.fecha_servicio <= :fecha_hasta';
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['buscar_cliente'])) {
            $where .= ' AND c.nombre LIKE :buscar_cliente';
            $params[':buscar_cliente'] = '%' . $filtros['buscar_cliente'] . '%';
        }
        if (!empty($filtros['placas'])) {
            $where .= ' AND cu.placas LIKE :placas';
            $params[':placas'] = '%' . $filtros['placas'] . '%';
        }
        if (!empty($filtros['mecanico_id'])) {
            $where .= ' AND b.mecanico_id = :mecanico_id';
            $params[':mecanico_id'] = $filtros['mecanico_id'];
        }
        if (!empty($filtros['folio'])) {
            $where .= ' AND f.folio LIKE :folio';
            $params[':folio'] = '%' . $filtros['folio'] . '%';
        }

        $sql = "SELECT b.id, b.factura_id, b.fecha_servicio, b.total, b.mano_obra, b.subtotal,
                       b.descripcion, b.trabajos_realizados,
                       f.folio, f.estado AS factura_estado,
                       c.id AS cliente_id, c.nombre AS cliente_nombre,
                       cu.id AS unidad_id,
                       CONCAT(cu.marca,' ',cu.modelo) AS unidad,
                       cu.placas,
                       COALESCE(m.nombre,'—') AS mecanico
                  FROM bitacoras_servicio b
                  INNER JOIN clientes_unidades cu ON cu.id = b.unidad_id
                  INNER JOIN clientes          c  ON c.id  = cu.cliente_id
                  INNER JOIN facturas          f  ON f.id  = b.factura_id
                  LEFT  JOIN mecanicos         m  ON m.id  = b.mecanico_id
                {$where}
                ORDER BY b.fecha_servicio DESC, b.id DESC";

        return $this->paginar($sql, $params, $pagina, 25);
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT b.*,
                    f.folio, f.estado AS factura_estado,
                    c.id AS cliente_id, c.nombre AS cliente_nombre,
                    c.rfc AS cliente_rfc, c.telefono AS cliente_tel,
                    cu.marca, cu.modelo, cu.anio, cu.placas, cu.numero_serie, cu.color,
                    COALESCE(m.nombre,'—') AS mecanico_nombre
               FROM bitacoras_servicio b
               INNER JOIN clientes_unidades cu ON cu.id = b.unidad_id
               INNER JOIN clientes          c  ON c.id  = cu.cliente_id
               INNER JOIN facturas          f  ON f.id  = b.factura_id
               LEFT  JOIN mecanicos         m  ON m.id  = b.mecanico_id
              WHERE b.id = :id",
            [':id' => $id]
        );
    }

    public function getByUnidad(int $unidad_id): array
    {
        return $this->fetchAll(
            "SELECT b.id, b.fecha_servicio, b.descripcion, b.trabajos_realizados,
                    b.productos_snapshot,
                    f.folio, f.id AS factura_id,
                    COALESCE(m.nombre,'—') AS mecanico,
                    su.nombre AS sucursal
               FROM bitacoras_servicio b
               INNER JOIN facturas   f  ON f.id  = b.factura_id
               LEFT  JOIN mecanicos  m  ON m.id  = b.mecanico_id
               LEFT  JOIN sucursales su ON su.id = f.sucursal_id
              WHERE b.unidad_id = :uid
              ORDER BY b.fecha_servicio DESC, b.id DESC",
            [':uid' => $unidad_id]
        );
    }

    public function crear(array $datos): int
    {
        $this->execute(
            "INSERT INTO bitacoras_servicio
                (unidad_id, factura_id, fecha_servicio, mecanico_id,
                 descripcion, trabajos_realizados, productos_snapshot,
                 mano_obra, subtotal, total)
             VALUES
                (:unidad_id, :factura_id, :fecha, :mecanico_id,
                 :descripcion, :trabajos, :snapshot,
                 :mano_obra, :subtotal, :total)",
            [
                ':unidad_id'   => $datos['unidad_id'],
                ':factura_id'  => $datos['factura_id'],
                ':fecha'       => $datos['fecha_servicio'],
                ':mecanico_id' => $datos['mecanico_id'] ?? null,
                ':descripcion' => $datos['descripcion'] ?? null,
                ':trabajos'    => $datos['trabajos_realizados'] ?? null,
                ':snapshot'    => $datos['productos_snapshot'] ?? null,
                ':mano_obra'   => $datos['mano_obra'] ?? 0,
                ':subtotal'    => $datos['subtotal'] ?? 0,
                ':total'       => $datos['total'] ?? 0,
            ]
        );
        return $this->lastInsertId();
    }
}
