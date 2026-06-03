<?php
/**
 * Actualización masiva — imágenes por lotes (sesión) y resolución por código.
 */

const IMPROGYP_BULK_LOTE_MAX = 12;

function improgyp_bulk_imagen_map_get(): array
{
    if (!isset($_SESSION['bulk_imagen_map']) || !is_array($_SESSION['bulk_imagen_map'])) {
        $_SESSION['bulk_imagen_map'] = [];
    }
    return $_SESSION['bulk_imagen_map'];
}

/** Número de archivos subidos en esta sesión (no claves del mapa). */
function improgyp_bulk_archivos_staging_count(): int
{
    return (int) ($_SESSION['bulk_imagen_files_count'] ?? 0);
}

function improgyp_bulk_imagen_map_clear(): void
{
    $_SESSION['bulk_imagen_map'] = [];
    $_SESSION['bulk_imagen_files_count'] = 0;
}

/**
 * Código SKU inferido del nombre de archivo (sin extensión).
 * Normaliza copias de macOS: "20LAS04 2.webp" → "20las04".
 */
function improgyp_bulk_codigo_desde_nombre(string $nombreArchivo): string
{
    $nombreArchivo = strtolower(trim($nombreArchivo));
    $stem = pathinfo($nombreArchivo, PATHINFO_FILENAME);
    if ($stem === '') {
        return '';
    }
    $stem = trim($stem);
    $stem = preg_replace('/\s+\(\d+\)$/', '', $stem);
    $stem = preg_replace('/\s+-\s+copy(\s*\(\d+\))?$/i', '', $stem);
    $stem = preg_replace('/\s+copy(\s*\(\d+\))?$/i', '', $stem);
    $stem = preg_replace('/\s+\d+$/', '', $stem);
    return trim($stem);
}

/**
 * @return array{normalized: bool, codigo: string}
 */
function improgyp_bulk_analizar_nombre_archivo(string $nombreOrig): array
{
    $codigo = improgyp_bulk_codigo_desde_nombre($nombreOrig);
    $stemRaw = strtolower(trim(pathinfo(strtolower(trim($nombreOrig)), PATHINFO_FILENAME)));
    $normalized = $codigo !== '' && $stemRaw !== '' && $codigo !== $stemRaw;

    return ['normalized' => $normalized, 'codigo' => $codigo];
}

function improgyp_bulk_imagen_map_register(string $nombreOrig, string $rutaRelativa): void
{
    $map = improgyp_bulk_imagen_map_get();
    $nombreOrig = strtolower(trim($nombreOrig));
    $base = strtolower(pathinfo($nombreOrig, PATHINFO_FILENAME));
    $codigo = improgyp_bulk_codigo_desde_nombre($nombreOrig);

    if ($nombreOrig !== '') {
        $map[$nombreOrig] = $rutaRelativa;
    }
    if ($base !== '') {
        $map[$base] = $rutaRelativa;
    }
    if ($codigo !== '' && $codigo !== $base) {
        $map[$codigo] = $rutaRelativa;
    }
    $_SESSION['bulk_imagen_map'] = $map;
}

function improgyp_bulk_imagen_files_increment(): void
{
    $_SESSION['bulk_imagen_files_count'] = improgyp_bulk_archivos_staging_count() + 1;
}

/**
 * @return array<string, mixed>
 */
function improgyp_bulk_staging_json_payload(int $okLote = 0, int $skipLote = 0, array $warnings = []): array
{
    return [
        'archivos_staging' => improgyp_bulk_archivos_staging_count(),
        'total_staging' => improgyp_bulk_archivos_staging_count(),
        'ok' => $okLote,
        'skip' => $skipLote,
        'warnings' => array_values(array_unique($warnings)),
        'lote_max' => IMPROGYP_BULK_LOTE_MAX,
    ];
}

/**
 * Convierte un upload a WebP en img_catalogo/ y devuelve ruta relativa o null.
 */
function improgyp_bulk_guardar_imagen_upload(string $tmpName, string $nombreOrig): ?string
{
    if (!is_uploaded_file($tmpName)) {
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    $validMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mime, $validMimes, true)) {
        return null;
    }

    $dir = dirname(__DIR__) . '/img_catalogo';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $nombreOrig = strtolower($nombreOrig);
    $ext = strtolower(pathinfo($nombreOrig, PATHINFO_EXTENSION));
    $codigo = improgyp_bulk_codigo_desde_nombre($nombreOrig);
    $suffix = $codigo !== '' ? preg_replace('/[^a-zA-Z0-9_-]/', '', $codigo) : '';
    if ($suffix === '') {
        $suffix = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($nombreOrig, PATHINFO_FILENAME));
    }
    if ($suffix === '') {
        $suffix = bin2hex(random_bytes(4));
    }
    $nombreWebp = 'bulk_' . $suffix . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.webp';
    $rutaDest = $dir . '/' . $nombreWebp;
    $rutaRel = 'img_catalogo/' . $nombreWebp;

    if ($ext === 'webp') {
        if (move_uploaded_file($tmpName, $rutaDest)) {
            return $rutaRel;
        }
        return null;
    }

    $imgGd = @imagecreatefromstring(file_get_contents($tmpName));
    if ($imgGd === false) {
        return null;
    }
    imagepalettetotruecolor($imgGd);
    imagealphablending($imgGd, false);
    imagesavealpha($imgGd, true);
    $ok = imagewebp($imgGd, $rutaDest, 75);
    imagedestroy($imgGd);

    return $ok ? $rutaRel : null;
}

/**
 * @return array{ok: int, skip: int, map: array<string, string>, warnings: string[]}
 */
function improgyp_bulk_procesar_files_array(array $files): array
{
    $ok = 0;
    $skip = 0;
    $warnings = [];

    $tmp = $files['tmp_name'] ?? null;
    if ($tmp === null || $tmp === '' || (is_array($tmp) && count($tmp) === 0)) {
        return [
            'ok' => 0,
            'skip' => 0,
            'map' => improgyp_bulk_imagen_map_get(),
            'warnings' => [],
        ];
    }

    $procesarUno = static function (string $tmpPath, string $fileName) use (&$ok, &$skip, &$warnings): void {
        if ($fileName === '') {
            $skip++;
            return;
        }
        $analisis = improgyp_bulk_analizar_nombre_archivo($fileName);
        if ($analisis['normalized']) {
            $stemRaw = strtolower(pathinfo(strtolower(trim($fileName)), PATHINFO_FILENAME));
            $warnings[] = basename($fileName) . ' → se enlazará como ' . strtoupper($analisis['codigo'])
                . ' (renómbralo a ' . strtoupper($analisis['codigo']) . '.webp para evitar confusiones).';
        }
        $ruta = improgyp_bulk_guardar_imagen_upload($tmpPath, $fileName);
        if ($ruta) {
            improgyp_bulk_imagen_map_register($fileName, $ruta);
            improgyp_bulk_imagen_files_increment();
            $ok++;
        } else {
            $skip++;
        }
    };

    $name = $files['name'] ?? '';

    if (is_array($tmp)) {
        $count = count($tmp);
        for ($i = 0; $i < $count; $i++) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $skip++;
                continue;
            }
            $procesarUno($files['tmp_name'][$i], $files['name'][$i] ?? '');
        }
    } else {
        if (($files['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => 0, 'skip' => 1, 'map' => improgyp_bulk_imagen_map_get(), 'warnings' => []];
        }
        $procesarUno((string) $tmp, is_array($name) ? '' : (string) $name);
    }

    return [
        'ok' => $ok,
        'skip' => $skip,
        'map' => improgyp_bulk_imagen_map_get(),
        'warnings' => array_values(array_unique($warnings)),
    ];
}

/**
 * Igual que el import CSV histórico: mapeo por nombre de archivo + fallback por código de producto.
 */
function improgyp_bulk_resolver_imagen_ruta(string $imgCsv, string $codigo, array $mapeo): string
{
    $imgCsv = trim($imgCsv);
    if ($imgCsv !== '') {
        $imgKey = strtolower($imgCsv);
        if (isset($mapeo[$imgKey])) {
            return $mapeo[$imgKey];
        }
        $stem = strtolower(pathinfo($imgKey, PATHINFO_FILENAME));
        if ($stem !== '' && isset($mapeo[$stem])) {
            return $mapeo[$stem];
        }
        $stemNorm = improgyp_bulk_codigo_desde_nombre($imgCsv);
        if ($stemNorm !== '' && isset($mapeo[$stemNorm])) {
            return $mapeo[$stemNorm];
        }
        if (strpos($imgCsv, 'http') === 0 || strpos($imgCsv, 'img_catalogo/') === 0) {
            return $imgCsv;
        }
    }

    $codigoKey = strtolower(trim($codigo));
    if ($codigoKey !== '' && isset($mapeo[$codigoKey])) {
        return $mapeo[$codigoKey];
    }

    return $imgCsv;
}

/**
 * Construye mapeo unificado: sesión (Ajax) + fotos_lote del mismo POST (compatibilidad).
 */
function improgyp_bulk_mapeo_desde_post(): array
{
    $mapeo = improgyp_bulk_imagen_map_get();

    if (!isset($_FILES['fotos_lote'])) {
        return $mapeo;
    }

    $total = count($_FILES['fotos_lote']['tmp_name'] ?? []);
    for ($i = 0; $i < $total; $i++) {
        if (($_FILES['fotos_lote']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            continue;
        }
        $nombre = $_FILES['fotos_lote']['name'][$i] ?? '';
        $ruta = improgyp_bulk_guardar_imagen_upload(
            $_FILES['fotos_lote']['tmp_name'][$i],
            $nombre
        );
        if ($ruta) {
            improgyp_bulk_imagen_map_register($nombre, $ruta);
            improgyp_bulk_imagen_files_increment();
        }
    }

    return improgyp_bulk_imagen_map_get();
}

/**
 * Cuenta filas del CSV con ID > 0 (para confirmación en UI).
 *
 * @return array{tiene_id: bool, filas_con_id: int, filas_datos: int}
 */
function improgyp_bulk_csv_resumen_ids(string $csvPath): array
{
    $res = ['tiene_id' => false, 'filas_con_id' => 0, 'filas_datos' => 0];
    $handle = fopen($csvPath, 'r');
    if ($handle === false) {
        return $res;
    }
    $first = fgets($handle);
    if ($first === false) {
        fclose($handle);
        return $res;
    }
    $delim = (strpos($first, ';') !== false) ? ';' : ',';
    rewind($handle);
    $headers = fgetcsv($handle, 1000, $delim);
    if (!$headers) {
        fclose($handle);
        return $res;
    }
    $idx = array_flip(array_map('strtolower', $headers));
    if (!isset($idx['id'])) {
        fclose($handle);
        return $res;
    }
    $res['tiene_id'] = true;
    while (($data = fgetcsv($handle, 1000, $delim)) !== false) {
        $nombre = isset($idx['nombre']) ? trim($data[$idx['nombre']] ?? '') : '';
        if ($nombre === '') {
            continue;
        }
        $res['filas_datos']++;
        if ((int) ($data[$idx['id']] ?? 0) > 0) {
            $res['filas_con_id']++;
        }
    }
    fclose($handle);
    return $res;
}
