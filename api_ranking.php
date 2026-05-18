<?php
require_once __DIR__ . '/security_headers.php';
error_reporting(0);
ini_set('display_errors', 0);
// api_ranking.php - Endpoint para Sorting Inteligente y Prueba Social Real
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

try {
    $cacheFile = __DIR__ . '/cache_ranking.json';
    $cacheTime = 2 * 60; // 2 minutos (Equilibrio entre rendimiento y agilidad de cambios)

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        header('X-Cache: HIT');
        echo file_get_contents($cacheFile);
        exit;
    }

    $db_port = $env['DB_PORT'] ?? '3306';

    // Soporte para puertos en DB_HOST (MAMP / Hostinger / etc)
    if (strpos($db_host, ':') !== false) {
        list($h, $p) = explode(':', $db_host);
        $dsn = "mysql:host=$h;port=$p;dbname=$db_name;charset=utf8mb4";
    } else {
        $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    }

    $pdo = new PDO($dsn, $db_user, $db_pass);
    
    // 1. Obtener productos impulsados activos
    $stmtBoost = $pdo->query("SELECT nombre_producto FROM productos_impulsados WHERE fecha_limite > NOW()");
    $impulsados = $stmtBoost->fetchAll(PDO::FETCH_COLUMN);
    
    // 2. Obtener Top Trending (Más vistos últimas 48h)
    $stmtTrend = $pdo->query("
        SELECT valor as nombre, COUNT(*) as clics 
        FROM metricas_b2c 
        WHERE evento = 'Ver Producto' 
        AND fecha > (NOW() - INTERVAL 48 HOUR)
        GROUP BY valor 
        ORDER BY clics DESC 
        LIMIT 10
    ");
    $tendencias = $stmtTrend->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        "status" => "success",
        "impulsados" => $impulsados,
        "tendencias" => $tendencias
    ];

    $jsonData = json_encode($response);
    file_put_contents($cacheFile, $jsonData);
    
    header('X-Cache: MISS');
    echo $jsonData;

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
}
?>
