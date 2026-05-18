<?php
ini_set('display_errors', 0); // Seguridad Fase 1
// api_metricas.php - El Sensor Ninja (Rastreo silencioso)
// --- PROTECCIÓN ANTI-SPAM B2C ---
session_start();
$current_time = time();

if (!isset($_SESSION['metric_requests'])) { 
    $_SESSION['metric_requests'] = []; 
}

// Filtramos peticiones: Máximo 30 eventos por minuto por usuario
$_SESSION['metric_requests'] = array_filter($_SESSION['metric_requests'], function($timestamp) use ($current_time) {
    return ($current_time - $timestamp) < 60;
});

if (count($_SESSION['metric_requests']) >= 30) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["status" => "rate_limit"]);
    exit;
}
$_SESSION['metric_requests'][] = $current_time;
// -----------------------------------------------------------------------

header('Content-Type: application/json; charset=utf-8');

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
$db_name = $env['DB_NAME'] ?? '';
$db_user = $env['DB_USER'] ?? '';
$db_pass = $env['DB_PASS'] ?? '';

// Soporte para puertos en DB_HOST (MAMP / Hostinger / etc)
if (strpos($db_host, ':') !== false) {
    list($h, $p) = explode(':', $db_host);
    $dsn = "mysql:host=$h;port=$p;dbname=$db_name;charset=utf8mb4";
} else {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
}

// Recibimos los datos por método sendBeacon (POST en crudo)
$data = json_decode(file_get_contents('php://input'), true);

// SANITIZACIÓN QUIRÚRGICA
$evento = substr(trim(strip_tags($data['e'] ?? '')), 0, 50);
$valor = substr(trim(strip_tags($data['v'] ?? '')), 0, 150);
$categoria = substr(trim(strip_tags($data['c'] ?? 'General')), 0, 50); // Nueva: Categoría
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; // Nueva: IP para geolocalización

if (!empty($evento) && !empty($valor)) {
    try {
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        
        // --- GEO LOCALIZACIÓN NINJA CON CACHÉ ---
        $region = 'Desconocida';
        if ($ip !== '127.0.0.1' && $ip !== '::1' && $ip !== '0.0.0.0' && filter_var($ip, FILTER_VALIDATE_IP)) {
            $cache_dir = __DIR__ . '/cache_geo';
            if (!is_dir($cache_dir)) mkdir($cache_dir, 0755, true);
            $cache_file = $cache_dir . '/' . md5($ip) . '.json';
            
            if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 86400) {
                // Caché válida por 24 horas
                $geo_cache = @json_decode(file_get_contents($cache_file), true);
                $region = $geo_cache['region'] ?? 'Desconocida';
            } else {
                $ctx = stream_context_create(['http' => ['timeout' => 1]]);
                $geo_raw = @json_decode(@file_get_contents("http://ip-api.com/json/{$ip}?fields=city,regionName", false, $ctx), true);
                if ($geo_raw && !empty($geo_raw['city'])) {
                    $region = $geo_raw['city'] . ", " . $geo_raw['regionName'];
                    @file_put_contents($cache_file, json_encode(['region' => $region, 'time' => time()]));
                }
            }
        }

        // Guardamos el movimiento completo (Tabla e Indices optimizados)
        $stmt = $pdo->prepare("INSERT INTO metricas_b2c (evento, valor, categoria, ip, region) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$evento, $valor, $categoria, $ip, $region]);
        
    } catch (Exception $e) {}
}

echo json_encode(["status" => "ok"]);
?>