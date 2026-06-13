<?php $esEditar = !empty($unidad); ?>

<div class="d-flex align-items-center mb-4 gap-2">
    <a href="<?= $appUrl ?>/?modulo=unidades" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="h3 mb-0">
        <i class="bi bi-rulers me-2 text-primary"></i>
        <?= $esEditar ? 'Editar unidad' : 'Nueva unidad' ?>
    </h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST"
                      action="<?= $appUrl ?>/?modulo=unidades&accion=<?= $esEditar ? 'editar&id=' . $unidad['id'] : 'nuevo' ?>">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">

                    <!-- Clave -->
                    <div class="mb-3">
                        <label for="clave" class="form-label fw-semibold">
                            Clave <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="clave"
                               name="clave"
                               class="form-control text-uppercase"
                               maxlength="10"
                               required
                               style="text-transform:uppercase"
                               value="<?= htmlspecialchars($unidad['clave'] ?? '') ?>">
                        <div class="form-text">Máximo 10 caracteres. Se guardará en mayúsculas. Ej: PZA, KG, LT</div>
                    </div>

                    <!-- Nombre -->
                    <div class="mb-4">
                        <label for="nombre" class="form-label fw-semibold">
                            Nombre <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="nombre"
                               name="nombre"
                               class="form-control"
                               maxlength="40"
                               required
                               value="<?= htmlspecialchars($unidad['nombre'] ?? '') ?>">
                        <div class="form-text">Nombre descriptivo. Ej: Pieza, Kilogramo, Litro</div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy me-1"></i>
                            <?= $esEditar ? 'Actualizar' : 'Guardar' ?>
                        </button>
                        <a href="<?= $appUrl ?>/?modulo=unidades" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Forzar mayúsculas en clave al escribir
document.getElementById('clave').addEventListener('input', function () {
    var pos = this.selectionStart;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(pos, pos);
});
</script>
