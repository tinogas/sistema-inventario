<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-arrow-left-right text-info me-2"></i>
            Traspaso — <?= htmlspecialchars($traspaso['folio_salida']) ?>
        </h4>
        <span class="badge badge-estado-<?= $traspaso['traspaso_estado'] ?> mt-1">
            <?= ucwords(str_replace('_',' ', $traspaso['traspaso_estado'])) ?>
        </span>
    </div>
    <a href="<?= $appUrl ?>/?modulo=traspasos" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th>Folio</th><td><code><?= htmlspecialchars($traspaso['folio_salida']) ?></code></td></tr>
                    <tr><th>Origen</th><td><?= htmlspecialchars($traspaso['sucursal_origen']) ?></td></tr>
                    <tr><th>Destino</th><td><?= htmlspecialchars($traspaso['sucursal_destino']) ?></td></tr>
                    <tr><th>Estado</th><td>
                        <span class="badge badge-estado-<?= $traspaso['traspaso_estado'] ?>">
                            <?= ucwords(str_replace('_',' ', $traspaso['traspaso_estado'])) ?>
                        </span>
                    </td></tr>
                    <tr><th>Enviado por</th><td><?= htmlspecialchars($traspaso['usuario']) ?></td></tr>
                    <tr><th>Fecha envío</th><td><?= date('d/m/Y H:i', strtotime($traspaso['fecha_envio'])) ?></td></tr>
                    <?php if ($traspaso['fecha_recepcion']): ?>
                    <tr><th>Recepción</th><td><?= date('d/m/Y H:i', strtotime($traspaso['fecha_recepcion'])) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($traspaso['notas']): ?>
                    <tr><th>Notas</th><td><?= htmlspecialchars($traspaso['notas']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de partidas -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
        Productos <span class="badge bg-secondary ms-1"><?= count($partidas) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Código</th><th>Producto</th><th>Unidad</th><th class="text-end">Cantidad enviada</th>
                    <?php if ($traspaso['traspaso_estado'] === 'en_transito' && Auth::tienePermiso('traspasos.confirmar')): ?>
                    <th class="text-end">Cantidad recibida</th>
                    <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($partidas as $i => $p): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                    <td><?= htmlspecialchars($p['producto_nombre']) ?></td>
                    <td><?= htmlspecialchars($p['unidad']) ?></td>
                    <td class="text-end"><?= number_format($p['cantidad'],3) ?></td>
                    <?php if ($traspaso['traspaso_estado'] === 'en_transito' && Auth::tienePermiso('traspasos.confirmar')): ?>
                    <td class="text-end">
                        <input type="number" name="recibido[<?= $p['producto_id'] ?>]"
                               class="form-control form-control-sm text-end recibido-input"
                               style="width:90px;display:inline-block"
                               value="<?= $p['cantidad'] ?>" min="0" step="any">
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Acciones según estado -->
<?php if ($traspaso['traspaso_estado'] === 'en_transito'): ?>
<div class="d-flex gap-2 justify-content-end">

    <?php if (Auth::tienePermiso('traspasos.confirmar')): ?>
    <form method="POST" action="<?= $appUrl ?>/?modulo=traspasos&accion=confirmar_recepcion">
        <input type="hidden" name="_csrf" value="<?= $csrf ?>">
        <input type="hidden" name="traspaso_id" value="<?= $traspaso['id'] ?>">
        <?php foreach ($partidas as $p): ?>
        <input type="hidden" class="recibido-hidden-<?= $p['producto_id'] ?>" name="recibido[<?= $p['producto_id'] ?>]" value="<?= $p['cantidad'] ?>">
        <?php endforeach; ?>
        <button type="submit" class="btn btn-success" onclick="copiarRecibidos()">
            <i class="bi bi-check2-circle me-1"></i> Confirmar recepción
        </button>
    </form>
    <?php endif; ?>

    <?php if (Auth::tienePermiso('traspasos.crear')): ?>
    <form method="POST" action="<?= $appUrl ?>/?modulo=traspasos&accion=cancelar"
          onsubmit="return confirm('¿Cancelar este traspaso? Se revertirá el stock en origen.')">
        <input type="hidden" name="_csrf" value="<?= $csrf ?>">
        <input type="hidden" name="traspaso_id" value="<?= $traspaso['id'] ?>">
        <button type="submit" class="btn btn-outline-danger">
            <i class="bi bi-x-circle me-1"></i> Cancelar traspaso
        </button>
    </form>
    <?php endif; ?>
</div>

<script>
function copiarRecibidos() {
    document.querySelectorAll('.recibido-input').forEach(function(input) {
        const pid = input.name.match(/\[(\d+)\]/)[1];
        const hidden = document.querySelector('.recibido-hidden-' + pid);
        if (hidden) hidden.value = input.value;
    });
}
</script>
<?php endif; ?>
