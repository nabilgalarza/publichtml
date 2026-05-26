<?php
/**
 * Variables compartidas para home_blog_section y blog.php (mismo layout).
 */

require_once __DIR__ . '/blog_layout_slots.php';
require_once __DIR__ . '/blog_helpers.php';

function blog_layout_load_config(): array
{
    $path = dirname(__DIR__) . '/config_blog.json';
    if (!file_exists($path)) {
        return [];
    }
    return json_decode(file_get_contents($path), true) ?: [];
}

function blog_layout_map_articles(array $articulos, string $base_url = ''): array
{
    return array_map(static function ($a) use ($base_url) {
        return [
            'titulo'         => $a['titulo'],
            'slug'           => $a['slug'],
            'categoria'      => $a['categoria'],
            'tiempo_lectura' => $a['tiempo_lectura'],
            'resumen'        => $a['resumen'] ?? '',
            'portada'        => blog_img_url($a['portada'] ?? '', $base_url),
            'fecha'          => $a['fecha'],
            'visitas'        => (int) ($a['visitas'] ?? 0),
        ];
    }, $articulos);
}

/**
 * @return array<string,mixed>|null null si no hay artículos
 */
function blog_layout_prepare(array $articulos, string $base_url = '', array $opts = []): ?array
{
    if (empty($articulos)) {
        return null;
    }

    $cfg = blog_layout_load_config();
    $layout = blog_layout_normalize($cfg['layout'] ?? 'editorial');
    $accent = $cfg['accent'] ?? '#3a86ff';
    $font = $cfg['font'] ?? 'sans';
    $fontMap = [
        'sans'  => "'Outfit', 'Plus Jakarta Sans', sans-serif",
        'serif' => "'Merriweather', Georgia, serif",
        'mono'  => "'Courier New', monospace",
    ];
    $imgFb = blog_img_url('favicon-app.png?v=5', $base_url);

    return [
        'layout'      => $layout,
        'accent'      => $accent,
        'accent_rgb'  => $cfg['accentRgb'] ?? '58, 134, 255',
        'font_css'    => $fontMap[$font] ?? $fontMap['sans'],
        'is_cyber'    => $layout === 'cyberneon',
        'heading_html'=> $opts['heading_html'] ?? 'Desde el <span>Blog</span>',
        'show_view_all' => $opts['show_view_all'] ?? true,
        'section_id'          => $opts['section_id'] ?? 'bl-home-section',
        'section_class_extra' => $opts['section_class_extra'] ?? '',
        'js_cfg'      => [
            'layout'      => $layout,
            'perPage'     => blog_layout_per_page($layout),
            'paginated'   => blog_layout_is_paginated($layout),
            'accent'      => $accent,
            'imgFallback' => $imgFb,
            'showDate'    => !empty($cfg['showDate']),
            'showRT'      => !empty($cfg['showReadTime']),
            'showViews'   => !empty($cfg['showViews']),
            'articles'    => blog_layout_map_articles($articulos, $base_url),
        ],
    ];
}
