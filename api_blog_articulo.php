<?php
/**
 * Artículo de blog por slug (JSON) — carga bajo demanda para modal en home.
 */
require_once __DIR__ . '/security_headers.php';
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/lib/blog_helpers.php';

$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', strtolower((string) $_GET['slug'])) : '';
if ($slug === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'slug requerido']);
    exit;
}

$art = blog_fetch_by_slug($slug);
if (!$art) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'no encontrado']);
    exit;
}

$base = '';
if (isset($_GET['base']) && is_string($_GET['base'])) {
    $base = preg_replace('/[^a-zA-Z0-9_\/\.\-]/', '', $_GET['base']);
}

echo json_encode([
    'success' => true,
    'article' => [
        'titulo'         => $art['titulo'],
        'slug'           => $art['slug'],
        'categoria'      => $art['categoria'] ?? '',
        'tiempo_lectura' => $art['tiempo_lectura'] ?? '',
        'resumen'        => $art['resumen'] ?? '',
        'portada'        => blog_img_url($art['portada'] ?? '', $base),
        'fecha'          => $art['fecha'] ?? '',
        'visitas'        => (int) ($art['visitas'] ?? 0),
        'contenido'      => $art['contenido'] ?? '',
    ],
], JSON_UNESCAPED_UNICODE);
