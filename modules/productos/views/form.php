<?php
/**
 * Vista: Formulario crear / editar producto
 * Variables disponibles: $producto (array|null), $categorias, $unidades, $proveedores,
 *                        $titulo, $appUrl, $csrf, $usuario
 */
$esEdicion = $producto !== null && !empty($producto['id']);
$accion    = $esEdicion
    ? $appUrl . '/?modulo=productos&accion=editar&id=' . (int) $producto['id']
    : $appUrl . '/?modulo=productos&accion=nuevo';

// Helpers para pre-llenar campos
$v = function(string $campo, mixed $defecto = '') use ($producto): string {
    return htmlspecialchars((string) ($producto[$campo] ?? $defecto));
};
$sel = function(string $campo, mixed $valor) use ($producto): string {
    return isset($producto[$campo]) && (string) $producto[$campo] === (string) $valor ? 'selected' : '';
};
?>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="mb-0">
        <i class="bi bi-box-seam me-2 text-primary"></i>
        <?= htmlspecialchars($titulo) ?>
    </h4>
    <a href="<?= $appUrl ?>/?modulo=productos" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver a la lista
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= $accion ?>" id="formProducto" novalidate>
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <!-- ======================== IDENTIFICACIÓN ======================== -->
            <h6 class="text-muted fw-semibold text-uppercase small mb-3 mt-1">
                <i class="bi bi-upc-scan me-1"></i>Identificación
            </h6>
            <div class="row g-3 mb-4">
                <!-- Código -->
                <div class="col-12 col-md-4">
                    <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="codigo" name="codigo"
                               class="form-control text-uppercase font-monospace"
                               value="<?= $v('codigo') ?>"
                               placeholder="Ej. MUE-001"
                               required maxlength="60"
                               autocomplete="off">
                        <button type="button" class="btn btn-outline-secondary" id="btnEscaner" title="Activar escáner de código de barras">
                            <i class="bi bi-upc-scan"></i>
                        </button>
                    </div>
                    <div class="form-text">Código principal único del producto.</div>
                </div>

                <!-- Código alterno -->
                <div class="col-12 col-md-4">
                    <label for="codigo_alterno" class="form-label">Código alterno</label>
                    <input type="text" id="codigo_alterno" name="codigo_alterno"
                           class="form-control text-uppercase font-monospace"
                           value="<?= $v('codigo_alterno') ?>"
                           placeholder="Ej. código de barras"
                           maxlength="60"
                           autocomplete="off">
                    <div class="form-text">Código de barras u otro código secundario.</div>
                </div>

                <!-- Nombre -->
                <div class="col-12 col-md-4">
                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="nombre" name="nombre"
                           class="form-control"
                           value="<?= $v('nombre') ?>"
                           placeholder="Nombre del producto"
                           required maxlength="200"
                           autocomplete="off">
                </div>

                <!-- Descripción -->
                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea id="descripcion" name="descripcion"
                              class="form-control"
                              rows="2"
                              placeholder="Descripción opcional del producto"
                              maxlength="1000"><?= $v('descripcion') ?></textarea>
                </div>
            </div>

            <!-- ======================== CLASIFICACIÓN ======================== -->
            <h6 class="text-muted fw-semibold text-uppercase small mb-3">
                <i class="bi bi-tags me-1"></i>Clasificación
            </h6>
            <div class="row g-3 mb-4">
                <!-- Categoría -->
                <div class="col-12 col-md-4">
                    <label for="categoria_id" class="form-label">Categoría</label>
                    <select id="categoria_id" name="categoria_id" class="form-select">
                        <option value="">— Sin categoría —</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $sel('categoria_id', $cat['id']) ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Unidad de medida -->
                <div class="col-12 col-md-4">
                    <label for="unidad_id" class="form-label">Unidad de medida <span class="text-danger">*</span></label>
                    <select id="unidad_id" name="unidad_id" class="form-select" required>
                        <option value="">— Selecciona —</option>
                        <?php foreach ($unidades as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $sel('unidad_id', $u['id']) ?>>
                            <?= htmlspecialchars($u['clave']) ?> — <?= htmlspecialchars($u['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Proveedor -->
                <div class="col-12 col-md-4">
                    <label for="proveedor_id" class="form-label">Proveedor</label>
                    <select id="proveedor_id" name="proveedor_id" class="form-select">
                        <option value="">— Sin proveedor —</option>
                        <?php foreach ($proveedores as $pv): ?>
                        <option value="<?= $pv['id'] ?>" <?= $sel('proveedor_id', $pv['id']) ?>>
                            <?= htmlspecialchars($pv['razon_social']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- ======================== PRECIOS Y STOCK ======================== -->
            <h6 class="text-muted fw-semibold text-uppercase small mb-3">
                <i class="bi bi-currency-dollar me-1"></i>Precios y stock
            </h6>
            <div class="row g-3 mb-4">
                <!-- Precio costo -->
                <div class="col-12 col-sm-4">
                    <label for="precio_costo" class="form-label">Precio costo</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="precio_costo" name="precio_costo"
                               class="form-control text-end"
                               value="<?= $v('precio_costo', '0.00') ?>"
                               min="0" step="0.01"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Precio venta -->
                <div class="col-12 col-sm-4">
                    <label for="precio_venta" class="form-label">Precio venta <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="precio_venta" name="precio_venta"
                               class="form-control text-end"
                               value="<?= $v('precio_venta', '0.00') ?>"
                               min="0" step="0.01"
                               required
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Stock mínimo -->
                <div class="col-12 col-sm-4">
                    <label for="stock_minimo" class="form-label">Stock mínimo</label>
                    <input type="number" id="stock_minimo" name="stock_minimo"
                           class="form-control text-end"
                           value="<?= $v('stock_minimo', '1') ?>"
                           min="0" step="0.001"
                           placeholder="1">
                    <div class="form-text">Nivel de alerta por stock bajo.</div>
                </div>
            </div>

            <!-- ======================== BOTONES ======================== -->
            <div class="d-flex gap-2 justify-content-end border-top pt-3 mt-2">
                <a href="<?= $appUrl ?>/?modulo=productos<?= $esEdicion ? '&accion=detalle&id=' . (int) $producto['id'] : '' ?>"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-primary" id="btnGuardar">
                    <i class="bi bi-floppy me-1"></i>
                    <?= $esEdicion ? 'Guardar cambios' : 'Crear producto' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== Modal escáner ===================== -->
<div class="modal fade" id="modalEscaner" tabindex="-1" aria-labelledby="modalEscanerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEscanerLabel">
                    <i class="bi bi-upc-scan me-2"></i>Escáner de código de barras
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="text-muted mb-3">
                    Coloca el cursor en el campo de código y escanea el producto.<br>
                    El escáner enviará el código automáticamente.
                </p>
                <input type="text" id="inputEscaner"
                       class="form-control form-control-lg font-monospace text-center"
                       placeholder="Escanea aquí…"
                       autocomplete="off">
                <div class="form-text mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    El campo se llenará al escanear. Presiona Enter o cierra el modal para confirmar.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarEscaner">
                    <i class="bi bi-check2 me-1"></i>Usar este código
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    // ── Validación de formulario ──────────────────────────────
    const form = document.getElementById('formProducto');
    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // ── Escáner de código de barras ──────────────────────────
    const btnEscaner      = document.getElementById('btnEscaner');
    const inputEscaner    = document.getElementById('inputEscaner');
    const campoCodigo     = document.getElementById('codigo');
    const modalEscanerEl  = document.getElementById('modalEscaner');
    // Lazy: Bootstrap JS carga al final del body, después de este script.
    // getOrCreateInstance se llama al usarlo (click), ya con bootstrap cargado.
    const getModalEscaner = () => bootstrap.Modal.getOrCreateInstance(modalEscanerEl);

    btnEscaner.addEventListener('click', function () {
        inputEscaner.value = '';
        getModalEscaner().show();
        // Dar foco al input del modal al abrirse
        modalEscanerEl.addEventListener('shown.bs.modal', function handler() {
            inputEscaner.focus();
            modalEscanerEl.removeEventListener('shown.bs.modal', handler);
        });
    });

    // Confirmar escáner al pulsar Enter en el input del modal
    inputEscaner.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            confirmarEscaner();
        }
    });

    document.getElementById('btnConfirmarEscaner').addEventListener('click', confirmarEscaner);

    function confirmarEscaner() {
        const codigo = inputEscaner.value.trim().toUpperCase();
        if (codigo !== '') {
            campoCodigo.value = codigo;
        }
        getModalEscaner().hide();
        campoCodigo.focus();
    }

    // ── Mayúsculas en tiempo real para los campos de código ──
    ['codigo', 'codigo_alterno'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function () {
                const pos = el.selectionStart;
                el.value = el.value.toUpperCase();
                el.setSelectionRange(pos, pos);
            });
        }
    });
})();
</script>
