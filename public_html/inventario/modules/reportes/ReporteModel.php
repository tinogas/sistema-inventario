<?php
require_once BASE_PATH . '/core/Model.php';

class ReporteModel extends Model
{
    public function getStock(?int $sucursal_id, ?int $categoria_id, string $buscar): array
    {
        $where  = 'WHERE p.activo = 1';
        $params = [];

        if ($sucursal_id) {
            $where .= ' AND ss.sucursal_id = :sid';
            $params[':sid'] = $sucursal_id;
        }
        if ($categoria_id) {
            $where .= ' AND p.categoria_id = :cat';
            $params[':cat'] = $categoria_id;
        }
        if ($buscar !== '') {
            $where .= ' AND (p.codigo LIKE :q OR p.nombre LIKE :q2)';
            $params[':q']  = "%{$buscar}%";
            $params[':q2'] = "%{$buscar}%";
        }

        $selectSucursal = $sucursal_id
            ? "ss.cantidad AS stock_actual, su.nombre AS sucursal"
            : "COALESCE(SUM(ss.cantidad),0) AS stock_actual, 'Todas' AS sucursal";

        $groupBy = $sucursal_id ? '' : 'GROUP BY p.id, p.codigo, p.nombre, c.nombre, u.clave, p.stock_minimo';

        return $this->fetchAll(
            "SELECT p.id, p.codigo, p.nombre AS producto,
                    COALESCE(c.nombre,'—') AS categoria,
                    COALESCE(u.clave,'PZA') AS unidad,
                    {$selectSucursal},
                    p.stock_minimo,
                    CASE WHEN " . ($sucursal_id ? "ss.cantidad" : "COALESCE(SUM(ss.cantidad),0)") . " <= p.stock_minimo THEN 1 ELSE 0 END AS bajo_minimo
             FROM productos p
             LEFT  JOIN categorias c       ON c.id = p.categoria_id
             LEFT  JOIN unidades   u       ON u.id = p.unidad_id
             LEFT  JOIN stock_sucursal ss  ON ss.producto_id = p.id
             " . ($sucursal_id ? "LEFT JOIN sucursales su ON su.id = ss.sucursal_id" : "") . "
             {$where}
             {$groupBy}
             ORDER BY p.nombre ASC",
            $params
        );
    }

    public function getMovimientos(?int $sucursal_id, string $tipo, string $desde, string $hasta, int $pagina): array
    {
        $where  = 'WHERE m.created_at BETWEEN :desde AND :hasta';
        $params = [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59'];

        if ($sucursal_id) {
            $where .= ' AND m.sucursal_id = :sid';
            $params[':sid'] = $sucursal_id;
        }
        if ($tipo) {
            $where .= ' AND m.tipo = :tipo';
            $params[':tipo'] = $tipo;
        }

        $sql = "SELECT m.id, m.folio, m.tipo, m.estado, m.created_at,
                       m.referencia_factura, su.nombre AS sucursal,
                       u.nombre AS usuario,
                       (SELECT COUNT(*) FROM movimientos_detalle WHERE movimiento_id=m.id) AS num_partidas
                FROM movimientos m
                INNER JOIN sucursales su ON su.id = m.sucursal_id
                INNER JOIN usuarios u    ON u.id  = m.usuario_id
                {$where}
                ORDER BY m.created_at DESC";

        return $this->paginar($sql, $params, $pagina, 30);
    }

    public function getAlertasStock(?int $sucursal_id): array
    {
        $where  = 'WHERE p.activo = 1 AND ss.cantidad <= p.stock_minimo';
        $params = [];

        if ($sucursal_id) {
            $where .= ' AND ss.sucursal_id = :sid';
            $params[':sid'] = $sucursal_id;
        }

        return $this->fetchAll(
            "SELECT p.id, p.codigo, p.nombre, COALESCE(c.nombre,'—') AS categoria,
                    COALESCE(u.clave,'PZA') AS unidad,
                    ss.cantidad AS stock_actual, p.stock_minimo,
                    su.nombre AS sucursal,
                    (ss.cantidad - p.stock_minimo) AS diferencia
             FROM stock_sucursal ss
             INNER JOIN productos p   ON p.id  = ss.producto_id
             INNER JOIN sucursales su ON su.id = ss.sucursal_id
             LEFT  JOIN categorias c  ON c.id  = p.categoria_id
             LEFT  JOIN unidades   u  ON u.id  = p.unidad_id
             {$where}
             ORDER BY diferencia ASC",
            $params
        );
    }

    public function getKardex(int $producto_id, ?int $sucursal_id, string $desde, string $hasta): array
    {
        $where  = "WHERE d.producto_id = :pid AND m.created_at BETWEEN :desde AND :hasta AND m.estado = 'confirmado'";
        $params = [':pid' => $producto_id, ':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59'];

        if ($sucursal_id) {
            $where .= ' AND m.sucursal_id = :sid';
            $params[':sid'] = $sucursal_id;
        }

        return $this->fetchAll(
            "SELECT m.created_at, m.folio, m.tipo, m.referencia_factura,
                    su.nombre AS sucursal, u.nombre AS usuario,
                    d.cantidad, d.precio_unitario,
                    CASE WHEN m.tipo IN ('entrada','traspaso_entrada') THEN d.cantidad ELSE 0 END AS entrada,
                    CASE WHEN m.tipo IN ('salida','traspaso_salida')   THEN d.cantidad ELSE 0 END AS salida
             FROM movimientos_detalle d
             INNER JOIN movimientos m  ON m.id  = d.movimiento_id
             INNER JOIN sucursales su  ON su.id = m.sucursal_id
             INNER JOIN usuarios   u   ON u.id  = m.usuario_id
             {$where}
             ORDER BY m.created_at ASC",
            $params
        );
    }

    public function getCategorias(): array
    {
        return $this->fetchAll('SELECT id, nombre FROM categorias WHERE activa=1 ORDER BY nombre');
    }
}
