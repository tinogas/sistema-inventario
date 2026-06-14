<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-building me-2 text-warning"></i>
        <?= isset($sucursal) ? 'Editar sucursal' : 'Nueva sucursal' ?>
    </h4>
    <a href="<?= $appUrl ?>/?modulo=sucursales" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card border-0 shadow-sm" style="max-width:600px">
    <div class="card-body p-4">
        <form method="POST" action="<?= $appUrl ?>/?modulo=sucursales&accion=<?= isset($sucursal) ? 'editar&id='.$sucursal['id'] : 'nuevo' ?>">
            <input type="hidden" name="_csrf" value="<?= $csrf ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control" required maxlength="100"
                       value="<?= htmlspecialchars($sucursal['nombre'] ?? '') ?>"
                       placeholder="Ej. Sucursal Norte">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Ciudad <span class="text-danger">*</span></label>
                <input type="text" name="ciudad" class="form-control" required maxlength="80"
                       value="<?= htmlspecialchars($sucursal['ciudad'] ?? '') ?>"
                       placeholder="Ej. Hermosillo, Sonora">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" name="direccion" class="form-control" maxlength="255"
                       value="<?= htmlspecialchars($sucursal['direccion'] ?? '') ?>"
                       placeholder="Calle, número, colonia">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Teléfono</label>
                <input type="text" name="telefono" class="form-control" maxlength="20"
                       value="<?= htmlspecialchars($sucursal['telefono'] ?? '') ?>"
                       placeholder="Ej. 662-123-4567">
            </div>
            <?php if (isset($sucursal)): ?>
            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="activa" id="chkActiva"
                           <?= $sucursal['activa'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="chkActiva">Sucursal activa</label>
                </div>
            </div>
            <?php endif; ?>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-semibold">
                    <i class="bi bi-check2 me-1"></i>
                    <?= isset($sucursal) ? 'Guardar cambios' : 'Crear sucursal' ?>
                </button>
                <a href="<?= $appUrl ?>/?modulo=sucursales" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
