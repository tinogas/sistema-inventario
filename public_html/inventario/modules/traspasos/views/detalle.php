<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-arrow-left-right text-info me-2"></i>
            Traspaso — <?= htmlspecialchars($traspaso['folio_salida']) ?>
        </h4>
        <span class="badge badge-estado-<?= htmlspecialchars($traspaso['traspaso_estado']) ?> mt-1">
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
                        <span class="badge badge-estado-<?= htmlspecialchars($traspaso['traspaso_estado']) ?>">
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
            <?php
                $enTransito = $traspaso['traspaso_estado'] === 'en_transito';
                $recibido   = $traspaso['traspaso_estado'] === 'recibido';
                $puedeConfirmar = $enTransito && Auth::tienePermiso('traspasos.confirmar');
            ?>
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Código</th><th>Producto</th><th>Unidad</th>
                        <th class="text-end">Enviada</th>
                        <?php if ($puedeConfirmar): ?>
                        <th class="text-end">Cantidad recibida</th>
                        <?php elseif ($recibido): ?>
                        <th class="text-end">Recibida</th>
                        <th class="text-end">Devuelta a origen</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($partidas as $i => $p): ?>
                <?php $faltante = $recibido && $p['devuelta'] !== null && $p['devuelta'] > 0; ?>
                <tr class="<?= $faltante ? 'table-warning' : '' ?>">
                    <td><?= $i+1 ?></td>
                    <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                    <td><?= htmlspecialchars($p['producto_nombre']) ?></td>
                    <td><?= htmlspecialchars($p['unidad']) ?></td>
                    <td class="text-end"><?= number_format($p['enviada'],3) ?></td>
                    <?php if ($puedeConfirmar): ?>
                    <td class="text-end">
                        <input type="number" name="recibido[<?= $p['producto_id'] ?>]"
                               class="form-control form-control-sm text-end recibido-input"
                               style="width:90px;display:inline-block"
                               value="<?= $p['enviada'] ?>" min="0" max="<?= $p['enviada'] ?>" step="any">
                    </td>
                    <?php elseif ($recibido): ?>
                    <td class="text-end fw-semibold"><?= number_format((float)$p['recibida'],3) ?></td>
                    <td class="text-end <?= $faltante ? 'text-danger fw-semibold' : 'text-muted' ?>">
                        <?= number_format((float)$p['devuelta'],3) ?>
                        <?php if ($faltante): ?><i class="bi bi-arrow-return-left ms-1" title="Devuelto al origen"></i><?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($recibido && array_filter($partidas, fn($p) => $p['devuelta'] > 0)): ?>
            <div class="alert alert-warning mb-0 rounded-0 border-0 small">
                <i class="bi bi-info-circle me-1"></i>
                Recepción parcial: las cantidades no recibidas se devolvieron automáticamente al stock de la sucursal de origen.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Acciones según estado -->
<?php if ($traspaso['traspaso_estado'] === 'en_transito'): ?>
<div class="d-flex gap-2 justify-content-end">

    <?php if (Auth::tienePermiso('traspasos.confirmar')): ?>
    <form method="POST" action="<?= $appUrl ?>/?modulo=traspasos&accion=confirmar_recepcion">
        <input type="hidden" name="_csrf" value="<?= $csrf ?>">
        <input type="hidden" name="traspaso_id" value="<?= (int)$traspaso['id'] ?>">
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
        <input type="hidden" name="traspaso_id" value="<?= (int)$traspaso['id'] ?>">
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
