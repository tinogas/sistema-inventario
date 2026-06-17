<?php
/** Vista: Mantenimiento de Base de datos. Variables: $conteos, $seed, $titulo, $appUrl, $csrf */
function _bd_tam(int $b): string {
    if ($b <= 0) return '—';
    $u=['B','KB','MB','GB']; $i=min((int)floor(log($b,1024)),3);
    return round($b/(1024**$i),2).' '.$u[$i];
}
$totalReg = array_sum($conteos);
?>
<h4 class="fw-bold mb-3"><i class="bi bi-hdd-stack text-primary me-2"></i>Base de datos</h4>

<div class="alert alert-danger d-flex align-items-start gap-2">
    <i class="bi bi-exclamation-octagon-fill fs-5"></i>
    <div class="small">
        <strong>Zona delicada.</strong> Estas acciones modifican TODA la base de datos
        <strong><?= htmlspecialchars(DB_NAME) ?></strong>. Te recomendamos generar un
        <a href="<?= $appUrl ?>/?modulo=backups">respaldo</a> antes de continuar.
    </div>
</div>

<div class="row g-3">
    <!-- Datos de ejemplo -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-stars text-warning me-1"></i>Datos de ejemplo (demostración)
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    Guarda una "foto" de los datos actuales como ejemplo y cárgala cuando quieras
                    (ideal para presentaciones). Cargar reemplaza TODO por los datos de ejemplo.
                </p>
                <div class="mb-3 small">
                    <?php if ($seed['existe']): ?>
                        <span class="badge bg-success-subtle text-success border"><i class="bi bi-check-circle me-1"></i>Seed disponible</span>
                        <span class="text-muted ms-1"><?= _bd_tam((int)$seed['tamano']) ?> · <?= htmlspecialchars($seed['fecha']) ?></span>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border">Sin seed guardado todavía</span>
                    <?php endif; ?>
                </div>

                <!-- Guardar seed con datos actuales -->
                <form method="POST" action="<?= $appUrl ?>/?modulo=basedatos&accion=guardar_seed" class="mb-3"
                      onsubmit="return confirm('¿Guardar los datos ACTUALES como seed de ejemplo? Se sobrescribe el anterior.');">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-save me-1"></i>Guardar datos actuales como ejemplo
                    </button>
                </form>

                <!-- Cargar datos de ejemplo -->
                <form method="POST" action="<?= $appUrl ?>/?modulo=basedatos&accion=cargar_ejemplo"
                      onsubmit="return confirm('Esto REEMPLAZA toda la base con los datos de ejemplo. ¿Continuar?');">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <label class="form-label small fw-semibold">Escribe <code>CARGAR</code> para confirmar</label>
                    <div class="input-group">
                        <input type="text" name="confirmar" class="form-control form-control-sm" placeholder="CARGAR" autocomplete="off"
                               <?= $seed['existe'] ? '' : 'disabled' ?>>
                        <button type="submit" class="btn btn-warning btn-sm" <?= $seed['existe'] ? '' : 'disabled' ?>>
                            <i class="bi bi-database-down me-1"></i>Cargar datos de ejemplo
                        </button>
                    </div>
                    <?php if (!$seed['existe']): ?>
                    <div class="form-text text-danger">Primero guarda un seed con los datos actuales.</div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Vaciar -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100 border-danger">
            <div class="card-header bg-white fw-semibold text-danger">
                <i class="bi bi-trash3 me-1"></i>Empezar desde cero
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    Vacía la base para iniciar limpio. <strong>Se conserva tu usuario administrador</strong>
                    y los catálogos base (sucursales, categorías, unidades). Se borra todo lo demás:
                    productos, existencias, movimientos, traspasos, facturas, proveedores, mecánicos,
                    servicios, otros usuarios, auditoría y respaldos.
                </p>
                <form method="POST" action="<?= $appUrl ?>/?modulo=basedatos&accion=vaciar"
                      onsubmit="return confirm('¡ATENCIÓN! Esto BORRA casi todos los datos de forma permanente. ¿Continuar?');">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <label class="form-label small fw-semibold">Escribe <code>VACIAR</code> para confirmar</label>
                    <div class="input-group">
                        <input type="text" name="confirmar" class="form-control form-control-sm" placeholder="VACIAR" autocomplete="off">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-exclamation-triangle me-1"></i>Vaciar base de datos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Estado actual -->
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between">
        <span><i class="bi bi-table me-1 text-secondary"></i>Estado actual</span>
        <span class="badge bg-secondary"><?= number_format($totalReg) ?> registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light"><tr><th>Tabla</th><th class="text-end">Registros</th></tr></thead>
                <tbody>
                <?php foreach ($conteos as $tabla => $n): ?>
                    <tr>
                        <td class="font-monospace small"><?= htmlspecialchars($tabla) ?></td>
                        <td class="text-end"><?= number_format($n) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
