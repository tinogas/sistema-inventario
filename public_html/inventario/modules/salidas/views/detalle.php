<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-box-arrow-up-right text-danger me-2"></i>
            Salida <?= htmlspecialchars($salida['folio']) ?>
        </h4>
        <span class="badge badge-estado-<?= htmlspecialchars($salida['estado'], ENT_QUOTES, 'UTF-8') ?> mt-1"><?= htmlspecialchars(ucfirst($salida['estado']), ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <a href="<?= $appUrl ?>/?modulo=salidas" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th>Folio</th><td><code><?= htmlspecialchars($salida['folio']) ?></code></td></tr>
                    <tr><th>Sucursal</th><td><?= htmlspecialchars($salida['sucursal_nombre']) ?></td></tr>
                    <tr><th>Mecánico</th><td><?= htmlspecialchars($salida['mecanico_nombre']) ?></td></tr>
                    <tr><th>Servicio</th><td><?= htmlspecialchars($salida['servicio_nombre']) ?></td></tr>
                    <tr><th>Ref. Proneg</th><td><?= htmlspecialchars($salida['referencia_factura'] ?: '—') ?></td></tr>
                    <tr><th>Registrado por</th><td><?= htmlspecialchars($salida['usuario_nombre']) ?></td></tr>
                    <tr><th>Fecha</th><td><?= date('d/m/Y H:i', strtotime($salida['created_at'])) ?></td></tr>
                    <?php if ($salida['notas']): ?>
                    <tr><th>Notas</th><td><?= htmlspecialchars($salida['notas']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
        Partidas <span class="badge bg-secondary ms-1"><?= count($partidas) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm tabla-partidas mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Código</th><th>Producto</th><th>Unidad</th>
                        <th class="text-end">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($partidas as $i => $p): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                    <td>
                        <a href="<?= $appUrl ?>/?modulo=productos&accion=detalle&id=<?= $p['producto_id'] ?>" class="text-decoration-none">
                            <?= htmlspecialchars($p['producto_nombre']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($p['unidad']) ?></td>
                    <td class="text-end fw-semibold"><?= number_format($p['cantidad'],3) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
