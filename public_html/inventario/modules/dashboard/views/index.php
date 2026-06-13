<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2 text-warning"></i>Dashboard</h4>
    <span class="text-muted small"><?= date('l, d \d\e F \d\e Y') ?></span>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon amber"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="kpi-value"><?= number_format($kpis['totalProductos']) ?></div>
                <div class="kpi-label">Productos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="bi bi-box-arrow-in-down-right"></i></div>
            <div>
                <div class="kpi-value"><?= $kpis['entradas'] ?></div>
                <div class="kpi-label">Entradas hoy</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon red"><i class="bi bi-box-arrow-up-right"></i></div>
            <div>
                <div class="kpi-value"><?= $kpis['salidas'] ?></div>
                <div class="kpi-label">Salidas hoy</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="bi bi-arrow-left-right"></i></div>
            <div>
                <div class="kpi-value"><?= $kpis['traspasos'] ?></div>
                <div class="kpi-label">En tránsito</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon <?= $kpis['alertasCount'] > 0 ? 'red' : 'green' ?>">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div>
                <div class="kpi-value"><?= $kpis['alertasCount'] ?></div>
                <div class="kpi-label">Alertas stock</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Gráfica de movimientos -->
    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-0 pb-0">
                <i class="bi bi-bar-chart me-1 text-primary"></i> Movimientos últimos 7 días
            </div>
            <div class="card-body">
                <canvas id="chartMovimientos" height="110"></canvas>
            </div>
        </div>
    </div>

    <!-- Alertas de stock -->
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pb-0">
                <span class="fw-semibold">
                    <i class="bi bi-exclamation-triangle text-warning me-1"></i> Stock bajo mínimo
                </span>
                <a href="<?= $appUrl ?>/?modulo=reportes&accion=alertas" class="btn btn-xs btn-outline-warning py-0 px-2" style="font-size:.75rem">Ver todo</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($alertas)): ?>
                    <p class="text-muted text-center py-4 mb-0"><i class="bi bi-check-circle text-success me-1"></i>Sin alertas de stock</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-end">Stock</th>
                                    <th class="text-end">Mínimo</th>
                                    <th>Sucursal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alertas as $a): ?>
                                <tr>
                                    <td>
                                        <a href="<?= $appUrl ?>/?modulo=productos&accion=detalle&id=<?= $a['id'] ?>" class="text-decoration-none text-truncate-150 d-inline-block">
                                            <?= htmlspecialchars($a['nombre']) ?>
                                        </a>
                                    </td>
                                    <td class="text-end fw-bold text-danger"><?= number_format($a['stock_actual'],2) ?></td>
                                    <td class="text-end text-muted"><?= number_format($a['stock_minimo'],2) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($a['sucursal']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Últimas actividades -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white fw-semibold border-0 pb-0">
        <i class="bi bi-clock-history me-1 text-secondary"></i> Últimas actividades
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Folio</th>
                        <th>Tipo</th>
                        <th>Sucursal</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimas)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">Sin actividad reciente</td></tr>
                    <?php else: ?>
                    <?php foreach ($ultimas as $u): ?>
                    <tr>
                        <td><code class="text-primary"><?= htmlspecialchars($u['folio']) ?></code></td>
                        <td><?= ucfirst(str_replace('_', ' ', $u['tipo'])) ?></td>
                        <td><?= htmlspecialchars($u['sucursal']) ?></td>
                        <td><?= htmlspecialchars($u['usuario']) ?></td>
                        <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                        <td>
                            <span class="badge badge-estado-<?= $u['estado'] ?>">
                                <?= ucfirst($u['estado']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';

// Gráfica movimientos 7 días
(function () {
    const data = <?= json_encode($movimientos7) ?>;
    const labels   = data.map(r => {
        const d = new Date(r.fecha + 'T00:00:00');
        return d.toLocaleDateString('es-MX', { weekday:'short', day:'numeric' });
    });
    const entradas = data.map(r => parseInt(r.entradas) || 0);
    const salidas  = data.map(r => parseInt(r.salidas)  || 0);

    const ctx = document.getElementById('chartMovimientos');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Entradas', data: entradas, backgroundColor: '#86efac', borderRadius: 4 },
                { label: 'Salidas',  data: salidas,  backgroundColor: '#fca5a5', borderRadius: 4 },
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
})();
</script>
