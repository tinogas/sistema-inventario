<?php
/** Vista: Respaldos de la base de datos. Variables: $backups, $titulo, $appUrl, $csrf */
function _bk_tamano(int $bytes): string {
    if ($bytes <= 0) return '—';
    $u = ['B','KB','MB','GB'];
    $i = (int) floor(log($bytes, 1024));
    $i = min($i, count($u) - 1);
    return round($bytes / (1024 ** $i), 2) . ' ' . $u[$i];
}
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-database-fill-gear text-primary me-2"></i>Respaldos de la base de datos</h4>
    <form method="POST" action="<?= $appUrl ?>/?modulo=backups&accion=crear"
          onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span>Generando…';">
        <input type="hidden" name="_csrf" value="<?= $csrf ?>">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Generar respaldo ahora
        </button>
    </form>
</div>

<div class="alert alert-info d-flex align-items-start gap-2">
    <i class="bi bi-info-circle fs-5"></i>
    <div class="small">
        Cada respaldo genera un archivo <code>.sql</code> con la estructura y todos los datos de la base
        <strong><?= htmlspecialchars(DB_NAME) ?></strong>. Guarda copias en un lugar seguro (descárgalas).
        Para restaurar, importa el <code>.sql</code> desde phpMyAdmin.
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-clock-history me-1 text-secondary"></i>Historial de respaldos
        <span class="badge bg-secondary ms-1"><?= count($backups) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Archivo</th>
                        <th class="text-end">Tamaño</th>
                        <th class="text-center">Tablas</th>
                        <th class="text-end">Registros</th>
                        <th>Usuario</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($backups)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox d-block fs-3 mb-1"></i>Aún no se han generado respaldos.
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($backups as $b): ?>
                    <tr>
                        <td class="small text-nowrap"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($b['created_at']))) ?></td>
                        <td class="font-monospace small"><?= htmlspecialchars($b['archivo']) ?></td>
                        <td class="text-end"><?= _bk_tamano((int)$b['tamano_bytes']) ?></td>
                        <td class="text-center"><?= (int)$b['num_tablas'] ?></td>
                        <td class="text-end"><?= number_format((int)$b['num_registros']) ?></td>
                        <td class="small"><?= htmlspecialchars($b['usuario_nombre'] ?? '—') ?></td>
                        <td class="text-center">
                            <?php if ($b['estado'] === 'completado'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>OK</span>
                            <?php else: ?>
                                <span class="badge bg-danger" title="<?= htmlspecialchars($b['notas'] ?? '') ?>">
                                    <i class="bi bi-x-circle me-1"></i>Error
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <?php if ($b['estado'] === 'completado' && !empty($b['existe'])): ?>
                            <a href="<?= $appUrl ?>/?modulo=backups&accion=descargar&id=<?= (int)$b['id'] ?>"
                               class="btn btn-sm btn-outline-success" title="Descargar">
                                <i class="bi bi-download"></i>
                            </a>
                            <?php elseif ($b['estado'] === 'completado'): ?>
                            <span class="badge bg-secondary-subtle text-secondary border" title="El archivo ya no está en el servidor">
                                sin archivo
                            </span>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                    data-id="<?= (int)$b['id'] ?>"
                                    data-archivo="<?= htmlspecialchars($b['archivo'], ENT_QUOTES) ?>" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Eliminar respaldo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Eliminar el respaldo <strong id="mArchivo"></strong>? Se borrará el archivo y el registro del historial.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="<?= $appUrl ?>/?modulo=backups&accion=eliminar">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="id" id="mId">
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('modalEliminar').addEventListener('show.bs.modal', function (e) {
    document.getElementById('mArchivo').textContent = e.relatedTarget.dataset.archivo;
    document.getElementById('mId').value = e.relatedTarget.dataset.id;
});
</script>
