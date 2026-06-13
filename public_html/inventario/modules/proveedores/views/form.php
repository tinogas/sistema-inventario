<?php $esEditar = !empty($proveedor); ?>

<div class="d-flex align-items-center mb-4 gap-2">
    <a href="<?= $appUrl ?>/?modulo=proveedores" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="h3 mb-0">
        <i class="bi bi-truck me-2 text-primary"></i>
        <?= $esEditar ? 'Editar proveedor' : 'Nuevo proveedor' ?>
    </h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST"
                      action="<?= $appUrl ?>/?modulo=proveedores&accion=<?= $esEditar ? 'editar&id=' . $proveedor['id'] : 'nuevo' ?>">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">

                    <!-- Razón social -->
                    <div class="mb-3">
                        <label for="razon_social" class="form-label fw-semibold">
                            Razón social <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="razon_social"
                               name="razon_social"
                               class="form-control"
                               maxlength="200"
                               required
                               value="<?= htmlspecialchars($proveedor['razon_social'] ?? '') ?>">
                    </div>

                    <div class="row">
                        <!-- RFC -->
                        <div class="col-md-6 mb-3">
                            <label for="rfc" class="form-label fw-semibold">RFC</label>
                            <input type="text"
                                   id="rfc"
                                   name="rfc"
                                   class="form-control text-uppercase"
                                   maxlength="15"
                                   style="text-transform:uppercase"
                                   value="<?= htmlspecialchars($proveedor['rfc'] ?? '') ?>">
                        </div>

                        <!-- Teléfono -->
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                            <input type="tel"
                                   id="telefono"
                                   name="telefono"
                                   class="form-control"
                                   maxlength="20"
                                   value="<?= htmlspecialchars($proveedor['telefono'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Contacto -->
                        <div class="col-md-6 mb-3">
                            <label for="contacto" class="form-label fw-semibold">Nombre de contacto</label>
                            <input type="text"
                                   id="contacto"
                                   name="contacto"
                                   class="form-control"
                                   maxlength="120"
                                   value="<?= htmlspecialchars($proveedor['contacto'] ?? '') ?>">
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-semibold">Correo electrónico</label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   class="form-control"
                                   maxlength="150"
                                   value="<?= htmlspecialchars($proveedor['email'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="mb-4">
                        <label for="notas" class="form-label fw-semibold">Notas</label>
                        <textarea id="notas"
                                  name="notas"
                                  class="form-control"
                                  rows="3"><?= htmlspecialchars($proveedor['notas'] ?? '') ?></textarea>
                        <div class="form-text">Información adicional, condiciones de pago, etc.</div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy me-1"></i>
                            <?= $esEditar ? 'Actualizar' : 'Guardar' ?>
                        </button>
                        <a href="<?= $appUrl ?>/?modulo=proveedores" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('rfc').addEventListener('input', function () {
    var pos = this.selectionStart;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(pos, pos);
});
</script>
