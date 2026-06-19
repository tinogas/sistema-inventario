<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? htmlspecialchars($titulo) . ' — ' : '' ?><?= htmlspecialchars($appName) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $appUrl ?>/assets/css/app.css">
    <?php if (Auth::estaImpersonando()): ?>
    <style>
        .sidebar      { top:96px !important; height:calc(100vh - 96px) !important; }
        .main-content { margin-top:96px !important; }
    </style>
    <?php endif; ?>
</head>
<body>

<!-- Navbar superior -->
<nav class="navbar navbar-dark bg-dark px-3 fixed-top" style="z-index:1040">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-outline-secondary" id="sidebarToggle">
            <i class="bi bi-list fs-5"></i>
        </button>
        <a class="navbar-brand fw-bold mb-0" href="<?= $appUrl ?>/?modulo=dashboard">
            <i class="bi bi-gear-wide-connected text-warning me-1"></i>
            <?= htmlspecialchars($appName) ?>
        </a>
    </div>
    <div class="d-flex align-items-center gap-3">
        <!-- Selector de sucursal (solo admin) -->
        <?php if ($usuario['rol'] === 'admin'): ?>
        <form method="GET" action="<?= $appUrl ?>/" class="d-flex align-items-center gap-2" id="frmSucursalNav">
            <input type="hidden" name="modulo" value="<?= htmlspecialchars($_GET['modulo'] ?? 'dashboard') ?>">
            <input type="hidden" name="accion" value="<?= htmlspecialchars($_GET['accion'] ?? 'index') ?>">
            <?php
            $db = Database::getInstance();
            $sucursalesNav = $db->query('SELECT id, nombre FROM sucursales WHERE activa=1 ORDER BY nombre')->fetchAll();
            $sucursalSelId = Auth::sucursalFiltro();
            ?>
            <select name="sucursal_id" class="form-select form-select-sm bg-dark text-white border-secondary"
                    onchange="this.form.submit()" style="width:160px">
                <option value="">Todas las sucursales</option>
                <?php foreach ($sucursalesNav as $s): ?>
                    <option value="<?= $s['id'] ?>"
                        <?= $sucursalSelId == $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php else: ?>
        <?php
        $db = Database::getInstance();
        $sucNombre = $db->prepare('SELECT nombre FROM sucursales WHERE id=?');
        $sucNombre->execute([$usuario['sucursal_id']]);
        $sucNombreVal = $sucNombre->fetchColumn();
        ?>
        <span class="badge bg-secondary">
            <i class="bi bi-building me-1"></i>
            <?= htmlspecialchars($sucNombreVal ?: 'Sin sucursal') ?>
        </span>
        <?php endif; ?>

        <!-- Badge alertas stock -->
        <a href="<?= $appUrl ?>/?modulo=reportes&accion=alertas" class="text-white position-relative text-decoration-none" title="Alertas de stock">
            <i class="bi bi-bell fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="badge-alertas" style="font-size:.6rem">
                <span class="visually-hidden">alertas</span>
            </span>
        </a>

        <!-- Usuario -->
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                <img src="<?= foto_o_avatar($usuario['foto'] ?? null, $usuario['nombre'] ?? '', $appUrl, 52) ?>" alt=""
                     class="rounded-circle" style="width:26px;height:26px;object-fit:cover">
                <?= htmlspecialchars($usuario['nombre']) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text text-muted small"><?= htmlspecialchars($usuario['email']) ?></span></li>
                <li><span class="dropdown-item-text text-muted small"><?= ucfirst($usuario['rol']) ?></span></li>
                <?php if (!Auth::estaImpersonando() && $usuario['rol'] === 'admin'): ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button type="button" class="dropdown-item text-info" data-bs-toggle="modal" data-bs-target="#modalUsarComo">
                        <i class="bi bi-person-bounding-box me-1"></i> Usar como…
                    </button>
                </li>
                <?php endif; ?>
                <?php if (Auth::estaImpersonando()): ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="<?= $appUrl ?>/?modulo=auth&accion=terminar_impersonacion" class="m-0">
                        <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                        <button type="submit" class="dropdown-item text-warning fw-semibold">
                            <i class="bi bi-arrow-return-left me-1"></i> Volver Admin
                        </button>
                    </form>
                </li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="<?= $appUrl ?>/?modulo=auth&accion=logout" class="m-0">
                        <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php if (Auth::estaImpersonando()): ?>
<div style="position:fixed;top:56px;left:0;right:0;z-index:1035;height:40px;background:#d97706;color:#fff;padding:0 1rem;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 6px rgba(0,0,0,.25)">
    <span class="fw-semibold small">
        <i class="bi bi-person-bounding-box me-1"></i>
        Actuando como: <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>
        <span class="badge bg-dark bg-opacity-50 ms-1"><?= ucfirst($usuario['rol']) ?></span>
    </span>
    <form method="POST" action="<?= $appUrl ?>/?modulo=auth&accion=terminar_impersonacion" class="m-0">
        <input type="hidden" name="_csrf" value="<?= $csrf ?>">
        <button type="submit" class="btn btn-sm btn-light py-0 px-2 text-dark fw-semibold">
            <i class="bi bi-arrow-return-left me-1"></i>Volver Admin
        </button>
    </form>
</div>
<?php endif; ?>

<!-- Sidebar -->
<div id="sidebar" class="sidebar">
    <nav class="sidebar-nav">
        <div class="sidebar-section">Inventario</div>
        <a href="<?= $appUrl ?>/?modulo=dashboard" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <?php if (Auth::tienePermiso('facturas.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=facturas" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'facturas' ? 'active' : '' ?>">
            <i class="bi bi-receipt text-warning"></i> Facturas
        </a>
        <?php endif; ?>
        <?php if (Auth::tienePermiso('entradas.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=entradas" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'entradas' ? 'active' : '' ?>">
            <i class="bi bi-box-arrow-in-down-right text-success"></i> Entradas
        </a>
        <?php endif; ?>
        <?php if (Auth::tienePermiso('salidas.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=salidas" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'salidas' ? 'active' : '' ?>">
            <i class="bi bi-box-arrow-up-right text-danger"></i> Salidas
        </a>
        <?php endif; ?>
        <?php if (Auth::tienePermiso('traspasos.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=traspasos" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'traspasos' ? 'active' : '' ?>">
            <i class="bi bi-arrow-left-right text-info"></i> Traspasos
        </a>
        <?php endif; ?>

        <?php if (Auth::tienePermiso('clientes.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=clientes" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'clientes' ? 'active' : '' ?>">
            <i class="bi bi-people text-primary"></i> Clientes
        </a>
        <?php endif; ?>
        <?php if (Auth::tienePermiso('bitacoras.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=bitacoras" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'bitacoras' ? 'active' : '' ?>">
            <i class="bi bi-journal-text text-info"></i> Bitácora
        </a>
        <?php endif; ?>

        <?php if (Auth::tienePermiso('productos.ver') || Auth::tienePermiso('proveedores.ver') || Auth::tienePermiso('mecanicos.ver') || Auth::tienePermiso('servicios.ver') || Auth::tienePermiso('categorias.ver') || Auth::tienePermiso('unidades.ver')): ?>
        <div class="sidebar-section mt-2">Catálogos</div>
        <?php if (Auth::tienePermiso('productos.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=productos" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'productos' ? 'active' : '' ?>">
            <i class="bi bi-box-seam"></i> Productos
        </a>
        <?php endif; ?>
        <?php if (Auth::tienePermiso('proveedores.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=proveedores" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'proveedores' ? 'active' : '' ?>">
            <i class="bi bi-truck"></i> Proveedores
        </a>
        <?php endif; ?>
        <?php if (Auth::tienePermiso('mecanicos.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=mecanicos" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'mecanicos' ? 'active' : '' ?>">
            <i class="bi bi-person-gear"></i> Mecánicos
        </a>
        <?php endif; ?>
        <?php if (Auth::tienePermiso('servicios.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=servicios" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'servicios' ? 'active' : '' ?>">
            <i class="bi bi-tools"></i> Servicios
        </a>
        <?php endif; ?>
        <?php if (Auth::tienePermiso('categorias.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=categorias" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'categorias' ? 'active' : '' ?>">
            <i class="bi bi-tags"></i> Categorías
        </a>
        <?php endif; ?>
        <?php if (Auth::tienePermiso('unidades.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=unidades" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'unidades' ? 'active' : '' ?>">
            <i class="bi bi-rulers"></i> Unidades
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (Auth::tienePermiso('reportes.ver')): ?>
        <div class="sidebar-section mt-2">Reportes</div>
        <a href="<?= $appUrl ?>/?modulo=reportes&accion=stock" class="sidebar-link <?= (($_GET['modulo'] ?? '') === 'reportes' && ($_GET['accion'] ?? '') === 'stock') ? 'active' : '' ?>">
            <i class="bi bi-table"></i> Stock actual
        </a>
        <a href="<?= $appUrl ?>/?modulo=reportes&accion=movimientos" class="sidebar-link <?= (($_GET['modulo'] ?? '') === 'reportes' && ($_GET['accion'] ?? '') === 'movimientos') ? 'active' : '' ?>">
            <i class="bi bi-clock-history"></i> Movimientos
        </a>
        <a href="<?= $appUrl ?>/?modulo=reportes&accion=alertas" class="sidebar-link <?= (($_GET['modulo'] ?? '') === 'reportes' && ($_GET['accion'] ?? '') === 'alertas') ? 'active' : '' ?>">
            <i class="bi bi-exclamation-triangle text-warning"></i> Alertas
        </a>
        <?php endif; ?>

        <?php if ($usuario['rol'] === 'admin'): ?>
        <div class="sidebar-section mt-2">Administración</div>
        <a href="<?= $appUrl ?>/?modulo=sucursales" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'sucursales' ? 'active' : '' ?>">
            <i class="bi bi-building"></i> Sucursales
        </a>
        <a href="<?= $appUrl ?>/?modulo=usuarios" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'usuarios' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Usuarios
        </a>
        <a href="<?= $appUrl ?>/?modulo=empresa" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'empresa' ? 'active' : '' ?>">
            <i class="bi bi-building-gear"></i> Datos de empresa
        </a>
        <a href="<?= $appUrl ?>/?modulo=backups" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'backups' ? 'active' : '' ?>">
            <i class="bi bi-database-fill-gear"></i> Respaldos BD
        </a>
        <a href="<?= $appUrl ?>/?modulo=basedatos" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'basedatos' ? 'active' : '' ?>">
            <i class="bi bi-hdd-stack"></i> Base de datos
        </a>
        <?php endif; ?>
    </nav>
</div>

<!-- Contenido principal -->
<div id="main-content" class="main-content">
    <!-- Flash messages -->
    <?php foreach ($flash as $tipo => $mensajes): ?>
        <?php foreach ($mensajes as $msg): ?>
            <?php
            $alertClass = match($tipo) {
                'success' => 'alert-success',
                'error'   => 'alert-danger',
                'warning' => 'alert-warning',
                default   => 'alert-info',
            };
            $icon = match($tipo) {
                'success' => 'bi-check-circle',
                'error'   => 'bi-x-circle',
                'warning' => 'bi-exclamation-triangle',
                default   => 'bi-info-circle',
            };
            ?>
            <div class="alert <?= $alertClass ?> alert-dismissible fade show mb-3" role="alert">
                <i class="bi <?= $icon ?> me-1"></i>
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <!-- Vista del módulo -->
    <?php require $vistaPath; ?>
</div>

<script>const APP_URL = '<?= $appUrl ?>';</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="<?= $appUrl ?>/assets/js/app.js?v=2"></script>
<?php if (isset($scriptExtra)) echo $scriptExtra; ?>

<?php if (!Auth::estaImpersonando() && $usuario['rol'] === 'admin'): ?>
<?php
$db = Database::getInstance();
$usersImp = $db->query(
    'SELECT u.id, u.nombre, u.email, u.rol, s.nombre AS sucursal_nombre
     FROM usuarios u
     LEFT JOIN sucursales s ON s.id = u.sucursal_id
     WHERE u.activo = 1 AND u.rol != "' . ROL_ADMIN . '"
     ORDER BY u.nombre'
)->fetchAll();
?>
<div class="modal fade" id="modalUsarComo" tabindex="-1" aria-labelledby="modalUsarComoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header bg-info bg-opacity-10 border-0 pb-2">
                <h6 class="modal-title fw-bold" id="modalUsarComoLabel">
                    <i class="bi bi-person-bounding-box me-1 text-info"></i>Usar como…
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 pb-2">
                    <input type="text" class="form-control form-control-sm" id="filtroUsarComo"
                           placeholder="Buscar por nombre o correo…" autocomplete="off">
                </div>
                <ul class="list-group list-group-flush" id="listaUsarComo">
                    <?php if (empty($usersImp)): ?>
                    <li class="list-group-item text-muted text-center py-4 small">No hay usuarios disponibles</li>
                    <?php else: ?>
                    <?php foreach ($usersImp as $u): ?>
                    <li class="list-group-item list-group-item-action py-2 px-3 user-imp-item"
                        data-search="<?= strtolower(htmlspecialchars($u['nombre'] . ' ' . $u['email'])) ?>">
                        <form method="POST" action="<?= $appUrl ?>/?modulo=auth&accion=impersonar"
                              class="d-flex align-items-center gap-2 m-0">
                            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                            <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-truncate"><?= htmlspecialchars($u['nombre']) ?></div>
                                <div class="text-muted small text-truncate">
                                    <?= htmlspecialchars($u['email']) ?>
                                    <?php if ($u['sucursal_nombre']): ?>
                                    · <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($u['sucursal_nombre']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="badge <?= $u['rol'] === 'almacenista' ? 'bg-primary' : 'bg-secondary' ?> me-1"><?= ucfirst($u['rol']) ?></span>
                            <button type="submit" class="btn btn-sm btn-outline-info flex-shrink-0" title="Usar como este usuario">
                                <i class="bi bi-box-arrow-in-right"></i>
                            </button>
                        </form>
                    </li>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('filtroUsarComo')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.user-imp-item').forEach(function (li) {
        li.style.display = li.dataset.search.includes(q) ? '' : 'none';
    });
});
</script>
<?php endif; ?>
</body>
</html>
