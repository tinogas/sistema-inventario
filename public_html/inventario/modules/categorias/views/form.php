<?php $esEditar = !empty($categoria); ?>

<div class="d-flex align-items-center mb-4 gap-2">
    <a href="<?= $appUrl ?>/?modulo=categorias" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="h3 mb-0">
        <i class="bi bi-tags me-2 text-primary"></i>
        <?= $esEditar ? 'Editar categoría' : 'Nueva categoría' ?>
    </h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST"
                      action="<?= $appUrl ?>/?modulo=categorias&accion=<?= $esEditar ? 'editar&id=' . $categoria['id'] : 'nuevo' ?>">
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
                               maxlength="80"
                               required
                               value="<?= htmlspecialchars($categoria['nombre'] ?? '') ?>">
                    </div>

                    <!-- Descripción -->
                    <div class="mb-4">
                        <label for="descripcion" class="form-label fw-semibold">Descripción</label>
                        <textarea id="descripcion"
                                  name="descripcion"
                                  class="form-control"
                                  rows="3"
                                  maxlength="255"><?= htmlspecialchars($categoria['descripcion'] ?? '') ?></textarea>
                        <div class="form-text">Máximo 255 caracteres. Opcional.</div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy me-1"></i>
                            <?= $esEditar ? 'Actualizar' : 'Guardar' ?>
                        </button>
                        <a href="<?= $appUrl ?>/?modulo=categorias" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
