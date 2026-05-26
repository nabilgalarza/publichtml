<?php
/**
 * Regenera catalogo.json desde improgyp_catalogo (misma lógica que dashboard regenerarJSON).
 * php scripts/sync_catalogo_from_db.php
 */
$root = dirname(__DIR__);
$envFile = $root . '/.env';
if (!file_exists($envFile)) {
    fwrite(STDERR, "Falta .env\n");
    exit(1);
}
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    if (strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v);
}

try {
    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $env['DB_HOST'] ?? '127.0.0.1',
            $env['DB_PORT'] ?? '8889',
            $env['DB_NAME'] ?? ''
        ),
        $env['DB_USER'] ?? 'root',
        $env['DB_PASS'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR conexión: ' . $e->getMessage() . "\n");
    exit(1);
}

$stmt = $pdo->query(
    'SELECT nombre, codigo, marca, categoria, imagen_url AS imagen, presentaciones_raw, desc_larga
     FROM improgyp_catalogo WHERE publicado = 1 ORDER BY id DESC'
);
$productos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
$catalogoOficial = [];

foreach ($productos_db as $row) {
    $presentaciones = [];
    foreach (explode("\n", $row['presentaciones_raw'] ?? '') as $l) {
        if (!empty(trim($l))) {
            $p = explode(':', $l, 2);
            $presentaciones[] = ['opcion' => trim($p[0]), 'precio' => trim($p[1] ?? '')];
        }
    }
    if (empty($presentaciones)) {
        $presentaciones[] = ['opcion' => 'Presentación Única', 'precio' => ''];
    }
    $catalogoOficial[] = [
        'nombre' => $row['nombre'],
        'codigo' => $row['codigo'],
        'marca' => $row['marca'],
        'categoria' => $row['categoria'],
        'imagen' => $row['imagen'],
        'presentaciones' => $presentaciones,
        'desc_larga' => $row['desc_larga'],
    ];
}

$out = $root . '/catalogo.json';
file_put_contents($out, json_encode($catalogoOficial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo 'catalogo.json actualizado: ' . count($catalogoOficial) . " productos publicados\n";
