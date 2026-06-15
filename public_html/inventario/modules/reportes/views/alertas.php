<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Alertas: Stock bajo mínimo</h4>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-danger fs-6"><?= count($datos) ?> productos</span>
        <?php if (!empty($datos)): ?>
        <a href="<?= $appUrl ?>/?modulo=reportes&accion=pedido<?= !empty($_GET['sucursal_id']) ? '&sucursal_id='.(int)$_GET['sucursal_id'] : '' ?>"
           class="btn btn-primary btn-sm" target="_blank">
            <i class="bi bi-cart-plus me-1"></i> Generar pedido
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($datos)): ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle me-2"></i>
    <strong>¡Excelente!</strong> No hay productos con stock bajo el mínimo establecido.
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Código</th><th>Producto</th><th>Categoría</th><th>Unidad</th>
                        <th class="text-end">Stock actual</th><th class="text-end">Mínimo</th>
                        <th class="text-end">Diferencia</th><th>Sucursal</th></tr>
                </thead>
                <tbody>
                <?php foreach ($datos as $d): ?>
                <tr class="table-warning">
                    <td><code><?= htmlspecialchars($d['codigo']) ?></code></td>
                    <td>
                        <a href="<?= $appUrl ?>/?modulo=productos&accion=detalle&id=<?= $d['id'] ?>" class="text-decoration-none fw-semibold">
                            <?= htmlspecialchars($d['nombre']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($d['categoria']) ?></td>
                    <td><?= htmlspecialchars($d['unidad']) ?></td>
                    <td class="text-end text-danger fw-bold"><?= number_format($d['stock_actual'],3) ?></td>
                    <td class="text-end"><?= number_format($d['stock_minimo'],3) ?></td>
                    <td class="text-end text-danger"><?= number_format($d['diferencia'],3) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($d['sucursal']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
