<?php
require_once BASE_PATH . '/core/Model.php';

class ClienteModel extends Model
{
    public function listar(string $buscar = '', int $pagina = 1): array
    {
        $where  = '';
        $params = [];

        if ($buscar !== '') {
            $where  = 'WHERE (nombre LIKE :buscar OR rfc LIKE :buscar2 OR telefono LIKE :buscar3)';
            $like   = '%' . $buscar . '%';
            $params = [':buscar' => $like, ':buscar2' => $like, ':buscar3' => $like];
        }

        $sql = "SELECT id, nombre, rfc, telefono, email, activo
                  FROM clientes
                {$where}
              ORDER BY nombre ASC";

        return $this->paginar($sql, $params, $pagina, 30);
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, nombre, rfc, telefono, email, direccion, notas, activo
               FROM clientes
              WHERE id = :id',
            [':id' => $id]
        );
    }

    public function crear(array $datos): int
    {
        $this->execute(
            'INSERT INTO clientes (nombre, rfc, telefono, email, direccion, notas, activo)
             VALUES (:nombre, :rfc, :telefono, :email, :direccion, :notas, 1)',
            [
                ':nombre'    => $datos['nombre'],
                ':rfc'       => $datos['rfc']       ?: null,
                ':telefono'  => $datos['telefono']  ?: null,
                ':email'     => $datos['email']     ?: null,
                ':direccion' => $datos['direccion'] ?: null,
                ':notas'     => $datos['notas']     ?: null,
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $datos): void
    {
        $this->execute(
            'UPDATE clientes
                SET nombre    = :nombre,
                    rfc       = :rfc,
                    telefono  = :telefono,
                    email     = :email,
                    direccion = :direccion,
                    notas     = :notas
              WHERE id = :id',
            [
                ':nombre'    => $datos['nombre'],
                ':rfc'       => $datos['rfc']       ?: null,
                ':telefono'  => $datos['telefono']  ?: null,
                ':email'     => $datos['email']     ?: null,
                ':direccion' => $datos['direccion'] ?: null,
                ':notas'     => $datos['notas']     ?: null,
                ':id'        => $id,
            ]
        );
    }

    public function toggleActivo(int $id): void
    {
        $this->execute(
            'UPDATE clientes SET activo = 1 - activo WHERE id = :id',
            [':id' => $id]
        );
    }

    public function getUnidades(int $cliente_id): array
    {
        return $this->fetchAll(
            'SELECT id, marca, modelo, anio, placas, numero_serie, color, notas, activo
               FROM clientes_unidades
              WHERE cliente_id = :cid
              ORDER BY marca, modelo',
            [':cid' => $cliente_id]
        );
    }

    public function buscarSugerencias(string $q): array
    {
        $like = '%' . $q . '%';
        return $this->fetchAll(
            'SELECT id, nombre, rfc, telefono
               FROM clientes
              WHERE activo = 1
                AND (nombre LIKE :q1 OR rfc LIKE :q2 OR telefono LIKE :q3)
              ORDER BY nombre ASC
              LIMIT 8',
            [':q1' => $like, ':q2' => $like, ':q3' => $like]
        );
    }
}
