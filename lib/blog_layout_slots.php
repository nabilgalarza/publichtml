<?php
/**
 * Layouts del blog en Home — fuente única.
 * home_blog_section.php · apariencia_blog.php · dashboard.php
 */

/** Layouts activos en admin y tienda */
function blog_layouts_allowed(): array
{
    return ['editorial', 'duo50', 'grid3', 'carousel', 'cyberneon'];
}

/** Migración de layouts eliminados */
function blog_layout_legacy_map(): array
{
    return [
        'asymmetric' => 'editorial',
        'bento'      => 'editorial',
        'magazine'   => 'editorial',
    ];
}

function blog_layout_normalize(string $layout): string
{
    $layout = preg_replace('/[^a-z0-9_-]/', '', $layout) ?: 'editorial';
    if (in_array($layout, blog_layouts_allowed(), true)) {
        return $layout;
    }
    return blog_layout_legacy_map()[$layout] ?? 'editorial';
}

/** Artículos visibles por página (o por ventana en carrusel) */
function blog_layout_per_page(string $layout): int
{
    return match (blog_layout_normalize($layout)) {
        'editorial' => 3,
        'duo50'     => 3,
        'grid3'     => 3,
        'cyberneon' => 4,
        'carousel'  => 3,
        default     => 3,
    };
}

/** Usa paginación por páginas (no carrusel scroll) */
function blog_layout_is_paginated(string $layout): bool
{
    return blog_layout_normalize($layout) !== 'carousel';
}

function blog_layout_slots(string $layout): int
{
    return blog_layout_per_page($layout);
}

function blog_home_fetch_limit(): int
{
    return 6;
}

/** Artículos por página en blog.php (archivo grid). */
function blog_archive_per_page(): int
{
    return 12;
}

function blog_layout_has_art(array $articles, int $index): bool
{
    return isset($articles[$index]) && is_array($articles[$index]);
}

/** Etiquetas para admin */
function blog_layout_admin_options(): array
{
    return [
        'editorial' => ['label' => 'Split / Editorial',  'icon' => 'fa-columns',      'color' => 'from-blue-500 to-blue-700'],
        'duo50'     => ['label' => 'Split 50/50',        'icon' => 'fa-table-columns', 'color' => 'from-indigo-500 to-indigo-700'],
        'grid3'     => ['label' => '3 Column Grid',      'icon' => 'fa-table-cells',  'color' => 'from-emerald-500 to-emerald-700'],
        'carousel'  => ['label' => 'Carrusel Moderno',   'icon' => 'fa-film',         'color' => 'from-pink-500 to-pink-700'],
        'cyberneon' => ['label' => 'Cyber Neon',         'icon' => 'fa-bolt',         'color' => 'from-violet-600 to-fuchsia-600'],
    ];
}
