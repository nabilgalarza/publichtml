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

function improgyp_include_locales_modal_once(): void
{
    if (defined('IMPROGYP_LOCALES_MODAL')) {
        return;
    }
    define('IMPROGYP_LOCALES_MODAL', true);
    include dirname(__DIR__) . '/components/locales_modal.php';
}
