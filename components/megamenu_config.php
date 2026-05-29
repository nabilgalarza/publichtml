<?php
/**
 * Configuración y normalización del megamenú B2C (compartido header + dashboard).
 */

function improgyp_megamenu_defaults() {
    return [
        [
            'id' => 'drywall',
            'title' => 'Drywall & Yeso',
            'icon' => 'fa-trowel-bricks',
            'iconColor' => 'text-[#3A86FF]',
            'titleLeft' => 'SISTEMAS CONSTRUCTIVOS',
            'titleRight' => 'HERRAMIENTAS MASTER',
            'linksLeft' => [
                ['name' => 'Herramientas Drywall', 'linkType' => 'category', 'linkValue' => 'Herramientas Drywall'],
                ['name' => 'Accesorios de obra', 'linkType' => 'category', 'linkValue' => 'Accesorios'],
            ],
            'linksRight' => [
                ['name' => 'Lijadoras de paneles', 'linkType' => 'category', 'linkValue' => 'Lijadoras de Paneles de Yeso'],
                ['name' => 'Lijadoras orbitales', 'linkType' => 'category', 'linkValue' => 'Lijadoras Orbitales'],
            ],
        ],
        [
            'id' => 'potencia',
            'title' => 'Línea de Potencia',
            'icon' => 'fa-bolt',
            'iconColor' => 'text-amber-500',
            'titleLeft' => 'HERRAMIENTAS ELÉCTRICAS',
            'titleRight' => 'LÍNEA CONCRETO Y OBRA',
            'linksLeft' => [
                ['name' => 'Taladros percutores', 'linkType' => 'category', 'linkValue' => 'Taladros Percutores'],
                ['name' => 'Amoladoras', 'linkType' => 'category', 'linkValue' => 'Amoladoras'],
                ['name' => 'Mezcladores', 'linkType' => 'category', 'linkValue' => 'Mezclador de Paletas'],
            ],
            'linksRight' => [
                ['name' => 'Línea de concreto', 'linkType' => 'category', 'linkValue' => 'Línea de Concreto'],
                ['name' => 'Cortadoras', 'linkType' => 'category', 'linkValue' => 'Cortadoras'],
                ['name' => 'Herramientas varias', 'linkType' => 'category', 'linkValue' => 'Herramientas Varias'],
            ],
        ],
        [
            'id' => 'aplicacion',
            'title' => 'Aplicación & Limpieza',
            'icon' => 'fa-spray-can-sparkles',
            'iconColor' => 'text-[#3A86FF]',
            'titleLeft' => 'SISTEMAS DE APLICACIÓN',
            'titleRight' => 'TRATAMIENTO Y LIMPIEZA',
            'linksLeft' => [
                ['name' => 'Pulverizadores', 'linkType' => 'category', 'linkValue' => 'Pulverizadores de Pintura'],
                ['name' => 'Pistolas de silicona', 'linkType' => 'category', 'linkValue' => 'Pistolas de Silicona'],
            ],
            'linksRight' => [
                ['name' => 'Pistolas de aire caliente', 'linkType' => 'category', 'linkValue' => 'Pistolas de Aire Caliente'],
                ['name' => 'Sopladoras', 'linkType' => 'category', 'linkValue' => 'Sopladoras'],
                ['name' => 'Aspiradoras', 'linkType' => 'category', 'linkValue' => 'Limpieza al Vacío'],
            ],
        ],
        [
            'id' => 'accesorios',
            'title' => 'Accesorios & Kits',
            'icon' => 'fa-gears',
            'iconColor' => 'text-slate-400',
            'titleLeft' => 'CONSUMIBLES PRO',
            'titleRight' => 'MÁS CATEGORÍAS',
            'linksLeft' => [
                ['name' => 'Accesorios', 'linkType' => 'category', 'linkValue' => 'Accesorios'],
            ],
            'linksRight' => [
                ['name' => 'Buscar tornillos', 'linkType' => 'search', 'linkValue' => 'tornillo'],
                ['name' => 'Buscar MAXXT', 'linkType' => 'search', 'linkValue' => 'maxxt'],
            ],
        ],
    ];
}

function improgyp_megamenu_migrate_link($item) {
    if (is_string($item)) {
        return ['name' => $item, 'linkType' => 'category', 'linkValue' => $item];
    }
    if (!is_array($item)) {
        return ['name' => 'Enlace', 'linkType' => 'category', 'linkValue' => ''];
    }
    return [
        'name' => $item['name'] ?? 'Enlace',
        'linkType' => ($item['linkType'] ?? '') === 'search' ? 'search' : 'category',
        'linkValue' => $item['linkValue'] ?? ($item['name'] ?? ''),
    ];
}

function improgyp_normalize_megamenu($raw) {
    if (!is_array($raw) || empty($raw)) {
        return improgyp_megamenu_defaults();
    }

    $out = [];
    foreach ($raw as $idx => $div) {
        if (!is_array($div)) continue;
        $title = trim($div['title'] ?? 'División');
        $id = trim($div['id'] ?? '');
        if ($id === '') {
            $id = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $title));
            $id = trim($id, '_') ?: 'division';
        }
        $linksLeft = $div['linksLeft'] ?? $div['catsLeft'] ?? [];
        $linksRight = $div['linksRight'] ?? $div['catsRight'] ?? [];
        $out[] = [
            'id' => $id,
            'title' => $title,
            'icon' => $div['icon'] ?? 'fa-tag',
            'iconColor' => $div['iconColor'] ?? 'text-slate-400',
            'titleLeft' => $div['titleLeft'] ?? 'COLUMNA IZQUIERDA',
            'titleRight' => $div['titleRight'] ?? 'COLUMNA DERECHA',
            'linksLeft' => array_values(array_map('improgyp_megamenu_migrate_link', is_array($linksLeft) ? $linksLeft : [])),
            'linksRight' => array_values(array_map('improgyp_megamenu_migrate_link', is_array($linksRight) ? $linksRight : [])),
        ];
    }

    return !empty($out) ? $out : improgyp_megamenu_defaults();
}

/** Mapa para JS (categoryDivisionMap) */
function improgyp_megamenu_js_map(array $divisions) {
    $map = [];
    foreach ($divisions as $div) {
        $map[$div['id']] = [
            'titleLeft' => $div['titleLeft'],
            'titleRight' => $div['titleRight'],
            'linksLeft' => $div['linksLeft'],
            'linksRight' => $div['linksRight'],
        ];
    }
    return $map;
}

function improgyp_megamenu_link_href(array $link) {
    $type = ($link['linkType'] ?? '') === 'search' ? 'search' : 'category';
    if ($type === 'search') {
        return 'productos.php?q=' . rawurlencode($link['linkValue'] ?? '');
    }
    return 'productos.php?cat=' . rawurlencode($link['linkValue'] ?? '');
}

function improgyp_megamenu_categorias_from_catalogo() {
    $path = __DIR__ . '/../catalogo.json';
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data)) return [];
    $cats = array_unique(array_filter(array_column($data, 'categoria')));
    sort($cats, SORT_LOCALE_STRING);
    return array_values($cats);
}

/** Enlaces de sitio para el pie del megamenú (sin Productos/Catálogo). */
function improgyp_header_site_nav_items(array $menu): array {
    $skip = ['producto', 'productos', 'catálogo', 'catalogo', 'catalog'];
    $out = [];
    foreach ($menu as $item) {
        if (!is_array($item) || trim($item['text'] ?? '') === '') {
            continue;
        }
        $t = mb_strtolower($item['text']);
        $skipItem = false;
        foreach ($skip as $needle) {
            if (strpos($t, $needle) !== false) {
                $skipItem = true;
                break;
            }
        }
        if ($skipItem) {
            continue;
        }
        $out[] = $item;
    }
    return $out;
}

function improgyp_header_site_nav_icon(string $text): string {
    $t = mb_strtolower($text);
    if (strpos($t, 'inicio') !== false) {
        return 'fa-house';
    }
    if (strpos($t, 'nosotros') !== false) {
        return 'fa-circle-info';
    }
    if (strpos($t, 'servicios') !== false) {
        return 'fa-handshake-angle';
    }
    if (strpos($t, 'blog') !== false) {
        return 'fa-newspaper';
    }
    if (strpos($t, 'contacto') !== false) {
        return 'fa-envelope';
    }
    return 'fa-link';
}

function improgyp_header_default_nivel3_menu(): array {
    return [
        ['text' => 'Inicio', 'link' => 'index.php'],
        ['text' => 'Nosotros', 'link' => 'index.php#nosotros'],
        ['text' => 'Blog', 'link' => 'blog.php'],
        ['text' => 'Contacto', 'link' => 'index.php#contacto'],
    ];
}

/** true si en JSON no hay megamenu guardado (la tienda usa defaults en runtime). */
function improgyp_megamenu_is_stored_empty($raw): bool {
    return !is_array($raw) || count($raw) === 0;
}

function improgyp_normalize_nivel3_menu($raw): array {
    if (!is_array($raw) || empty($raw)) {
        return improgyp_header_default_nivel3_menu();
    }
    $out = [];
    foreach ($raw as $item) {
        if (!is_array($item)) {
            continue;
        }
        $text = trim($item['text'] ?? '');
        if ($text === '') {
            continue;
        }
        $link = trim($item['link'] ?? '');
        $out[] = ['text' => $text, 'link' => $link !== '' ? $link : '#'];
    }
    return !empty($out) ? $out : improgyp_header_default_nivel3_menu();
}

/** Categorías enlazadas en el megamenú (linkType category). */
function improgyp_megamenu_linked_categories(array $divisions): array {
    $linked = [];
    foreach ($divisions as $div) {
        if (!is_array($div)) {
            continue;
        }
        foreach (['linksLeft', 'linksRight'] as $key) {
            $links = $div[$key] ?? [];
            if (!is_array($links)) {
                continue;
            }
            foreach ($links as $link) {
                if (!is_array($link)) {
                    continue;
                }
                if (($link['linkType'] ?? 'category') !== 'category') {
                    continue;
                }
                $v = trim($link['linkValue'] ?? '');
                if ($v !== '') {
                    $linked[$v] = true;
                }
            }
        }
    }
    return array_keys($linked);
}

/** Categorías del catálogo que no aparecen en ningún enlace del menú. */
function improgyp_megamenu_orphan_categories(array $divisions, ?array $catalogCategories = null): array {
    if ($catalogCategories === null) {
        $catalogCategories = improgyp_megamenu_categorias_from_catalogo();
    }
    $linked = improgyp_megamenu_linked_categories($divisions);
    return array_values(array_filter($catalogCategories, static function ($c) use ($linked) {
        return !in_array($c, $linked, true);
    }));
}
