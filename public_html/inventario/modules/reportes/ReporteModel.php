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

        // Siempre retorna una fila por (producto, sucursal) para poder mostrar
        // el desglose expandible en la vista. La agrupación visual se hace en stock.php.
        return $this->fetchAll(
            "SELECT p.id, p.codigo, p.nombre AS producto,
                    COALESCE(c.nombre,'—') AS categoria,
                    COALESCE(u.clave,'PZA') AS unidad,
                    COALESCE(ss.cantidad, 0) AS stock_actual,
                    p.stock_minimo,
                    su.id   AS sucursal_id,
                    su.nombre AS sucursal
             FROM productos p
             LEFT JOIN categorias    c  ON c.id  = p.categoria_id
             LEFT JOIN unidades      u  ON u.id  = p.unidad_id
             LEFT JOIN stock_sucursal ss ON ss.producto_id = p.id
             LEFT JOIN sucursales    su  ON su.id = ss.sucursal_id
             {$where}
             ORDER BY p.nombre ASC, su.nombre ASC",
            $params
        );
    }

    /**
     * Retorna los traspasos en tránsito activos agrupados por producto.
     * Clave del array resultado: producto_id → lista de filas con origen, destino y cantidad.
     * Si $sucursal_id se pasa, filtra solo traspasos que salen O entran a esa sucursal.
     */
    public function getTransitoActivo(?int $sucursal_id, ?int $categoria_id, string $buscar): array
    {
        $where  = "WHERE t.estado = 'en_transito' AND m.tipo = 'traspaso_salida'";
        $params = [];

        if ($sucursal_id) {
            $where .= ' AND (m.sucursal_id = :sid OR m.sucursal_dest_id = :sid2)';
            $params[':sid']  = $sucursal_id;
            $params[':sid2'] = $sucursal_id;
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

        $filas = $this->fetchAll(
            "SELECT md.producto_id,
                    SUM(md.cantidad)      AS cantidad,
                    m.folio               AS folio_traspaso,
                    t.id                  AS traspaso_id,
                    t.fecha_envio,
                    su_o.id               AS origen_id,
                    su_o.nombre           AS origen,
                    su_d.id               AS destino_id,
                    su_d.nombre           AS destino
             FROM movimientos_detalle md
             INNER JOIN movimientos  m    ON m.id    = md.movimiento_id
             INNER JOIN traspasos    t    ON t.movimiento_salida_id = m.id
             INNER JOIN productos    p    ON p.id    = md.producto_id
             INNER JOIN sucursales su_o   ON su_o.id = m.sucursal_id
             INNER JOIN sucursales su_d   ON su_d.id = m.sucursal_dest_id
             {$where}
             GROUP BY md.producto_id, t.id, m.folio, m.sucursal_id, m.sucursal_dest_id,
                      su_o.nombre, su_d.nombre, t.fecha_envio",
            $params
        );

        // Indexar por producto_id para lookup O(1) en la vista
        $idx = [];
        foreach ($filas as $f) {
            $idx[(int)$f['producto_id']][] = $f;
        }
        return $idx;
    }

    public function getMovimientos(?int $sucursal_id, string $tipo, string $desde, string $hasta, int $pagina, string $estado = '', string $producto = ''): array
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
        if ($estado !== '') {
            $where .= ' AND m.estado = :estado';
            $params[':estado'] = $estado;
        }
        if ($producto !== '') {
            $where .= ' AND EXISTS (SELECT 1 FROM movimientos_detalle md INNER JOIN productos p ON p.id = md.producto_id WHERE md.movimiento_id = m.id AND (p.nombre LIKE :prod OR p.codigo LIKE :prod2))';
            $params[':prod']  = '%' . $producto . '%';
            $params[':prod2'] = '%' . $producto . '%';
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

    public function getSucursales(): array
    {
        return $this->fetchAll('SELECT id, nombre FROM sucursales ORDER BY id ASC');
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

    /**
     * Productos bajo mínimo para generar el pedido de reabastecimiento.
     * Incluye proveedor y la cantidad sugerida a pedir (mínimo − actual).
     */
    public function getReabastecimiento(?int $sucursal_id): array
    {
        $where  = 'WHERE p.activo = 1 AND ss.cantidad <= p.stock_minimo';
        $params = [];
        if ($sucursal_id) {
            $where .= ' AND ss.sucursal_id = :sid';
            $params[':sid'] = $sucursal_id;
        }

        return $this->fetchAll(
            "SELECT p.id, p.codigo, p.nombre,
                    COALESCE(c.nombre,'—')  AS categoria,
                    COALESCE(u.clave,'PZA') AS unidad,
                    COALESCE(pv.razon_social,'—') AS proveedor,
                    su.id   AS sucursal_id,
                    su.nombre AS sucursal,
                    ss.cantidad      AS stock_actual,
                    p.stock_minimo,
                    GREATEST(p.stock_minimo - ss.cantidad, 0) AS a_pedir,
                    p.precio_costo
             FROM stock_sucursal ss
             INNER JOIN productos p   ON p.id  = ss.producto_id
             INNER JOIN sucursales su ON su.id = ss.sucursal_id
             LEFT  JOIN categorias c  ON c.id  = p.categoria_id
             LEFT  JOIN unidades   u  ON u.id  = p.unidad_id
             LEFT  JOIN proveedores pv ON pv.id = p.proveedor_id
             {$where}
             ORDER BY pv.razon_social, su.nombre, p.nombre",
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
