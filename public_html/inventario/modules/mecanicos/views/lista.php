<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-person-gear me-2 text-primary"></i>Mecánicos
    </h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= $appUrl ?>/?modulo=mecanicos&accion=exportar_csv"
           class="btn btn-sm btn-outline-secondary" title="Exportar CSV">
            <i class="bi bi-filetype-csv me-1"></i>CSV
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()" title="Imprimir">
            <i class="bi bi-printer"></i>
        </button>
        <?php if (Auth::tienePermiso('mecanicos.editar')): ?>
        <a href="<?= $appUrl ?>/?modulo=mecanicos&accion=nuevo" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nuevo mecánico
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($mecanicos)): ?>
            <p class="text-muted text-center py-5 mb-0">
                <i class="bi bi-person-x fs-3 d-block mb-2"></i>
                No hay mecánicos registrados.
            </p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Sucursal</th>
                        <th>Teléfono</th>
                        <th class="text-center">Activo</th>
                        <?php if (Auth::tienePermiso('mecanicos.editar')): ?>
                        <th class="text-end">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mecanicos as $m): ?>
                    <tr>
                        <td class="text-muted small"><?= $m['id'] ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($m['nombre']) ?></td>
                        <td>
                            <span class="badge bg-secondary">
                                <?= htmlspecialchars($m['sucursal_nombre']) ?>
                            </span>
                        </td>
                        <td><?= $m['telefono'] ? htmlspecialchars($m['telefono']) : '<span class="text-muted">—</span>' ?></td>
                        <td class="text-center">
                            <?php if ($m['activo']): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>Sí
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-x-circle me-1"></i>No
                                </span>
                            <?php endif; ?>
                        </td>
                        <?php if (Auth::tienePermiso('mecanicos.editar')): ?>
                        <td class="text-end">
                            <a href="<?= $appUrl ?>/?modulo=mecanicos&accion=editar&id=<?= $m['id'] ?>"
                               class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($m['activo']): ?>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Dar de baja"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEliminar"
                                    data-id="<?= $m['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($m['nombre'], ENT_QUOTES) ?>">
                                <i class="bi bi-person-dash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (Auth::tienePermiso('mecanicos.editar')): ?>
<!-- Modal confirmación baja -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalEliminarLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirmar baja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Deseas dar de baja al mecánico <strong id="modalNombre"></strong>?
                <p class="text-muted small mt-2 mb-0">El registro no se eliminará, solo se marcará como inactivo.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="<?= $appUrl ?>/?modulo=mecanicos&accion=eliminar" id="formEliminar">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="id" id="inputIdEliminar" value="">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-person-dash me-1"></i>Dar de baja
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('modalEliminar').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('modalNombre').textContent    = btn.dataset.nombre;
    document.getElementById('inputIdEliminar').value      = btn.dataset.id;
});
</script>
<?php endif; ?>
