<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-box-arrow-up-right text-danger me-2"></i>Salidas</h4>
    <?php if (Auth::tienePermiso('salidas.crear')): ?>
    <a href="<?= $appUrl ?>/?modulo=salidas&accion=nueva" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i> Nueva salida
    </a>
    <?php endif; ?>
</div>

<form method="GET" action="<?= $appUrl ?>/" class="mb-3">
    <input type="hidden" name="modulo" value="salidas">
    <div class="input-group" style="max-width:420px">
        <input type="text" name="buscar" class="form-control" placeholder="Folio, mecánico o referencia…"
               value="<?= htmlspecialchars($buscar) ?>">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        <?php if ($buscar): ?>
        <a href="<?= $appUrl ?>/?modulo=salidas" class="btn btn-outline-danger"><i class="bi bi-x"></i></a>
        <?php endif; ?>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Folio</th>
                        <th>Mecánico</th>
                        <th>Servicio</th>
                        <th>Ref. Proneg</th>
                        <th>Sucursal</th>
                        <th class="text-center">Partidas</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($resultado['filas'])): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No hay salidas registradas</td></tr>
                <?php else: ?>
                    <?php foreach ($resultado['filas'] as $s): ?>
                    <tr>
                        <td><code class="text-danger"><?= htmlspecialchars($s['folio']) ?></code></td>
                        <td><?= htmlspecialchars($s['mecanico']) ?></td>
                        <td><?= htmlspecialchars($s['servicio']) ?></td>
                        <td><?= htmlspecialchars($s['referencia_factura'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($s['sucursal']) ?></td>
                        <td class="text-center"><span class="badge bg-secondary"><?= $s['num_partidas'] ?></span></td>
                        <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></td>
                        <td>
                            <span class="badge badge-estado-<?= htmlspecialchars($s['estado'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= ucfirst($s['estado']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="<?= $appUrl ?>/?modulo=salidas&accion=detalle&id=<?= $s['id'] ?>"
                               class="btn btn-sm btn-outline-primary">
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
</div>

<?php if ($resultado['total_paginas'] > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-end mb-0">
        <?php for ($p = 1; $p <= $resultado['total_paginas']; $p++): ?>
        <li class="page-item <?= $p == $resultado['pagina'] ? 'active' : '' ?>">
            <a class="page-link" href="<?= $appUrl ?>/?modulo=salidas&buscar=<?= urlencode($buscar) ?>&pagina=<?= $p ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<p class="text-muted small mt-2"><?= number_format($resultado['total']) ?> registro(s)</p>
