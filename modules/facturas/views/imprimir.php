<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura <?= htmlspecialchars($factura['folio']) ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; font-size:12px; color:#222; background:#fff; padding:20px; }
        .page { max-width:800px; margin:0 auto; }

        /* Membrete */
        .membrete { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3px solid #f59e0b; padding-bottom:12px; margin-bottom:16px; }
        .membrete .taller h1 { font-size:20px; color:#1a2332; margin-bottom:4px; }
        .membrete .taller p { color:#555; font-size:11px; line-height:1.6; }
        .membrete .folio-box { text-align:right; }
        .membrete .folio-box .folio { font-size:22px; font-weight:bold; color:#f59e0b; }
        .membrete .folio-box .fecha { font-size:11px; color:#666; margin-top:4px; }
        .badge-estado { display:inline-block; padding:3px 10px; border-radius:4px; font-size:11px; font-weight:bold; }
        .estado-emitida { background:#dbeafe; color:#1e40af; }
        .estado-pagada  { background:#dcfce7; color:#166534; }

        /* Secciones de datos */
        .grid2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; }
        .seccion { border:1px solid #e2e8f0; border-radius:6px; padding:10px 12px; }
        .seccion h3 { font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-bottom:8px; border-bottom:1px solid #f1f5f9; padding-bottom:4px; }
        .seccion table { width:100%; }
        .seccion table th { font-weight:600; width:40%; color:#374151; padding:2px 0; }
        .seccion table td { color:#111; padding:2px 0; }

        /* Tabla de partidas */
        .partidas { width:100%; border-collapse:collapse; margin-bottom:0; }
        .partidas th { background:#f8fafc; border:1px solid #e2e8f0; padding:6px 8px; text-align:left; font-size:11px; text-transform:uppercase; color:#64748b; }
        .partidas td { border:1px solid #e2e8f0; padding:6px 8px; vertical-align:top; }
        .partidas .text-right { text-align:right; }
        .partidas tfoot td { font-weight:bold; background:#f8fafc; }
        .total-final { font-size:16px; color:#f59e0b; }

        /* Firma y notas */
        .firma-section { display:grid; grid-template-columns:1fr 1fr; gap:40px; margin-top:30px; }
        .firma-box { border-top:1px solid #374151; padding-top:6px; text-align:center; font-size:11px; color:#555; }
        .nota-fiscal { margin-top:20px; padding:10px; background:#fef9c3; border-left:3px solid #f59e0b; font-size:11px; color:#78350f; }

        /* Botón de impresión (no aparece al imprimir) */
        .btn-print { position:fixed; top:20px; right:20px; background:#f59e0b; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-size:14px; font-weight:bold; }

        @page {
            size: letter portrait;   /* 8.5in × 11in */
            margin: 1.5cm 1.8cm;     /* márgenes cómodos para carta */
        }

        @media print {
            .btn-print { display:none; }
            body  { padding:0; background:#fff; font-size:11px; }
            .page { max-width:100%; }

            /* Evitar que las secciones se corten entre páginas */
            .seccion, .firma-section { break-inside:avoid; }
            .partidas thead { display:table-header-group; } /* repite encabezado si hay salto */
            .partidas tfoot { display:table-footer-group; }
            .nota-fiscal    { break-inside:avoid; background:#fef9c3 !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }

            /* Forzar colores en impresión */
            .membrete { border-bottom-color:#f59e0b !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
            .total-final { color:#f59e0b !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        }
    </style>
</head>
<body>
<button class="btn-print" onclick="window.print()">🖨️ Imprimir</button>

<div class="page">
    <!-- Membrete -->
    <div class="membrete">
        <div class="taller">
            <h1>Taller de Muelles y Suspensiones</h1>
            <p>
                <?= htmlspecialchars($factura['sucursal_nombre']) ?> — <?= htmlspecialchars($factura['ciudad']) ?><br>
                <?php if ($factura['sucursal_dir']): ?>
                <?= htmlspecialchars($factura['sucursal_dir']) ?><br>
                <?php endif; ?>
                <?php if ($factura['sucursal_tel']): ?>
                Tel: <?= htmlspecialchars($factura['sucursal_tel']) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="folio-box">
            <div class="folio"><?= htmlspecialchars($factura['folio']) ?></div>
            <div class="fecha">
                Fecha: <?= date('d/m/Y', strtotime($factura['fecha_emision'] ?: $factura['created_at'])) ?>
            </div>
            <div style="margin-top:6px">
                <span class="badge-estado estado-<?= htmlspecialchars($factura['estado']) ?>">
                    <?= htmlspecialchars(strtoupper($factura['estado'])) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Datos del cliente y vehículo -->
    <div class="grid2">
        <div class="seccion">
            <h3>Datos del cliente</h3>
            <table>
                <tr><th>Nombre:</th><td><?= htmlspecialchars($factura['cliente_nombre']) ?></td></tr>
                <tr><th>Teléfono:</th><td><?= htmlspecialchars($factura['cliente_tel'] ?: '—') ?></td></tr>
            </table>
        </div>
        <div class="seccion">
            <h3>Vehículo</h3>
            <table>
                <tr><th>Marca / Modelo:</th><td><?= htmlspecialchars($factura['vh_marca'] . ' ' . $factura['vh_modelo']) ?></td></tr>
                <tr><th>Año:</th><td><?= $factura['vh_anio'] ?></td></tr>
                <tr><th>Placas:</th><td><?= htmlspecialchars($factura['vh_placas'] ?: '—') ?></td></tr>
            </table>
        </div>
        <div class="seccion">
            <h3>Servicio</h3>
            <table>
                <tr><th>Mecánico:</th><td><?= htmlspecialchars($factura['mecanico_nombre']) ?></td></tr>
                <?php if (!empty($serviciosDetalle)): ?>
                    <?php foreach ($serviciosDetalle as $s): ?>
                    <tr>
                        <th><?= htmlspecialchars($s['servicio_nombre']) ?>:</th>
                        <td><?= htmlspecialchars($s['descripcion'] ?: '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr><th>Tipo:</th><td><?= htmlspecialchars($factura['servicio_nombre']) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
        <?php if ($factura['referencia_proneg'] || $factura['notas']): ?>
        <div class="seccion">
            <h3>Notas</h3>
            <?php if ($factura['referencia_proneg']): ?>
            <p><strong>Ref. Proneg:</strong> <?= htmlspecialchars($factura['referencia_proneg']) ?></p>
            <?php endif; ?>
            <?php if ($factura['notas']): ?><p><?= htmlspecialchars($factura['notas']) ?></p><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tabla de partidas -->
    <table class="partidas">
        <thead>
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Unidad</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Precio unit.</th>
                <th class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
        <?php $subtotal=0; foreach ($detalle as $i => $d): $imp=$d['cantidad']*$d['precio_unitario']; $subtotal+=$imp; ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($d['codigo']) ?></td>
            <td><?= htmlspecialchars($d['producto_nombre']) ?></td>
            <td><?= htmlspecialchars($d['unidad']) ?></td>
            <td class="text-right"><?= number_format($d['cantidad'],3) ?></td>
            <td class="text-right">$<?= number_format($d['precio_unitario'],2) ?></td>
            <td class="text-right">$<?= number_format($imp,2) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"></td>
                <td class="text-right">Subtotal partes:</td>
                <td class="text-right">$<?= number_format($subtotal,2) ?></td>
            </tr>
            <?php if (!empty($serviciosDetalle)): ?>
                <?php foreach ($serviciosDetalle as $s): ?>
                <tr>
                    <td colspan="5"></td>
                    <td class="text-right"><?= htmlspecialchars($s['servicio_nombre']) ?><?= $s['descripcion'] ? ': ' . htmlspecialchars($s['descripcion']) : '' ?>:</td>
                    <td class="text-right">$<?= number_format($s['mano_obra'],2) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php elseif ($factura['mano_obra'] > 0): ?>
            <tr>
                <td colspan="5"></td>
                <td class="text-right"><?= htmlspecialchars($factura['mano_obra_desc'] ?: 'Mano de obra') ?>:</td>
                <td class="text-right">$<?= number_format($factura['mano_obra'],2) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="5"></td>
                <td class="text-right total-final">TOTAL:</td>
                <td class="text-right total-final">$<?= number_format($factura['total'],2) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Firmas -->
    <div class="firma-section">
        <div class="firma-box">Firma del cliente<br><br><br><?= htmlspecialchars($factura['cliente_nombre']) ?></div>
        <div class="firma-box">Autorizado por<br><br><br>Taller de Muelles y Suspensiones</div>
    </div>

    <!-- Nota fiscal -->
    <div class="nota-fiscal">
        Este documento es un comprobante interno de servicio. Para efectos fiscales, solicite su CFDI al personal del taller.
    </div>
</div>

<script>
// Imprimir automáticamente si viene con ?auto=1
if (new URLSearchParams(location.search).get('auto') === '1') window.print();
</script>
</body>
</html>
