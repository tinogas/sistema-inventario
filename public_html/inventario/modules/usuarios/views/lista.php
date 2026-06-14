<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-people me-2 text-primary"></i>Usuarios
    </h4>
    <a href="<?= $appUrl ?>/?modulo=usuarios&accion=nuevo" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nuevo usuario
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($usuarios)): ?>
            <p class="text-muted text-center py-5 mb-0">
                <i class="bi bi-people fs-3 d-block mb-2"></i>
                No hay usuarios registrados.
            </p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Correo electrónico</th>
                        <th>Rol</th>
                        <th>Sucursal</th>
                        <th>Último acceso</th>
                        <th class="text-center">Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $usuarioActualId = (int) Auth::usuario()['id'];
                    foreach ($usuarios as $u):
                        $esMismoCuenta = (int)$u['id'] === $usuarioActualId;
                    ?>
                    <tr>
                        <td class="text-muted small"><?= $u['id'] ?></td>
                        <td>
                            <img src="<?= !empty($u['foto']) ? $appUrl.'/'.htmlspecialchars($u['foto']) : 'https://ui-avatars.com/api/?name='.urlencode($u['nombre']).'&background=1a2332&color=fff&size=64' ?>"
                                 alt="" class="rounded-circle border" style="width:40px;height:40px;object-fit:cover">
                        </td>
                        <td class="fw-semibold">
                            <?= htmlspecialchars($u['nombre']) ?>
                            <?php if ($esMismoCuenta): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">Tú</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <?php
                            $badgeRol = match($u['rol']) {
                                'admin'       => 'bg-danger-subtle text-danger border border-danger-subtle',
                                'almacenista' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                default       => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                            };
                            $iconRol = match($u['rol']) {
                                'admin'       => 'bi-shield-fill',
                                'almacenista' => 'bi-person-badge',
                                default       => 'bi-eye',
                            };
                            ?>
                            <span class="badge <?= $badgeRol ?>">
                                <i class="bi <?= $iconRol ?> me-1"></i><?= ucfirst($u['rol']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['sucursal_nombre']): ?>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($u['sucursal_nombre']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">Todas</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small">
                            <?= $u['ultimo_acceso']
                                ? date('d/m/Y H:i', strtotime($u['ultimo_acceso']))
                                : '<span class="text-muted">Nunca</span>' ?>
                        </td>
                        <td class="text-center">
                            <?php if ($u['activo']): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>Sí
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-x-circle me-1"></i>No
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= $appUrl ?>/?modulo=usuarios&accion=editar&id=<?= $u['id'] ?>"
                               class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if (!$esMismoCuenta): ?>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Dar de baja"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEliminar"
                                    data-id="<?= $u['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($u['nombre'], ENT_QUOTES) ?>">
                                <i class="bi bi-person-dash"></i>
                            </button>
                            <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary" disabled title="No puedes darte de baja a ti mismo">
                                <i class="bi bi-person-dash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal confirmación baja -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalEliminarLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirmar baja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Deseas dar de baja al usuario <strong id="modalNombre"></strong>?
                <p class="text-muted small mt-2 mb-0">El usuario no podrá iniciar sesión pero sus registros se conservarán.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="<?= $appUrl ?>/?modulo=usuarios&accion=eliminar">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="id" id="inputIdEliminar" value="">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-person-dash me-1"></i>Dar de baja
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('modalEliminar').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('modalNombre').textContent = btn.dataset.nombre;
    document.getElementById('inputIdEliminar').value   = btn.dataset.id;
});
</script>
