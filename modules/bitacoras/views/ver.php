<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="<?= $appUrl ?>/?modulo=bitacoras" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h4 mb-0">
            <i class="bi bi-journal-check me-2 text-info"></i>
            Historial —
            <?= htmlspecialchars($bitacora['marca'] . ' ' . $bitacora['modelo']) ?>
            <?php if ($bitacora['placas']): ?>
            <span class="text-muted fw-normal font-monospace fs-6 ms-1">· <?= htmlspecialchars($bitacora['placas']) ?></span>
            <?php endif; ?>
        </h1>
    </div>
</div>

<!-- Vehículo y Cliente -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-0 pb-0">
                <i class="bi bi-car-front me-1 text-primary"></i> Vehículo
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th style="width:110px">Marca/Modelo</th><td class="fw-semibold"><?= htmlspecialchars($bitacora['marca'] . ' ' . $bitacora['modelo']) ?></td></tr>
                    <tr><th>Año</th><td><?= $bitacora['anio'] ?: '—' ?></td></tr>
                    <tr><th>Placas</th><td class="font-monospace"><?= htmlspecialchars($bitacora['placas'] ?: '—') ?></td></tr>
                    <?php if ($bitacora['numero_serie']): ?>
                    <tr><th>No. Serie</th><td class="font-monospace small"><?= htmlspecialchars($bitacora['numero_serie']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($bitacora['color']): ?>
                    <tr><th>Color</th><td><?= htmlspecialchars($bitacora['color']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-0 pb-0">
                <i class="bi bi-person me-1 text-primary"></i> Cliente
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th style="width:100px">Nombre</th>
                        <td>
                            <a href="<?= $appUrl ?>/?modulo=clientes&accion=detalle&id=<?= $bitacora['cliente_id'] ?>"
                               class="text-decoration-none fw-semibold">
                                <?= htmlspecialchars($bitacora['cliente_nombre']) ?>
                            </a>
                        </td>
                    </tr>
                    <?php if ($bitacora['cliente_rfc']): ?>
                    <tr><th>RFC</th><td class="font-monospace"><?= htmlspecialchars($bitacora['cliente_rfc']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($bitacora['cliente_tel']): ?>
                    <tr><th>Teléfono</th><td><?= htmlspecialchars($bitacora['cliente_tel']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Historial de servicios -->
<h5 class="fw-semibold mb-3">
    <i class="bi bi-clock-history me-1 text-info"></i>
    Historial de servicios
    <span class="badge bg-secondary ms-1"><?= count($historial) ?></span>
</h5>

<?php if (empty($historial)): ?>
<div class="text-center text-muted py-5">
    <i class="bi bi-journal fs-3 d-block mb-2"></i>
    Sin servicios registrados para esta unidad.
</div>
<?php else: ?>
<div class="d-flex flex-column gap-3">
<?php foreach ($historial as $srv): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-light d-flex flex-wrap align-items-center gap-3 py-2">
        <span class="fw-semibold text-nowrap">
            <i class="bi bi-calendar3 me-1 text-muted"></i>
            <?= date('d/m/Y', strtotime($srv['fecha_servicio'])) ?>
        </span>
        <a href="<?= $appUrl ?>/?modulo=facturas&accion=detalle&id=<?= $srv['factura_id'] ?>"
           class="font-monospace small text-decoration-none text-info fw-semibold text-nowrap">
            <i class="bi bi-receipt me-1"></i><?= htmlspecialchars($srv['folio']) ?>
        </a>
        <span class="text-muted small text-nowrap">
            <i class="bi bi-person-gear me-1"></i><?= htmlspecialchars($srv['mecanico']) ?>
        </span>
        <?php if ($srv['sucursal']): ?>
        <span class="text-muted small text-nowrap">
            <i class="bi bi-building me-1"></i><?= htmlspecialchars($srv['sucursal']) ?>
        </span>
        <?php endif; ?>
    </div>
    <div class="card-body pb-2">
        <?php if ($srv['descripcion']): ?>
        <p class="mb-2 small">
            <span class="fw-semibold">Descripción:</span>
            <?= nl2br(htmlspecialchars($srv['descripcion'])) ?>
        </p>
        <?php endif; ?>
        <?php if ($srv['trabajos_realizados']): ?>
        <div class="mb-2">
            <p class="fw-semibold small mb-1"><i class="bi bi-wrench-adjustable me-1 text-muted"></i>Trabajos realizados:</p>
            <ul class="list-unstyled mb-0 ps-3">
                <?php foreach (array_filter(array_map('trim', explode(';', $srv['trabajos_realizados']))) as $trabajo): ?>
                <li class="small">· <?= htmlspecialchars($trabajo) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (!empty($srv['productos'])): ?>
        <div class="mt-2">
            <p class="fw-semibold small mb-1"><i class="bi bi-box-seam me-1 text-muted"></i>Partes utilizadas:</p>
            <table class="table table-sm table-borderless mb-0" style="max-width:480px">
                <thead class="table-light">
                    <tr>
                        <th class="small">Producto</th>
                        <th class="small text-end" style="width:80px">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($srv['productos'] as $p): ?>
                <tr>
                    <td class="small"><?= htmlspecialchars($p['nombre']) ?></td>
                    <td class="small text-end font-monospace"><?= number_format((float)$p['cantidad'], 0) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
