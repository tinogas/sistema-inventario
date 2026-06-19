<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bitácora — <?= htmlspecialchars($bitacora['folio']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; margin: 0; padding: 20px; }
        .membrete { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 2px solid #333; padding-bottom: 12px; }
        .membrete-empresa h2 { margin: 0 0 4px 0; font-size: 16px; }
        .membrete-empresa p  { margin: 2px 0; font-size: 11px; color: #555; }
        .membrete-doc { text-align: right; }
        .membrete-doc h3 { margin: 0; font-size: 14px; color: #1a56db; }
        .membrete-doc p  { margin: 2px 0; font-size: 11px; color: #555; }
        .seccion { margin-bottom: 14px; }
        .seccion h4 { font-size: 11px; text-transform: uppercase; color: #555; border-bottom: 1px solid #ddd; padding-bottom: 3px; margin: 0 0 6px 0; }
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        table th { background: #f3f4f6; padding: 4px 6px; text-align: left; border: 1px solid #ddd; }
        table td { padding: 4px 6px; border: 1px solid #ddd; }
        .text-right { text-align: right; }
        .text-bold  { font-weight: bold; }
        .footer-totales { margin-top: 8px; text-align: right; }
        .footer-totales table { width: auto; margin-left: auto; }
        .footer-totales td { border: none; padding: 2px 8px; }
        .total-final { font-size: 14px; font-weight: bold; color: #1a56db; }
        @media print {
            body { padding: 10px; }
            button { display: none; }
        }
    </style>
</head>
<body>

<!-- Membrete -->
<div class="membrete">
    <div class="membrete-empresa">
        <h2><?= htmlspecialchars($empresa['razon_social'] ?? $empresa['nombre'] ?? 'Taller') ?></h2>
        <?php if (!empty($empresa['rfc'])): ?>
        <p>RFC: <?= htmlspecialchars($empresa['rfc']) ?></p>
        <?php endif; ?>
        <?php if (!empty($empresa['direccion'])): ?>
        <p><?= htmlspecialchars($empresa['direccion']) ?></p>
        <?php endif; ?>
        <?php if (!empty($empresa['telefono'])): ?>
        <p>Tel: <?= htmlspecialchars($empresa['telefono']) ?></p>
        <?php endif; ?>
    </div>
    <div class="membrete-doc">
        <h3>BITÁCORA DE SERVICIO</h3>
        <p>Folio: <strong><?= htmlspecialchars($bitacora['folio']) ?></strong></p>
        <p>Fecha: <?= date('d/m/Y', strtotime($bitacora['fecha_servicio'])) ?></p>
        <p>Mecánico: <?= htmlspecialchars($bitacora['mecanico_nombre']) ?></p>
    </div>
</div>

<div class="grid2">
    <!-- Datos del cliente -->
    <div class="seccion">
        <h4>Cliente</h4>
        <table>
            <tr><th style="width:80px">Nombre</th><td><?= htmlspecialchars($bitacora['cliente_nombre']) ?></td></tr>
            <?php if ($bitacora['cliente_rfc']): ?>
            <tr><th>RFC</th><td><?= htmlspecialchars($bitacora['cliente_rfc']) ?></td></tr>
            <?php endif; ?>
            <?php if ($bitacora['cliente_tel']): ?>
            <tr><th>Teléfono</th><td><?= htmlspecialchars($bitacora['cliente_tel']) ?></td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Datos del vehículo -->
    <div class="seccion">
        <h4>Vehículo</h4>
        <table>
            <tr><th style="width:80px">Marca/Modelo</th><td><?= htmlspecialchars($bitacora['marca'] . ' ' . $bitacora['modelo']) ?></td></tr>
            <tr><th>Año</th><td><?= $bitacora['anio'] ?: '—' ?></td></tr>
            <tr><th>Placas</th><td><?= htmlspecialchars($bitacora['placas'] ?: '—') ?></td></tr>
            <?php if ($bitacora['numero_serie']): ?>
            <tr><th>No. Serie</th><td><?= htmlspecialchars($bitacora['numero_serie']) ?></td></tr>
            <?php endif; ?>
            <?php if ($bitacora['color']): ?>
            <tr><th>Color</th><td><?= htmlspecialchars($bitacora['color']) ?></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Descripción / trabajos -->
<?php if ($bitacora['descripcion'] || $bitacora['trabajos_realizados']): ?>
<div class="seccion">
    <h4>Trabajos realizados</h4>
    <?php if ($bitacora['descripcion']): ?>
    <p style="margin:0 0 4px 0"><?= nl2br(htmlspecialchars($bitacora['descripcion'])) ?></p>
    <?php endif; ?>
    <?php if ($bitacora['trabajos_realizados']): ?>
    <p style="margin:0"><?= nl2br(htmlspecialchars($bitacora['trabajos_realizados'])) ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Partes -->
<?php if (!empty($productos)): ?>
<div class="seccion">
    <h4>Partes / Refacciones</h4>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th class="text-right" style="width:70px">Cant.</th>
                <th class="text-right" style="width:90px">Precio unit.</th>
                <th class="text-right" style="width:90px">Importe</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($productos as $i => $p): $imp = $p['cantidad'] * $p['precio_unitario']; ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($p['nombre']) ?></td>
            <td class="text-right"><?= number_format($p['cantidad'], 3) ?></td>
            <td class="text-right">$<?= number_format($p['precio_unitario'], 2) ?></td>
            <td class="text-right">$<?= number_format($imp, 2) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Totales -->
<div class="footer-totales">
    <table>
        <tr><td>Subtotal partes:</td><td class="text-right text-bold">$<?= number_format($bitacora['subtotal'], 2) ?></td></tr>
        <?php if ($bitacora['mano_obra'] > 0): ?>
        <tr><td>Mano de obra:</td><td class="text-right text-bold">$<?= number_format($bitacora['mano_obra'], 2) ?></td></tr>
        <?php endif; ?>
        <tr><td class="total-final">TOTAL:</td><td class="text-right total-final">$<?= number_format($bitacora['total'], 2) ?></td></tr>
    </table>
</div>

<div style="margin-top:30px;display:flex;justify-content:space-between">
    <div style="text-align:center;border-top:1px solid #999;width:180px;padding-top:4px;font-size:11px">Firma del cliente</div>
    <div style="text-align:center;border-top:1px solid #999;width:180px;padding-top:4px;font-size:11px">Firma del mecánico</div>
</div>

<div style="margin-top:20px;text-align:right">
    <button onclick="window.print()" style="padding:6px 16px;cursor:pointer;background:#1a56db;color:#fff;border:none;border-radius:4px;font-size:12px">
        Imprimir
    </button>
</div>

</body>
</html>
