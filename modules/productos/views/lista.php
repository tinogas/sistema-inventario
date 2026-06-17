<?php
/**
 * Vista: Lista paginada de productos
 * Variables disponibles: $paginacion, $buscar, $sucursal_id, $appUrl, $csrf, $usuario
 */
$filas        = $paginacion['filas'];
$totalPaginas = $paginacion['total_paginas'];
$paginaActual = $paginacion['pagina'];
$totalRegistros = $paginacion['total'];
?>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Productos</h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= $appUrl ?>/?modulo=productos&accion=exportar_csv<?= $sucursal_id ? '&sucursal_id=' . (int) $sucursal_id : '' ?>"
           class="btn btn-sm btn-outline-secondary" title="Exportar CSV">
            <i class="bi bi-filetype-csv me-1"></i>CSV
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()" title="Imprimir">
            <i class="bi bi-printer"></i>
        </button>
        <?php if (Auth::tienePermiso('productos.editar')): ?>
        <a href="<?= $appUrl ?>/?modulo=productos&accion=nuevo" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Nuevo producto
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Buscador -->
<form method="GET" action="<?= $appUrl ?>/" class="row g-2 mb-3" id="formBuscar">
    <input type="hidden" name="modulo" value="productos">
    <?php if ($sucursal_id): ?>
    <input type="hidden" name="sucursal_id" value="<?= (int) $sucursal_id ?>">
    <?php endif; ?>
    <div class="col-12 col-md-7 col-lg-5">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="buscar" id="campoBuscar"
                   class="form-control"
                   placeholder="Código, nombre o categoría…"
                   value="<?= htmlspecialchars($buscar) ?>"
                   autocomplete="off">
            <?php if ($buscar !== ''): ?>
            <a href="<?= $appUrl ?>/?modulo=productos<?= $sucursal_id ? '&sucursal_id=' . (int) $sucursal_id : '' ?>"
               class="btn btn-outline-secondary" title="Limpiar búsqueda">
                <i class="bi bi-x-lg"></i>
            </a>
            <?php endif; ?>
            <button type="submit" class="btn btn-outline-primary">Buscar</button>
        </div>
    </div>
</form>

<!-- Tabla -->
<div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-2">
        <thead class="table-dark">
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Unidad</th>
                <th class="text-end">Stock<?= $sucursal_id ? '' : ' total' ?></th>
                <th class="text-end">Precio venta</th>
                <th class="text-center">Activo</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($filas)): ?>
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                    <?= $buscar !== '' ? 'Sin resultados para "' . htmlspecialchars($buscar) . '"' : 'No hay productos registrados.' ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($filas as $p): ?>
            <?php
                $stockBajo = (float) $p['stock_actual'] < (float) $p['stock_minimo'];
            ?>
            <tr>
                <td>
                    <span class="font-monospace small"><?= htmlspecialchars($p['codigo']) ?></span>
                    <?php if (!empty($p['codigo_alterno'])): ?>
                    <br><span class="text-muted font-monospace" style="font-size:.75rem"><?= htmlspecialchars($p['codigo_alterno']) ?></span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['categoria'] ?? '—') ?></td>
                <td><?= htmlspecialchars($p['unidad_clave'] ?? '—') ?></td>
                <td class="text-end">
                    <span class="fw-semibold <?= $stockBajo ? 'text-danger' : 'text-success' ?>">
                        <?= number_format((float) $p['stock_actual'], 2) ?>
                    </span>
                    <?php if ($stockBajo): ?>
                    <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Stock bajo mínimo (<?= number_format((float) $p['stock_minimo'], 2) ?>)"></i>
                    <?php endif; ?>
                </td>
                <td class="text-end">$<?= number_format((float) $p['precio_venta'], 2) ?></td>
                <td class="text-center">
                    <?php if ($p['activo']): ?>
                        <span class="badge bg-success">Sí</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">No</span>
                    <?php endif; ?>
                </td>
                <td class="text-center text-nowrap">
                    <a href="<?= $appUrl ?>/?modulo=productos&accion=detalle&id=<?= $p['id'] ?>"
                       class="btn btn-sm btn-outline-info" title="Ver detalle">
                        <i class="bi bi-eye"></i>
                    </a>
                    <?php if (Auth::tienePermiso('productos.editar')): ?>
                    <a href="<?= $appUrl ?>/?modulo=productos&accion=editar&id=<?= $p['id'] ?>"
                       class="btn btn-sm btn-outline-secondary" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (Auth::esAdmin()): ?>
                    <form method="POST" action="<?= $appUrl ?>/?modulo=productos&accion=eliminar"
                          class="d-inline formEliminar">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="id"   value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                title="Eliminar"
                                data-nombre="<?= htmlspecialchars($p['nombre']) ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Info y paginación -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <small class="text-muted">
        <?= number_format($totalRegistros) ?> producto<?= $totalRegistros !== 1 ? 's' : '' ?> encontrado<?= $totalRegistros !== 1 ? 's' : '' ?>
    </small>

    <?php if ($totalPaginas > 1): ?>
    <nav aria-label="Paginación de productos">
        <ul class="pagination pagination-sm mb-0">
            <!-- Anterior -->
            <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                <a class="page-link"
                   href="<?= $appUrl ?>/?modulo=productos&buscar=<?= urlencode($buscar) ?>&pagina=<?= $paginaActual - 1 ?><?= $sucursal_id ? '&sucursal_id=' . (int) $sucursal_id : '' ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>

            <?php
            $rango = 2;
            $inicio = max(1, $paginaActual - $rango);
            $fin    = min($totalPaginas, $paginaActual + $rango);
            if ($inicio > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $appUrl ?>/?modulo=productos&buscar=<?= urlencode($buscar) ?>&pagina=1<?= $sucursal_id ? '&sucursal_id=' . (int) $sucursal_id : '' ?>">1</a>
                </li>
                <?php if ($inicio > 2): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $inicio; $i <= $fin; $i++): ?>
            <li class="page-item <?= $i === $paginaActual ? 'active' : '' ?>">
                <a class="page-link"
                   href="<?= $appUrl ?>/?modulo=productos&buscar=<?= urlencode($buscar) ?>&pagina=<?= $i ?><?= $sucursal_id ? '&sucursal_id=' . (int) $sucursal_id : '' ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>

            <?php if ($fin < $totalPaginas): ?>
                <?php if ($fin < $totalPaginas - 1): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $appUrl ?>/?modulo=productos&buscar=<?= urlencode($buscar) ?>&pagina=<?= $totalPaginas ?><?= $sucursal_id ? '&sucursal_id=' . (int) $sucursal_id : '' ?>"><?= $totalPaginas ?></a>
                </li>
            <?php endif; ?>

            <!-- Siguiente -->
            <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
                <a class="page-link"
                   href="<?= $appUrl ?>/?modulo=productos&buscar=<?= urlencode($buscar) ?>&pagina=<?= $paginaActual + 1 ?><?= $sucursal_id ? '&sucursal_id=' . (int) $sucursal_id : '' ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<script>
// Confirmación antes de eliminar
document.querySelectorAll('.formEliminar').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        const nombre = form.querySelector('[data-nombre]').dataset.nombre;
        if (!confirm('¿Eliminar el producto "' + nombre + '"?\n\nEsta acción no puede deshacerse.')) {
            e.preventDefault();
        }
    });
});

// Búsqueda en tiempo real: esperar 500 ms tras dejar de escribir
(function() {
    let timer;
    const input = document.getElementById('campoBuscar');
    if (!input) return;
    input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            document.getElementById('formBuscar').submit();
        }, 500);
    });
})();
</script>
