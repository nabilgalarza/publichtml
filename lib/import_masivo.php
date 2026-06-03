<?php
/**
 * Importación masiva — staging de fotos en BD + import CSV al catálogo en vivo.
 */

const IMPROGYP_IMPORT_LOTE_MAX = 12;
const IMPROGYP_IMPORT_ZIP_MAX_SEGUNDOS = 300;

/**
 * Mensaje legible para errores de subida PHP ($_FILES['error']).
 */
function improgyp_import_upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El ZIP supera el tamaño máximo permitido por el servidor (revisa upload_max_filesize y post_max_size en MAMP).',
        UPLOAD_ERR_PARTIAL => 'La subida del ZIP se interrumpió. Intenta de nuevo.',
        UPLOAD_ERR_NO_FILE => 'No se recibió ningún archivo ZIP.',
        UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'Error del servidor al guardar el ZIP. Revisa permisos o configuración PHP.',
        default => 'Error desconocido al subir el ZIP (código ' . $code . ').',
    };
}

function improgyp_import_batch_id(): string
{
    if (empty($_SESSION['import_batch_id']) || !is_string($_SESSION['import_batch_id'])) {
        $_SESSION['import_batch_id'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['import_batch_id'];
}

function improgyp_import_nuevo_batch(): string
{
    $_SESSION['import_batch_id'] = bin2hex(random_bytes(16));
    return $_SESSION['import_batch_id'];
}

function improgyp_import_tabla_catalogo(): string
{
    return 'improgyp_catalogo';
}

function improgyp_import_ensure_tables(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS improgyp_import_staging_fotos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        batch_id VARCHAR(64) NOT NULL,
        codigo VARCHAR(100) NOT NULL,
        imagen_url VARCHAR(255) NOT NULL,
        nombre_archivo VARCHAR(255) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_batch_codigo (batch_id, codigo),
        INDEX idx_batch (batch_id)
    )");

    try {
        $pdo->exec('DROP TABLE IF EXISTS improgyp_catalogo_demo');
    } catch (Exception $e) {
        /* ignorar */
    }
}

function improgyp_import_codigo_desde_nombre(string $nombreArchivo): string
{
    require_once __DIR__ . '/bulk_catalogo_helpers.php';
    return improgyp_bulk_codigo_desde_nombre($nombreArchivo);
}

function improgyp_import_tmp_dir(): string
{
    $dir = dirname(__DIR__) . '/storage/import_tmp';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

function improgyp_import_limpiar_directorio(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = scandir($dir);
    if ($items === false) {
        return;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            improgyp_import_limpiar_directorio($path);
            @rmdir($path);
        } else {
            @unlink($path);
        }
    }
}

function improgyp_import_guardar_csv_sesion(string $srcPath): bool
{
    if (!is_file($srcPath)) {
        return false;
    }
    $batch = improgyp_import_batch_id();
    $dest = improgyp_import_tmp_dir() . '/csv_' . preg_replace('/[^a-f0-9]/', '', $batch) . '.csv';
    if (!@copy($srcPath, $dest)) {
        return false;
    }
    $_SESSION['import_csv_path'] = $dest;
    return true;
}

function improgyp_import_csv_sesion_path(): ?string
{
    $p = $_SESSION['import_csv_path'] ?? null;
    if (is_string($p) && is_file($p)) {
        return $p;
    }
    return null;
}

function improgyp_import_limpiar_csv_sesion(): void
{
    $p = improgyp_import_csv_sesion_path();
    if ($p) {
        @unlink($p);
    }
    unset($_SESSION['import_csv_path']);
}

function improgyp_import_guardar_foto_desde_ruta(
    PDO $pdo,
    string $batchId,
    string $rutaAbs,
    string $nombreOrig
): ?array {
    require_once __DIR__ . '/bulk_catalogo_helpers.php';

    $codigo = improgyp_import_codigo_desde_nombre($nombreOrig);
    if ($codigo === '') {
        return null;
    }

    $ruta = improgyp_bulk_guardar_imagen_desde_ruta($rutaAbs, $nombreOrig);
    if ($ruta === null) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT imagen_url FROM improgyp_import_staging_fotos WHERE batch_id = ? AND codigo = ?'
    );
    $stmt->execute([$batchId, $codigo]);
    $old = $stmt->fetchColumn();
    if ($old && $old !== $ruta) {
        improgyp_import_borrar_imagen((string) $old);
    }

    $pdo->prepare(
        'INSERT INTO improgyp_import_staging_fotos (batch_id, codigo, imagen_url, nombre_archivo)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE imagen_url = VALUES(imagen_url), nombre_archivo = VALUES(nombre_archivo)'
    )->execute([$batchId, $codigo, $ruta, strtolower(trim($nombreOrig))]);

    return [
        'codigo' => $codigo,
        'imagen_url' => $ruta,
        'warning' => improgyp_bulk_analizar_nombre_archivo($nombreOrig)['normalized']
            ? basename($nombreOrig) . ' → enlazado como ' . strtoupper($codigo)
            : null,
    ];
}

/**
 * @return array<string, string> codigo => imagen_url
 */
function improgyp_import_staging_map(PDO $pdo, string $batchId): array
{
    $stmt = $pdo->prepare(
        'SELECT codigo, imagen_url FROM improgyp_import_staging_fotos WHERE batch_id = ?'
    );
    $stmt->execute([$batchId]);
    $map = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $c = strtolower(trim((string) $row['codigo']));
        if ($c !== '') {
            $map[$c] = (string) $row['imagen_url'];
        }
    }
    return $map;
}

function improgyp_import_staging_count(PDO $pdo, string $batchId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM improgyp_import_staging_fotos WHERE batch_id = ?');
    $stmt->execute([$batchId]);
    return (int) $stmt->fetchColumn();
}

function improgyp_import_staging_clear(PDO $pdo, string $batchId): void
{
    $stmt = $pdo->prepare('SELECT imagen_url FROM improgyp_import_staging_fotos WHERE batch_id = ?');
    $stmt->execute([$batchId]);
    while ($ruta = $stmt->fetchColumn()) {
        improgyp_import_borrar_imagen((string) $ruta);
    }
    $pdo->prepare('DELETE FROM improgyp_import_staging_fotos WHERE batch_id = ?')->execute([$batchId]);
}

function improgyp_import_borrar_imagen(string $ruta): void
{
    if ($ruta === '' || strpos($ruta, 'http') === 0) {
        return;
    }
    $abs = dirname(__DIR__) . '/' . ltrim($ruta, '/');
    if (is_file($abs)) {
        @unlink($abs);
    }
}

function improgyp_import_guardar_foto_staging(
    PDO $pdo,
    string $batchId,
    string $tmpName,
    string $nombreOrig
): ?array {
    require_once __DIR__ . '/bulk_catalogo_helpers.php';

    $codigo = improgyp_import_codigo_desde_nombre($nombreOrig);
    if ($codigo === '') {
        return null;
    }

    $ruta = improgyp_bulk_guardar_imagen_upload($tmpName, $nombreOrig);
    if ($ruta === null) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT imagen_url FROM improgyp_import_staging_fotos WHERE batch_id = ? AND codigo = ?'
    );
    $stmt->execute([$batchId, $codigo]);
    $old = $stmt->fetchColumn();
    if ($old && $old !== $ruta) {
        improgyp_import_borrar_imagen((string) $old);
    }

    $pdo->prepare(
        'INSERT INTO improgyp_import_staging_fotos (batch_id, codigo, imagen_url, nombre_archivo)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE imagen_url = VALUES(imagen_url), nombre_archivo = VALUES(nombre_archivo)'
    )->execute([$batchId, $codigo, $ruta, strtolower(trim($nombreOrig))]);

    return [
        'codigo' => $codigo,
        'imagen_url' => $ruta,
        'warning' => improgyp_bulk_analizar_nombre_archivo($nombreOrig)['normalized']
            ? basename($nombreOrig) . ' → enlazado como ' . strtoupper($codigo)
            : null,
    ];
}

/**
 * @return array{headers: array, idx: array, delim: string}|null
 */
function improgyp_import_csv_abrir(string $path): ?array
{
    $handle = fopen($path, 'r');
    if ($handle === false) {
        return null;
    }
    $first = fgets($handle);
    if ($first === false) {
        fclose($handle);
        return null;
    }
    $delim = (strpos($first, ';') !== false) ? ';' : ',';
    rewind($handle);
    $headers = fgetcsv($handle, 0, $delim);
    fclose($handle);
    if (!$headers) {
        return null;
    }
    return [
        'headers' => $headers,
        'idx' => array_flip(array_map('strtolower', $headers)),
        'delim' => $delim,
    ];
}

function improgyp_import_csv_preview(PDO $pdo, string $csvPath, string $tabla, string $batchId): array
{
    $meta = improgyp_import_csv_abrir($csvPath);
    if ($meta === null) {
        return ['error' => 'No se pudo leer el CSV.'];
    }
    $idx = $meta['idx'];
    $delim = $meta['delim'];
    $handle = fopen($csvPath, 'r');
    fgetcsv($handle, 0, $delim);

    $nuevos = 0;
    $actualizados = 0;
    $conId = 0;
    $filas = 0;
    $codigos = [];
    $duplicados = [];
    $vistos = [];
    $sinPrecio = 0;
    $sinCodigo = 0;
    $tieneColId = isset($idx['id']);

    while (($data = fgetcsv($handle, 0, $delim)) !== false) {
        $nombre = isset($idx['nombre']) ? trim($data[$idx['nombre']] ?? '') : '';
        if ($nombre === '') {
            continue;
        }
        $filas++;
        $codigo = isset($idx['codigo']) ? trim($data[$idx['codigo']] ?? '') : (isset($idx['sku']) ? trim($data[$idx['sku']] ?? '') : '');
        if ($codigo === '') {
            $sinCodigo++;
        }
        $rawP = isset($idx['unidad_precios']) ? trim($data[$idx['unidad_precios']] ?? '') : '';
        if ($rawP === '' || !preg_match('/\d/', $rawP)) {
            $sinPrecio++;
        }
        $id = isset($idx['id']) ? (int) ($data[$idx['id']] ?? 0) : 0;
        if ($id > 0) {
            $conId++;
        }
        if ($codigo !== '') {
            $ck = strtolower($codigo);
            if (isset($vistos[$ck])) {
                $duplicados[] = $codigo;
            }
            $vistos[$ck] = true;
            $codigos[] = $ck;
        }
        if ($id > 0) {
            $sel = $pdo->prepare("SELECT id FROM {$tabla} WHERE id = ? LIMIT 1");
            $sel->execute([$id]);
            if ($sel->fetchColumn()) {
                $actualizados++;
            } else {
                $nuevos++;
            }
        } elseif ($codigo !== '') {
            $sel = $pdo->prepare("SELECT id FROM {$tabla} WHERE codigo = ? LIMIT 1");
            $sel->execute([$codigo]);
            if ($sel->fetchColumn()) {
                $actualizados++;
            } else {
                $nuevos++;
            }
        } else {
            $nuevos++;
        }
    }
    fclose($handle);

    $fotosMap = improgyp_import_staging_map($pdo, $batchId);
    $conFoto = 0;
    $sinFoto = [];
    foreach ($codigos as $c) {
        if (isset($fotosMap[$c])) {
            $conFoto++;
        } else {
            $sinFoto[] = $c;
        }
    }

    return [
        'status' => 'success',
        'filas' => $filas,
        'nuevos' => $nuevos,
        'actualizados' => $actualizados,
        'con_id' => $conId,
        'duplicados_csv' => array_values(array_unique($duplicados)),
        'fotos_staging' => count($fotosMap),
        'fotos_coinciden_csv' => $conFoto,
        'fotos_faltan_csv' => count($sinFoto),
        'codigos_sin_foto' => array_slice(array_values(array_unique($sinFoto)), 0, 12),
        'sin_precio' => $sinPrecio,
        'sin_codigo' => $sinCodigo,
        'tiene_columna_id' => $tieneColId,
        'riesgo_id' => $conId >= 5,
        'modelo' => 'codigo_por_medida',
    ];
}

/**
 * ZIP: un CSV + imágenes (raíz o carpeta fotos/). Devuelve preview + guarda CSV en sesión.
 *
 * @return array<string, mixed>
 */
function improgyp_import_procesar_zip(PDO $pdo, string $batchId, string $zipPath): array
{
    @set_time_limit(IMPROGYP_IMPORT_ZIP_MAX_SEGUNDOS);

    if (!class_exists('ZipArchive')) {
        return ['error' => 'PHP ZipArchive no está disponible en este servidor.'];
    }
    if (!is_file($zipPath) || !is_readable($zipPath)) {
        return ['error' => 'No se pudo leer el archivo ZIP subido.'];
    }
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        return ['error' => 'No se pudo abrir el archivo ZIP.'];
    }

    $extractDir = improgyp_import_tmp_dir() . '/zip_' . preg_replace('/[^a-f0-9]/', '', $batchId);
    if (is_dir($extractDir)) {
        improgyp_import_limpiar_directorio($extractDir);
        @rmdir($extractDir);
    }
    mkdir($extractDir, 0755, true);
    if (!$zip->extractTo($extractDir)) {
        $zip->close();
        return ['error' => 'No se pudo extraer el ZIP.'];
    }
    $zip->close();

    $csvCandidates = [];
    $imagenesPendientes = [];
    $imgExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $fotosOk = 0;
    $fotosSkip = 0;
    $warnings = [];

    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($extractDir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iter as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $base = $file->getFilename();
        if (str_starts_with($base, '._')) {
            continue;
        }
        $ext = strtolower($file->getExtension());
        $full = $file->getPathname();
        if (strpos($full, '__MACOSX') !== false) {
            continue;
        }
        if ($ext === 'csv') {
            $csvCandidates[] = $full;
            continue;
        }
        if (in_array($ext, $imgExts, true)) {
            $imagenesPendientes[] = ['path' => $full, 'name' => $base];
        }
    }

    if ($csvCandidates === []) {
        improgyp_import_limpiar_directorio($extractDir);
        @rmdir($extractDir);
        return ['error' => 'El ZIP debe incluir un archivo .csv con los productos.'];
    }
    usort($csvCandidates, static function (string $a, string $b): int {
        $pa = strtolower(basename($a));
        $pb = strtolower(basename($b));
        $prio = ['productos.csv' => 0, 'catalogo.csv' => 1, 'catalogo_impled.csv' => 2];
        $sa = $prio[$pa] ?? 10;
        $sb = $prio[$pb] ?? 10;
        if ($sa !== $sb) {
            return $sa <=> $sb;
        }
        return strcmp($pa, $pb);
    });
    $csvPath = $csvCandidates[0];

    if (!improgyp_import_guardar_csv_sesion($csvPath)) {
        improgyp_import_limpiar_directorio($extractDir);
        @rmdir($extractDir);
        return ['error' => 'No se pudo guardar el CSV del paquete.'];
    }

    foreach ($imagenesPendientes as $img) {
        $res = improgyp_import_guardar_foto_desde_ruta($pdo, $batchId, $img['path'], $img['name']);
        if ($res) {
            $fotosOk++;
            if (!empty($res['warning'])) {
                $warnings[] = $res['warning'];
            }
        } else {
            $fotosSkip++;
        }
    }

    improgyp_import_limpiar_directorio($extractDir);
    @rmdir($extractDir);

    $tabla = improgyp_import_tabla_catalogo();
    $preview = improgyp_import_csv_preview($pdo, $csvPath, $tabla, $batchId);
    if (isset($preview['error'])) {
        return $preview;
    }
    $preview['zip_fotos_ok'] = $fotosOk;
    $preview['zip_fotos_skip'] = $fotosSkip;
    $preview['zip_warnings'] = array_values(array_unique($warnings));
    $preview['csv_en_sesion'] = true;

    return $preview;
}

function improgyp_import_resolver_imagen(string $imgCsv, string $codigo, array $fotosMap): string
{
    require_once __DIR__ . '/bulk_catalogo_helpers.php';
    $mapeo = $fotosMap;
    foreach ($fotosMap as $c => $ruta) {
        $mapeo[$c . '.webp'] = $ruta;
    }
    return improgyp_bulk_resolver_imagen_ruta($imgCsv, $codigo, $mapeo);
}

function improgyp_import_ejecutar_csv(
    PDO $pdo,
    string $csvPath,
    string $tabla,
    string $batchId,
    bool $permitirActualizarPorId
): array {
    $meta = improgyp_import_csv_abrir($csvPath);
    if ($meta === null) {
        return ['error' => 'CSV inválido.'];
    }
    $idx = $meta['idx'];
    $delim = $meta['delim'];
    $fotosMap = improgyp_import_staging_map($pdo, $batchId);

    $handle = fopen($csvPath, 'r');
    fgetcsv($handle, 0, $delim);

    $upd = 0;
    $new = 0;
    $err = 0;
    $imgOk = 0;
    $imgSin = 0;

    while (($data = fgetcsv($handle, 0, $delim)) !== false) {
        try {
            $nombre = isset($idx['nombre']) ? trim($data[$idx['nombre']] ?? '') : '';
            if ($nombre === '') {
                continue;
            }
            $codigo = isset($idx['codigo']) ? trim($data[$idx['codigo']] ?? '') : (isset($idx['sku']) ? trim($data[$idx['sku']] ?? '') : '');
            $marca = isset($idx['marca']) ? trim($data[$idx['marca']] ?? '') : '';
            $cat = isset($idx['categoria']) ? trim($data[$idx['categoria']] ?? '') : (isset($idx['categoría']) ? trim($data[$idx['categoría']] ?? '') : '');
            $id = isset($idx['id']) ? (int) ($data[$idx['id']] ?? 0) : 0;

            $pres = '';
            $rawP = isset($idx['unidad_precios']) ? trim($data[$idx['unidad_precios']] ?? '') : '';
            if ($rawP === '') {
                $rawP = isset($idx['presentaciones_raw']) ? trim($data[$idx['presentaciones_raw']] ?? '')
                    : (isset($idx['presentaciones_precios']) ? trim($data[$idx['presentaciones_precios']] ?? '') : '');
            }
            if ($rawP !== '') {
                if (stripos($rawP, 'Precio:') === 0) {
                    $rawP = 'Presentación Única: ' . trim(str_ireplace('Precio:', '', $rawP));
                }
                $lines = explode("\n", $rawP);
                foreach ($lines as &$line) {
                    if (strpos($line, ':') !== false) {
                        [$opt, $pr] = explode(':', $line, 2);
                        $line = trim($opt) . ': ' . preg_replace('/[^\d.]/', '', $pr);
                    }
                }
                $pres = implode("\n", $lines);
            }

            $descBase = isset($idx['descripcion_larga']) ? trim($data[$idx['descripcion_larga']] ?? '')
                : (isset($idx['desc_larga']) ? trim($data[$idx['desc_larga']] ?? '') : '');
            $datosTec = isset($idx['datos_tecnicos']) ? trim($data[$idx['datos_tecnicos']] ?? '') : '';
            $desc = $descBase . ($datosTec !== '' ? "\n\nDATOS TÉCNICOS:\n" . $datosTec : '');

            $imgCsv = isset($idx['archivo_imagen']) ? trim($data[$idx['archivo_imagen']] ?? '')
                : (isset($idx['imagen_url']) ? trim($data[$idx['imagen_url']] ?? '') : '');
            $imgFinal = improgyp_import_resolver_imagen($imgCsv, $codigo, $fotosMap);
            $tieneImg = $imgFinal !== ''
                && (strpos($imgFinal, 'http') === 0 || strpos($imgFinal, 'img_catalogo/') === 0);

            if ($tieneImg) {
                $imgOk++;
            } elseif ($codigo !== '') {
                $imgSin++;
            }

            if ($id > 0 && !$permitirActualizarPorId) {
                $id = 0;
            }

            if ($id <= 0 && $codigo !== '') {
                $sel = $pdo->prepare("SELECT id FROM {$tabla} WHERE codigo = ? LIMIT 1");
                $sel->execute([$codigo]);
                $id = (int) $sel->fetchColumn();
            }

            if ($id > 0) {
                if ($tieneImg) {
                    $stmtOld = $pdo->prepare("SELECT imagen_url FROM {$tabla} WHERE id = ?");
                    $stmtOld->execute([$id]);
                    $oldImg = $stmtOld->fetchColumn();
                    if ($oldImg && $oldImg !== $imgFinal && function_exists('borrarFotoFisica')) {
                        borrarFotoFisica($oldImg);
                    }
                    $pdo->prepare(
                        "UPDATE {$tabla} SET nombre=?, codigo=?, marca=?, categoria=?, presentaciones_raw=?, desc_larga=?, imagen_url=? WHERE id=?"
                    )->execute([$nombre, $codigo, $marca, $cat, $pres, $desc, $imgFinal, $id]);
                } else {
                    $pdo->prepare(
                        "UPDATE {$tabla} SET nombre=?, codigo=?, marca=?, categoria=?, presentaciones_raw=?, desc_larga=? WHERE id=?"
                    )->execute([$nombre, $codigo, $marca, $cat, $pres, $desc, $id]);
                }
                $upd++;
            } else {
                $pdo->prepare(
                    "INSERT INTO {$tabla} (nombre, codigo, marca, categoria, presentaciones_raw, desc_larga, imagen_url, publicado)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
                )->execute([$nombre, $codigo, $marca, $cat, $pres, $desc, $imgFinal]);
                $new++;
            }
            if ($cat !== '') {
                $pdo->prepare('INSERT IGNORE INTO categorias_admin (nombre) VALUES (?)')->execute([$cat]);
            }
            if ($marca !== '') {
                $pdo->prepare('INSERT IGNORE INTO marcas_admin (nombre) VALUES (?)')->execute([$marca]);
            }
        } catch (Exception $e) {
            $err++;
        }
    }
    fclose($handle);

    return [
        'status' => 'success',
        'upd' => $upd,
        'new' => $new,
        'err' => $err,
        'img_ok' => $imgOk,
        'img_sin' => $imgSin,
        'fotos_usadas' => count($fotosMap),
    ];
}

function improgyp_import_solo_fotos(PDO $pdo, string $tabla, string $batchId): array
{
    $map = improgyp_import_staging_map($pdo, $batchId);
    $ok = 0;
    $sin = 0;
    foreach ($map as $codigo => $ruta) {
        $sel = $pdo->prepare("SELECT id FROM {$tabla} WHERE LOWER(TRIM(codigo)) = ? LIMIT 1");
        $sel->execute([$codigo]);
        $id = (int) $sel->fetchColumn();
        if ($id > 0) {
            $pdo->prepare("UPDATE {$tabla} SET imagen_url = ? WHERE id = ?")->execute([$ruta, $id]);
            $ok++;
        } else {
            $sin++;
        }
    }
    return ['ok' => $ok, 'sin' => $sin, 'total' => count($map)];
}

function improgyp_import_vaciar_staging(PDO $pdo, string $batchId): void
{
    improgyp_import_staging_clear($pdo, $batchId);
    improgyp_import_nuevo_batch();
}
