<?php
/**
 * Purgar solo productos IMPLED (CLI). No toca MAXXT ni otras marcas.
 * Uso: php scripts/purgar_impled.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

require_once $root . '/lib/purgar_impled.php';

function cargarEnv(string $ruta): array
{
    if (!file_exists($ruta)) {
        return [];
    }
    $env = [];
    foreach (file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        if (strpos(trim($linea), '#') === 0 || strpos($linea, '=') === false) {
            continue;
        }
        [$nombre, $valor] = explode('=', $linea, 2);
        $env[trim($nombre)] = trim($valor);
    }
    return $env;
}

function borrarFotoFisica($ruta): void
{
    if (!$ruta || strpos($ruta, 'http') === 0 || $ruta === 'favicon-app.png' || $ruta === 'favicon-app.png?v=5') {
        return;
    }
    $ruta_absoluta = dirname(__DIR__) . '/' . ltrim($ruta, '/');
    if (file_exists($ruta_absoluta) && is_file($ruta_absoluta)) {
        @unlink($ruta_absoluta);
    }
}

function regenerarJSON(PDO $pdo): void
{
    $stmt = $pdo->query(
        "SELECT nombre, codigo, marca, categoria, imagen_url as imagen, presentaciones_raw, desc_larga
         FROM improgyp_catalogo WHERE publicado = 1 ORDER BY id DESC"
    );
    $productos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $catalogoOficial = [];
    foreach ($productos_db as $row) {
        $presentaciones = [];
        $lineas = explode("\n", $row['presentaciones_raw'] ?? '');
        foreach ($lineas as $l) {
            if (strpos($l, ':') !== false) {
                [$opt, $pr] = explode(':', $l, 2);
                $presentaciones[] = ['opcion' => trim($opt), 'precio' => trim($pr)];
            }
        }
        if ($presentaciones === []) {
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
    file_put_contents(
        dirname(__DIR__) . '/catalogo.json',
        json_encode($catalogoOficial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
}

$env = cargarEnv($root . '/.env');
$dbHost = $env['DB_HOST'] ?? 'localhost';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbName = $env['DB_NAME'] ?? '';
$dbUser = $env['DB_USER'] ?? '';
$dbPass = $env['DB_PASS'] ?? '';

if ($dbName === '') {
    fwrite(STDERR, "Error: DB_NAME vacío en .env\n");
    exit(1);
}

if (strpos($dbHost, ':') !== false) {
    [$dbHost, $dbPort] = explode(':', $dbHost, 2);
}

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
$pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$res = improgyp_purgar_impled($pdo);
echo "IMPLED eliminados: {$res['eliminados']}\n";
echo "Referencias de imagen limpiadas: {$res['fotos_borradas']}\n";
echo "catalogo.json regenerado.\n";
