<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-box-arrow-in-down-right text-success me-2"></i>Entradas</h4>
    <?php if (Auth::tienePermiso('entradas.crear')): ?>
    <a href="<?= $appUrl ?>/?modulo=entradas&accion=nueva" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i> Nueva entrada
    </a>
    <?php endif; ?>
</div>

<!-- Buscador -->
<form method="GET" action="<?= $appUrl ?>/" class="mb-3">
    <input type="hidden" name="modulo" value="entradas">
    <div class="input-group" style="max-width:400px">
        <input type="text" name="buscar" class="form-control" placeholder="Buscar por folio, proveedor o referencia…"
               value="<?= htmlspecialchars($buscar) ?>">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        <?php if ($buscar): ?>
        <a href="<?= $appUrl ?>/?modulo=entradas" class="btn btn-outline-danger"><i class="bi bi-x"></i></a>
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
                        <th>Proveedor</th>
                        <th>Ref. factura</th>
                        <th>Sucursal</th>
                        <th class="text-center">Partidas</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($resultado['filas'])): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No hay entradas registradas</td></tr>
                <?php else: ?>
                    <?php foreach ($resultado['filas'] as $e): ?>
                    <tr>
                        <td><code class="text-primary"><?= htmlspecialchars($e['folio']) ?></code></td>
                        <td><?= htmlspecialchars($e['proveedor']) ?></td>
                        <td><?= htmlspecialchars($e['referencia_factura'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($e['sucursal']) ?></td>
                        <td class="text-center"><span class="badge bg-secondary"><?= $e['num_partidas'] ?></span></td>
                        <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($e['created_at'])) ?></td>
                        <td>
                            <span class="badge badge-estado-<?= $e['estado'] ?>">
                                <?= ucfirst($e['estado']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="<?= $appUrl ?>/?modulo=entradas&accion=detalle&id=<?= $e['id'] ?>"
                               class="btn btn-sm btn-outline-primary" title="Ver detalle">
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

<!-- Paginación -->
<?php if ($resultado['total_paginas'] > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-end mb-0">
        <?php for ($p = 1; $p <= $resultado['total_paginas']; $p++): ?>
        <li class="page-item <?= $p == $resultado['pagina'] ? 'active' : '' ?>">
            <a class="page-link" href="<?= $appUrl ?>/?modulo=entradas&buscar=<?= urlencode($buscar) ?>&pagina=<?= $p ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<p class="text-muted small mt-2"><?= number_format($resultado['total']) ?> registro(s) en total</p>
