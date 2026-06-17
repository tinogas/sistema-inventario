<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>Kardex de producto</h4>
</div>

<!-- Selector de producto -->
<form method="GET" action="<?= $appUrl ?>/" class="row g-2 mb-4 align-items-end">
    <input type="hidden" name="modulo" value="reportes">
    <input type="hidden" name="accion" value="kardex">
    <?php if ($sucursal_id): ?><input type="hidden" name="sucursal_id" value="<?= $sucursal_id ?>"><?php endif; ?>
    <div class="col-md-4">
        <label class="form-label small fw-semibold">Producto (código o nombre)</label>
        <input type="text" id="buscarProductoKardex" class="form-control form-control-sm"
               placeholder="Busca el producto…"
               value="<?= $producto ? htmlspecialchars($producto['codigo'] . ' — ' . $producto['nombre']) : '' ?>">
        <input type="hidden" name="producto_id" id="hidProductoId" value="<?= $producto_id ?>">
        <div class="position-relative">
            <ul id="listaSugKardex" class="list-group position-absolute w-100 shadow" style="z-index:999;display:none;max-height:200px;overflow-y:auto"></ul>
        </div>
    </div>
    <div class="col-md-2">
        <label class="form-label small">Desde</label>
        <input type="date" name="desde" class="form-control form-control-sm" value="<?= htmlspecialchars($desde) ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label small">Hasta</label>
        <input type="date" name="hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($hasta) ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-search me-1"></i>Ver kardex</button>
    </div>
</form>

<?php if ($producto): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
        <strong><?= htmlspecialchars($producto['codigo']) ?></strong> — <?= htmlspecialchars($producto['nombre']) ?>
    </div>
    <div class="card-body p-0">
        <?php if (empty($kardex)): ?>
        <p class="text-muted text-center py-4">Sin movimientos en el período seleccionado.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Folio</th><th>Tipo</th><th>Sucursal</th><th>Ref.</th>
                        <th class="text-end text-success">Entrada</th>
                        <th class="text-end text-danger">Salida</th>
                        <th class="text-end">Saldo</th></tr>
                </thead>
                <tbody>
                <?php $saldo = 0; ?>
                <?php foreach ($kardex as $k): ?>
                <?php $saldo += $k['entrada'] - $k['salida']; ?>
                <tr>
                    <td class="small"><?= date('d/m/Y H:i', strtotime($k['created_at'])) ?></td>
                    <td><code class="small"><?= htmlspecialchars($k['folio']) ?></code></td>
                    <td><small><?= ucfirst(str_replace('_',' ',$k['tipo'])) ?></small></td>
                    <td><?= htmlspecialchars($k['sucursal']) ?></td>
                    <td class="small"><?= htmlspecialchars($k['referencia_factura'] ?: '—') ?></td>
                    <td class="text-end text-success"><?= $k['entrada'] > 0 ? number_format($k['entrada'],3) : '—' ?></td>
                    <td class="text-end text-danger"><?= $k['salida'] > 0 ? number_format($k['salida'],3) : '—' ?></td>
                    <td class="text-end fw-semibold <?= $saldo < 0 ? 'text-danger' : '' ?>"><?= number_format($saldo,3) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="fw-bold text-end">Totales:</td>
                        <td class="text-end text-success fw-bold"><?= number_format(array_sum(array_column($kardex,'entrada')),3) ?></td>
                        <td class="text-end text-danger fw-bold"><?= number_format(array_sum(array_column($kardex,'salida')),3) ?></td>
                        <td class="text-end fw-bold"><?= number_format($saldo,3) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php elseif ($producto_id): ?>
<div class="alert alert-warning">Producto no encontrado.</div>
<?php endif; ?>

<script>
const APP_URL = '<?= $appUrl ?>';
let debounce=null;
document.getElementById('buscarProductoKardex').addEventListener('input', function() {
    const q=this.value.trim(); clearTimeout(debounce);
    if(q.length<2){document.getElementById('listaSugKardex').style.display='none';return;}
    debounce=setTimeout(()=>{
        fetch(APP_URL+'/api/productos_buscar.php?q='+encodeURIComponent(q))
            .then(r=>r.json()).then(d=>{
                const lista=document.getElementById('listaSugKardex'); lista.innerHTML='';
                (d.sugerencias||[]).forEach(item=>{
                    const li=document.createElement('li');
                    li.className='list-group-item list-group-item-action cursor-pointer py-1';
                    li.textContent=`[${item.codigo}] ${item.nombre}`;
                    li.addEventListener('click',()=>{
                        document.getElementById('buscarProductoKardex').value=`${item.codigo} — ${item.nombre}`;
                        document.getElementById('hidProductoId').value=item.id;
                        lista.style.display='none';
                    });
                    lista.appendChild(li);
                });
                lista.style.display=(d.sugerencias||[]).length?'block':'none';
            });
    },250);
});
</script>
