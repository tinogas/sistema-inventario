<?php
/**
 * Vista: Detalle de un producto — stock por sucursal + últimos movimientos
 * Variables disponibles: $producto, $stockSucursales, $movimientos,
 *                        $titulo, $appUrl, $csrf, $usuario
 */

// Etiquetas legibles para el tipo de movimiento
$tipoLabel = [
    'entrada'          => ['texto' => 'Entrada',          'clase' => 'bg-success'],
    'salida'           => ['texto' => 'Salida',           'clase' => 'bg-danger'],
    'traspaso_salida'  => ['texto' => 'Traspaso salida',  'clase' => 'bg-warning text-dark'],
    'traspaso_entrada' => ['texto' => 'Traspaso entrada', 'clase' => 'bg-info text-dark'],
    'ajuste'           => ['texto' => 'Ajuste',           'clase' => 'bg-secondary'],
];

$estadoLabel = [
    'confirmado' => ['texto' => 'Confirmado', 'clase' => 'bg-success'],
    'borrador'   => ['texto' => 'Borrador',   'clase' => 'bg-secondary'],
    'cancelado'  => ['texto' => 'Cancelado',  'clase' => 'bg-danger'],
];

$stockTotal = array_sum(array_column($stockSucursales, 'cantidad'));
?>

<!-- Encabezado -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="mb-0">
        <i class="bi bi-box-seam me-2 text-primary"></i>
        <?= htmlspecialchars($producto['nombre']) ?>
    </h4>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (Auth::tienePermiso('productos.editar')): ?>
        <a href="<?= $appUrl ?>/?modulo=productos&accion=editar&id=<?= $producto['id'] ?>"
           class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i>Editar
        </a>
        <?php endif; ?>
        <a href="<?= $appUrl ?>/?modulo=productos" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<!-- ======================== DATOS DEL PRODUCTO ======================== -->
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-info-circle me-1 text-primary"></i>Información general
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Código</dt>
                    <dd class="col-sm-8 font-monospace"><?= htmlspecialchars($producto['codigo']) ?></dd>

                    <?php if (!empty($producto['codigo_alterno'])): ?>
                    <dt class="col-sm-4 text-muted">Código alterno</dt>
                    <dd class="col-sm-8 font-monospace"><?= htmlspecialchars($producto['codigo_alterno']) ?></dd>
                    <?php endif; ?>

                    <dt class="col-sm-4 text-muted">Nombre</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($producto['nombre']) ?></dd>

                    <?php if (!empty($producto['descripcion'])): ?>
                    <dt class="col-sm-4 text-muted">Descripción</dt>
                    <dd class="col-sm-8"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></dd>
                    <?php endif; ?>

                    <dt class="col-sm-4 text-muted">Categoría</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($producto['categoria'] ?? '—') ?></dd>

                    <dt class="col-sm-4 text-muted">Unidad</dt>
                    <dd class="col-sm-8">
                        <?= htmlspecialchars($producto['unidad_clave'] ?? '') ?>
                        <?php if (!empty($producto['unidad_nombre'])): ?>
                        <span class="text-muted small">— <?= htmlspecialchars($producto['unidad_nombre']) ?></span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4 text-muted">Proveedor</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($producto['proveedor_nombre'] ?? '—') ?></dd>

                    <dt class="col-sm-4 text-muted">Estado</dt>
                    <dd class="col-sm-8">
                        <?php if ($producto['activo']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4 text-muted">Alta</dt>
                    <dd class="col-sm-8 small"><?= htmlspecialchars($producto['created_at'] ?? '—') ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-currency-dollar me-1 text-success"></i>Precios y stock
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-6 text-muted">Precio costo</dt>
                    <dd class="col-sm-6 text-end">
                        $<?= number_format((float) $producto['precio_costo'], 2) ?>
                    </dd>

                    <dt class="col-sm-6 text-muted">Precio venta</dt>
                    <dd class="col-sm-6 text-end fw-bold text-success">
                        $<?= number_format((float) $producto['precio_venta'], 2) ?>
                    </dd>

                    <dt class="col-sm-6 text-muted">Stock mínimo</dt>
                    <dd class="col-sm-6 text-end">
                        <?= number_format((float) $producto['stock_minimo'], 3) ?>
                    </dd>

                    <dt class="col-sm-6 text-muted">Stock total</dt>
                    <dd class="col-sm-6 text-end fw-bold <?= $stockTotal < (float) $producto['stock_minimo'] ? 'text-danger' : 'text-primary' ?>">
                        <?= number_format($stockTotal, 3) ?>
                        <?php if ($stockTotal < (float) $producto['stock_minimo']): ?>
                        <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Por debajo del mínimo"></i>
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- ======================== STOCK POR SUCURSAL ======================== -->
<div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold">
        <i class="bi bi-buildings me-1 text-info"></i>Stock por sucursal
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <?php
                    $puedeEntrada = Auth::tienePermiso('entradas.crear');
                    $puedeSalida  = Auth::tienePermiso('salidas.crear');
                    $colAcciones  = $puedeEntrada || $puedeSalida;
                ?>
                <thead class="table-light">
                    <tr>
                        <th>Sucursal</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Stock mínimo</th>
                        <th class="text-center">Estado</th>
                        <?php if ($colAcciones): ?><th class="text-center">Movimiento</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($stockSucursales)): ?>
                    <tr>
                        <td colspan="<?= $colAcciones ? 5 : 4 ?>" class="text-center text-muted py-3">Sin sucursales activas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($stockSucursales as $ss):
                        $cant = (float) $ss['cantidad'];
                        $bajo = $cant < (float) $producto['stock_minimo'];
                        $sinStock = $cant <= 0;
                    ?>
                    <tr>
                        <td>
                            <i class="bi bi-building me-1 text-muted"></i>
                            <?= htmlspecialchars($ss['sucursal_nombre']) ?>
                        </td>
                        <td class="text-end fw-semibold <?= $bajo ? 'text-danger' : 'text-success' ?>">
                            <?= number_format($cant, 3) ?>
                        </td>
                        <td class="text-end text-muted">
                            <?= number_format((float) $producto['stock_minimo'], 3) ?>
                        </td>
                        <td class="text-center">
                            <?php if ($bajo): ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Bajo mínimo
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>OK
                                </span>
                            <?php endif; ?>
                        </td>
                        <?php if ($colAcciones): ?>
                        <td class="text-center text-nowrap">
                            <?php if ($puedeEntrada): ?>
                            <a href="<?= $appUrl ?>/?modulo=entradas&accion=nueva&producto_id=<?= $producto['id'] ?>&sucursal_id=<?= $ss['sucursal_id'] ?>"
                               class="btn btn-sm btn-success py-0 px-2" title="Dar entrada en <?= htmlspecialchars($ss['sucursal_nombre'], ENT_QUOTES) ?>">
                                <i class="bi bi-box-arrow-in-down-right"></i> Entrada
                            </a>
                            <?php endif; ?>
                            <?php if ($puedeSalida): ?>
                                <?php if ($sinStock): ?>
                                <button type="button" class="btn btn-sm btn-danger py-0 px-2 ms-1" disabled
                                        title="Sin existencias en esta sucursal">
                                    <i class="bi bi-box-arrow-up-right"></i> Salida
                                </button>
                                <?php else: ?>
                                <a href="<?= $appUrl ?>/?modulo=salidas&accion=nueva&producto_id=<?= $producto['id'] ?>&sucursal_id=<?= $ss['sucursal_id'] ?>"
                                   class="btn btn-sm btn-danger py-0 px-2 ms-1" title="Dar salida en <?= htmlspecialchars($ss['sucursal_nombre'], ENT_QUOTES) ?>">
                                    <i class="bi bi-box-arrow-up-right"></i> Salida
                                </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
                <?php if (count($stockSucursales) > 1): ?>
                <tfoot class="table-light fw-semibold">
                    <tr>
                        <td>Total</td>
                        <td class="text-end"><?= number_format($stockTotal, 3) ?></td>
                        <td></td>
                        <td></td>
                        <?php if ($colAcciones): ?><td></td><?php endif; ?>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- ======================== ÚLTIMOS MOVIMIENTOS ======================== -->
<div class="card shadow-sm">
    <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
        <span><i class="bi bi-clock-history me-1 text-warning"></i>Últimos movimientos</span>
        <a href="<?= $appUrl ?>/?modulo=reportes&accion=movimientos&producto_id=<?= $producto['id'] ?>"
           class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-table me-1"></i>Ver todos
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Folio</th>
                        <th>Tipo</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Precio unit.</th>
                        <th>Sucursal</th>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">
                            <i class="bi bi-inbox d-block fs-3 mb-1"></i>
                            Sin movimientos registrados para este producto.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $mov):
                        $tipo   = $tipoLabel[$mov['tipo']]   ?? ['texto' => $mov['tipo'],   'clase' => 'bg-secondary'];
                        $estado = $estadoLabel[$mov['estado']] ?? ['texto' => $mov['estado'], 'clase' => 'bg-secondary'];
                    ?>
                    <tr>
                        <td>
                            <span class="font-monospace small fw-semibold">
                                <?= htmlspecialchars($mov['folio']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= htmlspecialchars($tipo['clase']) ?> small">
                                <?= htmlspecialchars($tipo['texto']) ?>
                            </span>
                        </td>
                        <td class="text-end fw-semibold">
                            <?= number_format((float) $mov['cantidad'], 3) ?>
                        </td>
                        <td class="text-end text-muted small">
                            <?= $mov['precio_unitario'] > 0
                                ? '$' . number_format((float) $mov['precio_unitario'], 2)
                                : '—' ?>
                        </td>
                        <td class="small"><?= htmlspecialchars($mov['sucursal']) ?></td>
                        <td class="small text-nowrap">
                            <?= htmlspecialchars(
                                date('d/m/Y H:i', strtotime($mov['created_at']))
                            ) ?>
                        </td>
                        <td class="small"><?= htmlspecialchars($mov['usuario']) ?></td>
                        <td class="text-center">
                            <span class="badge <?= htmlspecialchars($estado['clase']) ?> small">
                                <?= htmlspecialchars($estado['texto']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
