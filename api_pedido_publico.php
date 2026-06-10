<?php
ini_set('display_errors', 0);
// api_pedido_publico.php - Registro de pedidos de la tienda pública
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

function cargarEnv($ruta) {
    if (!file_exists($ruta)) {
        return false;
    }
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lineas as $linea) {
        if (strpos(trim($linea), '#') === 0 || strpos($linea, '=') === false) {
            continue;
        }
        [$nombre, $valor] = explode('=', $linea, 2);
        $env[trim($nombre)] = trim($valor);
    }
    return $env;
}

/**
 * Asegura esquema pedidos_publicos (columna ip requerida por rate limit e INSERT).
 */
function improgyp_ensure_pedidos_publicos_schema(PDO $pdo): bool {
    $pdo->exec("CREATE TABLE IF NOT EXISTS pedidos_publicos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        total DECIMAL(10,2) NOT NULL,
        items_json LONGTEXT NOT NULL,
        status ENUM('contacto_iniciado', 'completado', 'cancelado') DEFAULT 'contacto_iniciado',
        source VARCHAR(255) DEFAULT 'directo',
        ip VARCHAR(45) DEFAULT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    try {
        if (!$pdo->query("SHOW COLUMNS FROM pedidos_publicos LIKE 'ip'")->fetch()) {
            $pdo->exec("ALTER TABLE pedidos_publicos ADD COLUMN ip VARCHAR(45) DEFAULT NULL AFTER source");
        }
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function improgyp_pedidos_publicos_has_ip(PDO $pdo): bool {
    try {
        return (bool) $pdo->query("SHOW COLUMNS FROM pedidos_publicos LIKE 'ip'")->fetch();
    } catch (Throwable $e) {
        return false;
    }
}

$env = cargarEnv(__DIR__ . '/.env');
$db_host = $env['DB_HOST'] ?? 'localhost';
$db_port = $env['DB_PORT'] ?? '3306';
$db_name = $env['DB_NAME'] ?? '';
$db_user = $env['DB_USER'] ?? 'root';
$db_pass = $env['DB_PASS'] ?? 'root';

if (strpos($db_host, ':') !== false) {
    [$h, $p] = explode(':', $db_host);
    $dsn = "mysql:host=$h;port=$p;dbname=$db_name;charset=utf8mb4";
} else {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
}

$raw = file_get_contents('php://input');
$input = json_decode($raw ?: '', true);

if (!$input || empty($input['items']) || !is_array($input['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos de orden incompletos.']);
    exit;
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    improgyp_ensure_pedidos_publicos_schema($pdo);
    $hasIp = improgyp_pedidos_publicos_has_ip($pdo);

    $ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }

    if ($hasIp) {
        $stmtLimit = $pdo->prepare(
            "SELECT COUNT(*) FROM pedidos_publicos WHERE ip = ? AND fecha > (NOW() - INTERVAL 1 MINUTE)"
        );
        $stmtLimit->execute([$ip]);
        if ((int) $stmtLimit->fetchColumn() >= 5) {
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => 'Demasiados intentos. Por favor espere un momento.']);
            exit;
        }
    }

    $total = (float) ($input['total'] ?? 0);
    $itemsJson = json_encode($input['items'], JSON_UNESCAPED_UNICODE);
    $source = substr((string) ($input['source'] ?? 'directo'), 0, 255);

    if ($hasIp) {
        $stmt = $pdo->prepare(
            "INSERT INTO pedidos_publicos (total, items_json, source, status, ip) VALUES (?, ?, ?, 'contacto_iniciado', ?)"
        );
        $stmt->execute([$total, $itemsJson, $source, $ip]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO pedidos_publicos (total, items_json, source, status) VALUES (?, ?, ?, 'contacto_iniciado')"
        );
        $stmt->execute([$total, $itemsJson, $source]);
    }

    echo json_encode(['success' => true, 'order_id' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al procesar el pedido. Por favor intente más tarde.']);
}
