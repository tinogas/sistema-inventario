<?php
require_once BASE_PATH . '/core/Model.php';

class ProveedorModel extends Model
{
    public function listar(string $buscar = '', int $pagina = 1): array
    {
        $where  = '';
        $params = [];

        if ($buscar !== '') {
            $where  = 'WHERE (razon_social LIKE :buscar OR rfc LIKE :buscar2)';
            $like   = '%' . $buscar . '%';
            $params = [':buscar' => $like, ':buscar2' => $like];
        }

        $sql = "SELECT id, razon_social, rfc, contacto, telefono, email, activo
                  FROM proveedores
                {$where}
              ORDER BY razon_social ASC";

        return $this->paginar($sql, $params, $pagina, 20);
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, razon_social, rfc, contacto, telefono, email, notas, activo
               FROM proveedores
              WHERE id = :id',
            [':id' => $id]
        );
    }

    public function crear(array $datos): int
    {
        $this->execute(
            'INSERT INTO proveedores (razon_social, rfc, contacto, telefono, email, notas, activo)
             VALUES (:razon_social, :rfc, :contacto, :telefono, :email, :notas, 1)',
            [
                ':razon_social' => $datos['razon_social'],
                ':rfc'          => $datos['rfc']      ?: null,
                ':contacto'     => $datos['contacto'] ?: null,
                ':telefono'     => $datos['telefono'] ?: null,
                ':email'        => $datos['email']    ?: null,
                ':notas'        => $datos['notas']    ?: null,
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $datos): void
    {
        $this->execute(
            'UPDATE proveedores
                SET razon_social = :razon_social,
                    rfc          = :rfc,
                    contacto     = :contacto,
                    telefono     = :telefono,
                    email        = :email,
                    notas        = :notas
              WHERE id = :id',
            [
                ':razon_social' => $datos['razon_social'],
                ':rfc'          => $datos['rfc']      ?: null,
                ':contacto'     => $datos['contacto'] ?: null,
                ':telefono'     => $datos['telefono'] ?: null,
                ':email'        => $datos['email']    ?: null,
                ':notas'        => $datos['notas']    ?: null,
                ':id'           => $id,
            ]
        );
    }

    public function eliminar(int $id): void
    {
        $this->execute(
            'UPDATE proveedores SET activo = 0 WHERE id = :id',
            [':id' => $id]
        );
    }
}
