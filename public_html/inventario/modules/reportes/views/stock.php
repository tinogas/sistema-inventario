<?php
// Agrupa los datos por producto para la vista expandible
$productos = [];
foreach ($datos as $fila) {
    $pid = $fila['id'];
    if (!isset($productos[$pid])) {
        $productos[$pid] = [
            'id'          => $fila['id'],
            'codigo'      => $fila['codigo'],
            'producto'    => $fila['producto'],
            'categoria'   => $fila['categoria'],
            'unidad'      => $fila['unidad'],
            'stock_minimo'=> $fila['stock_minimo'],
            'stock_total' => 0,
            'sucursales'  => [],
        ];
    }
    if ($fila['sucursal'] !== null) {
        $productos[$pid]['stock_total'] += (float)$fila['stock_actual'];
        $productos[$pid]['sucursales'][] = [
            'sucursal_id' => $fila['sucursal_id'],
            'sucursal'    => $fila['sucursal'],
            'stock'       => (float)$fila['stock_actual'],
        ];
    }
}
// $transito viene del controlador: [producto_id => [['origen','destino','cantidad','folio_traspaso',...], ...]]
$transito = $transito ?? [];
?>

<?php $esAdmin = Auth::esAdmin(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-table text-primary me-2"></i>Stock actual</h4>
    <div class="d-flex gap-2">
        <?php if ($esAdmin): ?>
        <button type="button" id="btnExpandAll" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrows-expand me-1"></i>Expandir todo
        </button>
        <?php endif; ?>
        <a href="?modulo=reportes&accion=stock&<?= http_build_query(array_filter(['sucursal_id'=>$sucursal_id,'categoria_id'=>$categoria,'buscar'=>$buscar,'exportar_xlsx'=>1])) ?>"
           class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Exportar XLSX
        </a>
        <a href="?modulo=reportes&accion=stock&<?= http_build_query(array_filter(['sucursal_id'=>$sucursal_id,'categoria_id'=>$categoria,'buscar'=>$buscar,'exportar'=>1])) ?>"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-filetype-csv me-1"></i>CSV
        </a>
    </div>
</div>

<!-- Filtros -->
<form method="GET" action="<?= $appUrl ?>/" class="row g-2 mb-3">
    <input type="hidden" name="modulo" value="reportes">
    <input type="hidden" name="accion" value="stock">
    <?php if ($sucursal_id): ?><input type="hidden" name="sucursal_id" value="<?= $sucursal_id ?>"><?php endif; ?>
    <div class="col-md-4">
        <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar producto…"
               value="<?= htmlspecialchars($buscar) ?>">
    </div>
    <div class="col-md-3">
        <select name="categoria_id" class="form-select form-select-sm">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $categoria == $c['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nombre']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-funnel me-1"></i>Filtrar</button>
        <a href="<?= $appUrl ?>/?modulo=reportes&accion=stock" class="btn btn-secondary btn-sm ms-1">Limpiar</a>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" id="tablaStock">
                <thead class="table-light">
                    <tr>
                        <?php if ($esAdmin): ?><th style="width:2rem"></th><?php endif; ?>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Unidad</th>
                        <th class="text-end">Stock total</th>
                        <th class="text-end">Stock mínimo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($productos)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Sin resultados</td></tr>
                <?php else: ?>
                <?php foreach ($productos as $p):
                    $bajo    = $p['stock_total'] <= $p['stock_minimo'];
                    $colId   = 'stock-det-' . $p['id'];
                    $hasSuc  = !empty($p['sucursales']);
                    $hasDet  = $hasSuc || !empty($transito[$p['id']]);
                    $trTotal = array_sum(array_column($transito[$p['id']] ?? [], 'cantidad'));
                ?>
                <!-- Fila principal del producto -->
                <tr class="<?= $bajo ? 'table-warning' : '' ?> align-middle">
                    <?php if ($esAdmin): ?>
                    <td class="text-center">
                        <?php if ($hasDet): ?>
                        <button class="btn btn-link btn-sm p-0 text-secondary toggle-row"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?= $colId ?>"
                                aria-expanded="false"
                                aria-controls="<?= $colId ?>">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                    <td>
                        <a href="<?= $appUrl ?>/?modulo=productos&accion=detalle&id=<?= $p['id'] ?>"
                           class="text-decoration-none fw-semibold">
                            <?= htmlspecialchars($p['producto']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($p['categoria']) ?></td>
                    <td><?= htmlspecialchars($p['unidad']) ?></td>
                    <td class="text-end fw-semibold <?= $bajo ? 'text-danger' : '' ?>">
                        <?= number_format($p['stock_total'], 3) ?>
                        <?php if ($trTotal > 0): ?>
                        <br><span class="badge bg-info text-dark fw-normal small" title="Unidades en tránsito en traspasos activos">
                            <i class="bi bi-truck me-1"></i><?= number_format($trTotal, 3) ?> en tránsito
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-muted"><?= number_format($p['stock_minimo'], 3) ?></td>
                    <td>
                        <?php if ($bajo): ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Bajo mínimo</span>
                        <?php else: ?>
                        <span class="badge bg-success-subtle text-success"><i class="bi bi-check me-1"></i>OK</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
                $trFilas = $transito[$p['id']] ?? [];
                $hasRows = $hasSuc || !empty($trFilas);
                ?>
                <?php if ($esAdmin && $hasRows): ?>
                <!-- Detalle por sucursal + en tránsito (collapse) -->
                <tr class="collapse" id="<?= $colId ?>">
                    <td colspan="8" class="p-0 border-0">
                        <table class="table table-sm mb-0 border-start border-primary border-3">
                            <thead class="table-secondary">
                                <tr>
                                    <th class="ps-4" style="width:2.5rem"></th>
                                    <th class="ps-2">Sucursal / Tránsito</th>
                                    <th class="text-end pe-4">Cantidad</th>
                                    <th></th><th></th><th></th><th></th><th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($p['sucursales'] as $s):
                                $bajSuc = $s['stock'] <= $p['stock_minimo'];
                            ?>
                            <tr class="<?= $bajSuc ? 'table-warning' : '' ?>">
                                <td></td>
                                <td class="ps-2">
                                    <i class="bi bi-building text-muted me-1"></i>
                                    <?= htmlspecialchars($s['sucursal']) ?>
                                </td>
                                <td class="text-end pe-4 fw-semibold <?= $bajSuc ? 'text-danger' : '' ?>">
                                    <?= number_format($s['stock'], 3) ?>
                                </td>
                                <td colspan="5"></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php foreach ($trFilas as $tr): ?>
                            <tr class="table-info">
                                <td></td>
                                <td class="ps-2">
                                    <i class="bi bi-truck text-info me-1"></i>
                                    <span class="text-info fw-semibold">En tránsito</span>
                                    <span class="text-muted small ms-1">
                                        de <?= htmlspecialchars($tr['origen']) ?>
                                        → <?= htmlspecialchars($tr['destino']) ?>
                                    </span>
                                    <a href="<?= $appUrl ?>/?modulo=traspasos&accion=detalle&id=<?= (int)$tr['traspaso_id'] ?>"
                                       class="badge bg-info text-dark text-decoration-none ms-1 small">
                                        <?= htmlspecialchars($tr['folio_traspaso']) ?>
                                    </a>
                                </td>
                                <td class="text-end pe-4 fw-semibold text-info">
                                    <?= number_format((float)$tr['cantidad'], 3) ?>
                                </td>
                                <td colspan="5"></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<p class="text-muted small mt-2"><?= count($productos) ?> producto(s)</p>

<script>
(function () {
    const btn = document.getElementById('btnExpandAll');
    if (!btn) return;

    let expanded = false;

    btn.addEventListener('click', function () {
        expanded = !expanded;
        document.querySelectorAll('#tablaStock .collapse').forEach(function (el) {
            const bsCol = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
            expanded ? bsCol.show() : bsCol.hide();
        });
        btn.innerHTML = expanded
            ? '<i class="bi bi-arrows-collapse me-1"></i>Colapsar todo'
            : '<i class="bi bi-arrows-expand me-1"></i>Expandir todo';
    });

    // Rotar ícono del chevron al expandir/colapsar individualmente
    document.querySelectorAll('#tablaStock .collapse').forEach(function (el) {
        el.addEventListener('show.bs.collapse', function () {
            const btn = document.querySelector('[data-bs-target="#' + el.id + '"] i');
            if (btn) { btn.classList.remove('bi-chevron-right'); btn.classList.add('bi-chevron-down'); }
        });
        el.addEventListener('hide.bs.collapse', function () {
            const btn = document.querySelector('[data-bs-target="#' + el.id + '"] i');
            if (btn) { btn.classList.remove('bi-chevron-down'); btn.classList.add('bi-chevron-right'); }
        });
    });
})();
</script>
