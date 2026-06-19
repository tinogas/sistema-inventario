<?php
require_once BASE_PATH . '/core/Model.php';

class UnidadClienteModel extends Model
{
    public function getByCliente(int $cliente_id): array
    {
        return $this->fetchAll(
            'SELECT id, cliente_id, marca, modelo, anio, placas, numero_serie, color, notas, activo
               FROM clientes_unidades
              WHERE cliente_id = :cid
              ORDER BY marca, modelo',
            [':cid' => $cliente_id]
        );
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT u.*, c.nombre AS cliente_nombre
               FROM clientes_unidades u
               INNER JOIN clientes c ON c.id = u.cliente_id
              WHERE u.id = :id',
            [':id' => $id]
        );
    }

    public function crear(array $datos): int
    {
        $this->execute(
            'INSERT INTO clientes_unidades
                (cliente_id, marca, modelo, anio, placas, numero_serie, color, notas, activo)
             VALUES
                (:cid, :marca, :modelo, :anio, :placas, :nserie, :color, :notas, 1)',
            [
                ':cid'    => $datos['cliente_id'],
                ':marca'  => $datos['marca'],
                ':modelo' => $datos['modelo'],
                ':anio'   => $datos['anio']         ?: null,
                ':placas' => $datos['placas']        ?: null,
                ':nserie' => $datos['numero_serie']  ?: null,
                ':color'  => $datos['color']         ?: null,
                ':notas'  => $datos['notas']         ?: null,
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $datos): void
    {
        $this->execute(
            'UPDATE clientes_unidades
                SET marca        = :marca,
                    modelo       = :modelo,
                    anio         = :anio,
                    placas       = :placas,
                    numero_serie = :nserie,
                    color        = :color,
                    notas        = :notas
              WHERE id = :id',
            [
                ':marca'  => $datos['marca'],
                ':modelo' => $datos['modelo'],
                ':anio'   => $datos['anio']        ?: null,
                ':placas' => $datos['placas']       ?: null,
                ':nserie' => $datos['numero_serie'] ?: null,
                ':color'  => $datos['color']        ?: null,
                ':notas'  => $datos['notas']        ?: null,
                ':id'     => $id,
            ]
        );
    }

    public function toggleActivo(int $id): void
    {
        $this->execute(
            'UPDATE clientes_unidades SET activo = 1 - activo WHERE id = :id',
            [':id' => $id]
        );
    }
}
