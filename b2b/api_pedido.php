<?php
// b2b/api_pedido.php - Registro de Órdenes Formales B2B
session_start();
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

$env = cargarEnv(dirname(__DIR__) . '/.env');
$db_host = $env['DB_HOST'] ?? '127.0.0.1';
$db_port = $env['DB_PORT'] ?? '3306';
$db_name = $env['DB_NAME'] ?? '';
$db_user = $env['DB_USER'] ?? 'root';
$db_pass = $env['DB_PASS'] ?? '';

if (strpos($db_host, ':') !== false) { 
    list($h, $p) = explode(':', $db_host); 
    $dsn = "mysql:host=$h;port=$p;dbname=$db_name;charset=utf8mb4"; 
} else { 
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4"; 
}

if (!isset($_SESSION['b2b_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión no autorizada.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Datos de orden incompletos.']);
    exit;
}

$ruc_cliente = $_SESSION['b2b_user']['ruc'];
$nombre_cliente = $_SESSION['b2b_user']['nombre'];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO pedidos_b2b (ruc_cliente, nombre_cliente, total, items_json, status) VALUES (?, ?, ?, ?, 'pendiente')");
    $stmt->execute([
        $ruc_cliente,
        $nombre_cliente,
        floatval($input['total'] ?? 0),
        json_encode($input['items'], JSON_UNESCAPED_UNICODE)
    ]);

    echo json_encode(['success' => true, 'order_id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al registrar pedido: ' . $e->getMessage()]);
}
