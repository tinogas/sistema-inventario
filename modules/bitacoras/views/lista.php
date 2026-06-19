<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="bi bi-journal-text me-2 text-info"></i>
        Bitácora de servicio
        <?php if ($clienteNombre): ?>
        <small class="text-muted fs-6 ms-2">— <?= htmlspecialchars($clienteNombre) ?></small>
        <?php endif; ?>
    </h1>
</div>

<!-- Filtros -->
<form method="GET" action="<?= $appUrl ?>/" class="mb-3">
    <input type="hidden" name="modulo" value="bitacoras">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Desde</label>
            <input type="date" name="fecha_desde" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Hasta</label>
            <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>">
        </div>
        <?php if ($filtros['cliente_id']): ?>
        <input type="hidden" name="cliente_id" value="<?= (int)$filtros['cliente_id'] ?>">
        <?php endif; ?>
        <?php if ($filtros['unidad_id']): ?>
        <input type="hidden" name="unidad_id" value="<?= (int)$filtros['unidad_id'] ?>">
        <?php endif; ?>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                <i class="bi bi-funnel me-1"></i> Filtrar
            </button>
        </div>
        <div class="col-md-2">
            <a href="<?= $appUrl ?>/?modulo=bitacoras" class="btn btn-sm btn-outline-secondary w-100">
                <i class="bi bi-x-lg me-1"></i> Limpiar
            </a>
        </div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Unidad</th>
                        <th>Placas</th>
                        <th>Folio</th>
                        <th>Mecánico</th>
                        <th class="text-end">Total</th>
                        <th style="width:80px" class="text-center">Ver</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($result['filas'])): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-journal fs-4 d-block mb-1"></i>
                            No hay registros en la bitácora.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($result['filas'] as $b): ?>
                    <tr>
                        <td class="text-nowrap"><?= date('d/m/Y', strtotime($b['fecha_servicio'])) ?></td>
                        <td>
                            <a href="<?= $appUrl ?>/?modulo=clientes&accion=detalle&id=<?= $b['cliente_id'] ?>"
                               class="text-decoration-none fw-semibold">
                                <?= htmlspecialchars($b['cliente_nombre']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($b['unidad']) ?></td>
                        <td class="font-monospace small"><?= htmlspecialchars($b['placas'] ?: '—') ?></td>
                        <td>
                            <a href="<?= $appUrl ?>/?modulo=facturas&accion=detalle&id=<?= $b['factura_id'] ?? '' ?>"
                               class="text-decoration-none font-monospace small">
                                <?= htmlspecialchars($b['folio']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($b['mecanico']) ?></td>
                        <td class="text-end fw-semibold">$<?= number_format($b['total'], 2) ?></td>
                        <td class="text-center">
                            <a href="<?= $appUrl ?>/?modulo=bitacoras&accion=ver&id=<?= $b['id'] ?>"
                               class="btn btn-sm btn-outline-info" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($result['total_paginas'] > 1): ?>
    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">
            Mostrando <?= count($result['filas']) ?> de <?= $result['total'] ?> registros
            (página <?= $result['pagina'] ?> de <?= $result['total_paginas'] ?>)
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php if ($result['pagina'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $appUrl ?>/?modulo=bitacoras&pagina=<?= $result['pagina'] - 1 ?>&fecha_desde=<?= urlencode($filtros['fecha_desde']??'') ?>&fecha_hasta=<?= urlencode($filtros['fecha_hasta']??'') ?>&cliente_id=<?= (int)($filtros['cliente_id']??0) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                <?php
                $desde = max(1, $result['pagina'] - 2);
                $hasta  = min($result['total_paginas'], $result['pagina'] + 2);
                for ($p = $desde; $p <= $hasta; $p++):
                ?>
                <li class="page-item <?= $p === $result['pagina'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $appUrl ?>/?modulo=bitacoras&pagina=<?= $p ?>&fecha_desde=<?= urlencode($filtros['fecha_desde']??'') ?>&fecha_hasta=<?= urlencode($filtros['fecha_hasta']??'') ?>&cliente_id=<?= (int)($filtros['cliente_id']??0) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
                <?php if ($result['pagina'] < $result['total_paginas']): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $appUrl ?>/?modulo=bitacoras&pagina=<?= $result['pagina'] + 1 ?>&fecha_desde=<?= urlencode($filtros['fecha_desde']??'') ?>&fecha_hasta=<?= urlencode($filtros['fecha_hasta']??'') ?>&cliente_id=<?= (int)($filtros['cliente_id']??0) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
