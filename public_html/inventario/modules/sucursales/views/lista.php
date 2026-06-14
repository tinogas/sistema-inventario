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
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Ciudad</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th class="text-center">Mapa</th>
                        <th class="text-center">Activa</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($sucursales)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No hay sucursales registradas</td></tr>
                <?php else: ?>
                    <?php foreach ($sucursales as $s): ?>
                    <tr>
                        <td class="text-muted"><?= htmlspecialchars((string)$s['id']) ?></td>
                        <td>
                            <img src="<?= foto_sucursal($s['foto'] ?? null, $appUrl, 112, 80) ?>"
                                 alt="" class="rounded border" style="width:56px;height:40px;object-fit:cover">
                        </td>
                        <td class="fw-semibold"><?= htmlspecialchars($s['nombre']) ?></td>
                        <td><?= htmlspecialchars($s['ciudad']) ?></td>
                        <td><?= htmlspecialchars($s['direccion'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($s['telefono'] ?: '—') ?></td>
                        <td class="text-center">
                            <?php if (!empty($s['latitud']) && !empty($s['longitud'])): ?>
                                <a href="https://www.google.com/maps?q=<?= $s['latitud'] ?>,<?= $s['longitud'] ?>"
                                   target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger" title="Ver ubicación">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($s['activa']): ?>
                                <span class="badge bg-success">Activa</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactiva</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="<?= $appUrl ?>/?modulo=sucursales&accion=editar&id=<?= htmlspecialchars((string)$s['id']) ?>"
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
