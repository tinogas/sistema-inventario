<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titulo) ? htmlspecialchars($titulo) . ' — ' : '' ?><?= htmlspecialchars($appName) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $appUrl ?>/assets/css/app.css">
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
            <button class="btn btn-sm btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i>
                <?= htmlspecialchars($usuario['nombre']) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text text-muted small"><?= htmlspecialchars($usuario['email']) ?></span></li>
                <li><span class="dropdown-item-text text-muted small"><?= ucfirst($usuario['rol']) ?></span></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?= $appUrl ?>/?modulo=auth&accion=logout">
                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<div id="sidebar" class="sidebar">
    <nav class="sidebar-nav">
        <div class="sidebar-section">Inventario</div>
        <a href="<?= $appUrl ?>/?modulo=dashboard" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="<?= $appUrl ?>/?modulo=entradas" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'entradas' ? 'active' : '' ?>">
            <i class="bi bi-box-arrow-in-down-right text-success"></i> Entradas
        </a>
        <a href="<?= $appUrl ?>/?modulo=salidas" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'salidas' ? 'active' : '' ?>">
            <i class="bi bi-box-arrow-up-right text-danger"></i> Salidas
        </a>
        <a href="<?= $appUrl ?>/?modulo=traspasos" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'traspasos' ? 'active' : '' ?>">
            <i class="bi bi-arrow-left-right text-info"></i> Traspasos
        </a>

        <div class="sidebar-section mt-2">Catálogos</div>
        <a href="<?= $appUrl ?>/?modulo=productos" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'productos' ? 'active' : '' ?>">
            <i class="bi bi-box-seam"></i> Productos
        </a>
        <a href="<?= $appUrl ?>/?modulo=proveedores" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'proveedores' ? 'active' : '' ?>">
            <i class="bi bi-truck"></i> Proveedores
        </a>
        <a href="<?= $appUrl ?>/?modulo=mecanicos" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'mecanicos' ? 'active' : '' ?>">
            <i class="bi bi-person-gear"></i> Mecánicos
        </a>
        <a href="<?= $appUrl ?>/?modulo=servicios" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'servicios' ? 'active' : '' ?>">
            <i class="bi bi-tools"></i> Servicios
        </a>
        <a href="<?= $appUrl ?>/?modulo=categorias" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'categorias' ? 'active' : '' ?>">
            <i class="bi bi-tags"></i> Categorías
        </a>
        <a href="<?= $appUrl ?>/?modulo=unidades" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'unidades' ? 'active' : '' ?>">
            <i class="bi bi-rulers"></i> Unidades
        </a>

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

        <?php if ($usuario['rol'] === 'admin'): ?>
        <div class="sidebar-section mt-2">Administración</div>
        <a href="<?= $appUrl ?>/?modulo=sucursales" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'sucursales' ? 'active' : '' ?>">
            <i class="bi bi-building"></i> Sucursales
        </a>
        <a href="<?= $appUrl ?>/?modulo=usuarios" class="sidebar-link <?= ($_GET['modulo'] ?? '') === 'usuarios' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Usuarios
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
    <?php require_once $vistaPath; ?>
</div>

<script>const APP_URL = '<?= $appUrl ?>';</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="<?= $appUrl ?>/assets/js/app.js"></script>
<?php if (isset($scriptExtra)) echo $scriptExtra; ?>
</body>
</html>
