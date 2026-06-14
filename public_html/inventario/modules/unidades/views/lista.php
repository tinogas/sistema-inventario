<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-rulers me-2 text-primary"></i>Unidades de medida</h1>
    <?php if (Auth::esAdmin()): ?>
    <a href="<?= $appUrl ?>/?modulo=unidades&accion=nuevo" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nueva unidad
    </a>
    <?php endif; ?>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px">#</th>
                        <th style="width:120px">Clave</th>
                        <th>Nombre</th>
                        <?php if (Auth::esAdmin()): ?>
                        <th style="width:130px" class="text-center">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($unidades)): ?>
                    <tr>
                        <td colspan="<?= Auth::esAdmin() ? 4 : 3 ?>" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            No hay unidades registradas.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($unidades as $uni): ?>
                    <tr>
                        <td class="text-muted small"><?= htmlspecialchars((string)$uni['id']) ?></td>
                        <td><span class="badge bg-secondary fs-6"><?= htmlspecialchars($uni['clave']) ?></span></td>
                        <td class="fw-semibold"><?= htmlspecialchars($uni['nombre']) ?></td>
                        <?php if (Auth::esAdmin()): ?>
                        <td class="text-center">
                            <a href="<?= $appUrl ?>/?modulo=unidades&accion=editar&id=<?= htmlspecialchars((string)$uni['id']) ?>"
                               class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Eliminar"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEliminar"
                                    data-id="<?= htmlspecialchars((string)$uni['id']) ?>"
                                    data-nombre="<?= htmlspecialchars($uni['clave'] . ' — ' . $uni['nombre']) ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (Auth::esAdmin()): ?>
<!-- Modal confirmar eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Deseas eliminar la unidad <strong id="modalNombre"></strong>?
                <p class="text-muted small mt-2 mb-0">No se puede eliminar si tiene productos asociados.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="<?= $appUrl ?>/?modulo=unidades&accion=eliminar" class="d-inline">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="id" id="modalId">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Eliminar
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
