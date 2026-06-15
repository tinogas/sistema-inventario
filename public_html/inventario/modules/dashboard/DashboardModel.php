<?php
require_once BASE_PATH . '/core/Model.php';

class DashboardModel extends Model
{
    public function getKpis(?int $sucursal_id): array
    {
        $sucWhere = $sucursal_id ? 'AND ss.sucursal_id = :sid' : '';
        $params   = $sucursal_id ? [':sid' => $sucursal_id] : [];

        $totalProductos = (int) $this->fetchColumn(
            "SELECT COUNT(DISTINCT p.id) FROM productos p
             INNER JOIN stock_sucursal ss ON ss.producto_id = p.id
             WHERE p.activo = 1 {$sucWhere}",
            $params
        );

        $entradas = (int) $this->fetchColumn(
            "SELECT COUNT(*) FROM movimientos
             WHERE tipo = 'entrada' AND DATE(created_at) = CURDATE()
             " . ($sucursal_id ? " AND sucursal_id = :sid" : ''),
            $params
        );

        $salidas = (int) $this->fetchColumn(
            "SELECT COUNT(*) FROM movimientos
             WHERE tipo = 'salida' AND DATE(created_at) = CURDATE()
             " . ($sucursal_id ? " AND sucursal_id = :sid" : ''),
            $params
        );

        $traspasos = (int) $this->fetchColumn(
            "SELECT COUNT(*) FROM traspasos WHERE estado = 'en_transito'"
        );

        $alertasCount = (int) $this->fetchColumn(
            "SELECT COUNT(*) FROM stock_sucursal ss
             INNER JOIN productos p ON p.id = ss.producto_id
             WHERE ss.cantidad <= p.stock_minimo AND p.activo = 1
             {$sucWhere}",
            $params
        );

        return compact('totalProductos','entradas','salidas','traspasos','alertasCount');
    }

    public function getAlertasStock(?int $sucursal_id, int $limite = 10): array
    {
        $sucWhere = $sucursal_id ? 'AND ss.sucursal_id = :sid' : '';
        $params   = $sucursal_id ? [':sid' => $sucursal_id] : [];

        return $this->fetchAll(
            "SELECT p.id, p.codigo, p.nombre,
                    ss.cantidad AS stock_actual,
                    p.stock_minimo,
                    su.nombre AS sucursal,
                    COALESCE(u.clave,'PZA') AS unidad
             FROM stock_sucursal ss
             INNER JOIN productos p  ON p.id  = ss.producto_id
             INNER JOIN sucursales su ON su.id = ss.sucursal_id
             LEFT  JOIN unidades   u  ON u.id  = p.unidad_id
             WHERE ss.cantidad <= p.stock_minimo AND p.activo = 1
             {$sucWhere}
             ORDER BY (ss.cantidad - p.stock_minimo) ASC
             LIMIT {$limite}",
            $params
        );
    }

    public function getMovimientos7Dias(?int $sucursal_id): array
    {
        // Generar los 7 días como base (garantiza que aparezcan días sin actividad)
        $dias = [];
        for ($i = 6; $i >= 0; $i--) {
            $f = date('Y-m-d', strtotime("-{$i} days"));
            $dias[$f] = ['fecha' => $f, 'entradas' => 0, 'salidas' => 0, 'traspasos' => 0, 'facturas' => 0];
        }

        // Movimientos (entradas, salidas, traspasos)
        $sucWhereM = $sucursal_id ? 'AND sucursal_id = :sid' : '';
        $paramsM   = $sucursal_id ? [':sid' => $sucursal_id] : [];
        $movRows   = $this->fetchAll(
            "SELECT DATE(created_at) AS fecha,
                    SUM(tipo = 'entrada')        AS entradas,
                    SUM(tipo = 'salida')          AS salidas,
                    SUM(tipo = 'traspaso_salida') AS traspasos
             FROM movimientos
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
               AND estado = 'confirmado'
             {$sucWhereM}
             GROUP BY DATE(created_at)",
            $paramsM
        );
        foreach ($movRows as $r) {
            if (isset($dias[$r['fecha']])) {
                $dias[$r['fecha']]['entradas']  = (int)$r['entradas'];
                $dias[$r['fecha']]['salidas']   = (int)$r['salidas'];
                $dias[$r['fecha']]['traspasos'] = (int)$r['traspasos'];
            }
        }

        // Facturas emitidas/pagadas (no canceladas ni borradores)
        $sucWhereF = $sucursal_id ? 'AND sucursal_id = :sid' : '';
        $paramsF   = $sucursal_id ? [':sid' => $sucursal_id] : [];
        $facRows   = $this->fetchAll(
            "SELECT DATE(created_at) AS fecha, COUNT(*) AS facturas
             FROM facturas
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
               AND estado IN ('emitida','pagada')
             {$sucWhereF}
             GROUP BY DATE(created_at)",
            $paramsF
        );
        foreach ($facRows as $r) {
            if (isset($dias[$r['fecha']])) {
                $dias[$r['fecha']]['facturas'] = (int)$r['facturas'];
            }
        }

        return array_values($dias);
    }

    public function getUltimasActividades(?int $sucursal_id, int $limite = 8): array
    {
        $sucWhere = $sucursal_id ? 'AND m.sucursal_id = :sid' : '';
        $params   = $sucursal_id ? [':sid' => $sucursal_id] : [];

        return $this->fetchAll(
            "SELECT m.folio, m.tipo, m.estado, m.created_at,
                    u.nombre AS usuario, su.nombre AS sucursal
             FROM movimientos m
             INNER JOIN usuarios u   ON u.id  = m.usuario_id
             INNER JOIN sucursales su ON su.id = m.sucursal_id
             WHERE 1=1 {$sucWhere}
             ORDER BY m.created_at DESC
             LIMIT {$limite}",
            $params
        );
    }
}
