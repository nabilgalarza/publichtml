<?php
/** @var array $secciones */
/** @var string $base_url */

foreach ($secciones as $sec) {
    if (isset($sec['activo']) && $sec['activo'] === false) continue;
    $tipo = $sec['tipo'] ?? '';
    switch ($tipo) {
        case 'slider':
            include __DIR__ . '/landing_section_slider.php';
            break;
        case 'categorias':
            include __DIR__ . '/landing_section_categorias.php';
            break;
        case 'tendencias':
            include __DIR__ . '/landing_section_tendencias.php';
            break;
        case 'mas_vendidos':
        case 'destacados':
            include __DIR__ . '/landing_section_mas_vendidos.php';
            break;
        case 'cta':
            include __DIR__ . '/landing_section_cta.php';
            break;
        case 'blog':
            include __DIR__ . '/home_blog_section.php';
            break;
        case 'logos':
            include __DIR__ . '/landing_section_logos.php';
            break;
        case 'locales':
            include __DIR__ . '/landing_section_locales.php';
            break;
        case 'features':
            include __DIR__ . '/landing_section_features.php';
            break;
    }
}
