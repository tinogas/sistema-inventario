<?php
require_once BASE_PATH . '/core/Model.php';

class SucursalModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        foreach ([
            "ALTER TABLE sucursales ADD COLUMN foto VARCHAR(255) NULL",
            "ALTER TABLE sucursales ADD COLUMN latitud DECIMAL(10,7) NULL",
            "ALTER TABLE sucursales ADD COLUMN longitud DECIMAL(10,7) NULL",
        ] as $sql) {
            try { $this->db->exec($sql); } catch (PDOException $e) { /* ya existe */ }
        }
    }

    public function listar(): array
    {
        return $this->fetchAll(
            'SELECT * FROM sucursales ORDER BY id ASC'
        );
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM sucursales WHERE id = :id',
            [':id' => $id]
        );
    }

    public function crear(array $d): int
    {
        $this->execute(
            'INSERT INTO sucursales (nombre, ciudad, direccion, telefono, foto, latitud, longitud, activa)
             VALUES (:nombre, :ciudad, :dir, :tel, :foto, :lat, :lng, 1)',
            [':nombre' => $d['nombre'], ':ciudad' => $d['ciudad'],
             ':dir' => $d['direccion'] ?: null, ':tel' => $d['telefono'] ?: null,
             ':foto' => $d['foto'] ?: null,
             ':lat' => ($d['latitud']  !== '' && $d['latitud']  !== null) ? $d['latitud']  : null,
             ':lng' => ($d['longitud'] !== '' && $d['longitud'] !== null) ? $d['longitud'] : null]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $d): void
    {
        $this->execute(
            'UPDATE sucursales SET nombre=:nombre, ciudad=:ciudad,
             direccion=:dir, telefono=:tel, foto=:foto, latitud=:lat, longitud=:lng,
             activa=:activa WHERE id=:id',
            [':nombre' => $d['nombre'], ':ciudad' => $d['ciudad'],
             ':dir' => $d['direccion'] ?: null, ':tel' => $d['telefono'] ?: null,
             ':foto' => $d['foto'] ?: null,
             ':lat' => ($d['latitud']  !== '' && $d['latitud']  !== null) ? $d['latitud']  : null,
             ':lng' => ($d['longitud'] !== '' && $d['longitud'] !== null) ? $d['longitud'] : null,
             ':activa' => $d['activa'] ? 1 : 0, ':id' => $id]
        );
    }
}
