<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-building me-2 text-warning"></i>
        <?= isset($sucursal) ? 'Editar sucursal' : 'Nueva sucursal' ?>
    </h4>
    <a href="<?= $appUrl ?>/?modulo=sucursales" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<?php
    $foto = $sucursal['foto'] ?? ($datos['foto'] ?? null);
    $lat  = $sucursal['latitud']  ?? ($datos['latitud']  ?? '');
    $lng  = $sucursal['longitud'] ?? ($datos['longitud'] ?? '');
?>
<div class="card border-0 shadow-sm" style="max-width:760px">
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data" action="<?= $appUrl ?>/?modulo=sucursales&accion=<?= isset($sucursal) ? 'editar&id='.$sucursal['id'] : 'nuevo' ?>">
            <input type="hidden" name="_csrf" value="<?= $csrf ?>">

            <div class="row g-3">
                <div class="col-md-7">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" required maxlength="100"
                               value="<?= htmlspecialchars($sucursal['nombre'] ?? ($datos['nombre'] ?? '')) ?>"
                               placeholder="Ej. Sucursal Norte">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ciudad <span class="text-danger">*</span></label>
                        <input type="text" name="ciudad" class="form-control" required maxlength="80"
                               value="<?= htmlspecialchars($sucursal['ciudad'] ?? ($datos['ciudad'] ?? '')) ?>"
                               placeholder="Ej. Hermosillo, Sonora">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Dirección</label>
                        <input type="text" name="direccion" class="form-control" maxlength="255"
                               value="<?= htmlspecialchars($sucursal['direccion'] ?? ($datos['direccion'] ?? '')) ?>"
                               placeholder="Calle, número, colonia">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" maxlength="20"
                               value="<?= htmlspecialchars($sucursal['telefono'] ?? ($datos['telefono'] ?? '')) ?>"
                               placeholder="Ej. 662-123-4567">
                    </div>
                </div>
                <div class="col-md-5">
                    <!-- Foto de la sucursal -->
                    <label class="form-label fw-semibold">Foto de la sucursal</label>
                    <div class="text-center mb-2">
                        <img id="previewFoto"
                             src="<?= $foto ? $appUrl.'/'.htmlspecialchars($foto) : 'https://placehold.co/300x180?text=Sucursal' ?>"
                             alt="Foto sucursal" class="img-fluid rounded border" style="max-height:160px;object-fit:cover">
                    </div>
                    <input type="file" name="foto" class="form-control form-control-sm" accept="image/*"
                           onchange="if(this.files[0]){document.getElementById('previewFoto').src=URL.createObjectURL(this.files[0]);}">
                    <div class="form-text">JPG, PNG, WEBP o GIF. Máx. 4 MB.</div>
                </div>
            </div>

            <!-- Ubicación / mapa -->
            <hr class="my-3">
            <h6 class="fw-semibold"><i class="bi bi-geo-alt text-danger me-1"></i>Ubicación en el mapa</h6>
            <div class="row g-3 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label small fw-semibold">Latitud</label>
                    <input type="text" name="latitud" id="inpLat" class="form-control form-control-sm"
                           value="<?= htmlspecialchars((string)$lat) ?>" placeholder="29.0729">
                </div>
                <div class="col-sm-4">
                    <label class="form-label small fw-semibold">Longitud</label>
                    <input type="text" name="longitud" id="inpLng" class="form-control form-control-sm"
                           value="<?= htmlspecialchars((string)$lng) ?>" placeholder="-110.9559">
                </div>
                <div class="col-sm-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="btnMostrarMapa">
                        <i class="bi bi-map me-1"></i> Mostrar/actualizar mapa
                    </button>
                </div>
            </div>
            <div class="form-text mb-2">
                Pega la latitud y longitud (cópialas de Google Maps: clic derecho sobre el punto → coordenadas).
            </div>
            <div id="wrapMapa" class="mb-3" style="<?= ($lat && $lng) ? '' : 'display:none' ?>">
                <iframe id="mapa" width="100%" height="240" style="border:1px solid #ddd;border-radius:6px"
                        loading="lazy"
                        src="<?= ($lat && $lng) ? 'https://www.openstreetmap.org/export/embed.html?bbox='.($lng-0.01).','.($lat-0.01).','.($lng+0.01).','.($lat+0.01).'&layer=mapnik&marker='.$lat.','.$lng : '' ?>"></iframe>
                <a id="linkGmaps" class="small" target="_blank" rel="noopener"
                   href="<?= ($lat && $lng) ? 'https://www.google.com/maps?q='.$lat.','.$lng : '#' ?>">
                   <i class="bi bi-box-arrow-up-right me-1"></i>Ver en Google Maps
                </a>
            </div>

            <?php if (isset($sucursal)): ?>
            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="activa" id="chkActiva"
                           <?= $sucursal['activa'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="chkActiva">Sucursal activa</label>
                </div>
            </div>
            <?php endif; ?>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-semibold">
                    <i class="bi bi-check2 me-1"></i>
                    <?= isset($sucursal) ? 'Guardar cambios' : 'Crear sucursal' ?>
                </button>
                <a href="<?= $appUrl ?>/?modulo=sucursales" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('btnMostrarMapa').addEventListener('click', function () {
    const lat = parseFloat(document.getElementById('inpLat').value);
    const lng = parseFloat(document.getElementById('inpLng').value);
    if (isNaN(lat) || isNaN(lng)) { alert('Captura latitud y longitud válidas.'); return; }
    const d = 0.01;
    const bbox = (lng - d) + ',' + (lat - d) + ',' + (lng + d) + ',' + (lat + d);
    document.getElementById('mapa').src =
        'https://www.openstreetmap.org/export/embed.html?bbox=' + bbox + '&layer=mapnik&marker=' + lat + ',' + lng;
    document.getElementById('linkGmaps').href = 'https://www.google.com/maps?q=' + lat + ',' + lng;
    document.getElementById('wrapMapa').style.display = '';
});
</script>
