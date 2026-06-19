<?php $esEditar = !empty($unidad); ?>

<div class="d-flex align-items-center mb-4 gap-2">
    <a href="<?= $appUrl ?>/?modulo=clientes&accion=detalle&id=<?= $cliente['id'] ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="h3 mb-0">
        <i class="bi bi-car-front me-2 text-primary"></i>
        <?= $esEditar ? 'Editar unidad' : 'Nueva unidad' ?>
    </h1>
</div>

<p class="text-muted mb-3">
    <i class="bi bi-person me-1"></i>
    Cliente: <strong><?= htmlspecialchars($cliente['nombre']) ?></strong>
</p>

<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST"
                      action="<?= $appUrl ?>/?modulo=unidad_cliente&accion=<?= $esEditar ? 'editar&id='.$unidad['id'] : 'nueva' ?>">
                    <input type="hidden" name="_csrf"       value="<?= $csrf ?>">
                    <input type="hidden" name="cliente_id"  value="<?= $cliente['id'] ?>">

                    <div class="row">
                        <!-- Marca -->
                        <div class="col-md-6 mb-3">
                            <label for="marca" class="form-label fw-semibold">
                                Marca <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="marca"
                                   name="marca"
                                   class="form-control"
                                   maxlength="50"
                                   required
                                   value="<?= htmlspecialchars($unidad['marca'] ?? '') ?>"
                                   placeholder="Toyota, Ford, Chevrolet…">
                        </div>
                        <!-- Modelo -->
                        <div class="col-md-6 mb-3">
                            <label for="modelo" class="form-label fw-semibold">
                                Modelo <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="modelo"
                                   name="modelo"
                                   class="form-control"
                                   maxlength="80"
                                   required
                                   value="<?= htmlspecialchars($unidad['modelo'] ?? '') ?>"
                                   placeholder="Hilux, F-150…">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Año -->
                        <div class="col-md-3 mb-3">
                            <label for="anio" class="form-label fw-semibold">Año</label>
                            <input type="number"
                                   id="anio"
                                   name="anio"
                                   class="form-control"
                                   min="1950"
                                   max="<?= date('Y') + 1 ?>"
                                   value="<?= htmlspecialchars($unidad['anio'] ?? '') ?>">
                        </div>
                        <!-- Placas -->
                        <div class="col-md-3 mb-3">
                            <label for="placas" class="form-label fw-semibold">Placas</label>
                            <input type="text"
                                   id="placas"
                                   name="placas"
                                   class="form-control"
                                   maxlength="20"
                                   style="text-transform:uppercase"
                                   value="<?= htmlspecialchars($unidad['placas'] ?? '') ?>"
                                   placeholder="ABC-123">
                        </div>
                        <!-- Color -->
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label fw-semibold">Color</label>
                            <input type="text"
                                   id="color"
                                   name="color"
                                   class="form-control"
                                   maxlength="30"
                                   value="<?= htmlspecialchars($unidad['color'] ?? '') ?>"
                                   placeholder="Blanco, Rojo…">
                        </div>
                    </div>

                    <!-- Número de serie -->
                    <div class="mb-3">
                        <label for="numero_serie" class="form-label fw-semibold">Número de serie / VIN</label>
                        <input type="text"
                               id="numero_serie"
                               name="numero_serie"
                               class="form-control"
                               maxlength="50"
                               value="<?= htmlspecialchars($unidad['numero_serie'] ?? '') ?>">
                    </div>

                    <!-- Notas -->
                    <div class="mb-4">
                        <label for="notas" class="form-label fw-semibold">Notas</label>
                        <textarea id="notas"
                                  name="notas"
                                  class="form-control"
                                  rows="2"><?= htmlspecialchars($unidad['notas'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy me-1"></i>
                            <?= $esEditar ? 'Actualizar' : 'Guardar' ?>
                        </button>
                        <a href="<?= $appUrl ?>/?modulo=clientes&accion=detalle&id=<?= $cliente['id'] ?>"
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
document.getElementById('placas').addEventListener('input', function () {
    var pos = this.selectionStart;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(pos, pos);
});
</script>
