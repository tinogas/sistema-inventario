<?php $esEditar = !empty($cliente); ?>

<div class="d-flex align-items-center mb-4 gap-2">
    <a href="<?= $appUrl ?>/?modulo=clientes<?= $esEditar ? '&accion=detalle&id='.$cliente['id'] : '' ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="h3 mb-0">
        <i class="bi bi-person-plus me-2 text-primary"></i>
        <?= $esEditar ? 'Editar cliente' : 'Nuevo cliente' ?>
    </h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST"
                      action="<?= $appUrl ?>/?modulo=clientes&accion=<?= $esEditar ? 'editar&id='.$cliente['id'] : 'nuevo' ?>">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">

                    <!-- Nombre -->
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-semibold">
                            Nombre <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="nombre"
                               name="nombre"
                               class="form-control"
                               maxlength="120"
                               required
                               value="<?= htmlspecialchars($cliente['nombre'] ?? '') ?>">
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
                                   value="<?= htmlspecialchars($cliente['rfc'] ?? '') ?>">
                        </div>
                        <!-- Teléfono -->
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                            <input type="tel"
                                   id="telefono"
                                   name="telefono"
                                   class="form-control"
                                   maxlength="20"
                                   value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>"
                                   placeholder="662-000-0000">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Correo electrónico</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               maxlength="80"
                               value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
                    </div>

                    <!-- Dirección -->
                    <div class="mb-3">
                        <label for="direccion" class="form-label fw-semibold">Dirección</label>
                        <input type="text"
                               id="direccion"
                               name="direccion"
                               class="form-control"
                               maxlength="200"
                               value="<?= htmlspecialchars($cliente['direccion'] ?? '') ?>">
                    </div>

                    <!-- Notas -->
                    <div class="mb-4">
                        <label for="notas" class="form-label fw-semibold">Notas</label>
                        <textarea id="notas"
                                  name="notas"
                                  class="form-control"
                                  rows="3"><?= htmlspecialchars($cliente['notas'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy me-1"></i>
                            <?= $esEditar ? 'Actualizar' : 'Guardar' ?>
                        </button>
                        <a href="<?= $appUrl ?>/?modulo=clientes<?= $esEditar ? '&accion=detalle&id='.$cliente['id'] : '' ?>"
                           class="btn btn-outline-secondary">
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
