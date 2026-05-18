<?php
// api_social.php - IMPROGYP Social AI API (Arquitectura Desacoplada B2B)
header('Content-Type: application/json');

function cargarEnv($ruta) {
    if (!file_exists($ruta)) return false;
    $lineas = @file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lineas) return false;
    $env = [];
    foreach ($lineas as $linea) {
        if (strpos(trim($linea), '#') === 0 || strpos($linea, '=') === false) continue;
        list($nombre, $valor) = explode('=', $linea, 2);
        $env[trim($nombre)] = trim($valor);
    }
    return $env;
}

error_reporting(0);
ini_set('display_errors', 0);
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo json_encode(['error' => "PHP Error: $errstr"]);
    exit;
});

$env = cargarEnv(__DIR__ . '/.env');
$db_host = $env['DB_HOST'] ?? 'localhost';
$db_port = $env['DB_PORT'] ?? '3306';
$db_name = $env['DB_NAME'] ?? '';
$db_user = $env['DB_USER'] ?? '';
$db_pass = $env['DB_PASS'] ?? '';

try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error de conexión a BD']);
    exit;
}

$action = $_GET['action'] ?? '';
$range = isset($_GET['range']) ? (int)$_GET['range'] : 7;

if ($action === 'analyze_instagram' || $action === 'analyze_facebook') {
    $platform = ($action === 'analyze_facebook') ? 'facebook' : 'instagram';
    
    // Consulta simulable pasiva a la base de datos (Ingestada por el microservicio)
    $stmt = $pdo->prepare("SELECT * FROM social_metrics WHERE platform = :platform ORDER BY timestamp DESC LIMIT 1");
    $stmt->execute(['platform' => $platform]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode([
            'status' => 'success',
            'report' => json_decode($data['ai_assessment'], true) ?: ['analisis_breve' => 'Sin datos', 'consejos' => [], 'idea_proximo_post' => ''],
            'raw_metrics' => json_decode($data['raw_metrics'], true) ?: [],
            'account_insights' => json_decode($data['account_insights'], true) ?: ['reach' => 0, 'interactions' => 0, 'impressions' => 0],
            'range_days' => $range
        ]);
    } else {
        // MOCK DATA: Si no hay datos, enviamos un default elegante hasta que el microservicio inyecte
        echo json_encode([
            'status' => 'success',
            'report' => [
                'analisis_breve' => 'El microservicio externo aún no ha inyectado las métricas a la BD local.',
                'consejos' => ['Revisar conexión del microservicio', 'Verificar credenciales de Meta Graph', 'Asegurar que el cron job está activo'],
                'idea_proximo_post' => 'Preparando el entorno analítico...'
            ],
            'raw_metrics' => [],
            'account_insights' => ['reach' => 0, 'interactions' => 0, 'impressions' => 0],
            'range_days' => $range,
            'info' => 'Modo pasivo - Data dummy'
        ]);
    }
    exit;
} else {
    echo json_encode(['error' => 'Acción no válida']);
}
