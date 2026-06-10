<?php
ini_set('display_errors', 0);
session_start();
$current_time = time();

if (!isset($_SESSION['metric_requests'])) {
    $_SESSION['metric_requests'] = [];
}

$_SESSION['metric_requests'] = array_filter($_SESSION['metric_requests'], static function ($timestamp) use ($current_time) {
    return ($current_time - $timestamp) < 60;
});

if (count($_SESSION['metric_requests']) >= 30) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'rate_limit']);
    exit;
}
$_SESSION['metric_requests'][] = $current_time;

header('Content-Type: application/json; charset=utf-8');

function cargarEnv($ruta) {
    if (!file_exists($ruta)) {
        return false;
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

$env = cargarEnv(__DIR__ . '/.env');
$db_host = $env['DB_HOST'] ?? 'localhost';
$db_name = $env['DB_NAME'] ?? '';
$db_user = $env['DB_USER'] ?? '';
$db_pass = $env['DB_PASS'] ?? '';
$db_port = $env['DB_PORT'] ?? '3306';

if (strpos($db_host, ':') !== false) {
    [$h, $p] = explode(':', $db_host);
    $dsn = "mysql:host=$h;port=$p;dbname=$db_name;charset=utf8mb4";
} else {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
}

$data = json_decode(file_get_contents('php://input'), true) ?: [];

$evento = substr(trim(strip_tags($data['e'] ?? '')), 0, 50);
$valor = substr(trim(strip_tags($data['v'] ?? '')), 0, 150);
$categoria = substr(trim(strip_tags($data['c'] ?? 'General')), 0, 50);
$visitor_id = substr(preg_replace('/[^a-zA-Z0-9\-]/', '', $data['vid'] ?? ''), 0, 36);
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

if ($evento !== '' && $valor !== '') {
    try {
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            if (!$pdo->query("SHOW COLUMNS FROM metricas_b2c LIKE 'visitor_id'")->fetch()) {
                $pdo->exec("ALTER TABLE metricas_b2c ADD COLUMN visitor_id VARCHAR(36) DEFAULT NULL AFTER region, ADD INDEX idx_visitor_id (visitor_id)");
            }
        } catch (Throwable $e) {
            /* columna ya existe */
        }

        $region = 'Desconocida';
        if ($ip !== '127.0.0.1' && $ip !== '::1' && $ip !== '0.0.0.0' && filter_var($ip, FILTER_VALIDATE_IP)) {
            $cache_dir = __DIR__ . '/cache_geo';
            if (!is_dir($cache_dir)) {
                mkdir($cache_dir, 0755, true);
            }
            $cache_file = $cache_dir . '/' . md5($ip) . '.json';

            if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 86400) {
                $geo_cache = json_decode(file_get_contents($cache_file), true);
                $region = $geo_cache['region'] ?? 'Desconocida';
            } else {
                $ctx = stream_context_create(['http' => ['timeout' => 1]]);
                $geo_raw = json_decode(@file_get_contents("http://ip-api.com/json/{$ip}?fields=city,regionName", false, $ctx), true);
                if ($geo_raw && !empty($geo_raw['city'])) {
                    $region = $geo_raw['city'] . ', ' . $geo_raw['regionName'];
                    file_put_contents($cache_file, json_encode(['region' => $region, 'time' => time()]));
                }
            }
        }

        $stmt = $pdo->prepare(
            'INSERT INTO metricas_b2c (evento, valor, categoria, ip, region, visitor_id) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $evento,
            $valor,
            $categoria,
            $ip,
            $region,
            $visitor_id !== '' ? $visitor_id : null,
        ]);
    } catch (Throwable $e) {
        /* silent */
    }
}

echo json_encode(['status' => 'ok']);
