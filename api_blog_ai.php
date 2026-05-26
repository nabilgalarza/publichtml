<?php
/**
 * api_blog_ai.php — Generación y mejora de artículos con Gemini (solo admin logueado).
 */
session_start();
require_once __DIR__ . '/security_headers.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_logueado'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado. Inicia sesión en el dashboard.']);
    exit;
}

$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Falta archivo .env']);
    exit;
}

$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    if (strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, " \t\"'");
}

$apiKey = $env['GEMINI_API_KEY_PAID'] ?? $env['GEMINI_API_KEY'] ?? '';
if ($apiKey === '') {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'GEMINI_API_KEY no configurada en .env']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? $_POST['action'] ?? 'generar';

function blog_ai_gemini_request(string $apiKey, string $model, string $prompt): array
{
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);
    $payload = [
        'contents' => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => [
            'temperature' => 0.75,
            'responseMimeType' => 'application/json',
        ],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 90,
    ]);
    if (file_exists(__DIR__ . '/cacert.pem')) {
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    } else {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'error' => 'Error de conexión IA: ' . $err];
    }
    curl_close($ch);

    $json = json_decode($raw, true);
    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
    if ($text === '') {
        return ['ok' => false, 'error' => 'Respuesta vacía de Gemini'];
    }
    if (strpos($text, '```') !== false) {
        $text = preg_replace('/```json\s*|\s*```/', '', $text);
        $text = trim($text);
    }
    $parsed = json_decode($text, true);
    if (!is_array($parsed)) {
        return ['ok' => false, 'error' => 'La IA no devolvió JSON válido', 'raw' => mb_substr($text, 0, 400)];
    }
    return ['ok' => true, 'data' => $parsed];
}

$model = $env['GEMINI_MODEL'] ?? 'gemini-2.5-flash';

if ($action === 'generar') {
    $tema = trim($input['tema'] ?? '');
    $categoria = trim($input['categoria'] ?? 'Herramientas');
    if ($tema === '') {
        echo json_encode(['ok' => false, 'error' => 'Indica un tema o título base']);
        exit;
    }

    $prompt = <<<PROMPT
Eres redactor técnico de IMPROGYP (Ecuador): herramientas para drywall, construcción en seco, MAXXT, etc.
Tema del artículo: "{$tema}"
Categoría: "{$categoria}"

Escribe un artículo de blog profesional en español (Ecuador), útil para instaladores y contratistas.
Devuelve SOLO JSON válido con estas claves:
{
  "titulo": "título atractivo max 80 caracteres",
  "resumen": "2-3 oraciones para tarjeta",
  "contenido": "HTML con <p>, <h2>, <ul> (3-5 párrafos/secciones, sin scripts)",
  "tiempo_lectura": "ej: 5 min",
  "categoria": "{$categoria}"
}
PROMPT;

    $res = blog_ai_gemini_request($apiKey, $model, $prompt);
    if (!$res['ok']) {
        echo json_encode($res);
        exit;
    }
    $d = $res['data'];
    require_once __DIR__ . '/lib/blog_helpers.php';
    echo json_encode([
        'ok' => true,
        'titulo' => $d['titulo'] ?? $tema,
        'resumen' => $d['resumen'] ?? '',
        'contenido' => $d['contenido'] ?? '<p></p>',
        'tiempo_lectura' => $d['tiempo_lectura'] ?? '5 min',
        'categoria' => $d['categoria'] ?? $categoria,
        'slug_sugerido' => blog_slugify($d['titulo'] ?? $tema),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'mejorar') {
    $contenido = trim($input['contenido'] ?? '');
    $instruccion = trim($input['instruccion'] ?? 'Mejora claridad y tono profesional');
    if ($contenido === '') {
        echo json_encode(['ok' => false, 'error' => 'Contenido vacío']);
        exit;
    }

    $prompt = <<<PROMPT
Mejora este artículo HTML de blog IMPROGYP según: "{$instruccion}".
Mantén etiquetas HTML seguras (p, h2, ul, li, strong). Sin scripts.
Devuelve SOLO JSON: {"contenido": "...html mejorado..."}

Artículo actual:
{$contenido}
PROMPT;

    $res = blog_ai_gemini_request($apiKey, $model, $prompt);
    if (!$res['ok']) {
        echo json_encode($res);
        exit;
    }
    echo json_encode([
        'ok' => true,
        'contenido' => $res['data']['contenido'] ?? $contenido,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Acción no válida. Usa generar o mejorar.']);
