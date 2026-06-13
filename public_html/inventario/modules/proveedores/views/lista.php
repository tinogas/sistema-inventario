<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-truck me-2 text-primary"></i>Proveedores</h1>
    <?php if (Auth::esAdmin()): ?>
    <a href="<?= $appUrl ?>/?modulo=proveedores&accion=nuevo" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nuevo proveedor
    </a>
    <?php endif; ?>
</div>

<!-- Buscador -->
<form method="GET" action="<?= $appUrl ?>/" class="mb-3">
    <input type="hidden" name="modulo" value="proveedores">
    <div class="input-group" style="max-width:420px">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text"
               name="buscar"
               class="form-control"
               placeholder="Buscar por nombre o RFC…"
               value="<?= htmlspecialchars($buscar) ?>">
        <button type="submit" class="btn btn-outline-primary">Buscar</button>
        <?php if ($buscar !== ''): ?>
        <a href="<?= $appUrl ?>/?modulo=proveedores" class="btn btn-outline-secondary" title="Limpiar búsqueda">
            <i class="bi bi-x-lg"></i>
        </a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Razón social</th>
                        <th style="width:130px">RFC</th>
                        <th>Contacto</th>
                        <th style="width:130px">Teléfono</th>
                        <th style="width:80px" class="text-center">Activo</th>
                        <?php if (Auth::esAdmin()): ?>
                        <th style="width:130px" class="text-center">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($result['filas'])): ?>
                    <tr>
                        <td colspan="<?= Auth::esAdmin() ? 6 : 5 ?>" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            <?= $buscar !== '' ? 'No se encontraron resultados para la búsqueda.' : 'No hay proveedores registrados.' ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($result['filas'] as $prov): ?>
                    <tr class="<?= !$prov['activo'] ? 'table-secondary text-muted' : '' ?>">
                        <td class="fw-semibold">
                            <?= htmlspecialchars($prov['razon_social']) ?>
                            <?php if (!$prov['activo']): ?>
                                <span class="badge bg-secondary ms-1">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="font-monospace small"><?= htmlspecialchars($prov['rfc'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($prov['contacto'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($prov['telefono'] ?? '—') ?></td>
                        <td class="text-center">
                            <?php if ($prov['activo']): ?>
                                <span class="badge bg-success">Sí</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <?php if (Auth::esAdmin()): ?>
                        <td class="text-center">
                            <a href="<?= $appUrl ?>/?modulo=proveedores&accion=editar&id=<?= $prov['id'] ?>"
                               class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($prov['activo']): ?>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Desactivar"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEliminar"
                                    data-id="<?= $prov['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($prov['razon_social']) ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($result['total_paginas'] > 1): ?>
    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">
            Mostrando <?= count($result['filas']) ?> de <?= $result['total'] ?> proveedores
            (página <?= $result['pagina'] ?> de <?= $result['total_paginas'] ?>)
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php if ($result['pagina'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $appUrl ?>/?modulo=proveedores&buscar=<?= urlencode($buscar) ?>&pagina=<?= $result['pagina'] - 1 ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>

                <?php
                $desde = max(1, $result['pagina'] - 2);
                $hasta  = min($result['total_paginas'], $result['pagina'] + 2);
                for ($p = $desde; $p <= $hasta; $p++):
                ?>
                <li class="page-item <?= $p === $result['pagina'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $appUrl ?>/?modulo=proveedores&buscar=<?= urlencode($buscar) ?>&pagina=<?= $p ?>">
                        <?= $p ?>
                    </a>
                </li>
                <?php endfor; ?>

                <?php if ($result['pagina'] < $result['total_paginas']): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $appUrl ?>/?modulo=proveedores&buscar=<?= urlencode($buscar) ?>&pagina=<?= $result['pagina'] + 1 ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php if (Auth::esAdmin()): ?>
<!-- Modal confirmar desactivación -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirmar desactivación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Deseas desactivar al proveedor <strong id="modalNombre"></strong>?
                <p class="text-muted small mt-2 mb-0">El registro se conservará pero no aparecerá en búsquedas activas.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="<?= $appUrl ?>/?modulo=proveedores&accion=eliminar" class="d-inline">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="id" id="modalId">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Desactivar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('modalEliminar').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    document.getElementById('modalId').value           = btn.dataset.id;
    document.getElementById('modalNombre').textContent = btn.dataset.nombre;
});
</script>
<?php endif; ?>
