<?php
/**
 * Restaura catalogo.json desde git HEAD y sincroniza improgyp_catalogo (MAMP).
 * Uso: php scripts/restaurar_catalogo_git.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

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

function presentacionesRaw(array $presentaciones): string
{
    if ($presentaciones === []) {
        return "Presentación Única: \n";
    }
    $lines = [];
    foreach ($presentaciones as $pr) {
        $opt = trim((string) ($pr['opcion'] ?? 'Presentación Única'));
        $precio = trim((string) ($pr['precio'] ?? ''));
        $lines[] = $opt . ': ' . $precio;
    }
    return implode("\n", $lines);
}

$jsonGit = shell_exec('git show HEAD:catalogo.json 2>/dev/null');
if ($jsonGit === null || $jsonGit === '') {
    fwrite(STDERR, "Error: no se pudo leer HEAD:catalogo.json.\n");
    exit(1);
}

$catalogo = json_decode($jsonGit, true);
if (!is_array($catalogo)) {
    fwrite(STDERR, "Error: JSON inválido en git HEAD.\n");
    exit(1);
}

file_put_contents($root . '/catalogo.json', json_encode(
    $catalogo,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
));
echo "catalogo.json restaurado desde git HEAD (" . count($catalogo) . " productos)\n";

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

$codigosGit = [];
foreach ($catalogo as $row) {
    $c = trim((string) ($row['codigo'] ?? ''));
    if ($c !== '') {
        $codigosGit[$c] = $row;
    }
}

$pdo->beginTransaction();
$actualizados = 0;
$insertados = 0;

$sel = $pdo->prepare('SELECT id FROM improgyp_catalogo WHERE codigo = ? LIMIT 1');
$upd = $pdo->prepare(
    'UPDATE improgyp_catalogo SET nombre=?, marca=?, categoria=?, presentaciones_raw=?, desc_larga=?, imagen_url=?, publicado=1 WHERE id=?'
);
$ins = $pdo->prepare(
    'INSERT INTO improgyp_catalogo (nombre, codigo, marca, categoria, presentaciones_raw, desc_larga, imagen_url, publicado)
     VALUES (?, ?, ?, ?, ?, ?, ?, 1)'
);

foreach ($codigosGit as $codigo => $row) {
    $nombre = trim((string) ($row['nombre'] ?? ''));
    $marca = trim((string) ($row['marca'] ?? ''));
    $categoria = trim((string) ($row['categoria'] ?? ''));
    $pres = presentacionesRaw($row['presentaciones'] ?? []);
    $desc = trim((string) ($row['desc_larga'] ?? ''));
    $img = trim((string) ($row['imagen'] ?? ''));

    $sel->execute([$codigo]);
    $id = (int) $sel->fetchColumn();

    if ($id > 0) {
        $upd->execute([$nombre, $marca, $categoria, $pres, $desc, $img, $id]);
        $actualizados++;
    } else {
        $ins->execute([$nombre, $codigo, $marca, $categoria, $pres, $desc, $img]);
        $insertados++;
    }
}

$impRows = $pdo->query("SELECT codigo FROM improgyp_catalogo WHERE marca = 'IMPLED'")->fetchAll(PDO::FETCH_COLUMN);
$eliminados = 0;
$del = $pdo->prepare('DELETE FROM improgyp_catalogo WHERE codigo = ?');
foreach ($impRows as $codigoImp) {
    if (!isset($codigosGit[$codigoImp])) {
        $del->execute([$codigoImp]);
        $eliminados++;
    }
}

$idsBajos = $pdo->query(
    "SELECT id, codigo FROM improgyp_catalogo WHERE id <= 120 AND marca = 'IMPLED'"
)->fetchAll(PDO::FETCH_ASSOC);
$idsLimpiados = 0;
foreach ($idsBajos as $fila) {
    if (!isset($codigosGit[$fila['codigo']])) {
        $pdo->prepare('DELETE FROM improgyp_catalogo WHERE id = ?')->execute([(int) $fila['id']]);
        $idsLimpiados++;
    }
}

$pdo->commit();

echo "BD: {$actualizados} actualizados por codigo, {$insertados} insertados\n";
echo "Eliminados IMPLED huerfanos: {$eliminados}, filas id<=120 IMPLED extra: {$idsLimpiados}\n";
echo "Listo.\n";
