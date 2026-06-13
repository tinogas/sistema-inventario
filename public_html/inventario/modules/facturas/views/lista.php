<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-receipt text-warning me-2"></i>Facturas de servicio</h4>
    <?php if (Auth::tienePermiso('facturas.crear')): ?>
    <a href="<?= $appUrl ?>/?modulo=facturas&accion=nueva" class="btn btn-warning">
        <i class="bi bi-plus-lg me-1"></i> Nueva factura
    </a>
    <?php endif; ?>
</div>

<!-- Filtros -->
<form method="GET" action="<?= $appUrl ?>/" class="row g-2 mb-3 align-items-center">
    <input type="hidden" name="modulo" value="facturas">
    <?php if (Auth::sucursalFiltro()): ?><input type="hidden" name="sucursal_id" value="<?= Auth::sucursalFiltro() ?>"><?php endif; ?>
    <div class="col-md-3">
        <select name="estado" class="form-select form-select-sm">
            <option value="">Todos los estados</option>
            <?php foreach (['borrador','emitida','pagada','cancelada'] as $e): ?>
            <option value="<?= $e ?>" <?= $estado===$e?'selected':'' ?>><?= ucfirst($e) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <input type="text" name="buscar" class="form-control form-control-sm"
               placeholder="Folio, cliente o placas…" value="<?= htmlspecialchars($buscar) ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-search me-1"></i>Filtrar</button>
        <a href="<?= $appUrl ?>/?modulo=facturas" class="btn btn-secondary btn-sm ms-1">Limpiar</a>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Vehículo</th>
                        <th>Mecánico</th>
                        <th>Sucursal</th>
                        <th class="text-end">Total</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-center">Acc.</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($resultado['filas'])): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No hay facturas registradas</td></tr>
                <?php else: ?>
                    <?php foreach ($resultado['filas'] as $f): ?>
                    <?php
                    $badgeColor = match($f['estado']) {
                        'borrador'  => 'bg-warning text-dark',
                        'emitida'   => 'bg-primary',
                        'pagada'    => 'bg-success',
                        'cancelada' => 'bg-secondary',
                        default     => 'bg-light text-dark'
                    };
                    ?>
                    <tr>
                        <td><code class="text-warning"><?= htmlspecialchars($f['folio']) ?></code></td>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($f['cliente_nombre']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($f['cliente_tel'] ?: '') ?></small>
                        </td>
                        <td>
                            <div><?= htmlspecialchars($f['vh_marca'] . ' ' . $f['vh_modelo']) ?></div>
                            <small class="text-muted"><?= $f['vh_anio'] ?> <?= htmlspecialchars($f['vh_placas'] ?: '') ?></small>
                        </td>
                        <td><?= htmlspecialchars($f['mecanico']) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($f['sucursal']) ?></span></td>
                        <td class="text-end fw-bold">$<?= number_format($f['total'],2) ?></td>
                        <td class="text-muted small"><?= date('d/m/Y', strtotime($f['created_at'])) ?></td>
                        <td><span class="badge <?= $badgeColor ?>"><?= ucfirst($f['estado']) ?></span></td>
                        <td class="text-center">
                            <a href="<?= $appUrl ?>/?modulo=facturas&accion=detalle&id=<?= $f['id'] ?>"
                               class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($resultado['total_paginas'] > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-end mb-0">
        <?php for ($p=1; $p<=$resultado['total_paginas']; $p++): ?>
        <li class="page-item <?= $p==$resultado['pagina']?'active':'' ?>">
            <a class="page-link" href="<?= $appUrl ?>/?modulo=facturas&estado=<?= urlencode($estado) ?>&buscar=<?= urlencode($buscar) ?>&pagina=<?= $p ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<p class="text-muted small mt-2"><?= number_format($resultado['total']) ?> factura(s)</p>
