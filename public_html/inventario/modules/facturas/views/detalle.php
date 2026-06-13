<?php
$badgeColor = match($factura['estado']) {
    'borrador'  => 'bg-warning text-dark',
    'emitida'   => 'bg-primary',
    'pagada'    => 'bg-success',
    'cancelada' => 'bg-secondary',
    default     => 'bg-light text-dark'
};
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-receipt text-warning me-2"></i>
            <?= htmlspecialchars($factura['folio']) ?>
        </h4>
        <span class="badge <?= $badgeColor ?> mt-1"><?= ucfirst($factura['estado']) ?></span>
    </div>
    <div class="d-flex gap-2">
        <?php if (in_array($factura['estado'], ['emitida','pagada'])): ?>
        <a href="<?= $appUrl ?>/?modulo=facturas&accion=imprimir&id=<?= $factura['id'] ?>"
           class="btn btn-sm btn-outline-secondary" target="_blank">
            <i class="bi bi-printer me-1"></i> Imprimir
        </a>
        <?php endif; ?>
        <a href="<?= $appUrl ?>/?modulo=facturas" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Datos del servicio -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-0 pb-0"><i class="bi bi-person me-1"></i>Cliente y vehículo</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th>Cliente</th><td><?= htmlspecialchars($factura['cliente_nombre']) ?></td></tr>
                    <tr><th>Teléfono</th><td><?= htmlspecialchars($factura['cliente_tel'] ?: '—') ?></td></tr>
                    <tr><th>Vehículo</th><td><?= htmlspecialchars($factura['vh_marca'].' '.$factura['vh_modelo']) ?></td></tr>
                    <tr><th>Año</th><td><?= $factura['vh_anio'] ?></td></tr>
                    <tr><th>Placas</th><td><?= htmlspecialchars($factura['vh_placas'] ?: '—') ?></td></tr>
                    <tr><th>Mecánico</th><td><?= htmlspecialchars($factura['mecanico_nombre']) ?></td></tr>
                    <tr><th>Servicio</th><td><?= htmlspecialchars($factura['servicio_nombre']) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <!-- Datos administrativos -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-0 pb-0"><i class="bi bi-info-circle me-1"></i>Información de la factura</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th>Folio</th><td><code><?= htmlspecialchars($factura['folio']) ?></code></td></tr>
                    <tr><th>Sucursal</th><td><?= htmlspecialchars($factura['sucursal_nombre']) ?></td></tr>
                    <tr><th>Creada</th><td><?= date('d/m/Y H:i', strtotime($factura['created_at'])) ?></td></tr>
                    <?php if ($factura['fecha_emision']): ?>
                    <tr><th>Emitida</th><td><?= date('d/m/Y H:i', strtotime($factura['fecha_emision'])) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($factura['fecha_pago']): ?>
                    <tr><th>Pagada</th><td><?= date('d/m/Y H:i', strtotime($factura['fecha_pago'])) ?></td></tr>
                    <?php endif; ?>
                    <tr><th>Ref. Proneg</th><td><?= htmlspecialchars($factura['referencia_proneg'] ?: '—') ?></td></tr>
                    <tr><th>Registrado por</th><td><?= htmlspecialchars($factura['usuario_nombre']) ?></td></tr>
                    <?php if ($factura['movimiento_id']): ?>
                    <tr><th>Salida inventario</th><td>
                        <a href="<?= $appUrl ?>/?modulo=salidas&accion=detalle&id=<?= $factura['movimiento_id'] ?>" class="text-decoration-none">
                            Ver movimiento <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Partidas -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">Partes utilizadas <span class="badge bg-secondary ms-1"><?= count($detalle) ?></span></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Código</th><th>Producto</th><th>Unidad</th>
                        <th class="text-end">Cantidad</th><th class="text-end">Precio unit.</th><th class="text-end">Importe</th></tr>
                </thead>
                <tbody>
                <?php $subtotal=0; foreach ($detalle as $i => $d): $imp=$d['cantidad']*$d['precio_unitario']; $subtotal+=$imp; ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><code><?= htmlspecialchars($d['codigo']) ?></code></td>
                    <td><?= htmlspecialchars($d['producto_nombre']) ?></td>
                    <td><?= htmlspecialchars($d['unidad']) ?></td>
                    <td class="text-end"><?= number_format($d['cantidad'],3) ?></td>
                    <td class="text-end">$<?= number_format($d['precio_unitario'],2) ?></td>
                    <td class="text-end fw-semibold">$<?= number_format($imp,2) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr><td colspan="5"></td><td class="text-end">Subtotal partes:</td><td class="text-end fw-bold">$<?= number_format($subtotal,2) ?></td></tr>
                    <?php if ($factura['mano_obra'] > 0): ?>
                    <tr><td colspan="5"></td><td class="text-end"><?= htmlspecialchars($factura['mano_obra_desc'] ?: 'Mano de obra') ?>:</td>
                        <td class="text-end fw-bold">$<?= number_format($factura['mano_obra'],2) ?></td></tr>
                    <?php endif; ?>
                    <tr><td colspan="5"></td><td class="text-end fw-bold fs-5">Total:</td>
                        <td class="text-end fw-bold fs-5 text-warning">$<?= number_format($factura['total'],2) ?></td></tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Acciones según estado -->
<div class="d-flex gap-2 justify-content-end flex-wrap">
    <?php if ($factura['estado'] === 'borrador'): ?>
        <a href="<?= $appUrl ?>/?modulo=facturas&accion=editar&id=<?= $factura['id'] ?>" class="btn btn-outline-warning">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <form method="POST" action="<?= $appUrl ?>/?modulo=facturas&accion=emitir">
            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
            <input type="hidden" name="id"    value="<?= $factura['id'] ?>">
            <button type="submit" class="btn btn-warning fw-semibold">
                <i class="bi bi-send me-1"></i> Emitir factura
            </button>
        </form>
    <?php endif; ?>

    <?php if ($factura['estado'] === 'emitida'): ?>
        <form method="POST" action="<?= $appUrl ?>/?modulo=facturas&accion=pagar">
            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
            <input type="hidden" name="id"    value="<?= $factura['id'] ?>">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-cash-stack me-1"></i> Marcar como pagada
            </button>
        </form>
    <?php endif; ?>

    <?php if (in_array($factura['estado'], ['borrador','emitida','pagada'])): ?>
        <form method="POST" action="<?= $appUrl ?>/?modulo=facturas&accion=cancelar"
              onsubmit="return confirm('¿Cancelar esta factura?<?= $factura['estado']!=='borrador'?' El stock será revertido.':'' ?>')">
            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
            <input type="hidden" name="id"    value="<?= $factura['id'] ?>">
            <button type="submit" class="btn btn-outline-danger">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </button>
        </form>
    <?php endif; ?>
</div>
