<?php
class Auth
{
    public static function intentarLogin(string $email, string $password): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT id, nombre, email, password_hash, rol, sucursal_id, foto
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
        Session::set('usuario_foto',         $usuario['foto'] ?? null);

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
            'foto'        => Session::get('usuario_foto'),
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

    // ---- Impersonación (solo admin puede usarla) ----

    public static function estaImpersonando(): bool
    {
        return (bool) Session::get('_impersonando');
    }

    public static function iniciarImpersonacion(array $target): void
    {
        Session::set('_imp_id',     Session::get('usuario_id'));
        Session::set('_imp_nombre', Session::get('usuario_nombre'));
        Session::set('_imp_email',  Session::get('usuario_email'));
        Session::set('_imp_foto',   Session::get('usuario_foto'));
        Session::set('_impersonando', true);

        Session::set('usuario_id',         $target['id']);
        Session::set('usuario_nombre',      $target['nombre']);
        Session::set('usuario_email',       $target['email']);
        Session::set('usuario_rol',         $target['rol']);
        Session::set('usuario_sucursal_id', $target['sucursal_id'] ?? null);
        Session::set('usuario_foto',        $target['foto'] ?? null);
    }

    public static function terminarImpersonacion(): void
    {
        Session::set('usuario_id',         Session::get('_imp_id'));
        Session::set('usuario_nombre',      Session::get('_imp_nombre'));
        Session::set('usuario_email',       Session::get('_imp_email'));
        Session::set('usuario_rol',         ROL_ADMIN);
        Session::set('usuario_sucursal_id', null);
        Session::set('usuario_foto',        Session::get('_imp_foto'));

        Session::delete('_impersonando');
        Session::delete('_imp_id');
        Session::delete('_imp_nombre');
        Session::delete('_imp_email');
        Session::delete('_imp_foto');
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
