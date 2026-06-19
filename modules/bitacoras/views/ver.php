<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="<?= $appUrl ?>/?modulo=bitacoras" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h4 mb-0">
            <i class="bi bi-journal-check me-2 text-info"></i>
            Bitácora — <?= htmlspecialchars($bitacora['folio']) ?>
        </h1>
    </div>
    <?php if (Auth::tienePermiso('bitacoras.imprimir')): ?>
    <a href="<?= $appUrl ?>/?modulo=bitacoras&accion=imprimir&id=<?= $bitacora['id'] ?>"
       class="btn btn-sm btn-outline-secondary" target="_blank">
        <i class="bi bi-printer me-1"></i> Imprimir
    </a>
    <?php endif; ?>
</div>

<div class="row g-3 mb-4">
    <!-- Unidad -->
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

    <!-- Cliente -->
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

    <!-- Info del servicio -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-0 pb-0">
                <i class="bi bi-tools me-1 text-primary"></i> Servicio realizado
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($bitacora['fecha_servicio'])) ?></div>
                    <div class="col-md-3"><strong>Mecánico:</strong> <?= htmlspecialchars($bitacora['mecanico_nombre']) ?></div>
                    <div class="col-md-3">
                        <strong>Factura:</strong>
                        <a href="<?= $appUrl ?>/?modulo=facturas&accion=detalle&id=<?= $bitacora['factura_id'] ?>"
                           class="font-monospace text-decoration-none">
                            <?= htmlspecialchars($bitacora['folio']) ?>
                        </a>
                    </div>
                </div>
                <?php if ($bitacora['descripcion']): ?>
                <div class="mb-2">
                    <strong>Descripción:</strong>
                    <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($bitacora['descripcion'])) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($bitacora['trabajos_realizados']): ?>
                <div>
                    <strong>Trabajos realizados:</strong>
                    <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($bitacora['trabajos_realizados'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Partidas -->
<?php if (!empty($productos)): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">
        Partes utilizadas <span class="badge bg-secondary ms-1"><?= count($productos) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Precio unit.</th>
                        <th class="text-end">Importe</th>
                    </tr>
                </thead>
                <tbody>
                <?php $subtotal = 0; foreach ($productos as $i => $p): $imp = $p['cantidad'] * $p['precio_unitario']; $subtotal += $imp; ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td class="text-end"><?= number_format($p['cantidad'], 3) ?></td>
                    <td class="text-end">$<?= number_format($p['precio_unitario'], 2) ?></td>
                    <td class="text-end fw-semibold">$<?= number_format($imp, 2) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr><td colspan="3"></td><td class="text-end">Subtotal partes:</td><td class="text-end fw-bold">$<?= number_format($bitacora['subtotal'], 2) ?></td></tr>
                    <?php if ($bitacora['mano_obra'] > 0): ?>
                    <tr><td colspan="3"></td><td class="text-end">Mano de obra:</td><td class="text-end fw-bold">$<?= number_format($bitacora['mano_obra'], 2) ?></td></tr>
                    <?php endif; ?>
                    <tr><td colspan="3"></td><td class="text-end fw-bold fs-5">Total:</td><td class="text-end fw-bold fs-5 text-info">$<?= number_format($bitacora['total'], 2) ?></td></tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
