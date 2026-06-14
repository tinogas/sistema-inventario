<div class="d-flex align-items-center mb-4 gap-2">
    <a href="<?= $appUrl ?>/?modulo=usuarios" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="fw-bold mb-0">
        <i class="bi bi-person-plus me-2 text-primary"></i>
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

<div class="card border-0 shadow-sm" style="max-width:620px">
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data"
              action="<?= $appUrl ?>/?modulo=usuarios&accion=<?= isset($id) ? 'editar&id=' . $id : 'nuevo' ?>"
              id="formUsuario"
              autocomplete="off">
            <input type="hidden" name="_csrf" value="<?= $csrf ?>">

            <!-- Foto -->
            <div class="mb-3 text-center">
                <?php $fotoActual = $datos['foto'] ?? ($usuario['foto'] ?? null); ?>
                <img id="previewFoto"
                     src="<?= foto_o_avatar($fotoActual, $datos['nombre'] ?: 'Usuario', $appUrl, 128) ?>"
                     alt="Foto" class="rounded-circle border" style="width:120px;height:120px;object-fit:cover">
                <div class="mt-2">
                    <label for="foto" class="form-label fw-semibold small">Foto del usuario</label>
                    <input type="file" id="foto" name="foto" class="form-control form-control-sm" accept="image/*"
                           data-preview="previewFoto">
                    <div class="form-text">JPG, PNG, WEBP o GIF. Máx. 4 MB.</div>
                </div>
            </div>

            <!-- Nombre -->
            <div class="mb-3">
                <label for="nombre" class="form-label fw-semibold">
                    Nombre completo <span class="text-danger">*</span>
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

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">
                    Correo electrónico <span class="text-danger">*</span>
                </label>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control"
                       value="<?= htmlspecialchars($datos['email']) ?>"
                       maxlength="150"
                       required>
            </div>

            <!-- Contraseña -->
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">
                    Contraseña
                    <?php if (isset($id)): ?>
                        <span class="text-muted fw-normal small">(dejar en blanco para no cambiar)</span>
                    <?php else: ?>
                        <span class="text-danger">*</span>
                    <?php endif; ?>
                </label>
                <div class="input-group">
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control"
                           minlength="6"
                           autocomplete="new-password"
                           <?= !isset($id) ? 'required' : '' ?>>
                    <button type="button"
                            class="btn btn-outline-secondary"
                            id="btnTogglePass"
                            title="Mostrar/ocultar contraseña">
                        <i class="bi bi-eye" id="icoPass"></i>
                    </button>
                </div>
                <div class="form-text">Mínimo 6 caracteres.</div>
            </div>

            <!-- Rol -->
            <div class="mb-3">
                <label for="rol" class="form-label fw-semibold">
                    Rol <span class="text-danger">*</span>
                </label>
                <select id="rol" name="rol" class="form-select" required>
                    <option value="">— Selecciona un rol —</option>
                    <option value="admin"
                        <?= $datos['rol'] === 'admin' ? 'selected' : '' ?>>
                        Admin
                    </option>
                    <option value="almacenista"
                        <?= $datos['rol'] === 'almacenista' ? 'selected' : '' ?>>
                        Almacenista
                    </option>
                    <option value="consulta"
                        <?= $datos['rol'] === 'consulta' ? 'selected' : '' ?>>
                        Consulta
                    </option>
                </select>
            </div>

            <!-- Sucursal -->
            <div class="mb-4" id="wrapSucursal">
                <label for="sucursal_id" class="form-label fw-semibold">
                    Sucursal
                    <span class="text-danger" id="lblSucursalReq">*</span>
                    <span class="text-muted fw-normal small d-none" id="lblSucursalOpc">(no aplica para admin)</span>
                </label>
                <select id="sucursal_id" name="sucursal_id" class="form-select">
                    <option value="">— Selecciona una sucursal —</option>
                    <?php foreach ($sucursales as $s): ?>
                    <option value="<?= $s['id'] ?>"
                        <?= (int)$datos['sucursal_id'] === (int)$s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    <?= isset($id) ? 'Guardar cambios' : 'Crear usuario' ?>
                </button>
                <a href="<?= $appUrl ?>/?modulo=usuarios" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// ---- Toggle mostrar/ocultar contraseña ----
document.getElementById('btnTogglePass').addEventListener('click', function () {
    const inp = document.getElementById('password');
    const ico = document.getElementById('icoPass');
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        ico.className = 'bi bi-eye';
    }
});

// ---- Sucursal: deshabilitar si rol = admin ----
const selRol       = document.getElementById('rol');
const selSucursal  = document.getElementById('sucursal_id');
const lblReq       = document.getElementById('lblSucursalReq');
const lblOpc       = document.getElementById('lblSucursalOpc');

function actualizarSucursal() {
    const esAdmin = selRol.value === 'admin';
    selSucursal.disabled = esAdmin;
    if (esAdmin) {
        selSucursal.value = '';
        lblReq.classList.add('d-none');
        lblOpc.classList.remove('d-none');
    } else {
        lblReq.classList.remove('d-none');
        lblOpc.classList.add('d-none');
    }
}

selRol.addEventListener('change', actualizarSucursal);
// Aplicar estado inicial al cargar
actualizarSucursal();
</script>
