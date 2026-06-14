<?php
require_once BASE_PATH . '/core/Model.php';

class ProductoModel extends Model
{
    /**
     * Lista paginada de productos con stock.
     * Si $sucursal_id es null devuelve stock total (suma de todas las sucursales).
     */
    public function listar(?int $sucursal_id, string $buscar, int $pagina): array
    {
        $params = [];
        $where  = ['p.activo = 1'];

        if ($buscar !== '') {
            $where[] = '(p.codigo LIKE :buscar1 OR p.codigo_alterno LIKE :buscar2 OR p.nombre LIKE :buscar3 OR c.nombre LIKE :buscar4)';
            $like = '%' . $buscar . '%';
            $params[':buscar1'] = $like;
            $params[':buscar2'] = $like;
            $params[':buscar3'] = $like;
            $params[':buscar4'] = $like;
        }

        if ($sucursal_id !== null) {
            $stockExpr       = 'COALESCE(ss.cantidad, 0)';
            $stockJoin       = "LEFT JOIN stock_sucursal ss ON ss.producto_id = p.id AND ss.sucursal_id = :suc_id";
            $params[':suc_id'] = $sucursal_id;
        } else {
            $stockExpr = 'COALESCE(stot.total_stock, 0)';
            $stockJoin = 'LEFT JOIN (SELECT producto_id, SUM(cantidad) AS total_stock FROM stock_sucursal GROUP BY producto_id) stot ON stot.producto_id = p.id';
        }

        $whereSQL = implode(' AND ', $where);

        $sql = "SELECT
                    p.id,
                    p.codigo,
                    p.codigo_alterno,
                    p.nombre,
                    p.precio_venta,
                    p.stock_minimo,
                    p.activo,
                    c.nombre   AS categoria,
                    u.clave    AS unidad_clave,
                    u.nombre   AS unidad_nombre,
                    {$stockExpr} AS stock_actual
                FROM productos p
                LEFT JOIN categorias c ON c.id = p.categoria_id
                LEFT JOIN unidades   u ON u.id = p.unidad_id
                {$stockJoin}
                WHERE {$whereSQL}
                ORDER BY p.nombre ASC";

        return $this->paginar($sql, $params, $pagina, 20);
    }

    /**
     * Busca un producto por código exacto o código alterno.
     * Retorna null si no existe.
     */
    public function buscarPorCodigo(string $codigo): ?array
    {
        return $this->fetchOne(
            'SELECT p.*, c.nombre AS categoria, u.clave AS unidad_clave, u.nombre AS unidad_nombre
             FROM productos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             LEFT JOIN unidades   u ON u.id = p.unidad_id
             WHERE (p.codigo = :cod1 OR p.codigo_alterno = :cod2) AND p.activo = 1
             LIMIT 1',
            [':cod1' => $codigo, ':cod2' => $codigo]
        );
    }

    /**
     * Retorna hasta 8 productos (id, codigo, nombre) para autocomplete AJAX.
     */
    public function buscarSugerencias(string $q): array
    {
        return $this->fetchAll(
            'SELECT id, codigo, codigo_alterno, nombre
             FROM productos
             WHERE activo = 1
               AND (codigo LIKE :q1 OR codigo_alterno LIKE :q2 OR nombre LIKE :q3)
             ORDER BY nombre ASC
             LIMIT 8',
            [':q1' => '%' . $q . '%', ':q2' => '%' . $q . '%', ':q3' => '%' . $q . '%']
        );
    }

    /**
     * Producto completo con categoría, unidad y proveedor.
     */
    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT
                p.*,
                c.nombre       AS categoria,
                u.clave        AS unidad_clave,
                u.nombre       AS unidad_nombre,
                pv.razon_social AS proveedor_nombre
             FROM productos p
             LEFT JOIN categorias c  ON c.id  = p.categoria_id
             LEFT JOIN unidades   u  ON u.id  = p.unidad_id
             LEFT JOIN proveedores pv ON pv.id = p.proveedor_id
             WHERE p.id = :id',
            [':id' => $id]
        );
    }

    /**
     * Stock del producto en cada sucursal activa.
     */
    public function getStockPorSucursal(int $id): array
    {
        return $this->fetchAll(
            'SELECT
                s.id    AS sucursal_id,
                s.nombre AS sucursal_nombre,
                COALESCE(ss.cantidad, 0) AS cantidad
             FROM sucursales s
             LEFT JOIN stock_sucursal ss ON ss.sucursal_id = s.id AND ss.producto_id = :id
             WHERE s.activa = 1
             ORDER BY s.nombre ASC',
            [':id' => $id]
        );
    }

    /**
     * Historial de movimientos en los que participa el producto.
     */
    public function getUltimosMovimientos(int $id, int $limite = 15): array
    {
        return $this->fetchAll(
            'SELECT
                m.folio,
                m.tipo,
                m.created_at,
                m.estado,
                md.cantidad,
                md.precio_unitario,
                s.nombre  AS sucursal,
                u.nombre  AS usuario
             FROM movimientos_detalle md
             JOIN movimientos m ON m.id = md.movimiento_id
             JOIN sucursales  s ON s.id = m.sucursal_id
             JOIN usuarios    u ON u.id = m.usuario_id
             WHERE md.producto_id = :id
             ORDER BY m.created_at DESC
             LIMIT ' . (int) $limite,
            [':id' => $id]
        );
    }

    /**
     * Inserta un nuevo producto y retorna el id generado.
     */
    public function crear(array $datos): int
    {
        $this->execute(
            'INSERT INTO productos
                (codigo, codigo_alterno, nombre, descripcion,
                 categoria_id, unidad_id, proveedor_id,
                 precio_costo, precio_venta, stock_minimo, activo)
             VALUES
                (:codigo, :codigo_alterno, :nombre, :descripcion,
                 :categoria_id, :unidad_id, :proveedor_id,
                 :precio_costo, :precio_venta, :stock_minimo, 1)',
            [
                ':codigo'         => $datos['codigo'],
                ':codigo_alterno' => $datos['codigo_alterno'] ?: null,
                ':nombre'         => $datos['nombre'],
                ':descripcion'    => $datos['descripcion'] ?: null,
                ':categoria_id'   => $datos['categoria_id'] ?: null,
                ':unidad_id'      => $datos['unidad_id'],
                ':proveedor_id'   => $datos['proveedor_id'] ?: null,
                ':precio_costo'   => $datos['precio_costo'],
                ':precio_venta'   => $datos['precio_venta'],
                ':stock_minimo'   => $datos['stock_minimo'],
            ]
        );
        return $this->lastInsertId();
    }

    /**
     * Actualiza los datos de un producto existente.
     */
    public function actualizar(int $id, array $datos): void
    {
        $this->execute(
            'UPDATE productos SET
                codigo         = :codigo,
                codigo_alterno = :codigo_alterno,
                nombre         = :nombre,
                descripcion    = :descripcion,
                categoria_id   = :categoria_id,
                unidad_id      = :unidad_id,
                proveedor_id   = :proveedor_id,
                precio_costo   = :precio_costo,
                precio_venta   = :precio_venta,
                stock_minimo   = :stock_minimo
             WHERE id = :id',
            [
                ':codigo'         => $datos['codigo'],
                ':codigo_alterno' => $datos['codigo_alterno'] ?: null,
                ':nombre'         => $datos['nombre'],
                ':descripcion'    => $datos['descripcion'] ?: null,
                ':categoria_id'   => $datos['categoria_id'] ?: null,
                ':unidad_id'      => $datos['unidad_id'],
                ':proveedor_id'   => $datos['proveedor_id'] ?: null,
                ':precio_costo'   => $datos['precio_costo'],
                ':precio_venta'   => $datos['precio_venta'],
                ':stock_minimo'   => $datos['stock_minimo'],
                ':id'             => $id,
            ]
        );
    }

    /**
     * Soft-delete: marca activo=0.
     * Lanza excepción si el producto tiene movimientos registrados.
     */
    public function eliminar(int $id): void
    {
        $usosEnMovimientos = (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM movimientos_detalle WHERE producto_id = :id',
            [':id' => $id]
        );

        if ($usosEnMovimientos > 0) {
            throw new RuntimeException(
                'No se puede eliminar el producto porque tiene movimientos de inventario registrados.'
            );
        }

        $this->execute(
            'UPDATE productos SET activo = 0 WHERE id = :id',
            [':id' => $id]
        );
    }

    // -------------------------------------------------------
    // Catálogos auxiliares para los formularios
    // -------------------------------------------------------

    public function getCategorias(): array
    {
        return $this->fetchAll('SELECT id, nombre FROM categorias WHERE activa = 1 ORDER BY nombre ASC');
    }

    public function getUnidades(): array
    {
        return $this->fetchAll('SELECT id, clave, nombre FROM unidades ORDER BY nombre ASC');
    }

    public function getProveedores(): array
    {
        return $this->fetchAll('SELECT id, razon_social FROM proveedores WHERE activo = 1 ORDER BY razon_social ASC');
    }
}
