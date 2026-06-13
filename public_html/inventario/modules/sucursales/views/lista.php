<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-building me-2 text-warning"></i>Sucursales</h4>
    <a href="<?= $appUrl ?>/?modulo=sucursales&accion=nuevo" class="btn btn-warning">
        <i class="bi bi-plus-lg me-1"></i> Nueva sucursal
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Ciudad</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th class="text-center">Activa</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($sucursales)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay sucursales registradas</td></tr>
                <?php else: ?>
                    <?php foreach ($sucursales as $s): ?>
                    <tr>
                        <td class="text-muted"><?= $s['id'] ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($s['nombre']) ?></td>
                        <td><?= htmlspecialchars($s['ciudad']) ?></td>
                        <td><?= htmlspecialchars($s['direccion'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($s['telefono'] ?: '—') ?></td>
                        <td class="text-center">
                            <?php if ($s['activa']): ?>
                                <span class="badge bg-success">Activa</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactiva</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="<?= $appUrl ?>/?modulo=sucursales&accion=editar&id=<?= $s['id'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
