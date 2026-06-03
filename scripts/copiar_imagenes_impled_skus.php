<?php
/**
 * Busca imágenes por SKU en Extreme SSD/PROVIND/IMPLED y copia al Escritorio/imagenes.
 * Uso: php scripts/copiar_imagenes_impled_skus.php [ruta_json]
 */
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/lib/bulk_catalogo_helpers.php';

$origen = '/Volumes/Extreme SSD/PROVIND/IMPLED';
$destino = getenv('HOME') . '/Desktop/imagenes';
$jsonPath = $argv[1] ?? $root . '/recursos/impled_skus_busqueda.json';

if (!is_dir($origen)) {
    fwrite(STDERR, "No se encuentra el disco: {$origen}\n");
    exit(1);
}

if (!is_file($jsonPath)) {
    fwrite(STDERR, "JSON no encontrado: {$jsonPath}\n");
    exit(1);
}

$data = json_decode(file_get_contents($jsonPath), true);
$productos = $data['productos'] ?? [];
if ($productos === []) {
    fwrite(STDERR, "Lista de productos vacía.\n");
    exit(1);
}

if (!is_dir($destino)) {
    mkdir($destino, 0755, true);
}

$exts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'tif', 'tiff'];
$index = []; // codigo normalizado => [ ['path'=>, 'name'=>], ... ]

$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($origen, FilesystemIterator::SKIP_DOTS)
);

foreach ($iter as $file) {
    if (!$file->isFile()) {
        continue;
    }
    $name = $file->getFilename();
    if (strpos($name, '.') === 0 || str_starts_with($name, '._') || stripos($name, '__MACOSX') !== false) {
        continue;
    }
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, $exts, true)) {
        continue;
    }
    $codigo = improgyp_bulk_codigo_desde_nombre($name);
    if ($codigo === '') {
        continue;
    }
    $index[$codigo][] = [
        'path' => $file->getPathname(),
        'name' => $name,
    ];
}

/** Alias conocidos SKU solicitado → código en nombre de archivo en disco */
$alias = [
    '20apcino1' => '20apcin01',
    '20san02' => '20sano2',
    '20esto1' => '20est01',
    '20lijo6' => '20lij06',
    '20pisto2' => '20pist02',
    '20perhio2' => '20perhi02',
    '20ceto3' => '20cet02',
    '20ceto2' => '20cet02',
];

function buscar_en_disco(string $raiz, string $sku): ?array
{
    $exts = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
    $needle = strtolower($sku);
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($raiz, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iter as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $name = $file->getFilename();
        if (str_starts_with($name, '._')) {
            continue;
        }
        if (stripos($name, $needle) === false) {
            continue;
        }
        if (!in_array(strtolower($file->getExtension()), $exts, true)) {
            continue;
        }
        return ['path' => $file->getPathname(), 'name' => $name];
    }
    return null;
}

function resolver_ruta(string $sku, array $index, array $alias): ?array
{
    $key = strtolower(trim($sku));
    $candidatos = [$key];
    if (isset($alias[$key])) {
        $candidatos[] = $alias[$key];
    }
    // O↔0 en posiciones medias (ej. APCINO1 → APCIN01)
    if (preg_match('/o(\d)/i', $key)) {
        $candidatos[] = preg_replace('/o(\d)/i', '0$1', $key);
    }
    if (preg_match('/0(\d)/', $key)) {
        $candidatos[] = preg_replace('/0(\d)/', 'o$1', $key, 1);
    }

    foreach (array_unique($candidatos) as $c) {
        if (!empty($index[$c])) {
            return $index[$c][0];
        }
    }

    $needle = strtolower($sku);
    foreach ($index as $items) {
        foreach ($items as $item) {
            if (stripos($item['name'], $needle) !== false) {
                return $item;
            }
        }
    }

    return null;
}

$ok = 0;
$faltan = [];
$reporte = [];

foreach ($productos as $p) {
    $sku = trim((string) ($p['sku'] ?? ''));
    if ($sku === '') {
        continue;
    }
    $hit = resolver_ruta($sku, $index, $alias);
    if ($hit === null) {
        $hit = buscar_en_disco($origen, $sku);
    }
    if ($hit === null) {
        $faltan[] = $sku;
        continue;
    }
    $ext = strtolower(pathinfo($hit['path'], PATHINFO_EXTENSION));
    $destFile = $destino . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sku) . '.' . $ext;
    if (!@copy($hit['path'], $destFile)) {
        $faltan[] = $sku . ' (error copia)';
        continue;
    }
    $ok++;
    $reporte[] = sprintf("%s ← %s", $sku, $hit['name']);
}

$reportePath = $destino . '/_reporte_copia.txt';
file_put_contents(
    $reportePath,
    "Origen: {$origen}\nDestino: {$destino}\nFecha: " . date('Y-m-d H:i:s') . "\n\n"
    . "Copiadas: {$ok}\nFaltantes: " . count($faltan) . "\n\n=== COPIADAS ===\n"
    . implode("\n", $reporte) . "\n\n=== FALTANTES ===\n"
    . implode("\n", $faltan) . "\n"
);

echo "Copiadas: {$ok}\n";
echo "Faltantes: " . count($faltan) . "\n";
echo "Reporte: {$reportePath}\n";
if ($faltan !== []) {
    echo "\nPrimeros faltantes:\n";
    foreach (array_slice($faltan, 0, 20) as $f) {
        echo "  - {$f}\n";
    }
}
