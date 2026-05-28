<?php
/**
 * Datos compartidos para components/footer.php
 */
require_once __DIR__ . '/../components/megamenu_config.php';

function improgyp_footer_nav_items(): array
{
    $path = dirname(__DIR__) . '/config_header.json';
    $data = [];
    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path), true) ?? [];
    }
    $menu = $data['nivel3_menu'] ?? [];
    if (!is_array($menu) || $menu === []) {
        $menu = improgyp_header_default_nivel3_menu();
    }
    return improgyp_header_site_nav_items($menu);
}

function improgyp_footer_matriz_contact(): array
{
    $path = dirname(__DIR__) . '/locales.json';
    if (!file_exists($path)) {
        return [
            'telefono' => '(04) 288-5678',
            'email' => 'gye@improgyp.com',
            'whatsapp' => '593991754887',
            'ciudad' => 'Guayaquil',
        ];
    }
    $locales = json_decode(file_get_contents($path), true);
    if (!is_array($locales)) {
        return ['telefono' => '', 'email' => '', 'whatsapp' => '593991754887', 'ciudad' => ''];
    }
    foreach ($locales as $loc) {
        if (($loc['id'] ?? '') === 'gye-matriz') {
            return [
                'telefono' => $loc['telefono'] ?? '',
                'email' => $loc['email'] ?? '',
                'whatsapp' => preg_replace('/\D/', '', $loc['whatsapp'] ?? '593991754887'),
                'ciudad' => $loc['ciudad'] ?? 'Guayaquil',
            ];
        }
    }
    $first = $locales[0] ?? [];
    return [
        'telefono' => $first['telefono'] ?? '',
        'email' => $first['email'] ?? '',
        'whatsapp' => preg_replace('/\D/', '', $first['whatsapp'] ?? '593991754887'),
        'ciudad' => $first['ciudad'] ?? '',
    ];
}

/**
 * ¿Enlace del bloque Sitio activo? (misma lógica que megamenú en header.php)
 */
function improgyp_footer_nav_item_active(string $link, string $text, string $currentPage, string $pageKind = ''): bool
{
    $textLower = mb_strtolower(trim($text));
    $linkPath = basename(parse_url($link, PHP_URL_PATH) ?: '');

    if ($pageKind === 'blog' || $currentPage === 'blog.php') {
        return $linkPath === 'blog.php';
    }

    if ($currentPage === 'index.php') {
        if (strpos($textLower, 'inicio') !== false) {
            return $linkPath === 'index.php';
        }
        return false;
    }

    if ($linkPath === '' || $linkPath === $currentPage) {
        return $currentPage === $linkPath && strpos($textLower, 'inicio') !== false;
    }

    return $currentPage === $linkPath;
}

/**
 * Enlaces inferiores del pie (Tienda, Blog, B2B, Contacto).
 */
function improgyp_footer_bottom_link_active(string $key, string $currentPage, string $pageKind = ''): bool
{
    switch ($key) {
        case 'tienda':
            return $pageKind === 'tienda' || $currentPage === 'productos.php';
        case 'blog':
            return $pageKind === 'blog' || $currentPage === 'blog.php';
        case 'b2b':
            return false;
        case 'contacto':
            return false;
        default:
            return false;
    }
}

function improgyp_footer_link_class(bool $active, string $base = 'hover:text-white transition-colors'): string
{
    if ($active) {
        return 'text-[#3A86FF] hover:text-[#3A86FF] transition-colors';
    }
    return $base;
}

function improgyp_include_locales_modal_once(): void
{
    if (defined('IMPROGYP_LOCALES_MODAL')) {
        return;
    }
    define('IMPROGYP_LOCALES_MODAL', true);
    include dirname(__DIR__) . '/components/locales_modal.php';
}
