<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-arrow-left-right text-info me-2"></i>Nuevo traspaso</h4>
    <a href="<?= $appUrl ?>/?modulo=traspasos" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<form method="POST" action="<?= $appUrl ?>/?modulo=traspasos&accion=nuevo" id="frmTraspaso">
    <input type="hidden" name="_csrf" value="<?= $csrf ?>">

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Sucursal origen <span class="text-danger">*</span></label>
            <?php if (Auth::esAdmin()): ?>
            <select name="sucursal_origen_id" class="form-select" id="selOrigen" required onchange="filtrarDestino()">
                <option value="">— Seleccionar —</option>
                <?php foreach ($sucursales as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php else: ?>
            <input type="hidden" name="sucursal_origen_id" value="<?= Auth::sucursalActual() ?>">
            <?php
            $db = Database::getInstance();
            $sN = $db->prepare('SELECT nombre FROM sucursales WHERE id=?');
            $sN->execute([Auth::sucursalActual()]);
            ?>
            <input type="text" class="form-control" value="<?= htmlspecialchars($sN->fetchColumn()) ?>" readonly>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Sucursal destino <span class="text-danger">*</span></label>
            <select name="sucursal_dest_id" class="form-select" id="selDestino" required>
                <option value="">— Seleccionar —</option>
                <?php foreach ($sucursales as $s): ?>
                <option value="<?= $s['id'] ?>"
                    <?= (!Auth::esAdmin() && (int)$s['id'] === (int)Auth::sucursalActual()) ? 'disabled' : '' ?>>
                    <?= htmlspecialchars($s['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Notas</label>
            <input type="text" name="notas" class="form-control" placeholder="Motivo del traspaso…" maxlength="255">
        </div>
    </div>

    <!-- Escáner -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-info bg-opacity-10 d-flex align-items-center gap-2">
            <i class="bi bi-upc-scan fs-5 text-info"></i>
            <span class="fw-semibold">Agregar productos al traspaso</span>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Código o nombre</label>
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
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Stock disponible</label>
                    <input type="text" id="inputStock" class="form-control text-end" value="—" disabled
                           title="Stock disponible (entre paréntesis aparece cuánto hay en tránsito en otros traspasos)">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-info w-100" id="btnAgregar">
                        <i class="bi bi-plus-lg me-1"></i> Agregar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de partidas -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <span class="fw-semibold">Productos a traspasar</span>
            <span class="badge bg-info text-dark ms-2" id="numPartidas">0</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table tabla-partidas mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Código</th><th>Producto</th><th class="text-end">Cantidad</th><th></th></tr>
                    </thead>
                    <tbody id="bodyPartidas">
                        <tr id="trVacio"><td colspan="5" class="text-center text-muted py-4">
                            <i class="bi bi-upc-scan me-2"></i>Agrega productos al traspaso
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end">
        <a href="<?= $appUrl ?>/?modulo=traspasos" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-info" id="btnConfirmar" disabled>
            <i class="bi bi-send me-1"></i> Enviar traspaso
        </button>
    </div>
</form>

<script src="<?= $appUrl ?>/assets/js/escaner.js"></script>
<script>
const APP_URL = '<?= $appUrl ?>';
let partidas   = [];
let prodActual = null; // producto cargado en campos, pendiente de confirmar con +Agregar

// Obtiene el ID de la sucursal origen seleccionada
function getOrigenId() {
    return document.getElementById('selOrigen')?.value
        || '<?= Auth::sucursalActual() ?? '' ?>';
}

// URL de búsqueda por código (incluye sucursal_id de la sucursal origen para stock correcto)
function apiUrl(codigo) {
    const sid = getOrigenId();
    return APP_URL + '/api/productos_buscar.php?codigo=' + encodeURIComponent(codigo)
         + (sid ? '&sucursal_id=' + encodeURIComponent(sid) : '');
}

// ---- Escáner: carga en campos sin agregar (usuario confirma con +Agregar) ----
EscanerHandler.iniciar(function(codigo) { buscarYCargar(codigo); });

// ---- Cargar producto en campos (muestra stock de sucursal origen, NO agrega a tabla) ----
function cargarProducto(prod) {
    prodActual = prod;
    const stockAct    = parseFloat(prod.stock_actual    ?? 0);
    const stockTr     = parseFloat(prod.stock_en_transito ?? 0);
    const stockDisp   = parseFloat(prod.stock_disponible ?? stockAct);
    let stockLabel    = stockDisp.toFixed(3);
    if (stockTr > 0) stockLabel += ' (−' + stockTr.toFixed(3) + ' en tránsito)';
    document.getElementById('inputStock').value = stockLabel;
    document.getElementById('inputCantidad').focus();
    document.getElementById('inputCantidad').select();
}

function buscarYCargar(codigo) {
    if (!codigo.trim()) return;
    fetch(apiUrl(codigo))
        .then(r => r.json())
        .then(data => {
            if (data.encontrado) cargarProducto(data.producto);
            else mostrarAlerta('Código no encontrado: ' + esc(codigo), 'warning');
        })
        .catch(() => mostrarAlerta('Error al buscar el producto.', 'danger'));
}

// ---- Agregar producto a la tabla con validación de stock disponible ----
function agregarProducto(prod) {
    const qty       = parseFloat(document.getElementById('inputCantidad').value) || 1;
    const stockDisp = prod.stock_disponible !== undefined
                      ? parseFloat(prod.stock_disponible)
                      : parseFloat(prod.stock_actual ?? 0);

    if (stockDisp < qty) {
        mostrarAlerta(
            'Stock insuficiente en origen para "' + esc(prod.nombre) + '": '
            + 'disponible ' + stockDisp.toFixed(3) + ', requerido ' + qty + '.',
            'warning'
        );
        document.getElementById('inputCantidad').focus();
        document.getElementById('inputCantidad').select();
        return;
    }

    const idx = partidas.findIndex(p => p.producto_id == prod.id);
    if (idx >= 0) partidas[idx].cantidad += qty;
    else partidas.push({ producto_id: prod.id, codigo: prod.codigo, nombre: prod.nombre, cantidad: qty });
    prodActual = null;
    renderTabla();
    document.getElementById('inputEscaner').value  = '';
    document.getElementById('inputCantidad').value = '1';
    document.getElementById('inputStock').value    = '—';
    document.getElementById('inputEscaner').focus();
}

function renderTabla() {
    const tbody = document.getElementById('bodyPartidas');
    tbody.innerHTML = '';
    if (!partidas.length) {
        tbody.appendChild(document.getElementById('trVacio'));
        document.getElementById('numPartidas').textContent = '0';
        document.getElementById('btnConfirmar').disabled = true;
        return;
    }
    partidas.forEach((p, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${i+1}</td><td><code>${esc(p.codigo)}</code></td><td>${esc(p.nombre)}</td>
        <td class="text-end">
            <input type="hidden" name="producto_id[]" value="${p.producto_id}">
            <input type="number" name="cantidad[]" class="form-control form-control-sm text-end"
                   style="width:80px;display:inline-block" value="${p.cantidad}"
                   min="0.001" step="any" data-idx="${i}" onchange="cambiarCantidad(this)">
        </td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarPartida(${i})">
            <i class="bi bi-trash"></i></button></td>`;
        tbody.appendChild(tr);
    });
    document.getElementById('numPartidas').textContent = partidas.length;
    document.getElementById('btnConfirmar').disabled = false;
}

function cambiarCantidad(input) { partidas[parseInt(input.dataset.idx)].cantidad = parseFloat(input.value)||0; }
function quitarPartida(idx) { partidas.splice(idx,1); renderTabla(); }
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function mostrarAlerta(msg, tipo) {
    const div = document.createElement('div');
    div.className = `alert alert-${tipo} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
    div.style.zIndex = 9999;
    const span = document.createElement('span'); span.textContent = msg;
    const btn  = document.createElement('button'); btn.type='button'; btn.className='btn-close'; btn.setAttribute('data-bs-dismiss','alert');
    div.appendChild(span); div.appendChild(btn);
    document.body.appendChild(div); setTimeout(() => div.remove(), 5000);
}

// ---- Botón +Agregar: usa prodActual si existe, si no busca ----
document.getElementById('btnAgregar').addEventListener('click', function() {
    if (prodActual) agregarProducto(prodActual);
    else { const c = document.getElementById('inputEscaner').value.trim(); if(c) buscarYCargar(c); }
});

// ---- Enter: igual que botón ----
document.getElementById('inputEscaner').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        if (prodActual) agregarProducto(prodActual);
        else if (this.value.trim()) buscarYCargar(this.value.trim());
    }
});

// ---- Autocomplete: tipear borra prodActual; seleccionar solo carga en campos ----
let debounce = null;
document.getElementById('inputEscaner').addEventListener('input', function() {
    prodActual = null;
    document.getElementById('inputStock').value = '—';
    const q = this.value.trim(); clearTimeout(debounce);
    if (q.length < 2) { document.getElementById('listaSugerencias').style.display='none'; return; }
    debounce = setTimeout(() => {
        const sid = getOrigenId();
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
        // Solo carga en campos — usuario confirma con +Agregar
        li.addEventListener('click', () => {
            lista.style.display = 'none';
            document.getElementById('inputEscaner').value = item.codigo;
            buscarYCargar(item.codigo);
        });
        lista.appendChild(li);
    });
    lista.style.display = 'block';
}
document.addEventListener('click', e => {
    if (!e.target.closest('#sugerenciasWrap') && !e.target.closest('#inputEscaner'))
        document.getElementById('listaSugerencias').style.display = 'none';
});

// ---- Al cambiar sucursal origen: limpiar producto cargado y stock ----
document.getElementById('selOrigen')?.addEventListener('change', function() {
    prodActual = null;
    document.getElementById('inputEscaner').value = '';
    document.getElementById('inputStock').value   = '—';
    filtrarDestino();
});

function filtrarDestino() {
    const origenVal = document.getElementById('selOrigen')?.value;
    document.querySelectorAll('#selDestino option').forEach(opt => {
        opt.disabled = (origenVal && opt.value === origenVal && opt.value !== '');
    });
}
</script>
