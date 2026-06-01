<?php
/**
 * Imágenes de sucursales (uploads/locales) — home showroom y modal.
 */

function improgyp_local_imagen_dir(): string
{
    $dir = dirname(__DIR__) . '/uploads/locales';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

function improgyp_local_imagen_borrar(?string $ruta): void
{
    if (!$ruta || strpos($ruta, 'http') === 0) {
        return;
    }
    $abs = dirname(__DIR__) . '/' . ltrim($ruta, '/');
    if (is_file($abs)) {
        @unlink($abs);
    }
}

/**
 * @param array|null $file $_FILES['imagen']
 * @return string Ruta relativa uploads/locales/... o vacío
 */
function improgyp_local_imagen_guardar(?array $file, string $localId, ?string $imagenActual, bool $quitar): string
{
    if ($quitar) {
        improgyp_local_imagen_borrar($imagenActual);
        return '';
    }

    if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return trim((string) $imagenActual);
    }

    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
        return trim((string) $imagenActual);
    }

    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
        $ext = 'jpg';
    }

    $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $localId) ?: 'local';
    $fname = $safeId . '_' . time() . '.' . $ext;
    $dest = improgyp_local_imagen_dir() . '/' . $fname;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return trim((string) $imagenActual);
    }

    if ($imagenActual && $imagenActual !== 'uploads/locales/' . $fname) {
        improgyp_local_imagen_borrar($imagenActual);
    }

    return 'uploads/locales/' . $fname;
}
