<div class="d-flex align-items-center mb-4 gap-2">
    <a href="<?= $appUrl ?>/?modulo=mecanicos" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="fw-bold mb-0">
        <i class="bi bi-person-gear me-2 text-primary"></i>
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

<div class="card border-0 shadow-sm" style="max-width:560px">
    <div class="card-body p-4">
        <form method="POST"
              action="<?= $appUrl ?>/?modulo=mecanicos&accion=<?= isset($id) ? 'editar&id=' . $id : 'nuevo' ?>">
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
                       value="<?= htmlspecialchars($datos['nombre']) ?>"
                       maxlength="120"
                       required
                       autofocus>
            </div>

            <!-- Sucursal -->
            <div class="mb-3">
                <label for="sucursal_id" class="form-label fw-semibold">
                    Sucursal <span class="text-danger">*</span>
                </label>
                <select id="sucursal_id" name="sucursal_id" class="form-select" required>
                    <option value="">— Selecciona una sucursal —</option>
                    <?php foreach ($sucursales as $s): ?>
                    <option value="<?= $s['id'] ?>"
                        <?= (int)$datos['sucursal_id'] === (int)$s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Teléfono -->
            <div class="mb-4">
                <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                <input type="tel"
                       id="telefono"
                       name="telefono"
                       class="form-control"
                       value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>"
                       maxlength="20"
                       placeholder="Ej. 662-000-0000">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    <?= isset($id) ? 'Guardar cambios' : 'Crear mecánico' ?>
                </button>
                <a href="<?= $appUrl ?>/?modulo=mecanicos" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
