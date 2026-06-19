<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="<?= $appUrl ?>/?modulo=clientes" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0">
            <i class="bi bi-person-circle me-2 text-primary"></i>
            <?= htmlspecialchars($cliente['nombre']) ?>
            <?php if (!$cliente['activo']): ?>
            <span class="badge bg-secondary ms-1 fs-6">Inactivo</span>
            <?php endif; ?>
        </h1>
    </div>
    <div class="d-flex gap-2">
        <?php if (Auth::tienePermiso('clientes.editar')): ?>
        <a href="<?= $appUrl ?>/?modulo=clientes&accion=editar&id=<?= $cliente['id'] ?>"
           class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <form method="POST" action="<?= $appUrl ?>/?modulo=clientes&accion=toggle_activo" class="d-inline"
              onsubmit="return confirm('¿Confirmas cambiar el estado del cliente?')">
            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
            <input type="hidden" name="id" value="<?= $cliente['id'] ?>">
            <button type="submit" class="btn btn-sm <?= $cliente['activo'] ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                <i class="bi bi-<?= $cliente['activo'] ? 'slash-circle' : 'check-circle' ?> me-1"></i>
                <?= $cliente['activo'] ? 'Desactivar' : 'Activar' ?>
            </button>
        </form>
        <?php endif; ?>
        <?php if (Auth::tienePermiso('bitacoras.ver')): ?>
        <a href="<?= $appUrl ?>/?modulo=bitacoras&cliente_id=<?= $cliente['id'] ?>"
           class="btn btn-sm btn-outline-info">
            <i class="bi bi-journal-text me-1"></i> Bitácoras
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Datos del cliente -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-0 pb-0">
                <i class="bi bi-person me-1 text-primary"></i> Datos de contacto
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <?php if ($cliente['rfc']): ?>
                    <tr><th style="width:100px">RFC</th><td class="font-monospace"><?= htmlspecialchars($cliente['rfc']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($cliente['telefono']): ?>
                    <tr><th>Teléfono</th><td><?= htmlspecialchars($cliente['telefono']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($cliente['email']): ?>
                    <tr><th>Correo</th><td><a href="mailto:<?= htmlspecialchars($cliente['email']) ?>"><?= htmlspecialchars($cliente['email']) ?></a></td></tr>
                    <?php endif; ?>
                    <?php if ($cliente['direccion']): ?>
                    <tr><th>Dirección</th><td><?= htmlspecialchars($cliente['direccion']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($cliente['notas']): ?>
                    <tr><th>Notas</th><td class="text-muted small"><?= nl2br(htmlspecialchars($cliente['notas'])) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-0 pb-0">
                <i class="bi bi-car-front me-1 text-primary"></i> Unidades registradas
                <span class="badge bg-primary ms-1"><?= count($unidades) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($unidades)): ?>
                <div class="text-center text-muted py-4 px-3">
                    <i class="bi bi-car-front fs-3 d-block mb-2"></i>
                    Sin unidades registradas.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Vehículo</th>
                                <th>Año</th>
                                <th>Placas</th>
                                <th>Color</th>
                                <th style="width:80px" class="text-center">Estado</th>
                                <?php if (Auth::tienePermiso('clientes.editar')): ?>
                                <th style="width:80px"></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($unidades as $u): ?>
                        <tr class="<?= !$u['activo'] ? 'table-secondary text-muted' : '' ?>">
                            <td class="fw-semibold"><?= htmlspecialchars($u['marca'] . ' ' . $u['modelo']) ?></td>
                            <td><?= $u['anio'] ?: '—' ?></td>
                            <td class="font-monospace"><?= htmlspecialchars($u['placas'] ?: '—') ?></td>
                            <td><?= htmlspecialchars($u['color'] ?: '—') ?></td>
                            <td class="text-center">
                                <span class="badge <?= $u['activo'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $u['activo'] ? 'Activa' : 'Inactiva' ?>
                                </span>
                            </td>
                            <?php if (Auth::tienePermiso('clientes.editar')): ?>
                            <td class="text-center">
                                <a href="<?= $appUrl ?>/?modulo=unidad_cliente&accion=editar&id=<?= $u['id'] ?>"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php if (Auth::tienePermiso('clientes.editar')): ?>
            <div class="card-footer bg-transparent">
                <a href="<?= $appUrl ?>/?modulo=unidad_cliente&accion=nueva&cliente_id=<?= $cliente['id'] ?>"
                   class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Agregar unidad
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
