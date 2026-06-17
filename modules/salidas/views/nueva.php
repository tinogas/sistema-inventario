<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-box-arrow-up-right text-danger me-2"></i>Nueva salida</h4>
    <a href="<?= $appUrl ?>/?modulo=salidas" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<form method="POST" action="<?= $appUrl ?>/?modulo=salidas&accion=nueva" id="frmSalida">
    <input type="hidden" name="_csrf" value="<?= $csrf ?>">

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Sucursal <span class="text-danger">*</span></label>
            <?php if (Auth::esAdmin()): ?>
            <select name="sucursal_id" class="form-select" required>
                <option value="">— Seleccionar —</option>
                <?php foreach ($sucursales as $s): ?>
                <option value="<?= $s['id'] ?>" <?= (!empty($precargaSucursal) && (int)$precargaSucursal === (int)$s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php else: ?>
            <input type="hidden" name="sucursal_id" value="<?= Auth::sucursalActual() ?>">
            <?php
            $db = Database::getInstance();
            $sN = $db->prepare('SELECT nombre FROM sucursales WHERE id=?');
            $sN->execute([Auth::sucursalActual()]);
            ?>
            <input type="text" class="form-control" value="<?= htmlspecialchars($sN->fetchColumn()) ?>" readonly>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Mecánico</label>
            <select name="mecanico_id" class="form-select">
                <option value="">— Sin mecánico —</option>
                <?php foreach ($mecanicos as $m): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Tipo de servicio</label>
            <select name="servicio_id" class="form-select" id="selectServicio">
                <option value="">— Sin servicio —</option>
                <?php foreach ($servicios as $sv): ?>
                <option value="<?= $sv['id'] ?>"><?= htmlspecialchars($sv['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">
                Folio Proneg / Orden de servicio
                <span class="badge bg-warning text-dark ms-1" style="font-size:.7rem">Integración Proneg</span>
            </label>
            <input type="text" name="referencia_factura" class="form-control"
                   placeholder="Ej. OS-2025-0123 o número de factura Proneg" maxlength="80">
            <div class="form-text">Escribe el número de orden o folio de factura de Proneg para trazabilidad.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Notas</label>
            <textarea name="notas" class="form-control" rows="2" placeholder="Observaciones…" maxlength="500"></textarea>
        </div>
    </div>

    <!-- Escáner -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-danger bg-opacity-10 d-flex align-items-center gap-2">
            <i class="bi bi-upc-scan fs-5 text-danger"></i>
            <span class="fw-semibold">Captura de productos</span>
            <span class="badge bg-danger ms-auto">Escáner activo</span>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Código de barras / Buscar producto</label>
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
                    <label class="form-label small fw-semibold">Stock disponible</label>
                    <input type="text" id="inputStockDisp" class="form-control" value="—" readonly>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger w-100" id="btnAgregar">
                        <i class="bi bi-plus-lg me-1"></i> Agregar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de partidas -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <span class="fw-semibold">Partidas de la salida</span>
            <span class="badge bg-danger ms-2" id="numPartidas">0</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table tabla-partidas mb-0" id="tablaPartidas">
                    <thead class="table-light">
                        <tr>
                            <th>#</th><th>Código</th><th>Producto</th>
                            <th class="text-end">Stock disp.</th>
                            <th class="text-end">Cantidad</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="bodyPartidas">
                        <tr id="trVacio"><td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-upc-scan me-2"></i>Escanea o busca productos
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Alerta de stock insuficiente -->
    <div id="alertaStockInsuf" class="alert alert-warning d-none">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <strong>Advertencia:</strong> Algunos productos tienen stock insuficiente.
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="forzar_stock" id="chkForzar">
            <label class="form-check-label" for="chkForzar">
                Continuar de todas formas (el stock quedará en negativo, se registrará en auditoría)
            </label>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end">
        <a href="<?= $appUrl ?>/?modulo=salidas" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-danger" id="btnConfirmar" disabled>
            <i class="bi bi-check2-circle me-1"></i> Confirmar salida
        </button>
    </div>
</form>

<script src="<?= $appUrl ?>/assets/js/escaner.js"></script>
<script>
const APP_URL = '<?= $appUrl ?>';
let partidas   = [];
let prodActual = null;

// Obtiene sucursal_id del FORMULARIO de salida (no del navbar).
// IMPORTANTE: scope a #frmSalida — el navbar de admins tiene otro
// <select name="sucursal_id"> que aparece antes en el DOM y lo capturaría.
function getSucursalId() {
    const form = document.getElementById('frmSalida');
    const campo = form ? form.querySelector('[name="sucursal_id"]') : null;
    return campo ? (campo.value || '') : '<?= Auth::sucursalActual() ?? '' ?>';
}

function apiUrlCodigo(codigo) {
    const sid = getSucursalId();
    return APP_URL + '/api/productos_buscar.php?codigo=' + encodeURIComponent(codigo)
         + (sid ? '&sucursal_id=' + encodeURIComponent(sid) : '');
}

EscanerHandler.iniciar(function (codigo) { buscarYCargar(codigo); });

// Precarga desde el detalle de un producto: carga el producto (la sucursal ya viene preseleccionada)
<?php if (!empty($precargaCodigo)): ?>
window.addEventListener('DOMContentLoaded', function () {
    buscarYCargar(<?= json_encode($precargaCodigo) ?>);
});
<?php endif; ?>

function cargarProducto(prod) {
    prodActual = prod;
    const actual   = parseFloat(prod.stock_actual ?? 0);
    const transito = parseFloat(prod.stock_en_transito ?? 0);
    const disp     = (prod.stock_disponible !== undefined && prod.stock_disponible !== null)
                     ? parseFloat(prod.stock_disponible) : actual;
    // Mostrar: "12 (15 - 3 en tránsito)" — disponible y el desglose actual − tránsito
    let label = String(disp);
    if (transito > 0) label += ` (${actual} - ${transito} en tránsito)`;
    document.getElementById('inputStockDisp').value = label;
    document.getElementById('inputCantidad').focus();
    document.getElementById('inputCantidad').select();
}

function buscarYCargar(codigo) {
    if (!codigo.trim()) return;
    const sid = getSucursalId();
    if (!sid) {
        mostrarAlerta('Selecciona la sucursal primero para ver el stock correcto de esa sucursal.', 'warning');
        document.getElementById('frmSalida')?.querySelector('[name="sucursal_id"]')?.focus();
        return;
    }
    fetch(apiUrlCodigo(codigo))
        .then(r => r.json())
        .then(data => {
            if (data.encontrado) cargarProducto(data.producto);
            else mostrarAlerta('Código no encontrado: ' + codigo, 'warning');
        });
}

// ---- Agregar producto a tabla con validación de stock ----
function agregarProducto(prod) {
    const qty = parseFloat(document.getElementById('inputCantidad').value) || 1;
    // stock_disponible = stock_actual − en_tránsito (calculado por la API)
    const stockDisp = prod.stock_disponible !== undefined && prod.stock_disponible !== null
                      ? parseFloat(prod.stock_disponible)
                      : (prod.stock_actual !== null && prod.stock_actual !== undefined
                         ? parseFloat(prod.stock_actual) : null);
    if (stockDisp !== null && qty > stockDisp) {
        mostrarAlerta(
            `Stock insuficiente para "${prod.nombre}": disponible ${stockDisp.toFixed(3)}, requerido ${qty}. Ajusta la cantidad o usa "Forzar stock".`,
            'warning'
        );
        document.getElementById('inputCantidad').focus();
        document.getElementById('inputCantidad').select();
        return;
    }

    const idx = partidas.findIndex(p => p.producto_id == prod.id);
    if (idx >= 0) {
        partidas[idx].cantidad += qty;
    } else {
        partidas.push({
            producto_id:  prod.id,
            codigo:       prod.codigo,
            nombre:       prod.nombre,
            cantidad:     qty,
            precio_unitario: prod.precio_venta || 0,
            // Guardar el DISPONIBLE (actual − tránsito) para que la tabla y la
            // validación usen el mismo número que el campo "Stock disponible".
            stock_disp:   stockDisp,
        });
    }
    prodActual = null;
    renderTabla();
    document.getElementById('inputEscaner').value  = '';
    document.getElementById('inputCantidad').value = '1';
    document.getElementById('inputStockDisp').value = '—';
    document.getElementById('inputEscaner').focus();
}

function renderTabla() {
    const tbody = document.getElementById('bodyPartidas');
    const trV   = document.getElementById('trVacio');
    tbody.innerHTML = '';
    if (!partidas.length) { tbody.appendChild(trV); actualizarBtn(); return; }

    let insuf = false;
    partidas.forEach((p, i) => {
        const stockBajo = p.stock_disp !== null && p.stock_disp !== undefined && p.cantidad > p.stock_disp;
        if (stockBajo) insuf = true;
        const tr = document.createElement('tr');
        tr.className = stockBajo ? 'table-warning' : '';
        tr.innerHTML = `
            <td>${i+1}</td>
            <td><code>${esc(p.codigo)}</code></td>
            <td>${esc(p.nombre)} ${stockBajo ? '<span class="badge bg-warning text-dark">Stock bajo</span>' : ''}</td>
            <td class="text-end ${stockBajo?'text-danger fw-bold':''}">${(p.stock_disp !== null && p.stock_disp !== undefined) ? Number(p.stock_disp).toFixed(3) : '—'}</td>
            <td class="text-end">
                <input type="hidden" name="producto_id[]"     value="${p.producto_id}">
                <input type="hidden" name="precio_unitario[]" value="${p.precio_unitario}">
                <input type="number" name="cantidad[]" class="form-control form-control-sm text-end" style="width:80px;display:inline-block"
                       value="${p.cantidad}" min="0.001" step="any" data-idx="${i}" onchange="cambiarCantidad(this)">
            </td>
            <td><button type="button" class="btn btn-sm btn-outline-danger btn-quitar" onclick="quitarPartida(${i})"><i class="bi bi-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('numPartidas').textContent = partidas.length;
    document.getElementById('alertaStockInsuf').classList.toggle('d-none', !insuf);
    actualizarBtn();
}

function actualizarBtn() {
    document.getElementById('btnConfirmar').disabled = partidas.length === 0;
}

function cambiarCantidad(input) {
    partidas[parseInt(input.dataset.idx)].cantidad = parseFloat(input.value) || 0;
    renderTabla();
}
function quitarPartida(idx) { partidas.splice(idx, 1); renderTabla(); }

function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function mostrarAlerta(msg, tipo) {
    const div = document.createElement('div');
    div.className = `alert alert-${tipo} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
    div.style.zIndex = 9999;
    const span = document.createElement('span');
    span.textContent = msg;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-close';
    btn.setAttribute('data-bs-dismiss', 'alert');
    div.appendChild(span);
    div.appendChild(btn);
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 4000);
}

// ---- Botón +Agregar: usa prodActual si existe, si no busca ----
document.getElementById('btnAgregar').addEventListener('click', function() {
    if (prodActual) {
        agregarProducto(prodActual);
    } else {
        const c = document.getElementById('inputEscaner').value.trim();
        if (c) buscarYCargar(c);
    }
});

// ---- Enter: igual que botón ----
document.getElementById('inputEscaner').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        if (prodActual) agregarProducto(prodActual);
        else if (this.value.trim()) buscarYCargar(this.value.trim());
    }
});

// ---- Autocomplete: tipear borra prodActual; seleccionar carga sin agregar ----
let debounce = null;
document.getElementById('inputEscaner').addEventListener('input', function() {
    prodActual = null;
    const q = this.value.trim(); clearTimeout(debounce);
    if (q.length < 2) { document.getElementById('listaSugerencias').style.display='none'; return; }
    debounce = setTimeout(() => {
        const sid = getSucursalId();
        const url = APP_URL + '/api/productos_buscar.php?q=' + encodeURIComponent(q)
                  + (sid ? '&sucursal_id=' + encodeURIComponent(sid) : '');
        fetch(url).then(r => r.json()).then(d => mostrarSugerencias(d.sugerencias || []));
    }, 250);
});
function mostrarSugerencias(items) {
    const lista = document.getElementById('listaSugerencias');
    lista.innerHTML = '';
    if (!items.length) { lista.style.display='none'; return; }
    items.forEach(item => {
        const li = document.createElement('li');
        li.className = 'list-group-item list-group-item-action cursor-pointer py-1';
        li.textContent = `[${item.codigo}] ${item.nombre}`;
        // Solo carga en campos — el usuario confirma con +Agregar
        li.addEventListener('click', () => {
            lista.style.display = 'none';
            document.getElementById('inputEscaner').value = item.codigo;
            buscarYCargar(item.codigo);
        });
        lista.appendChild(li);
    });
    lista.style.display = 'block';
}
document.addEventListener('click', e => { if (!e.target.closest('#sugerenciasWrap') && !e.target.closest('#inputEscaner')) document.getElementById('listaSugerencias').style.display='none'; });
</script>
