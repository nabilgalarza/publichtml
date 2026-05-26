<?php
/**
 * Prueba rápida de conexión PDO con .env
 * php scripts/test_db_connection.php
 */
$root = dirname(__DIR__);
$envFile = $root . '/.env';
if (!file_exists($envFile)) {
    fwrite(STDERR, "Falta .env — copia desde .env.example\n");
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

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '8889';
$name = $env['DB_NAME'] ?? '';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $n = (int) $pdo->query('SELECT COUNT(*) FROM improgyp_catalogo')->fetchColumn();
    $adm = (int) $pdo->query('SELECT COUNT(*) FROM usuarios_admin')->fetchColumn();
    echo "OK — Conectado a «{$name}» en {$host}:{$port}\n";
    echo "Productos en improgyp_catalogo: {$n}\n";
    echo "Usuarios admin: {$adm}\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
