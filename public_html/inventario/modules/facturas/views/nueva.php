<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-receipt text-warning me-2"></i>
        <?= isset($factura) ? 'Editar ' . htmlspecialchars($factura['folio']) : 'Nueva factura de servicio' ?>
    </h4>
    <a href="<?= $appUrl ?>/?modulo=facturas" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<?php $editId = $factura['id'] ?? 0; ?>
<form method="POST" action="<?= $appUrl ?>/?modulo=facturas&accion=guardar" id="frmFactura">
    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
    <?php if ($editId): ?><input type="hidden" name="id" value="<?= $editId ?>"><?php endif; ?>

    <div class="row g-3 mb-3">
        <!-- Sucursal -->
        <div class="col-md-4">
            <label class="form-label fw-semibold">Sucursal <span class="text-danger">*</span></label>
            <?php if (Auth::esAdmin()): ?>
            <select name="sucursal_id" class="form-select" required>
                <option value="">— Seleccionar —</option>
                <?php foreach ($sucursales as $s): ?>
                <option value="<?= $s['id'] ?>" <?= ($factura['sucursal_id'] ?? Auth::sucursalActual()) == $s['id'] ? 'selected':'' ?>>
                    <?= htmlspecialchars($s['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php else: ?>
            <input type="hidden" name="sucursal_id" value="<?= Auth::sucursalActual() ?>">
            <?php
            $db=Database::getInstance(); $sn=$db->prepare('SELECT nombre FROM sucursales WHERE id=?');
            $sn->execute([Auth::sucursalActual()]);
            ?>
            <input type="text" class="form-control" value="<?= htmlspecialchars($sn->fetchColumn()) ?>" readonly>
            <?php endif; ?>
        </div>
        <!-- Mecánico -->
        <div class="col-md-4">
            <label class="form-label fw-semibold">Mecánico</label>
            <select name="mecanico_id" class="form-select">
                <option value="">— Sin mecánico —</option>
                <?php foreach ($mecanicos as $m): ?>
                <option value="<?= $m['id'] ?>" <?= ($factura['mecanico_id']??'')==$m['id']?'selected':'' ?>>
                    <?= htmlspecialchars($m['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Servicio -->
        <div class="col-md-4">
            <label class="form-label fw-semibold">Tipo de servicio</label>
            <select name="servicio_id" class="form-select">
                <option value="">— Sin servicio —</option>
                <?php foreach ($servicios as $sv): ?>
                <option value="<?= $sv['id'] ?>" <?= ($factura['servicio_id']??'')==$sv['id']?'selected':'' ?>>
                    <?= htmlspecialchars($sv['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Datos del cliente y vehículo -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold"><i class="bi bi-person me-1"></i>Datos del cliente y vehículo</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Nombre del cliente <span class="text-danger">*</span></label>
                    <input type="text" name="cliente_nombre" class="form-control" required maxlength="150"
                           value="<?= htmlspecialchars($factura['cliente_nombre'] ?? '') ?>"
                           placeholder="Nombre completo">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Teléfono</label>
                    <input type="text" name="cliente_tel" class="form-control" maxlength="25"
                           value="<?= htmlspecialchars($factura['cliente_tel'] ?? '') ?>"
                           placeholder="662-123-4567">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Marca <span class="text-danger">*</span></label>
                    <input type="text" name="vh_marca" class="form-control" required maxlength="60"
                           value="<?= htmlspecialchars($factura['vh_marca'] ?? '') ?>"
                           placeholder="Toyota, Ford, Chevrolet…">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Modelo <span class="text-danger">*</span></label>
                    <input type="text" name="vh_modelo" class="form-control" required maxlength="80"
                           value="<?= htmlspecialchars($factura['vh_modelo'] ?? '') ?>"
                           placeholder="Hilux, F-150, Silverado…">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Año <span class="text-danger">*</span></label>
                    <input type="number" name="vh_anio" class="form-control" required min="1980" max="<?= date('Y')+1 ?>"
                           value="<?= $factura['vh_anio'] ?? date('Y') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Placas</label>
                    <input type="text" name="vh_placas" class="form-control" maxlength="20"
                           value="<?= htmlspecialchars($factura['vh_placas'] ?? '') ?>"
                           placeholder="ABC-123" style="text-transform:uppercase">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Ref. Proneg</label>
                    <input type="text" name="referencia_proneg" class="form-control" maxlength="80"
                           value="<?= htmlspecialchars($factura['referencia_proneg'] ?? '') ?>"
                           placeholder="Folio Proneg">
                </div>
            </div>
        </div>
    </div>

    <!-- Escáner de partes -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-warning bg-opacity-10 d-flex align-items-center gap-2">
            <i class="bi bi-upc-scan fs-5 text-warning"></i>
            <span class="fw-semibold">Partes / Refacciones utilizadas</span>
            <span class="badge bg-warning text-dark ms-auto">Escáner activo</span>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Código o nombre del producto</label>
                    <div class="escaner-wrap">
                        <input type="text" id="inputEscaner" class="form-control"
                               placeholder="Escanea o escribe el código…" autocomplete="off">
                        <span class="escaner-icon"><i class="bi bi-upc-scan"></i></span>
                    </div>
                    <div id="sugerenciasWrap" class="position-relative">
                        <ul id="listaSugerencias" class="list-group position-absolute w-100 shadow" style="z-index:999;display:none;max-height:200px;overflow-y:auto"></ul>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Cantidad</label>
                    <input type="number" id="inputCantidad" class="form-control" value="1" min="0.001" step="any">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Precio venta ($)</label>
                    <input type="number" id="inputPrecio" class="form-control" value="0" min="0" step="any">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-warning w-100" id="btnAgregar">
                        <i class="bi bi-plus-lg me-1"></i> Agregar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de partidas -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">
            Partes <span class="badge bg-warning text-dark ms-2" id="numPartidas">0</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table tabla-partidas mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Código</th><th>Producto</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Precio unit.</th>
                            <th class="text-end">Importe</th>
                            <th></th></tr>
                    </thead>
                    <tbody id="bodyPartidas">
                        <tr id="trVacio"><td colspan="7" class="text-center text-muted py-3">
                            <i class="bi bi-upc-scan me-1"></i>Agrega partes/refacciones
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Mano de obra y totales -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Descripción de mano de obra</label>
            <input type="text" name="mano_obra_desc" class="form-control" maxlength="200"
                   value="<?= htmlspecialchars($factura['mano_obra_desc'] ?? '') ?>"
                   placeholder="Cambio de muelles delanteros, alineación…">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Costo mano de obra ($)</label>
            <input type="number" name="mano_obra" id="inputManoObra" class="form-control"
                   value="<?= $factura['mano_obra'] ?? 0 ?>" min="0" step="any" onchange="calcularTotales()">
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-0 h-100">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between small"><span>Subtotal partes:</span><span id="lblSubtotal">$0.00</span></div>
                    <div class="d-flex justify-content-between small"><span>Mano de obra:</span><span id="lblManoObra">$0.00</span></div>
                    <hr class="my-1">
                    <div class="d-flex justify-content-between fw-bold"><span>Total:</span><span id="lblTotal" class="text-warning fs-5">$0.00</span></div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Notas</label>
            <textarea name="notas" class="form-control" rows="2" maxlength="500"
                      placeholder="Observaciones…"><?= htmlspecialchars($factura['notas'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end">
        <a href="<?= $appUrl ?>/?modulo=facturas" class="btn btn-secondary">Cancelar</a>
        <button type="submit" name="accion_form" value="borrador" class="btn btn-outline-warning" id="btnBorrador">
            <i class="bi bi-floppy me-1"></i> Guardar borrador
        </button>
    </div>
</form>

<script src="<?= $appUrl ?>/assets/js/escaner.js"></script>
<script>
const APP_URL = '<?= $appUrl ?>';

// Pre-cargar partidas existentes (modo editar)
let partidas = <?= isset($detalle) ? json_encode(array_map(fn($d) => [
    'producto_id'    => (int)$d['producto_id'],
    'codigo'         => $d['codigo'],
    'nombre'         => $d['producto_nombre'],
    'cantidad'       => (float)$d['cantidad'],
    'precio_unitario'=> (float)$d['precio_unitario'],
], $detalle)) : '[]' ?>;

EscanerHandler.iniciar(function(codigo) { buscarProducto(codigo); });
if (partidas.length) renderTabla();

function buscarProducto(codigo) {
    if (!codigo.trim()) return;
    fetch(APP_URL + '/api/productos_buscar.php?codigo=' + encodeURIComponent(codigo))
        .then(r => r.json())
        .then(data => {
            if (data.encontrado) {
                document.getElementById('inputPrecio').value = data.producto.precio_venta || 0;
                agregarProducto(data.producto);
            } else mostrarAlerta('Código no encontrado: ' + codigo, 'warning');
        });
}

function agregarProducto(prod) {
    const qty   = parseFloat(document.getElementById('inputCantidad').value) || 1;
    const precio = parseFloat(document.getElementById('inputPrecio').value) || (prod.precio_venta || 0);
    const idx = partidas.findIndex(p => p.producto_id == prod.id);
    if (idx >= 0) { partidas[idx].cantidad += qty; }
    else partidas.push({ producto_id: prod.id, codigo: prod.codigo, nombre: prod.nombre, cantidad: qty, precio_unitario: precio });
    renderTabla();
    document.getElementById('inputEscaner').value  = '';
    document.getElementById('inputCantidad').value = '1';
    document.getElementById('inputEscaner').focus();
}

function renderTabla() {
    const tbody = document.getElementById('bodyPartidas');
    tbody.innerHTML = '';
    if (!partidas.length) { tbody.appendChild(document.getElementById('trVacio')); document.getElementById('numPartidas').textContent='0'; calcularTotales(); return; }
    partidas.forEach((p, i) => {
        const sub = p.cantidad * p.precio_unitario;
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${i+1}</td><td><code>${esc(p.codigo)}</code></td><td>${esc(p.nombre)}</td>
        <td class="text-end">
            <input type="hidden" name="producto_id[]" value="${p.producto_id}">
            <input type="hidden" name="precio_unitario[]" value="${p.precio_unitario}">
            <input type="number" name="cantidad[]" class="form-control form-control-sm text-end" style="width:80px;display:inline-block"
                   value="${p.cantidad}" min="0.001" step="any" data-idx="${i}" onchange="cambiarCantidad(this)">
        </td>
        <td class="text-end">$${p.precio_unitario.toFixed(2)}</td>
        <td class="text-end fw-semibold importe-col">$${sub.toFixed(2)}</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarPartida(${i})"><i class="bi bi-trash"></i></button></td>`;
        tbody.appendChild(tr);
    });
    document.getElementById('numPartidas').textContent = partidas.length;
    calcularTotales();
}

function calcularTotales() {
    const subtotal  = partidas.reduce((s,p) => s + p.cantidad*p.precio_unitario, 0);
    const manoObra  = parseFloat(document.getElementById('inputManoObra').value) || 0;
    const total     = subtotal + manoObra;
    document.getElementById('lblSubtotal').textContent = '$'+subtotal.toFixed(2);
    document.getElementById('lblManoObra').textContent = '$'+manoObra.toFixed(2);
    document.getElementById('lblTotal').textContent    = '$'+total.toFixed(2);
}

function cambiarCantidad(input) { partidas[parseInt(input.dataset.idx)].cantidad=parseFloat(input.value)||0; renderTabla(); }
function quitarPartida(idx) { partidas.splice(idx,1); renderTabla(); }
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function mostrarAlerta(msg, tipo) {
    const div=document.createElement('div');
    div.className=`alert alert-${tipo} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
    div.style.zIndex=9999;
    const txt=document.createElement('span');
    txt.textContent=msg;
    const btn=document.createElement('button');
    btn.type='button'; btn.className='btn-close'; btn.setAttribute('data-bs-dismiss','alert');
    div.appendChild(txt); div.appendChild(btn);
    document.body.appendChild(div); setTimeout(()=>div.remove(),4000);
}

document.getElementById('btnAgregar').addEventListener('click', function() { const c=document.getElementById('inputEscaner').value.trim(); if(c) buscarProducto(c); });
document.getElementById('inputEscaner').addEventListener('keydown', function(e) { if(e.key==='Enter'){e.preventDefault(); if(this.value.trim()) buscarProducto(this.value.trim());} });

let debounce=null;
document.getElementById('inputEscaner').addEventListener('input', function() {
    const q=this.value.trim(); clearTimeout(debounce);
    if(q.length<2){document.getElementById('listaSugerencias').style.display='none';return;}
    debounce=setTimeout(()=>fetch(APP_URL+'/api/productos_buscar.php?q='+encodeURIComponent(q)).then(r=>r.json()).then(d=>{
        const lista=document.getElementById('listaSugerencias'); lista.innerHTML='';
        (d.sugerencias||[]).forEach(item=>{
            const li=document.createElement('li'); li.className='list-group-item list-group-item-action cursor-pointer py-1';
            li.textContent=`[${item.codigo}] ${item.nombre}`;
            li.addEventListener('click',()=>{ lista.style.display='none'; document.getElementById('inputEscaner').value=item.codigo; buscarProducto(item.codigo); });
            lista.appendChild(li);
        }); lista.style.display=(d.sugerencias||[]).length?'block':'none';
    }),250);
});
document.addEventListener('click',e=>{ if(!e.target.closest('#sugerenciasWrap')&&!e.target.closest('#inputEscaner')) document.getElementById('listaSugerencias').style.display='none'; });
document.getElementById('inputManoObra').addEventListener('input', calcularTotales);
</script>
