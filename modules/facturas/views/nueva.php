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
        <div class="col-md-6">
            <label class="form-label fw-semibold">Sucursal <span class="text-danger">*</span></label>
            <?php if (Auth::esAdmin()): ?>
            <select name="sucursal_id" id="selSucursal" class="form-select" required onchange="filtrarMecanicos()">
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
        <!-- Mecánico (se filtra por la sucursal seleccionada) -->
        <div class="col-md-6">
            <label class="form-label fw-semibold">Mecánico</label>
            <select name="mecanico_id" id="selMecanico" class="form-select">
                <option value="">— Sin mecánico —</option>
                <?php foreach ($mecanicos as $m): ?>
                <option value="<?= $m['id'] ?>" data-sucursal="<?= (int)$m['sucursal_id'] ?>"
                        <?= ($factura['mecanico_id']??'')==$m['id']?'selected':'' ?>>
                    <?= htmlspecialchars($m['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Búsqueda de cliente del catálogo -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-primary bg-opacity-10 fw-semibold">
            <i class="bi bi-person-check me-1 text-primary"></i>
            Cliente del catálogo
            <span class="text-muted fw-normal small ms-2">— opcional, auto-rellena los campos</span>
        </div>
        <div class="card-body pb-2">
            <input type="hidden" name="cliente_id" id="hidClienteId" value="<?= (int)($factura['cliente_id'] ?? 0) ?>">
            <input type="hidden" name="unidad_id"  id="hidUnidadId"  value="<?= (int)($factura['unidad_id']  ?? 0) ?>">

            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Buscar cliente</label>
                    <div class="position-relative">
                        <input type="text" id="inputClienteBuscar" class="form-control"
                               placeholder="Nombre, RFC o teléfono…"
                               value="<?= htmlspecialchars($factura['cliente_nombre'] ?? '') ?>"
                               autocomplete="off">
                        <ul id="listaClientesSug" class="list-group position-absolute w-100 shadow"
                            style="z-index:999;display:none;max-height:200px;overflow-y:auto"></ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Unidad del cliente</label>
                    <select id="selUnidadCatalogo" class="form-select">
                        <option value="">— Seleccionar unidad —</option>
                        <?php if (!empty($factura['unidad_id'])): ?>
                        <option value="<?= (int)$factura['unidad_id'] ?>" selected>
                            <?= htmlspecialchars(($factura['vh_marca'] ?? '') . ' ' . ($factura['vh_modelo'] ?? '') . ' ' . ($factura['vh_placas'] ?? '')) ?>
                        </option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="btnLimpiarCliente" title="Quitar cliente del catálogo">
                        <i class="bi bi-x-circle me-1"></i> Quitar
                    </button>
                    <a href="<?= $appUrl ?>/?modulo=clientes&accion=nuevo" target="_blank"
                       class="btn btn-sm btn-outline-primary" title="Crear nuevo cliente">
                        <i class="bi bi-person-plus"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Datos del cliente y vehículo -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-light fw-semibold"><i class="bi bi-person me-1"></i>Datos del cliente y vehículo</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Nombre del cliente <span class="text-danger">*</span></label>
                    <input type="text" name="cliente_nombre" id="inputClienteNombre" class="form-control" required maxlength="150"
                           value="<?= htmlspecialchars($factura['cliente_nombre'] ?? '') ?>"
                           placeholder="Nombre completo">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Teléfono</label>
                    <input type="text" name="cliente_tel" id="inputClienteTel" class="form-control" maxlength="25"
                           value="<?= htmlspecialchars($factura['cliente_tel'] ?? '') ?>"
                           placeholder="662-123-4567">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Marca <span class="text-danger">*</span></label>
                    <input type="text" name="vh_marca" id="inputVhMarca" class="form-control" required maxlength="60"
                           value="<?= htmlspecialchars($factura['vh_marca'] ?? '') ?>"
                           placeholder="Toyota, Ford, Chevrolet…">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Modelo <span class="text-danger">*</span></label>
                    <input type="text" name="vh_modelo" id="inputVhModelo" class="form-control" required maxlength="80"
                           value="<?= htmlspecialchars($factura['vh_modelo'] ?? '') ?>"
                           placeholder="Hilux, F-150, Silverado…">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Año <span class="text-danger">*</span></label>
                    <input type="number" name="vh_anio" id="inputVhAnio" class="form-control" required min="1980" max="<?= date('Y')+1 ?>"
                           value="<?= $factura['vh_anio'] ?? date('Y') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Placas</label>
                    <input type="text" name="vh_placas" id="inputVhPlacas" class="form-control" maxlength="20"
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

    <!-- Servicios / Mano de obra -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-info bg-opacity-10 fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-tools text-info"></i>
            Servicios / Mano de obra
            <span class="badge bg-info text-dark ms-1" id="numServicios">0</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0" id="tabla-servicios">
                    <thead class="table-light">
                        <tr>
                            <th style="width:35%">Tipo de servicio</th>
                            <th>Descripción</th>
                            <th class="text-end" style="width:140px">Mano de obra ($)</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="body-servicios">
                        <tr id="tr-sin-servicios">
                            <td colspan="4" class="text-center text-muted py-2">Sin servicios agregados</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            <button type="button" id="btn-agregar-servicio" class="btn btn-sm btn-outline-info">
                <i class="bi bi-plus-lg me-1"></i> Agregar servicio
            </button>
        </div>
    </div>

    <!-- Descuento, totales y notas -->
    <div class="row g-3 mb-4">
        <div class="col-md-7">
            <label class="form-label fw-semibold">Notas</label>
            <textarea name="notas" class="form-control" rows="3" maxlength="500"
                      placeholder="Observaciones…"><?= htmlspecialchars($factura['notas'] ?? '') ?></textarea>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Descuento</label>
            <div class="input-group">
<?php $tieneDesc = (float)($factura['descuento_pct'] ?? 0) > 0; ?>
                <div class="input-group-text">
                    <input class="form-check-input mt-0" type="checkbox" id="chkDescuento"
                           onchange="toggleDescuento(this)" title="Aplicar descuento"
                           <?= $tieneDesc ? 'checked' : '' ?>>
                </div>
                <input type="number" name="descuento_pct" id="inputDescuento" class="form-control text-end"
                       value="<?= (float)($factura['descuento_pct'] ?? 0) ?>"
                       min="0" max="100" step="0.1" placeholder="%" <?= $tieneDesc ? '' : 'disabled' ?>
                       oninput="calcularTotales()">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-0 h-100">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between small"><span>Partes:</span><span id="lblSubtotal">$0.00</span></div>
                    <div class="d-flex justify-content-between small"><span>Mano de obra:</span><span id="lblManoObra">$0.00</span></div>
                    <div class="d-flex justify-content-between small text-danger" id="rowDescuento" style="display:none!important">
                        <span>Descuento (<span id="lblPct">0</span>%):</span><span id="lblDescuento" class="text-danger">−$0.00</span>
                    </div>
                    <hr class="my-1">
                    <div class="d-flex justify-content-between fw-bold"><span>Total:</span><span id="lblTotal" class="text-warning fs-5">$0.00</span></div>
                </div>
            </div>
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

let partidas = <?= isset($detalle) ? json_encode(array_map(fn($d) => [
    'producto_id'    => (int)$d['producto_id'],
    'codigo'         => $d['codigo'],
    'nombre'         => $d['producto_nombre'],
    'cantidad'       => (float)$d['cantidad'],
    'precio_unitario'=> (float)$d['precio_unitario'],
], $detalle)) : '[]' ?>;

let prodActual = null;

// ---- Sucursal ID del FORMULARIO de factura (no del navbar) ----
// IMPORTANTE: scope a #frmFactura — el navbar de admins tiene otro
// <select name="sucursal_id"> que aparece antes en el DOM y lo capturaría.
function getSucursalId() {
    const form = document.getElementById('frmFactura');
    const campo = form ? form.querySelector('[name="sucursal_id"]') : null;
    return campo ? (campo.value || '') : '<?= Auth::sucursalActual() ?? '' ?>';
}

function apiUrlCodigo(codigo) {
    const sid = getSucursalId();
    return APP_URL + '/api/productos_buscar.php?codigo=' + encodeURIComponent(codigo)
        + (sid ? '&sucursal_id=' + encodeURIComponent(sid) : '');
}

EscanerHandler.iniciar(function(codigo) { buscarYCargar(codigo); });
if (partidas.length) renderTabla();
calcularTotales();  // Inicializa el resumen con mano de obra/descuento ya cargados

// ---- Filtrar mecánicos según la sucursal seleccionada ----
function filtrarMecanicos() {
    const sid = getSucursalId();
    const sel = document.getElementById('selMecanico');
    if (!sel) return;
    let seleccionInvalida = false;
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return; // "— Sin mecánico —" siempre visible
        const suc = opt.getAttribute('data-sucursal');
        const visible = !sid || suc === String(sid);
        opt.hidden = !visible;
        opt.disabled = !visible;
        if (!visible && opt.selected) seleccionInvalida = true;
    });
    // Si el mecánico elegido no pertenece a la sucursal, limpiar la selección
    if (seleccionInvalida) sel.value = '';
}
filtrarMecanicos();  // aplicar al cargar (sucursal inicial / del almacenista)

// ---- Catálogo de servicios (para las filas dinámicas) ----
const catalogoServicios = <?= json_encode($servicios) ?>;
const serviciosEdit     = <?= isset($serviciosDetalle) ? json_encode(array_values($serviciosDetalle)) : '[]' ?>;

function actualizarNumServicios() {
    const rows = document.querySelectorAll('#body-servicios tr:not(#tr-sin-servicios)');
    document.getElementById('numServicios').textContent = rows.length;
}

function agregarFilaServicio(datos = {}) {
    const tbody   = document.getElementById('body-servicios');
    const trVacio = document.getElementById('tr-sin-servicios');
    if (trVacio) trVacio.remove();

    const tr = document.createElement('tr');

    let options = '<option value="">— Tipo —</option>';
    catalogoServicios.forEach(s => {
        const sel = (datos.servicio_id != null && String(datos.servicio_id) === String(s.id)) ? ' selected' : '';
        options += `<option value="${s.id}" data-precio="${s.precio}"${sel}>${esc(s.nombre)}</option>`;
    });

    const mo = datos.mano_obra != null ? parseFloat(datos.mano_obra).toFixed(2) : '0.00';

    tr.innerHTML = `
        <td>
            <select name="srv_servicio_id[]" class="form-select form-select-sm sel-srv-tipo">${options}</select>
        </td>
        <td>
            <input type="text" name="srv_descripcion[]" class="form-control form-control-sm"
                   value="${esc(datos.descripcion || '')}" placeholder="Descripción del servicio…">
        </td>
        <td>
            <input type="number" name="srv_mano_obra[]" class="form-control form-control-sm text-end inp-mo-servicio"
                   step="0.01" min="0" value="${mo}">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-srv">
                <i class="bi bi-trash"></i>
            </button>
        </td>`;

    tr.querySelector('.sel-srv-tipo').addEventListener('change', function() {
        const opt    = this.options[this.selectedIndex];
        const precio = parseFloat(opt.dataset.precio || 0);
        if (precio > 0) tr.querySelector('.inp-mo-servicio').value = precio.toFixed(2);
        calcularTotales();
    });
    tr.querySelector('.inp-mo-servicio').addEventListener('input', calcularTotales);
    tr.querySelector('.btn-quitar-srv').addEventListener('click', function() {
        tr.remove();
        if (!document.querySelectorAll('#body-servicios tr').length) {
            const trV = document.createElement('tr');
            trV.id = 'tr-sin-servicios';
            trV.innerHTML = '<td colspan="4" class="text-center text-muted py-2">Sin servicios agregados</td>';
            document.getElementById('body-servicios').appendChild(trV);
        }
        actualizarNumServicios();
        calcularTotales();
    });

    tbody.appendChild(tr);
    actualizarNumServicios();
    calcularTotales();
}

serviciosEdit.forEach(s => agregarFilaServicio(s));
document.getElementById('btn-agregar-servicio').addEventListener('click', () => agregarFilaServicio());

// ---- Descuento ----
function toggleDescuento(chk) {
    const inp = document.getElementById('inputDescuento');
    inp.disabled = !chk.checked;
    if (!chk.checked) inp.value = '0';
    calcularTotales();
}

// ---- Cargar producto en campos sin agregar ----
function cargarProducto(prod) {
    prodActual = prod;
    document.getElementById('inputPrecio').value = prod.precio_venta || 0;
    document.getElementById('inputCantidad').focus();
    document.getElementById('inputCantidad').select();
}

function buscarYCargar(codigo) {
    if (!codigo.trim()) return;
    fetch(apiUrlCodigo(codigo))
        .then(r => r.json())
        .then(data => {
            if (data.encontrado) cargarProducto(data.producto);
            else mostrarAlerta('Código no encontrado: ' + codigo, 'warning');
        });
}

function agregarProducto(prod) {
    const qty    = parseFloat(document.getElementById('inputCantidad').value) || 1;
    const precio = parseFloat(document.getElementById('inputPrecio').value) || (prod.precio_venta || 0);

    // Validar stock antes de agregar (fix #7)
    const stockDisp = prod.stock_actual !== null && prod.stock_actual !== undefined
                      ? parseFloat(prod.stock_actual) : null;
    if (stockDisp !== null && qty > stockDisp) {
        mostrarAlerta(
            `Stock insuficiente para "${prod.nombre}": disponible ${stockDisp}, requerido ${qty}. Ajusta la cantidad.`,
            'warning'
        );
        document.getElementById('inputCantidad').focus();
        document.getElementById('inputCantidad').select();
        return;
    }

    const idx = partidas.findIndex(p => p.producto_id == prod.id);
    if (idx >= 0) { partidas[idx].cantidad += qty; }
    else partidas.push({ producto_id: prod.id, codigo: prod.codigo, nombre: prod.nombre, cantidad: qty, precio_unitario: precio });
    prodActual = null;
    renderTabla();
    document.getElementById('inputEscaner').value  = '';
    document.getElementById('inputCantidad').value = '1';
    document.getElementById('inputPrecio').value   = '';
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
    const subtotal = partidas.reduce((s,p) => s + p.cantidad*p.precio_unitario, 0);
    const manoObra = Array.from(document.querySelectorAll('.inp-mo-servicio'))
                         .reduce((s, inp) => s + (parseFloat(inp.value) || 0), 0);
    const chk      = document.getElementById('chkDescuento');
    const pct      = chk?.checked ? (parseFloat(document.getElementById('inputDescuento').value) || 0) : 0;
    const bruto    = subtotal + manoObra;
    const descMonto = bruto * pct / 100;
    const total    = bruto - descMonto;

    document.getElementById('lblSubtotal').textContent = '$'+subtotal.toFixed(2);
    document.getElementById('lblManoObra').textContent = '$'+manoObra.toFixed(2);
    const rowDesc = document.getElementById('rowDescuento');
    if (pct > 0) {
        rowDesc.style.removeProperty('display');
        document.getElementById('lblPct').textContent      = pct.toFixed(1);
        document.getElementById('lblDescuento').textContent = '−$'+descMonto.toFixed(2);
    } else {
        rowDesc.style.display = 'none';
    }
    document.getElementById('lblTotal').textContent = '$'+total.toFixed(2);
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

document.getElementById('btnAgregar').addEventListener('click', function() {
    if (prodActual) agregarProducto(prodActual);
    else { const c=document.getElementById('inputEscaner').value.trim(); if(c) buscarYCargar(c); }
});
document.getElementById('inputEscaner').addEventListener('keydown', function(e) {
    if(e.key==='Enter'){
        e.preventDefault();
        if (prodActual) agregarProducto(prodActual);
        else if(this.value.trim()) buscarYCargar(this.value.trim());
    }
});

let debounce=null;
document.getElementById('inputEscaner').addEventListener('input', function() {
    prodActual = null;
    const q=this.value.trim(); clearTimeout(debounce);
    if(q.length<2){document.getElementById('listaSugerencias').style.display='none';return;}
    debounce = setTimeout(() => {
        const sid = getSucursalId();
        const url = APP_URL + '/api/productos_buscar.php?q=' + encodeURIComponent(q)
                  + (sid ? '&sucursal_id=' + encodeURIComponent(sid) : '');
        fetch(url).then(r => r.json()).then(d => {
            const lista = document.getElementById('listaSugerencias');
            lista.innerHTML = '';
            (d.sugerencias || []).forEach(item => {
                const li = document.createElement('li');
                li.className = 'list-group-item list-group-item-action cursor-pointer py-1';
                li.textContent = `[${item.codigo}] ${item.nombre}`;
                li.addEventListener('click', () => {
                    lista.style.display = 'none';
                    document.getElementById('inputEscaner').value = item.codigo;
                    buscarYCargar(item.codigo);
                });
                lista.appendChild(li);
            });
            lista.style.display = (d.sugerencias || []).length ? 'block' : 'none';
        });
    }, 250);
});
document.addEventListener('click',e=>{ if(!e.target.closest('#sugerenciasWrap')&&!e.target.closest('#inputEscaner')) document.getElementById('listaSugerencias').style.display='none'; });

// ---- Catálogo de clientes ----
let debounceCliente = null;
const inputBuscar   = document.getElementById('inputClienteBuscar');
const listaClientes = document.getElementById('listaClientesSug');
const selUnidad     = document.getElementById('selUnidadCatalogo');
const hidCli        = document.getElementById('hidClienteId');
const hidUni        = document.getElementById('hidUnidadId');

inputBuscar.addEventListener('input', function () {
    hidCli.value = '';
    hidUni.value = '';
    selUnidad.innerHTML = '<option value="">— Seleccionar unidad —</option>';
    clearTimeout(debounceCliente);
    const q = this.value.trim();
    if (q.length < 2) { listaClientes.style.display = 'none'; return; }
    debounceCliente = setTimeout(() => {
        fetch(APP_URL + '/api/clientes_buscar.php?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(d => {
                listaClientes.innerHTML = '';
                (d.sugerencias || []).forEach(c => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item list-group-item-action py-1 cursor-pointer';
                    li.textContent = c.nombre + (c.telefono ? ' — ' + c.telefono : '');
                    li.addEventListener('click', () => seleccionarCliente(c));
                    listaClientes.appendChild(li);
                });
                listaClientes.style.display = (d.sugerencias || []).length ? 'block' : 'none';
            });
    }, 250);
});

function seleccionarCliente(c) {
    hidCli.value = c.id;
    inputBuscar.value = c.nombre;
    listaClientes.style.display = 'none';
    document.getElementById('inputClienteNombre').value = c.nombre;
    document.getElementById('inputClienteTel').value    = c.telefono || '';

    // Cargar unidades del cliente
    fetch(APP_URL + '/api/unidades_buscar.php?cliente_id=' + c.id)
        .then(r => r.json())
        .then(d => {
            selUnidad.innerHTML = '<option value="">— Seleccionar unidad —</option>';
            (d.unidades || []).forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = u.marca + ' ' + u.modelo + (u.placas ? ' — ' + u.placas : '') + (u.anio ? ' (' + u.anio + ')' : '');
                opt.dataset.marca  = u.marca;
                opt.dataset.modelo = u.modelo;
                opt.dataset.anio   = u.anio   || '';
                opt.dataset.placas = u.placas || '';
                selUnidad.appendChild(opt);
            });
        });
}

selUnidad.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    hidUni.value = opt.value || '';
    if (!opt.value) return;
    document.getElementById('inputVhMarca').value  = opt.dataset.marca  || '';
    document.getElementById('inputVhModelo').value = opt.dataset.modelo || '';
    document.getElementById('inputVhAnio').value   = opt.dataset.anio   || '';
    document.getElementById('inputVhPlacas').value = opt.dataset.placas || '';
});

document.getElementById('btnLimpiarCliente').addEventListener('click', function () {
    hidCli.value = ''; hidUni.value = '';
    inputBuscar.value = '';
    selUnidad.innerHTML = '<option value="">— Seleccionar unidad —</option>';
});

document.addEventListener('click', e => {
    if (!e.target.closest('#listaClientesSug') && !e.target.closest('#inputClienteBuscar')) {
        listaClientes.style.display = 'none';
    }
});
</script>
