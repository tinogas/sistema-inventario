<?php
/**
 * helpers.php — Funciones de apoyo para las vistas.
 */

if (!function_exists('avatar_iniciales')) {
    /**
     * Genera un avatar de iniciales como Data URI SVG (local, sin internet).
     * Determinístico por nombre; cada persona ve SUS propias iniciales.
     */
    function avatar_iniciales(string $nombre, int $size = 64, string $bg = '1a2332', string $fg = 'ffffff'): string
    {
        $nombre = trim($nombre);
        $partes = preg_split('/\s+/', $nombre) ?: [];
        $primera = $partes[0] ?? '';
        $ultima  = count($partes) > 1 ? $partes[count($partes) - 1] : '';
        $ini = mb_strtoupper(mb_substr($primera, 0, 1) . mb_substr($ultima, 0, 1));
        if ($ini === '') {
            $ini = '?';
        }
        $fuente = round($size / 2.3, 1);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">'
             . '<rect width="' . $size . '" height="' . $size . '" rx="' . $size . '" fill="#' . $bg . '"/>'
             . '<text x="50%" y="50%" dy=".35em" text-anchor="middle" '
             . 'font-family="Segoe UI, Arial, sans-serif" font-size="' . $fuente . '" fill="#' . $fg . '">'
             . htmlspecialchars($ini, ENT_QUOTES) . '</text></svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Devuelve la URL de la foto si existe, o un avatar de iniciales si no.
     */
    function foto_o_avatar(?string $foto, string $nombre, string $appUrl, int $size = 64, string $bg = '1a2332'): string
    {
        if (!empty($foto)) {
            return $appUrl . '/' . htmlspecialchars($foto);
        }
        return avatar_iniciales($nombre, $size, $bg);
    }

    /**
     * Placeholder rectangular local (SVG) para imágenes sin foto (p.ej. sucursales).
     */
    function placeholder_rect(string $texto = '', int $w = 300, int $h = 180): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '">'
             . '<rect width="' . $w . '" height="' . $h . '" fill="#e9ecef"/>'
             . '<text x="50%" y="50%" dy=".35em" text-anchor="middle" '
             . 'font-family="Segoe UI, Arial, sans-serif" font-size="' . max(11, (int)($h / 9)) . '" fill="#8a939b">'
             . htmlspecialchars($texto, ENT_QUOTES) . '</text></svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * URL de la foto de la sucursal o un placeholder local.
     */
    function foto_sucursal(?string $foto, string $appUrl, int $w = 300, int $h = 180): string
    {
        if (!empty($foto)) {
            return $appUrl . '/' . htmlspecialchars($foto);
        }
        return placeholder_rect('Sucursal', $w, $h);
    }
}
