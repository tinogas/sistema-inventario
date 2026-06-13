<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-table text-primary me-2"></i>Stock actual</h4>
    <a href="?modulo=reportes&accion=stock&<?= http_build_query(array_filter(['sucursal_id'=>$sucursal_id,'categoria_id'=>$categoria,'buscar'=>$buscar,'exportar'=>1])) ?>"
       class="btn btn-outline-success btn-sm">
        <i class="bi bi-download me-1"></i> Exportar CSV
    </a>
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
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th><th>Producto</th><th>Categoría</th><th>Unidad</th>
                        <th class="text-end">Stock actual</th><th class="text-end">Stock mínimo</th>
                        <th>Sucursal</th><th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($datos)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Sin resultados</td></tr>
                <?php else: ?>
                <?php foreach ($datos as $d): ?>
                <tr class="<?= $d['bajo_minimo'] ? 'table-warning' : '' ?>">
                    <td><code><?= htmlspecialchars($d['codigo']) ?></code></td>
                    <td>
                        <a href="<?= $appUrl ?>/?modulo=productos&accion=detalle&id=<?= $d['id'] ?>" class="text-decoration-none">
                            <?= htmlspecialchars($d['producto']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($d['categoria']) ?></td>
                    <td><?= htmlspecialchars($d['unidad']) ?></td>
                    <td class="text-end fw-semibold <?= $d['bajo_minimo'] ? 'text-danger' : '' ?>">
                        <?= number_format($d['stock_actual'],3) ?>
                    </td>
                    <td class="text-end text-muted"><?= number_format($d['stock_minimo'],3) ?></td>
                    <td><?= htmlspecialchars($d['sucursal']) ?></td>
                    <td>
                        <?php if ($d['bajo_minimo']): ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Bajo mínimo</span>
                        <?php else: ?>
                        <span class="badge bg-success-subtle text-success"><i class="bi bi-check me-1"></i>OK</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<p class="text-muted small mt-2"><?= count($datos) ?> producto(s)</p>
