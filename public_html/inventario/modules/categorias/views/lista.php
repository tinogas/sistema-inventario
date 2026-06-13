<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-tags me-2 text-primary"></i>Categorías</h1>
    <?php if (Auth::esAdmin()): ?>
    <a href="<?= $appUrl ?>/?modulo=categorias&accion=nuevo" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nueva categoría
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
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th style="width:90px" class="text-center">Activa</th>
                        <?php if (Auth::esAdmin()): ?>
                        <th style="width:130px" class="text-center">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categorias)): ?>
                    <tr>
                        <td colspan="<?= Auth::esAdmin() ? 5 : 4 ?>" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            No hay categorías registradas.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td class="text-muted small"><?= $cat['id'] ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($cat['nombre']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($cat['descripcion'] ?? '—') ?></td>
                        <td class="text-center">
                            <?php if ($cat['activa']): ?>
                                <span class="badge bg-success">Sí</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <?php if (Auth::esAdmin()): ?>
                        <td class="text-center">
                            <a href="<?= $appUrl ?>/?modulo=categorias&accion=editar&id=<?= $cat['id'] ?>"
                               class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($cat['activa']): ?>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Desactivar"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEliminar"
                                    data-id="<?= $cat['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($cat['nombre']) ?>">
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
</div>

<?php if (Auth::esAdmin()): ?>
<!-- Modal confirmar eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirmar desactivación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Deseas desactivar la categoría <strong id="modalNombre"></strong>?
                <p class="text-muted small mt-2 mb-0">No se puede desactivar si tiene productos asociados.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="<?= $appUrl ?>/?modulo=categorias&accion=eliminar" class="d-inline">
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
    document.getElementById('modalId').value    = btn.dataset.id;
    document.getElementById('modalNombre').textContent = btn.dataset.nombre;
});
</script>
<?php endif; ?>
