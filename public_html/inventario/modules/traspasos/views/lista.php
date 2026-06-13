<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-arrow-left-right text-info me-2"></i>Traspasos entre sucursales</h4>
    <?php if (Auth::tienePermiso('traspasos.crear')): ?>
    <a href="<?= $appUrl ?>/?modulo=traspasos&accion=nuevo" class="btn btn-info">
        <i class="bi bi-plus-lg me-1"></i> Nuevo traspaso
    </a>
    <?php endif; ?>
</div>

<form method="GET" action="<?= $appUrl ?>/" class="mb-3">
    <input type="hidden" name="modulo" value="traspasos">
    <div class="input-group" style="max-width:400px">
        <input type="text" name="buscar" class="form-control" placeholder="Buscar por folio…"
               value="<?= htmlspecialchars($buscar) ?>">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        <?php if ($buscar): ?>
        <a href="<?= $appUrl ?>/?modulo=traspasos" class="btn btn-outline-danger"><i class="bi bi-x"></i></a>
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
                        <th>Origen</th>
                        <th>Destino</th>
                        <th class="text-center">Partidas</th>
                        <th>Enviado</th>
                        <th>Recibido</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($resultado['filas'])): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No hay traspasos registrados</td></tr>
                <?php else: ?>
                    <?php foreach ($resultado['filas'] as $t): ?>
                    <tr>
                        <td><code class="text-info"><?= htmlspecialchars($t['folio_salida']) ?></code></td>
                        <td><?= htmlspecialchars($t['sucursal_origen']) ?></td>
                        <td><?= htmlspecialchars($t['sucursal_destino']) ?></td>
                        <td class="text-center"><span class="badge bg-secondary"><?= $t['num_partidas'] ?></span></td>
                        <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($t['fecha_envio'])) ?></td>
                        <td class="text-muted small">
                            <?= $t['fecha_recepcion'] ? date('d/m/Y H:i', strtotime($t['fecha_recepcion'])) : '—' ?>
                        </td>
                        <td>
                            <span class="badge badge-estado-<?= $t['traspaso_estado'] ?>">
                                <?= ucwords(str_replace('_',' ', $t['traspaso_estado'])) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="<?= $appUrl ?>/?modulo=traspasos&accion=detalle&id=<?= $t['traspaso_id'] ?>"
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
        <?php for ($p=1; $p<=$resultado['total_paginas']; $p++): ?>
        <li class="page-item <?= $p==$resultado['pagina']?'active':'' ?>">
            <a class="page-link" href="<?= $appUrl ?>/?modulo=traspasos&buscar=<?= urlencode($buscar) ?>&pagina=<?= $p ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<p class="text-muted small mt-2"><?= number_format($resultado['total']) ?> registro(s)</p>
