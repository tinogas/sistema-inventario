<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-box-arrow-in-down-right text-success me-2"></i>
            Entrada <?= htmlspecialchars($entrada['folio']) ?>
        </h4>
        <span class="badge badge-estado-<?= $entrada['estado'] ?> mt-1"><?= ucfirst($entrada['estado']) ?></span>
    </div>
    <a href="<?= $appUrl ?>/?modulo=entradas" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th>Folio</th><td><code><?= htmlspecialchars($entrada['folio']) ?></code></td></tr>
                    <tr><th>Sucursal</th><td><?= htmlspecialchars($entrada['sucursal_nombre']) ?></td></tr>
                    <tr><th>Proveedor</th><td><?= htmlspecialchars($entrada['proveedor_nombre']) ?></td></tr>
                    <tr><th>Ref. factura</th><td><?= htmlspecialchars($entrada['referencia_factura'] ?: '—') ?></td></tr>
                    <tr><th>UUID CFDI</th><td class="small"><?= htmlspecialchars($entrada['uuid_cfdi'] ?: '—') ?></td></tr>
                    <tr><th>Registrado por</th><td><?= htmlspecialchars($entrada['usuario_nombre']) ?></td></tr>
                    <tr><th>Fecha</th><td><?= date('d/m/Y H:i', strtotime($entrada['created_at'])) ?></td></tr>
                    <?php if ($entrada['notas']): ?>
                    <tr><th>Notas</th><td><?= htmlspecialchars($entrada['notas']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
        Partidas <span class="badge bg-secondary ms-1"><?= count($partidas) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm tabla-partidas mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Unidad</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Precio unit.</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                <?php $total = 0; ?>
                <?php foreach ($partidas as $i => $p): ?>
                <?php $sub = $p['cantidad'] * $p['precio_unitario']; $total += $sub; ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                    <td>
                        <a href="<?= $appUrl ?>/?modulo=productos&accion=detalle&id=<?= $p['producto_id'] ?>" class="text-decoration-none">
                            <?= htmlspecialchars($p['producto_nombre']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($p['unidad']) ?></td>
                    <td class="text-end"><?= number_format($p['cantidad'],3) ?></td>
                    <td class="text-end"><?= number_format($p['precio_unitario'],2) ?></td>
                    <td class="text-end fw-semibold"><?= '$' . number_format($sub,2) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr><td colspan="6" class="text-end fw-bold">Total:</td>
                        <td class="text-end fw-bold">$<?= number_format($total,2) ?></td></tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php if ($entrada['estado'] === 'confirmado' && Auth::tienePermiso('entradas.crear')): ?>
<div class="mt-3 d-flex justify-content-end">
    <form method="POST" action="<?= $appUrl ?>/?modulo=entradas&accion=cancelar"
          onsubmit="return confirm('¿Cancelar esta entrada? Se revertirá el stock.')">
        <input type="hidden" name="_csrf" value="<?= $csrf ?>">
        <input type="hidden" name="id"    value="<?= $entrada['id'] ?>">
        <button type="submit" class="btn btn-outline-danger">
            <i class="bi bi-x-circle me-1"></i> Cancelar entrada
        </button>
    </form>
</div>
<?php endif; ?>
