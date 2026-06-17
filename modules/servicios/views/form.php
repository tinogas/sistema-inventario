<div class="d-flex align-items-center mb-4 gap-2">
    <a href="<?= $appUrl ?>/?modulo=servicios" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="fw-bold mb-0">
        <i class="bi bi-tools me-2 text-primary"></i>
        <?= htmlspecialchars($titulo) ?>
    </h4>
</div>

<?php if (!empty($errores)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-x-circle me-1"></i>
    <strong>Corrige los siguientes errores:</strong>
    <ul class="mb-0 mt-1">
        <?php foreach ($errores as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST"
      action="<?= $appUrl ?>/?modulo=servicios&accion=<?= isset($id) ? 'editar&id=' . $id : 'nuevo' ?>"
      id="formServicio">
    <input type="hidden" name="_csrf" value="<?= $csrf ?>">

    <div class="row g-4">
        <!-- Datos generales -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0 pb-0">
                    <i class="bi bi-info-circle me-1 text-primary"></i>Datos generales
                </div>
                <div class="card-body">
                    <!-- Nombre -->
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-semibold">
                            Nombre <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="nombre"
                               name="nombre"
                               class="form-control"
                               value="<?= htmlspecialchars($datos['nombre']) ?>"
                               maxlength="120"
                               required
                               autofocus>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-semibold">Descripción</label>
                        <textarea id="descripcion"
                                  name="descripcion"
                                  class="form-control"
                                  rows="3"
                                  maxlength="2000"><?= htmlspecialchars($datos['descripcion'] ?? '') ?></textarea>
                    </div>

                    <!-- Precio -->
                    <div class="mb-3">
                        <label for="precio" class="form-label fw-semibold">
                            Precio <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number"
                                   id="precio"
                                   name="precio"
                                   class="form-control"
                                   value="<?= htmlspecialchars((string)$datos['precio']) ?>"
                                   min="0"
                                   step="0.01"
                                   required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos asociados -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold border-0 pb-0 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-box-seam me-1 text-secondary"></i>Productos que usa este servicio</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAgregarProducto">
                        <i class="bi bi-plus-lg me-1"></i>Agregar producto
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" id="tablaProductos">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th style="width:110px">Cantidad</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyProductos">
                                <?php foreach ($items as $item): ?>
                                <tr class="fila-producto">
                                    <td>
                                        <input type="hidden"
                                               name="producto_id[]"
                                               class="inp-pid"
                                               value="<?= (int)$item['producto_id'] ?>">
                                        <span class="txt-producto-nombre">
                                            [<?= htmlspecialchars($item['producto_codigo']) ?>]
                                            <?= htmlspecialchars($item['producto_nombre']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number"
                                               name="cantidad[]"
                                               class="form-control form-control-sm"
                                               value="<?= htmlspecialchars((string)$item['cantidad']) ?>"
                                               min="0.001"
                                               step="0.001"
                                               required>
                                    </td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-quitar-fila"
                                                title="Quitar">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($items)): ?>
                                <tr id="filaVacia">
                                    <td colspan="3" class="text-center text-muted small py-3">
                                        Sin productos asociados. Haz clic en "Agregar producto".
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>
            <?= isset($id) ? 'Guardar cambios' : 'Crear servicio' ?>
        </button>
        <a href="<?= $appUrl ?>/?modulo=servicios" class="btn btn-outline-secondary">
            Cancelar
        </a>
    </div>
</form>

<!-- Modal búsqueda de producto -->
<div class="modal fade" id="modalBuscarProducto" tabindex="-1" aria-labelledby="modalBuscarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalBuscarLabel">
                    <i class="bi bi-search me-2 text-primary"></i>Buscar producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text"
                       id="inputBuscarProducto"
                       class="form-control mb-3"
                       placeholder="Escribe nombre o código del producto…">
                <div id="resultadosBusqueda" class="list-group" style="max-height:260px;overflow-y:auto">
                    <p class="text-muted text-center small py-2">Escribe al menos 2 caracteres.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';

// ----- Quitar fila -----
document.getElementById('tbodyProductos').addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-quitar-fila');
    if (!btn) return;
    btn.closest('tr').remove();
    mostrarFilaVaciaOcultar();
});

function mostrarFilaVaciaOcultar() {
    const tbody = document.getElementById('tbodyProductos');
    const filas = tbody.querySelectorAll('tr.fila-producto');
    let vacia = document.getElementById('filaVacia');
    if (filas.length === 0) {
        if (!vacia) {
            vacia = document.createElement('tr');
            vacia.id = 'filaVacia';
            vacia.innerHTML = '<td colspan="3" class="text-center text-muted small py-3">Sin productos asociados. Haz clic en "Agregar producto".</td>';
            tbody.appendChild(vacia);
        }
    } else {
        if (vacia) vacia.remove();
    }
}

// ----- Modal buscar producto -----
// Inicialización lazy: Bootstrap JS carga después de esta vista, así que no se puede
// instanciar el Modal al parsear el script. Se crea la primera vez que se necesita.
const inputBuscar = document.getElementById('inputBuscarProducto');
const resultados  = document.getElementById('resultadosBusqueda');

function getModalBuscar() {
    return bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBuscarProducto'));
}

document.getElementById('btnAgregarProducto').addEventListener('click', function () {
    inputBuscar.value = '';
    resultados.innerHTML = '<p class="text-muted text-center small py-2">Escribe al menos 2 caracteres.</p>';
    getModalBuscar().show();
    setTimeout(() => inputBuscar.focus(), 300);
});

let timerBusqueda;
inputBuscar.addEventListener('input', function () {
    clearTimeout(timerBusqueda);
    const q = this.value.trim();
    if (q.length < 2) {
        resultados.innerHTML = '<p class="text-muted text-center small py-2">Escribe al menos 2 caracteres.</p>';
        return;
    }
    timerBusqueda = setTimeout(() => buscarProducto(q), 300);
});

function buscarProducto(q) {
    resultados.innerHTML = '<p class="text-muted text-center small py-2"><i class="bi bi-hourglass-split me-1"></i>Buscando…</p>';
    fetch(APP_URL + '/?modulo=servicios&accion=buscar_productos&q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                resultados.innerHTML = '<p class="text-muted text-center small py-2">Sin resultados.</p>';
                return;
            }
            resultados.innerHTML = '';
            data.forEach(p => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action small';
                btn.innerHTML = '<span class="fw-semibold me-1">[' + escHtml(p.codigo) + ']</span>' + escHtml(p.nombre);
                btn.addEventListener('click', () => agregarProducto(p));
                resultados.appendChild(btn);
            });
        })
        .catch(() => {
            resultados.innerHTML = '<p class="text-danger text-center small py-2">Error al buscar.</p>';
        });
}

function agregarProducto(p) {
    // Evitar duplicados
    const existentes = document.querySelectorAll('#tbodyProductos .inp-pid');
    for (const inp of existentes) {
        if (parseInt(inp.value) === parseInt(p.id)) {
            getModalBuscar().hide();
            return;
        }
    }

    const filaVacia = document.getElementById('filaVacia');
    if (filaVacia) filaVacia.remove();

    const tbody = document.getElementById('tbodyProductos');
    const tr    = document.createElement('tr');
    tr.className = 'fila-producto';
    tr.innerHTML =
        '<td>' +
            '<input type="hidden" name="producto_id[]" class="inp-pid" value="' + parseInt(p.id) + '">' +
            '<span class="txt-producto-nombre">[' + escHtml(p.codigo) + '] ' + escHtml(p.nombre) + '</span>' +
        '</td>' +
        '<td>' +
            '<input type="number" name="cantidad[]" class="form-control form-control-sm" value="1" min="0.001" step="0.001" required>' +
        '</td>' +
        '<td>' +
            '<button type="button" class="btn btn-sm btn-outline-danger btn-quitar-fila" title="Quitar"><i class="bi bi-x"></i></button>' +
        '</td>';
    tbody.appendChild(tr);
    getModalBuscar().hide();
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>
