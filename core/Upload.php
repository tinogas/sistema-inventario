<?php
/**
 * Upload — Helper para subir imágenes (fotos de mecánicos, usuarios, sucursales).
 * Guarda en /uploads/fotos/ y devuelve la ruta relativa al raíz del sistema.
 * No requiere extensiones especiales (usa finfo, incluido en PHP estándar).
 */
class Upload
{
    private const DIR_REL = 'uploads/fotos/';
    private const MAX_BYTES = 4194304; // 4 MB
    private const TIPOS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /**
     * Procesa el archivo subido en $_FILES[$campo].
     * - Si no se subió nada, devuelve $actual (la foto previa, si la hay).
     * - Si es válido, lo guarda con nombre único, borra la foto anterior y
     *   devuelve la ruta relativa "uploads/fotos/...".
     * - Si es inválido, lanza RuntimeException.
     *
     * @param string      $campo   nombre del input file
     * @param string      $prefijo prefijo del archivo (p.ej. "mecanico", "usuario", "sucursal")
     * @param string|null $actual  ruta de la foto actual (para conservarla o reemplazarla)
     */
    public static function imagen(string $campo, string $prefijo, ?string $actual = null): ?string
    {
        $f = $_FILES[$campo] ?? null;
        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $actual; // no se subió una imagen nueva
        }
        if ($f['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo subir la imagen (código ' . $f['error'] . ').');
        }
        if ($f['size'] > self::MAX_BYTES) {
            throw new RuntimeException('La imagen supera el tamaño máximo permitido (4 MB).');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($f['tmp_name']);
        if (!isset(self::TIPOS[$mime])) {
            throw new RuntimeException('Formato no permitido. Usa JPG, PNG, WEBP o GIF.');
        }

        $dirAbs = rtrim(BASE_PATH, '/\\') . '/' . self::DIR_REL;
        if (!is_dir($dirAbs) && !@mkdir($dirAbs, 0775, true) && !is_dir($dirAbs)) {
            throw new RuntimeException('No se pudo crear la carpeta de imágenes.');
        }

        $ext     = self::TIPOS[$mime];
        $nombre  = preg_replace('/[^a-z0-9]/', '', strtolower($prefijo))
                 . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destino = $dirAbs . $nombre;

        if (!move_uploaded_file($f['tmp_name'], $destino)) {
            throw new RuntimeException('No se pudo guardar la imagen en el servidor.');
        }

        // Borrar la foto anterior si estaba dentro de uploads/fotos
        if ($actual && strpos($actual, self::DIR_REL) === 0) {
            $antAbs = rtrim(BASE_PATH, '/\\') . '/' . $actual;
            if (is_file($antAbs)) {
                @unlink($antAbs);
            }
        }

        return self::DIR_REL . $nombre;
    }
}
