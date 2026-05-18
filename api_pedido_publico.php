<?php
ini_set('display_errors', 0); // Seguridad Fase 1
// api_pedido_publico.php - Registro de pedidos de la tienda pública
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

function cargarEnv($ruta) {
    if (!file_exists($ruta)) return false;
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lineas as $linea) {
        if (strpos(trim($linea), '#') === 0 || strpos($linea, '=') === false) continue;
        list($nombre, $valor) = explode('=', $linea, 2);
        $env[trim($nombre)] = trim($valor);
    }
    return $env;
}

$env = cargarEnv(__DIR__ . '/.env');
$db_host = $env['DB_HOST'] ?? 'localhost';
$db_port = $env['DB_PORT'] ?? '3306';
$db_name = $env['DB_NAME'] ?? '';
$db_user = $env['DB_USER'] ?? 'root';
$db_pass = $env['DB_PASS'] ?? 'root';

if (strpos($db_host, ':') !== false) { 
    list($h, $p) = explode(':', $db_host); 
    $dsn = "mysql:host=$h;port=$p;dbname=$db_name;charset=utf8mb4"; 
} else { 
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4"; 
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos de orden incompletos.']);
    exit;
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. CAPTURAR IP REAL
    $ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }

    // 2. BLOQUEO POR IP (Rate Limit: máx 5 pedidos por minuto)
    $stmtLimit = $pdo->prepare("SELECT COUNT(*) FROM pedidos_publicos WHERE ip = ? AND fecha > (NOW() - INTERVAL 1 MINUTE)");
    $stmtLimit->execute([$ip]);
    if ($stmtLimit->fetchColumn() >= 5) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Demasiados intentos. Por favor espere un momento.']);
        exit;
    }

    // 3. INSERTAR PEDIDO
    $stmt = $pdo->prepare("INSERT INTO pedidos_publicos (total, items_json, source, status, ip) VALUES (?, ?, ?, 'contacto_iniciado', ?)");
    $stmt->execute([
        floatval($input['total'] ?? 0),
        json_encode($input['items'], JSON_UNESCAPED_UNICODE),
        $input['source'] ?? 'directo',
        $ip
    ]);

    echo json_encode(['success' => true, 'order_id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al procesar el pedido. Por favor intente más tarde.']);
}
