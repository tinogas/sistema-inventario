<?php
/** Vista imprimible: Pedido de reabastecimiento. Variables: $datos, $empresa, $appUrl, $sucursal_id */
$totalImporte = 0.0;
foreach ($datos as $d) { $totalImporte += (float)$d['a_pedir'] * (float)$d['precio_costo']; }
$qxlsx = http_build_query(array_filter(['modulo'=>'reportes','accion'=>'pedido','sucursal_id'=>$sucursal_id,'xlsx'=>1]));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pedido de reabastecimiento</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    body { background:#fff; color:#000; font-size:13px; }
    .doc { max-width: 1000px; margin: 0 auto; padding: 1.5rem; }
    .empresa-nombre { font-size:1.4rem; font-weight:700; color:#1a2332; }
    table { font-size:12px; }
    @media print {
        .no-print { display:none !important; }
        .doc { max-width:100%; padding:0; }
        a[href]:after { content:""; }
    }
</style>
</head>
<body>
<div class="doc">

    <!-- Barra de acciones (no se imprime) -->
    <div class="no-print d-flex justify-content-between align-items-center mb-3">
        <a href="<?= $appUrl ?>/?modulo=reportes&accion=alertas" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver a alertas
        </a>
        <div class="d-flex gap-2">
            <a href="<?= $appUrl ?>/?<?= $qxlsx ?>" class="btn btn-sm btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Exportar XLSX
            </a>
            <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Imprimir
            </button>
        </div>
    </div>

    <!-- Encabezado con datos de la empresa -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-3">
        <div>
            <div class="empresa-nombre"><?= htmlspecialchars($empresa['nombre'] ?? 'Empresa') ?></div>
            <?php if (!empty($empresa['rfc'])): ?><div>RFC: <?= htmlspecialchars($empresa['rfc']) ?></div><?php endif; ?>
            <?php if (!empty($empresa['direccion'])): ?><div><?= htmlspecialchars($empresa['direccion']) ?></div><?php endif; ?>
            <div>
                <?= htmlspecialchars(trim(($empresa['ciudad'] ?? '') . (!empty($empresa['cp']) ? ', C.P. ' . $empresa['cp'] : ''))) ?>
            </div>
            <?php if (!empty($empresa['telefono'])): ?><div>Tel: <?= htmlspecialchars($empresa['telefono']) ?></div><?php endif; ?>
            <?php if (!empty($empresa['email'])): ?><div><?= htmlspecialchars($empresa['email']) ?></div><?php endif; ?>
        </div>
        <div class="text-end">
            <h5 class="fw-bold mb-1">PEDIDO DE REABASTECIMIENTO</h5>
            <div class="text-muted">Fecha: <?= date('d/m/Y H:i') ?></div>
            <div class="text-muted"><?= count($datos) ?> partida(s)</div>
        </div>
    </div>

    <?php if (empty($datos)): ?>
    <div class="alert alert-success">No hay productos bajo el mínimo. No se requiere pedido.</div>
    <?php else: ?>
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>Código</th><th>Producto</th><th>Proveedor</th><th>Sucursal</th>
                <th class="text-center">Unidad</th>
                <th class="text-end">Stock</th><th class="text-end">Mínimo</th>
                <th class="text-end">A pedir</th>
                <th class="text-end">Costo unit.</th><th class="text-end">Importe est.</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($datos as $d):
            $importe = (float)$d['a_pedir'] * (float)$d['precio_costo'];
        ?>
            <tr>
                <td class="font-monospace"><?= htmlspecialchars($d['codigo']) ?></td>
                <td><?= htmlspecialchars($d['nombre']) ?></td>
                <td><?= htmlspecialchars($d['proveedor']) ?></td>
                <td><?= htmlspecialchars($d['sucursal']) ?></td>
                <td class="text-center"><?= htmlspecialchars($d['unidad']) ?></td>
                <td class="text-end text-danger"><?= number_format((float)$d['stock_actual'], 2) ?></td>
                <td class="text-end"><?= number_format((float)$d['stock_minimo'], 2) ?></td>
                <td class="text-end fw-bold"><?= number_format((float)$d['a_pedir'], 2) ?></td>
                <td class="text-end">$<?= number_format((float)$d['precio_costo'], 2) ?></td>
                <td class="text-end">$<?= number_format($importe, 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="table-light fw-bold">
                <td colspan="9" class="text-end">TOTAL ESTIMADO</td>
                <td class="text-end">$<?= number_format($totalImporte, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="row mt-5">
        <div class="col-6">
            <div style="border-top:1px solid #000; width:80%; padding-top:4px">Solicita</div>
        </div>
        <div class="col-6">
            <div style="border-top:1px solid #000; width:80%; padding-top:4px">Autoriza</div>
        </div>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
