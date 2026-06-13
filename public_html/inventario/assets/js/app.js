/* ============================================================
   Inventario Taller — JavaScript global
   ============================================================ */

// Toggle sidebar
document.addEventListener('DOMContentLoaded', function () {
    const sidebar     = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const toggleBtn   = document.getElementById('sidebarToggle');

    if (toggleBtn && sidebar && mainContent) {
        toggleBtn.addEventListener('click', function () {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('open');
            } else {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            }
        });
    }

    // Badge de alertas de stock
    cargarBadgeAlertas();

    // Tooltips de Bootstrap
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach(el => new bootstrap.Tooltip(el));
});

function cargarBadgeAlertas() {
    const badge = document.getElementById('badge-alertas');
    if (!badge) return;
    fetch(APP_URL + '/api/alertas_stock.php')
        .then(r => r.json())
        .then(data => {
            if (data.total > 0) {
                badge.textContent = data.total > 99 ? '99+' : data.total;
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(() => { badge.style.display = 'none'; });
}

// Confirmación de eliminación
function confirmarEliminar(mensaje, url) {
    if (confirm(mensaje || '¿Estás seguro de que deseas eliminar este registro?')) {
        window.location.href = url;
    }
}

// Formatear número como moneda MXN
function formatMXN(valor) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(valor);
}

// Formatear número
function formatNum(valor, decimales) {
    return new Intl.NumberFormat('es-MX', { minimumFractionDigits: decimales || 0 }).format(valor);
}
