<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-tools me-2 text-primary"></i>Servicios
    </h4>
    <?php if (Auth::esAdmin() || Auth::tienePermiso('servicios.ver')): ?>
    <?php if (Auth::esAdmin()): ?>
    <a href="<?= $appUrl ?>/?modulo=servicios&accion=nuevo" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nuevo servicio
    </a>
    <?php endif; ?>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($servicios)): ?>
            <p class="text-muted text-center py-5 mb-0">
                <i class="bi bi-tools fs-3 d-block mb-2"></i>
                No hay servicios registrados.
            </p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-end">Precio</th>
                        <th class="text-center">Activo</th>
                        <?php if (Auth::esAdmin()): ?>
                        <th class="text-end">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicios as $s): ?>
                    <tr>
                        <td class="text-muted small"><?= $s['id'] ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($s['nombre']) ?></td>
                        <td class="text-muted small">
                            <?= $s['descripcion']
                                ? htmlspecialchars(mb_substr($s['descripcion'], 0, 80)) . (mb_strlen($s['descripcion']) > 80 ? '…' : '')
                                : '<span class="text-muted">—</span>' ?>
                        </td>
                        <td class="text-end fw-semibold">
                            $<?= number_format((float)$s['precio'], 2) ?>
                        </td>
                        <td class="text-center">
                            <?php if ($s['activo']): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>Sí
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-x-circle me-1"></i>No
                                </span>
                            <?php endif; ?>
                        </td>
                        <?php if (Auth::esAdmin()): ?>
                        <td class="text-end">
                            <a href="<?= $appUrl ?>/?modulo=servicios&accion=editar&id=<?= $s['id'] ?>"
                               class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Dar de baja"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEliminar"
                                    data-id="<?= $s['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($s['nombre'], ENT_QUOTES) ?>">
                                <i class="bi bi-trash"></i>
                            </button>
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

<?php if (Auth::esAdmin()): ?>
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
                ¿Deseas dar de baja el servicio <strong id="modalNombre"></strong>?
                <p class="text-muted small mt-2 mb-0">El registro no se eliminará, solo se marcará como inactivo.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="<?= $appUrl ?>/?modulo=servicios&accion=eliminar">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="id" id="inputIdEliminar" value="">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Dar de baja
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('modalEliminar').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('modalNombre').textContent = btn.dataset.nombre;
    document.getElementById('inputIdEliminar').value   = btn.dataset.id;
});
</script>
<?php endif; ?>
