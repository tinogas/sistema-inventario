<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-box-arrow-in-down-right text-success me-2"></i>Nueva entrada</h4>
    <a href="<?= $appUrl ?>/?modulo=entradas" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<form method="POST" action="<?= $appUrl ?>/?modulo=entradas&accion=nueva" id="frmEntrada">
    <input type="hidden" name="_csrf" value="<?= $csrf ?>">

    <div class="row g-3 mb-4">
        <!-- Sucursal -->
        <div class="col-md-4">
            <label class="form-label fw-semibold">Sucursal <span class="text-danger">*</span></label>
            <?php if (Auth::esAdmin()): ?>
            <select name="sucursal_id" class="form-select" required>
                <option value="">— Seleccionar —</option>
                <?php foreach ($sucursales as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php else: ?>
            <input type="hidden" name="sucursal_id" value="<?= Auth::sucursalActual() ?>">
            <?php
            $db = Database::getInstance();
            $sNombre = $db->prepare('SELECT nombre FROM sucursales WHERE id=?');
            $sNombre->execute([Auth::sucursalActual()]);
            $sNombreVal = $sNombre->fetchColumn();
            ?>
            <input type="text" class="form-control" value="<?= htmlspecialchars($sNombreVal) ?>" readonly>
            <?php endif; ?>
        </div>
        <!-- Proveedor -->
        <div class="col-md-4">
            <label class="form-label fw-semibold">Proveedor</label>
            <select name="proveedor_id" class="form-select">
                <option value="">— Sin proveedor —</option>
                <?php foreach ($proveedores as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['razon_social']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Referencia factura -->
        <div class="col-md-4">
            <label class="form-label fw-semibold">Referencia / Folio factura (Proneg)</label>
            <input type="text" name="referencia_factura" class="form-control" placeholder="Ej. FAC-2025-001" maxlength="80">
        </div>
        <!-- Importar CFDI -->
        <div class="col-md-6">
            <label class="form-label fw-semibold">Importar CFDI XML (opcional)</label>
            <div class="input-group">
                <input type="file" id="archivoCfdi" class="form-control" accept=".xml">
                <button type="button" class="btn btn-outline-secondary" id="btnImportarCfdi">
                    <i class="bi bi-cloud-upload me-1"></i> Importar
                </button>
            </div>
            <div class="form-text">Sube el XML del CFDI del proveedor para pre-llenar las partidas automáticamente.</div>
            <input type="hidden" name="uuid_cfdi" id="uuidCfdi">
        </div>
        <!-- Notas -->
        <div class="col-md-6">
            <label class="form-label fw-semibold">Notas</label>
            <textarea name="notas" class="form-control" rows="2" placeholder="Observaciones…" maxlength="500"></textarea>
        </div>
    </div>

    <!-- Escáner -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-success bg-opacity-10 d-flex align-items-center gap-2">
            <i class="bi bi-upc-scan fs-5 text-success"></i>
            <span class="fw-semibold">Captura de productos</span>
            <span class="badge bg-success ms-auto" id="badge-escaner">Escáner activo</span>
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
                    <label class="form-label small fw-semibold">Precio unitario ($)</label>
                    <input type="number" id="inputPrecio" class="form-control" value="0" min="0" step="any">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success w-100" id="btnAgregar">
                        <i class="bi bi-plus-lg me-1"></i> Agregar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de partidas -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <span class="fw-semibold">Partidas de la entrada</span>
            <span class="badge bg-primary ms-2" id="numPartidas">0</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table tabla-partidas mb-0" id="tablaPartidas">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Precio unit.</th>
                            <th class="text-end">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="bodyPartidas">
                        <tr id="trVacio">
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-upc-scan me-2"></i>Escanea o busca productos para agregar
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="5" class="text-end">Total:</td>
                            <td class="text-end" id="totalGeneral">$0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end">
        <a href="<?= $appUrl ?>/?modulo=entradas" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-success" id="btnConfirmar" disabled>
            <i class="bi bi-check2-circle me-1"></i> Confirmar entrada
        </button>
    </div>
</form>

<script src="<?= $appUrl ?>/assets/js/escaner.js"></script>
<script>
const APP_URL = '<?= $appUrl ?>';
let partidas  = [];  // [{producto_id, codigo, nombre, cantidad, precio_unitario}, ...]

// ---- Iniciar escáner ----
EscanerHandler.iniciar(function (codigo) {
    buscarProductoPorCodigo(codigo);
});

// ---- Buscar producto por código (escáner o manual) ----
function buscarProductoPorCodigo(codigo) {
    if (!codigo.trim()) return;
    fetch(APP_URL + '/api/productos_buscar.php?codigo=' + encodeURIComponent(codigo))
        .then(r => r.json())
        .then(data => {
            if (data.encontrado) {
                agregarProducto(data.producto);
            } else {
                mostrarAlerta('Código no encontrado: ' + codigo, 'warning');
            }
        })
        .catch(() => mostrarAlerta('Error al buscar el producto.', 'danger'));
}

// ---- Agregar producto a la tabla ----
function agregarProducto(prod) {
    const idx = partidas.findIndex(p => p.producto_id == prod.id);
    const qty   = parseFloat(document.getElementById('inputCantidad').value) || 1;
    const precio = prod.precio_costo || parseFloat(document.getElementById('inputPrecio').value) || 0;

    if (idx >= 0) {
        partidas[idx].cantidad       += qty;
        partidas[idx].precio_unitario = precio;
    } else {
        partidas.push({
            producto_id:    prod.id,
            codigo:         prod.codigo,
            nombre:         prod.nombre,
            cantidad:       qty,
            precio_unitario: precio,
        });
    }
    renderTabla();
    document.getElementById('inputEscaner').value  = '';
    document.getElementById('inputCantidad').value = '1';
    document.getElementById('inputPrecio').value   = precio;
    document.getElementById('inputEscaner').focus();
}

function renderTabla() {
    const tbody = document.getElementById('bodyPartidas');
    const trVacio = document.getElementById('trVacio');
    tbody.innerHTML = '';

    if (partidas.length === 0) {
        tbody.appendChild(trVacio);
        document.getElementById('numPartidas').textContent = '0';
        document.getElementById('totalGeneral').textContent = '$0.00';
        document.getElementById('btnConfirmar').disabled = true;
        return;
    }

    let total = 0;
    partidas.forEach((p, i) => {
        const subtotal = p.cantidad * p.precio_unitario;
        total += subtotal;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${i+1}</td>
            <td><code>${esc(p.codigo)}</code></td>
            <td>${esc(p.nombre)}</td>
            <td class="text-end">
                <input type="hidden" name="producto_id[]"    value="${p.producto_id}">
                <input type="hidden" name="precio_unitario[]" value="${p.precio_unitario}">
                <input type="number" name="cantidad[]" class="form-control form-control-sm text-end" style="width:80px;display:inline-block"
                       value="${p.cantidad}" min="0.001" step="any" data-idx="${i}" onchange="cambiarCantidad(this)">
            </td>
            <td class="text-end">${fmt(p.precio_unitario)}</td>
            <td class="text-end fw-semibold">${fmt(subtotal)}</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger btn-quitar" onclick="quitarPartida(${i})"><i class="bi bi-trash"></i></button></td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('numPartidas').textContent   = partidas.length;
    document.getElementById('totalGeneral').textContent  = fmt(total);
    document.getElementById('btnConfirmar').disabled     = false;
}

function cambiarCantidad(input) {
    const idx = parseInt(input.dataset.idx);
    partidas[idx].cantidad = parseFloat(input.value) || 0;
    renderTabla();
}

function quitarPartida(idx) {
    partidas.splice(idx, 1);
    renderTabla();
}

function fmt(n) {
    return new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(n);
}

function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function mostrarAlerta(msg, tipo) {
    const div = document.createElement('div');
    div.className = `alert alert-${tipo} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
    div.style.zIndex = 9999;
    div.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 4000);
}

// ---- Botón agregar manual ----
document.getElementById('btnAgregar').addEventListener('click', function () {
    const codigo = document.getElementById('inputEscaner').value.trim();
    if (codigo) buscarProductoPorCodigo(codigo);
});

document.getElementById('inputEscaner').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const codigo = this.value.trim();
        if (codigo) buscarProductoPorCodigo(codigo);
    }
});

// ---- Sugerencias de búsqueda (autocomplete) ----
let debounceTimer = null;
document.getElementById('inputEscaner').addEventListener('input', function () {
    const q = this.value.trim();
    clearTimeout(debounceTimer);
    if (q.length < 2) { document.getElementById('listaSugerencias').style.display = 'none'; return; }
    debounceTimer = setTimeout(function () {
        fetch(APP_URL + '/api/productos_buscar.php?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => mostrarSugerencias(data.sugerencias || []));
    }, 250);
});

function mostrarSugerencias(items) {
    const lista = document.getElementById('listaSugerencias');
    lista.innerHTML = '';
    if (!items.length) { lista.style.display = 'none'; return; }
    items.forEach(item => {
        const li = document.createElement('li');
        li.className = 'list-group-item list-group-item-action cursor-pointer py-1';
        li.textContent = `[${item.codigo}] ${item.nombre}`;
        li.addEventListener('click', function () {
            lista.style.display = 'none';
            document.getElementById('inputEscaner').value = item.codigo;
            buscarProductoPorCodigo(item.codigo);
        });
        lista.appendChild(li);
    });
    lista.style.display = 'block';
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('#sugerenciasWrap') && !e.target.closest('#inputEscaner')) {
        document.getElementById('listaSugerencias').style.display = 'none';
    }
});

// ---- Importar CFDI XML ----
document.getElementById('btnImportarCfdi').addEventListener('click', function () {
    const archivo = document.getElementById('archivoCfdi').files[0];
    if (!archivo) { alert('Selecciona un archivo XML primero.'); return; }
    const form = new FormData();
    form.append('archivo', archivo);
    fetch(APP_URL + '/api/cfdi_importar.php', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => {
            if (data.error) { mostrarAlerta(data.error, 'danger'); return; }
            document.getElementById('uuidCfdi').value = data.uuid || '';
            (data.partidas || []).forEach(p => {
                partidas.push({
                    producto_id:     p.producto_id || 0,
                    codigo:          p.codigo      || p.clave_sat,
                    nombre:          p.descripcion,
                    cantidad:        parseFloat(p.cantidad)  || 1,
                    precio_unitario: parseFloat(p.precio)    || 0,
                });
            });
            renderTabla();
            mostrarAlerta('CFDI importado: ' + (data.partidas||[]).length + ' partidas.', 'success');
        })
        .catch(() => mostrarAlerta('Error al leer el XML.', 'danger'));
});
</script>
