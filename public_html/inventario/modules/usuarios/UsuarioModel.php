<?php
require_once BASE_PATH . '/core/Model.php';

class UsuarioModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        try { $this->db->exec("ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) NULL"); }
        catch (PDOException $e) { /* ya existe */ }
    }

    public function listar(): array
    {
        return $this->fetchAll(
            'SELECT u.id, u.nombre, u.email, u.rol, u.activo, u.ultimo_acceso, u.created_at, u.foto,
                    s.nombre AS sucursal_nombre
             FROM usuarios u
             LEFT JOIN sucursales s ON s.id = u.sucursal_id
             ORDER BY u.nombre'
        );
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT u.id, u.nombre, u.email, u.rol, u.sucursal_id, u.activo,
                    u.ultimo_acceso, u.created_at, u.foto,
                    s.nombre AS sucursal_nombre
             FROM usuarios u
             LEFT JOIN sucursales s ON s.id = u.sucursal_id
             WHERE u.id = :id',
            [':id' => $id]
        );
    }

    public function crear(array $datos): int
    {
        $hash = password_hash($datos['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        $this->execute(
            'INSERT INTO usuarios (nombre, email, password_hash, rol, sucursal_id, foto)
             VALUES (:nombre, :email, :hash, :rol, :sucursal_id, :foto)',
            [
                ':nombre'      => $datos['nombre'],
                ':email'       => strtolower(trim($datos['email'])),
                ':hash'        => $hash,
                ':rol'         => $datos['rol'],
                ':sucursal_id' => ($datos['sucursal_id'] > 0) ? (int)$datos['sucursal_id'] : null,
                ':foto'        => $datos['foto'] ?? null,
            ]
        );
        return $this->lastInsertId();
    }

    /**
     * Actualiza el usuario. Si $datos['password'] viene vacío no cambia la contraseña.
     */
    public function actualizar(int $id, array $datos): void
    {
        if (!empty($datos['password'])) {
            $hash = password_hash($datos['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $this->execute(
                'UPDATE usuarios
                 SET nombre = :nombre, email = :email, password_hash = :hash,
                     rol = :rol, sucursal_id = :sucursal_id, foto = :foto
                 WHERE id = :id',
                [
                    ':nombre'      => $datos['nombre'],
                    ':email'       => strtolower(trim($datos['email'])),
                    ':hash'        => $hash,
                    ':rol'         => $datos['rol'],
                    ':sucursal_id' => ($datos['sucursal_id'] > 0) ? (int)$datos['sucursal_id'] : null,
                    ':foto'        => $datos['foto'] ?? null,
                    ':id'          => $id,
                ]
            );
        } else {
            $this->execute(
                'UPDATE usuarios
                 SET nombre = :nombre, email = :email,
                     rol = :rol, sucursal_id = :sucursal_id, foto = :foto
                 WHERE id = :id',
                [
                    ':nombre'      => $datos['nombre'],
                    ':email'       => strtolower(trim($datos['email'])),
                    ':rol'         => $datos['rol'],
                    ':sucursal_id' => ($datos['sucursal_id'] > 0) ? (int)$datos['sucursal_id'] : null,
                    ':foto'        => $datos['foto'] ?? null,
                    ':id'          => $id,
                ]
            );
        }
    }

    /**
     * Baja lógica. Lanza excepción si el id coincide con el usuario actual.
     */
    public function eliminar(int $id, int $usuarioActualId): void
    {
        if ($id === $usuarioActualId) {
            throw new \RuntimeException('No puedes darte de baja a ti mismo.');
        }
        $this->execute(
            'UPDATE usuarios SET activo = 0 WHERE id = :id',
            [':id' => $id]
        );
    }

    /**
     * Verifica que el email no esté en uso por otro usuario.
     */
    public function emailExiste(string $email, ?int $excluirId = null): bool
    {
        if ($excluirId !== null) {
            $count = (int) $this->fetchColumn(
                'SELECT COUNT(*) FROM usuarios WHERE email = :email AND id != :excluir',
                [':email' => strtolower(trim($email)), ':excluir' => $excluirId]
            );
        } else {
            $count = (int) $this->fetchColumn(
                'SELECT COUNT(*) FROM usuarios WHERE email = :email',
                [':email' => strtolower(trim($email))]
            );
        }
        return $count > 0;
    }

    /**
     * Devuelve todas las sucursales activas para el select del formulario.
     */
    public function getSucursales(): array
    {
        return $this->fetchAll(
            'SELECT id, nombre FROM sucursales WHERE activa = 1 ORDER BY nombre'
        );
    }
}
