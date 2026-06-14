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
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
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
                    <label class="form-label small fw-semibold">Stock en origen</label>
                    <input type="text" id="inputStock" class="form-control" value="—" readonly>
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
let partidas = [];

EscanerHandler.iniciar(function(codigo) { buscarProducto(codigo); });

function buscarProducto(codigo) {
    if (!codigo.trim()) return;
    fetch(APP_URL + '/api/productos_buscar.php?codigo=' + encodeURIComponent(codigo))
        .then(r => r.json())
        .then(data => {
            if (data.encontrado) {
                document.getElementById('inputStock').value = data.producto.stock_actual ?? '—';
                agregarProducto(data.producto);
            } else { const safeCode = String(codigo).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); mostrarAlerta('Código no encontrado: ' + safeCode, 'warning'); }
        });
}

function agregarProducto(prod) {
    const qty = parseFloat(document.getElementById('inputCantidad').value) || 1;
    const idx = partidas.findIndex(p => p.producto_id == prod.id);
    if (idx >= 0) partidas[idx].cantidad += qty;
    else partidas.push({ producto_id: prod.id, codigo: prod.codigo, nombre: prod.nombre, cantidad: qty });
    renderTabla();
    document.getElementById('inputEscaner').value = '';
    document.getElementById('inputCantidad').value = '1';
    document.getElementById('inputEscaner').focus();
}

function renderTabla() {
    const tbody = document.getElementById('bodyPartidas');
    tbody.innerHTML = '';
    if (!partidas.length) { tbody.appendChild(document.getElementById('trVacio')); document.getElementById('numPartidas').textContent='0'; document.getElementById('btnConfirmar').disabled=true; return; }
    partidas.forEach((p, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${i+1}</td><td><code>${esc(p.codigo)}</code></td><td>${esc(p.nombre)}</td>
        <td class="text-end"><input type="hidden" name="producto_id[]" value="${p.producto_id}">
        <input type="number" name="cantidad[]" class="form-control form-control-sm text-end" style="width:80px;display:inline-block" value="${p.cantidad}" min="0.001" step="any" data-idx="${i}" onchange="cambiarCantidad(this)"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarPartida(${i})"><i class="bi bi-trash"></i></button></td>`;
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
    div.style.zIndex=9999;
    div.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    document.body.appendChild(div); setTimeout(()=>div.remove(),4000);
}

document.getElementById('btnAgregar').addEventListener('click', function() {
    const c = document.getElementById('inputEscaner').value.trim(); if(c) buscarProducto(c);
});
document.getElementById('inputEscaner').addEventListener('keydown', function(e) {
    if(e.key==='Enter'){e.preventDefault(); if(this.value.trim()) buscarProducto(this.value.trim());}
});
let debounce=null;
document.getElementById('inputEscaner').addEventListener('input', function() {
    const q=this.value.trim(); clearTimeout(debounce);
    if(q.length<2){document.getElementById('listaSugerencias').style.display='none';return;}
    debounce=setTimeout(()=>{
        fetch(APP_URL+'/api/productos_buscar.php?q='+encodeURIComponent(q))
            .then(r=>r.json()).then(d=>mostrarSugerencias(d.sugerencias||[]));
    },250);
});
function mostrarSugerencias(items){
    const lista=document.getElementById('listaSugerencias'); lista.innerHTML='';
    if(!items.length){lista.style.display='none';return;}
    items.forEach(item=>{
        const li=document.createElement('li');
        li.className='list-group-item list-group-item-action cursor-pointer py-1';
        li.textContent=`[${item.codigo}] ${item.nombre}`;
        li.addEventListener('click',()=>{lista.style.display='none';document.getElementById('inputEscaner').value=item.codigo;buscarProducto(item.codigo);});
        lista.appendChild(li);
    });
    lista.style.display='block';
}
document.addEventListener('click',e=>{if(!e.target.closest('#sugerenciasWrap')&&!e.target.closest('#inputEscaner')) document.getElementById('listaSugerencias').style.display='none';});
function filtrarDestino(){
    const origenVal=document.getElementById('selOrigen')?.value;
    document.querySelectorAll('#selDestino option').forEach(opt=>{
        opt.disabled=(origenVal&&opt.value===origenVal&&opt.value!=='');
    });
}
</script>
