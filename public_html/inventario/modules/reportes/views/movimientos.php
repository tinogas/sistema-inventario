<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-clock-history text-secondary me-2"></i>Movimientos</h4>
    <a href="?modulo=reportes&accion=movimientos&<?= http_build_query(array_filter(['sucursal_id'=>$sucursal_id,'tipo'=>$tipo,'desde'=>$desde,'hasta'=>$hasta,'exportar'=>1])) ?>"
       class="btn btn-outline-success btn-sm">
        <i class="bi bi-download me-1"></i> Exportar CSV
    </a>
</div>

<form method="GET" action="<?= $appUrl ?>/" class="row g-2 mb-3 align-items-end">
    <input type="hidden" name="modulo" value="reportes">
    <input type="hidden" name="accion" value="movimientos">
    <?php if ($sucursal_id): ?><input type="hidden" name="sucursal_id" value="<?= $sucursal_id ?>"><?php endif; ?>
    <div class="col-md-2">
        <label class="form-label small">Tipo</label>
        <select name="tipo" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="entrada"          <?= $tipo==='entrada'?'selected':'' ?>>Entrada</option>
            <option value="salida"           <?= $tipo==='salida'?'selected':'' ?>>Salida</option>
            <option value="traspaso_salida"  <?= $tipo==='traspaso_salida'?'selected':'' ?>>Traspaso salida</option>
            <option value="traspaso_entrada" <?= $tipo==='traspaso_entrada'?'selected':'' ?>>Traspaso entrada</option>
            <option value="ajuste"           <?= $tipo==='ajuste'?'selected':'' ?>>Ajuste</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label small">Desde</label>
        <input type="date" name="desde" class="form-control form-control-sm" value="<?= $desde ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label small">Hasta</label>
        <input type="date" name="hasta" class="form-control form-control-sm" value="<?= $hasta ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-funnel me-1"></i>Filtrar</button>
        <a href="<?= $appUrl ?>/?modulo=reportes&accion=movimientos" class="btn btn-secondary btn-sm ms-1">Limpiar</a>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Folio</th><th>Tipo</th><th>Sucursal</th><th>Ref. factura</th>
                        <th class="text-center">Partidas</th><th>Fecha</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                <?php if (empty($resultado['filas'])): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Sin resultados para los filtros seleccionados</td></tr>
                <?php else: ?>
                <?php foreach ($resultado['filas'] as $m): ?>
                <tr>
                    <td><code><?= htmlspecialchars($m['folio']) ?></code></td>
                    <td><?= htmlspecialchars(ucfirst(str_replace('_',' ', $m['tipo']))) ?></td>
                    <td><?= htmlspecialchars($m['sucursal']) ?></td>
                    <td><?= htmlspecialchars($m['referencia_factura'] ?: '—') ?></td>
                    <td class="text-center"><span class="badge bg-secondary"><?= (int)$m['num_partidas'] ?></span></td>
                    <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                    <td><span class="badge badge-estado-<?= htmlspecialchars($m['estado']) ?>"><?= htmlspecialchars(ucfirst($m['estado'])) ?></span></td>
                    <td>
                        <?php
                        $modDestino = match($m['tipo']) {
                            'entrada','salida' => $m['tipo'] . 's',
                            'traspaso_salida','traspaso_entrada' => 'traspasos',
                            default => 'dashboard'
                        };
                        ?>
                        <a href="<?= $appUrl ?>/?modulo=<?= $modDestino ?>&accion=detalle&id=<?= $m['id'] ?>"
                           class="btn btn-xs btn-outline-primary py-0 px-1" style="font-size:.75rem">Ver</a>
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
            <a class="page-link" href="<?= $appUrl ?>/?modulo=reportes&accion=movimientos&tipo=<?= urlencode($tipo) ?>&desde=<?= $desde ?>&hasta=<?= $hasta ?>&pagina=<?= $p ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<p class="text-muted small mt-2"><?= number_format($resultado['total']) ?> movimiento(s) en el período</p>
