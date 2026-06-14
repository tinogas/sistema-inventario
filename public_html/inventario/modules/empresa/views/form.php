<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-building-gear me-2 text-primary"></i>Datos de la empresa</h4>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= $appUrl ?>/?modulo=empresa&accion=guardar" autocomplete="off">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <div class="row g-3">

                <!-- Nombre -->
                <div class="col-md-8">
                    <label for="nombre" class="form-label fw-semibold">
                        Nombre de la empresa <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre" class="form-control"
                           value="<?= htmlspecialchars($empresa['nombre'] ?? '') ?>"
                           required maxlength="200"
                           placeholder="Ej. Taller Muelles Sonora">
                </div>

                <!-- RFC -->
                <div class="col-md-4">
                    <label for="rfc" class="form-label fw-semibold">RFC</label>
                    <input type="text" id="rfc" name="rfc" class="form-control"
                           value="<?= htmlspecialchars($empresa['rfc'] ?? '') ?>"
                           maxlength="13"
                           placeholder="Ej. TMS800101ABC">
                </div>

                <!-- Dirección -->
                <div class="col-12">
                    <label for="direccion" class="form-label fw-semibold">Dirección</label>
                    <input type="text" id="direccion" name="direccion" class="form-control"
                           value="<?= htmlspecialchars($empresa['direccion'] ?? '') ?>"
                           maxlength="300"
                           placeholder="Calle, número, colonia">
                </div>

                <!-- Ciudad -->
                <div class="col-md-6">
                    <label for="ciudad" class="form-label fw-semibold">Ciudad</label>
                    <input type="text" id="ciudad" name="ciudad" class="form-control"
                           value="<?= htmlspecialchars($empresa['ciudad'] ?? '') ?>"
                           maxlength="100"
                           placeholder="Ej. Hermosillo">
                </div>

                <!-- Código postal -->
                <div class="col-md-3">
                    <label for="cp" class="form-label fw-semibold">Código postal</label>
                    <input type="text" id="cp" name="cp" class="form-control"
                           value="<?= htmlspecialchars($empresa['cp'] ?? '') ?>"
                           maxlength="10"
                           placeholder="Ej. 83000">
                </div>

                <!-- Teléfono -->
                <div class="col-md-3">
                    <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="form-control"
                           value="<?= htmlspecialchars($empresa['telefono'] ?? '') ?>"
                           maxlength="30"
                           placeholder="Ej. 662 123 4567">
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">Correo electrónico</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($empresa['email'] ?? '') ?>"
                           maxlength="150"
                           placeholder="Ej. contacto@tallermuelles.com">
                </div>

                <!-- Logo path -->
                <div class="col-md-6">
                    <label for="logo_path" class="form-label fw-semibold">Ruta del logo</label>
                    <input type="text" id="logo_path" name="logo_path" class="form-control"
                           value="<?= htmlspecialchars($empresa['logo_path'] ?? '') ?>"
                           maxlength="300"
                           placeholder="Ej. assets/img/logo.png">
                    <div class="form-text">Ruta relativa a la raíz del sistema.</div>
                </div>

                <!-- Pie de factura -->
                <div class="col-12">
                    <label for="pie_factura" class="form-label fw-semibold">Pie de página para impresión de facturas</label>
                    <textarea id="pie_factura" name="pie_factura" class="form-control" rows="3"
                              maxlength="500"
                              placeholder="Ej. Gracias por su preferencia. Garantía sujeta a condiciones del servicio."><?= htmlspecialchars($empresa['pie_factura'] ?? '') ?></textarea>
                    <div class="form-text">Este texto aparece al pie de cada factura impresa.</div>
                </div>

            </div><!-- /row -->

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy me-1"></i> Guardar
                </button>
                <a href="<?= $appUrl ?>/?modulo=dashboard" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
