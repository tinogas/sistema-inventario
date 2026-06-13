<?php
class Auth
{
    public static function intentarLogin(string $email, string $password): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT id, nombre, email, password_hash, rol, sucursal_id
             FROM usuarios
             WHERE email = :email AND activo = 1
             LIMIT 1'
        );
        $stmt->execute([':email' => trim($email)]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            return false;
        }

        // Actualizar último acceso
        $db->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id')
           ->execute([':id' => $usuario['id']]);

        // Guardar en sesión
        Session::regenerar();
        Session::set('usuario_id',          $usuario['id']);
        Session::set('usuario_nombre',       $usuario['nombre']);
        Session::set('usuario_email',        $usuario['email']);
        Session::set('usuario_rol',          $usuario['rol']);
        Session::set('usuario_sucursal_id',  $usuario['sucursal_id']);

        // Si es almacenista sin sucursal asignada, usar sucursal 1 por defecto
        if ($usuario['rol'] !== ROL_ADMIN && empty($usuario['sucursal_id'])) {
            Session::set('usuario_sucursal_id', 1);
        }

        return true;
    }

    public static function logout(): void
    {
        Session::destruir();
    }

    public static function estaAutenticado(): bool
    {
        return Session::has('usuario_id');
    }

    public static function usuario(): array
    {
        return [
            'id'          => Session::get('usuario_id'),
            'nombre'      => Session::get('usuario_nombre'),
            'email'       => Session::get('usuario_email'),
            'rol'         => Session::get('usuario_rol'),
            'sucursal_id' => Session::get('usuario_sucursal_id'),
        ];
    }

    public static function tienePermiso(string $permiso): bool
    {
        $rol = Session::get('usuario_rol');
        $permisos = PERMISOS[$rol] ?? [];
        return in_array('*', $permisos) || in_array($permiso, $permisos);
    }

    public static function esAdmin(): bool
    {
        return Session::get('usuario_rol') === ROL_ADMIN;
    }

    // Devuelve el ID de sucursal que debe aplicarse en los queries
    // Admin: el que esté seleccionado en ?sucursal_id= (o null para todas)
    // Almacenista/Consulta: siempre su sucursal asignada
    public static function sucursalFiltro(): ?int
    {
        if (self::esAdmin()) {
            $sid = filter_input(INPUT_GET, 'sucursal_id', FILTER_VALIDATE_INT);
            return $sid ?: null;
        }
        return (int) Session::get('usuario_sucursal_id');
    }

    public static function sucursalActual(): ?int
    {
        return (int) Session::get('usuario_sucursal_id') ?: null;
    }
}
