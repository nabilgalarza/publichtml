<?php
// api_login.php - Validador de Accesos B2B
require_once dirname(__DIR__) . '/lib/b2b_config.php';
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);

// --- CABECERAS DE SEGURIDAD (FASE 3) ---
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// --- 0. PROTECCIÓN ANTI-FUERZA BRUTA (FASE 2) ---
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
    $tiempo_bloqueo = 600; // 10 minutos
    $segundos_restantes = ($_SESSION['last_attempt_time'] + $tiempo_bloqueo) - time();
    
    if ($segundos_restantes > 0) {
        $mins = ceil($segundos_restantes / 60);
        echo json_encode([
            "success" => false, 
            "message" => "Demasiados intentos fallidos. Por seguridad, espera $mins minutos o contacta a soporte."
        ]);
        exit;
    } else {
        // Reiniciar contador tras el tiempo de espera
        $_SESSION['login_attempts'] = 0;
    }
}
// 1. Cargar configuración de base de datos (Búsqueda agresiva)
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

// Intentar varias rutas comunes para el .env
$rutas_env = [
    __DIR__ . '/../.env',
    $_SERVER['DOCUMENT_ROOT'] . '/.env',
    dirname(__DIR__) . '/.env'
];

$env = false;
foreach ($rutas_env as $r) {
    if (file_exists($r)) {
        $env = cargarEnv($r);
        break;
    }
}

if (!$env) {
    echo json_encode(["success" => false, "message" => "Error de configuración interna."]);
    exit;
}

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

// 2. Recibir credenciales desde la App
$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true) ?: [];
$ruc = trim($input['ruc'] ?? '');
$pin = trim($input['pin'] ?? '');

if (empty($ruc) || empty($pin)) {
    echo json_encode(["success" => false, "message" => "Por favor ingresa tu RUC y PIN."]);
    exit;
}

if (!improgyp_b2b_ruc_permitido($ruc)) {
    improgyp_b2b_api_denegado_respuesta();
}

// 3. Verificar en la Base de Datos
try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    $stmt = $pdo->prepare("SELECT ruc, pin, nombre, descuento, activo FROM usuarios_b2b WHERE ruc = ? LIMIT 1");
    $stmt->execute([$ruc]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    $login_valido = false;
    if ($usuario) {
        if (isset($usuario['activo']) && (int) $usuario['activo'] === 0) {
            echo json_encode(["success" => false, "message" => "Tu acceso mayorista está suspendido. Contacta a IMPROGYP."]);
            exit;
        }
        // Validación híbrida: Hash para nuevos / Texto Plano para existentes
        if (password_verify($pin, $usuario['pin'])) {
            $login_valido = true;
        } elseif ($pin === $usuario['pin']) {
            $login_valido = true;
        }
    }
    if ($login_valido) {
        // Seguridad: Limpiar intentos y regenerar ID de sesión
        $_SESSION['login_attempts'] = 0;
        session_regenerate_id(true);

        $_SESSION['b2b_user'] = [
            "ruc" => $usuario['ruc'],
            "nombre" => $usuario['nombre'],
            "descuento" => $usuario['descuento']
        ];

        // 3.5 Recuperar Historial Permanente para Sincronización Local
        $apiH = [];
        $sessH = [];
        try {
            $stmtH = $pdo->prepare("SELECT mensaje, remitente FROM b2b_historial_chat WHERE ruc_cliente = ? ORDER BY id DESC LIMIT 20");
            $stmtH->execute([$ruc]);
            $rawH = array_reverse($stmtH->fetchAll(PDO::FETCH_ASSOC));

            $ultimoQ = "";
            foreach ($rawH as $m) {
                if ($m['remitente'] === 'cliente') {
                    $apiH[] = ["role" => "user", "parts" => [["text" => $m['mensaje']]]];
                    $ultimoQ = $m['mensaje'];
                } else {
                    $apiH[] = ["role" => "model", "parts" => [["text" => $m['mensaje']]]];
                    if (!empty($ultimoQ)) {
                        $sessH[] = ["q" => $ultimoQ, "aRaw" => $m['mensaje']];
                        $ultimoQ = "";
                    }
                }
            }
        } catch (Exception $eH) { /* Fallback silencioso si no hay tabla */ }

        echo json_encode([
            "success" => true,
            "data" => $_SESSION['b2b_user'],
            "history" => [
                "apiH" => $apiH,
                "sessH" => $sessH
            ]
        ]);
    } else {
        // Registrar intento fallido
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['last_attempt_time'] = time();
        
        echo json_encode(["success" => false, "message" => "RUC o PIN incorrectos. Solicita acceso a soporte."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error de conexión con el servidor maestro."]);
}
