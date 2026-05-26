<?php
require_once __DIR__ . '/security_headers.php';
header('Content-Type: application/json; charset=utf-8');

function cargarEnv($ruta) {
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if ($nombre === '' || $telefono === '' || $mensaje === '') {
    echo json_encode(['status' => 'error', 'message' => 'Completa nombre, teléfono y mensaje.']);
    exit;
}

$payload = json_encode([
    'nombre' => $nombre,
    'telefono' => $telefono,
    'email' => $email,
    'mensaje' => $mensaje,
], JSON_UNESCAPED_UNICODE);

$env = cargarEnv(__DIR__ . '/.env');
$db_host = $env['DB_HOST'] ?? 'localhost';
$db_name = $env['DB_NAME'] ?? '';
$db_user = $env['DB_USER'] ?? '';
$db_pass = $env['DB_PASS'] ?? '';
$db_port = $env['DB_PORT'] ?? '3306';

try {
    if (strpos($db_host, ':') !== false) {
        [$h, $p] = explode(':', $db_host);
        $dsn = "mysql:host=$h;port=$p;dbname=$db_name;charset=utf8mb4";
    } else {
        $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    }
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $stmt = $pdo->prepare('INSERT INTO metricas_b2c (evento, valor, categoria, ip) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        'Solicitud Asesoría Home',
        $payload,
        'Asesoría',
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
    echo json_encode(['status' => 'success', 'message' => 'Solicitud registrada correctamente.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo registrar la solicitud.']);
}
