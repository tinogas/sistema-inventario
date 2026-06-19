<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="bi bi-people me-2 text-primary"></i>Clientes</h1>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (Auth::tienePermiso('clientes.crear')): ?>
        <a href="<?= $appUrl ?>/?modulo=clientes&accion=nuevo" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Nuevo cliente
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Buscador -->
<form method="GET" action="<?= $appUrl ?>/" class="mb-3">
    <input type="hidden" name="modulo" value="clientes">
    <div class="input-group" style="max-width:420px">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text"
               name="buscar"
               class="form-control"
               placeholder="Buscar por nombre, RFC o teléfono…"
               value="<?= htmlspecialchars($buscar) ?>">
        <button type="submit" class="btn btn-outline-primary">Buscar</button>
        <?php if ($buscar !== ''): ?>
        <a href="<?= $appUrl ?>/?modulo=clientes" class="btn btn-outline-secondary" title="Limpiar">
            <i class="bi bi-x-lg"></i>
        </a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th style="width:130px">RFC</th>
                        <th style="width:140px">Teléfono</th>
                        <th style="width:200px">Correo</th>
                        <th style="width:80px" class="text-center">Activo</th>
                        <th style="width:100px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($result['filas'])): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            <?= $buscar !== '' ? 'No se encontraron resultados.' : 'No hay clientes registrados.' ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($result['filas'] as $c): ?>
                    <tr class="<?= !$c['activo'] ? 'table-secondary text-muted' : '' ?>">
                        <td class="fw-semibold">
                            <a href="<?= $appUrl ?>/?modulo=clientes&accion=detalle&id=<?= $c['id'] ?>" class="text-decoration-none">
                                <?= htmlspecialchars($c['nombre']) ?>
                            </a>
                            <?php if (!$c['activo']): ?>
                                <span class="badge bg-secondary ms-1">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="font-monospace small"><?= htmlspecialchars($c['rfc'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($c['telefono'] ?? '—') ?></td>
                        <td class="small"><?= htmlspecialchars($c['email'] ?? '—') ?></td>
                        <td class="text-center">
                            <span class="badge <?= $c['activo'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $c['activo'] ? 'Sí' : 'No' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="<?= $appUrl ?>/?modulo=clientes&accion=detalle&id=<?= $c['id'] ?>"
                               class="btn btn-sm btn-outline-info me-1" title="Ver ficha">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if (Auth::tienePermiso('clientes.editar')): ?>
                            <a href="<?= $appUrl ?>/?modulo=clientes&accion=editar&id=<?= $c['id'] ?>"
                               class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($result['total_paginas'] > 1): ?>
    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">
            Mostrando <?= count($result['filas']) ?> de <?= $result['total'] ?> clientes
            (página <?= $result['pagina'] ?> de <?= $result['total_paginas'] ?>)
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php if ($result['pagina'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $appUrl ?>/?modulo=clientes&buscar=<?= urlencode($buscar) ?>&pagina=<?= $result['pagina'] - 1 ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                <?php
                $desde = max(1, $result['pagina'] - 2);
                $hasta  = min($result['total_paginas'], $result['pagina'] + 2);
                for ($p = $desde; $p <= $hasta; $p++):
                ?>
                <li class="page-item <?= $p === $result['pagina'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $appUrl ?>/?modulo=clientes&buscar=<?= urlencode($buscar) ?>&pagina=<?= $p ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
                <?php if ($result['pagina'] < $result['total_paginas']): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $appUrl ?>/?modulo=clientes&buscar=<?= urlencode($buscar) ?>&pagina=<?= $result['pagina'] + 1 ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
