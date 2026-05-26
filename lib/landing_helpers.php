<?php
/**
 * Helpers para index.php (landing) — catálogo, categorías, destacados.
 */

function improgyp_landing_load_catalogo() {
    static $cache = null;
    if ($cache !== null) return $cache;
    $path = dirname(__DIR__) . '/catalogo.json';
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    $cache = is_array($data) ? $data : [];
    return $cache;
}

function improgyp_landing_img_url($ruta, $base_url = '') {
    if (!$ruta) return 'favicon-app.png?v=5';
    if (preg_match('~^https?://~i', $ruta)) return $ruta;
    $ruta = ltrim(str_replace('./', '', $ruta), '/');
    return $base_url . $ruta;
}

function improgyp_landing_card_badge_html($badge) {
    if ($badge === 'top') {
        return '<div class="absolute top-[10px] left-[10px] bg-[#1B263B]/90 backdrop-blur-md text-white text-[10px] font-black px-2 py-1 rounded-md shadow-lg z-10 flex items-center gap-1 border border-[#1B263B]"><i class="fa-solid fa-bolt-lightning text-white"></i> TOP</div>';
    }
    if ($badge === 'tendencia') {
        return '<div class="absolute top-[10px] left-[10px] bg-[#3A86FF] text-white text-[10px] font-black px-2.5 py-1 rounded-full shadow-lg z-10 uppercase tracking-wider">Tendencia</div>';
    }
    return '';
}

function improgyp_landing_precio_display($prod) {
    if (empty($prod['presentaciones'][0]['precio'])) return 'Consultar';
    $p = $prod['presentaciones'][0]['precio'];
    $p = explode('|', $p)[0];
    $p = trim($p);
    if ($p === '' || $p === 'Consultar') return 'Consultar';
    if (strpos($p, '$') !== false) return $p;
    return '$' . $p;
}

function improgyp_landing_dedup_catalogo(array $items) {
    $seen = [];
    $out = [];
    foreach ($items as $prod) {
        $key = (!empty($prod['codigo']) && trim($prod['codigo']) !== '')
            ? trim($prod['codigo'])
            : ($prod['nombre'] ?? '');
        if ($key === '' || isset($seen[$key])) continue;
        $seen[$key] = true;
        $out[] = $prod;
    }
    return $out;
}

function improgyp_landing_categorias($limite = 8) {
    $catalogo = improgyp_landing_load_catalogo();
    $counts = [];
    foreach ($catalogo as $p) {
        $cat = trim($p['categoria'] ?? '');
        if ($cat === '') continue;
        $counts[$cat] = ($counts[$cat] ?? 0) + 1;
    }
    arsort($counts);
    $cats = array_slice(array_keys($counts), 0, (int) $limite);
    $icons = [
        'Accesorios' => 'fa-gears',
        'Herramientas Drywall' => 'fa-trowel-bricks',
        'Taladros Percutores' => 'fa-screwdriver-wrench',
        'Amoladoras' => 'fa-circle-notch',
        'Lijadoras de Paneles de Yeso' => 'fa-sheet-plastic',
        'Pulverizadores de Pintura' => 'fa-spray-can',
    ];
    $result = [];
    foreach ($cats as $cat) {
        $result[] = [
            'nombre' => $cat,
            'count' => $counts[$cat],
            'icon' => $icons[$cat] ?? 'fa-tag',
            'href' => 'productos.php?cat=' . rawurlencode($cat),
        ];
    }
    return $result;
}

function improgyp_landing_ranking(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = ['impulsados' => [], 'tendencias' => []];
    $path = dirname(__DIR__) . '/cache_ranking.json';
    if (file_exists($path)) {
        $rank = json_decode(file_get_contents($path), true);
        if (is_array($rank)) {
            $cache['impulsados'] = is_array($rank['impulsados'] ?? null) ? $rank['impulsados'] : [];
            $cache['tendencias'] = is_array($rank['tendencias'] ?? null) ? $rank['tendencias'] : [];
        }
    }
    return $cache;
}

/** Refresca cache_ranking.json si está vacío o vencido (2 min). */
function improgyp_landing_ensure_ranking_cache(): void
{
    $path = dirname(__DIR__) . '/cache_ranking.json';
    $maxAge = 120;
    if (file_exists($path) && (time() - filemtime($path)) < $maxAge) {
        $data = json_decode(file_get_contents($path), true);
        if (is_array($data) && (!empty($data['impulsados']) || !empty($data['tendencias']))) {
            return;
        }
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8888';
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/publichtml'), '/\\');
    if ($base === '' || $base === '.') {
        $base = '/publichtml';
    }
    $url = $scheme . '://' . $host . $base . '/api_ranking.php';
    $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
    @file_get_contents($url, false, $ctx);
}

function improgyp_landing_section_heading(array $sec): array
{
    $normal = trim($sec['titulo_normal'] ?? '');
    $resalt = trim($sec['titulo_resaltado'] ?? '');
    if ($normal === '' && !empty($sec['titulo'])) {
        $normal = trim($sec['titulo']);
    }
    return [
        'normal' => $normal,
        'resalt' => $resalt,
        'sub' => trim($sec['subtitulo'] ?? ''),
    ];
}

function improgyp_landing_catalogo_fallback($limite = 8, $badge = '')
{
    $items = improgyp_landing_dedup_catalogo(improgyp_landing_load_catalogo());
    $out = [];
    foreach ($items as $p) {
        if ($badge !== '') {
            $p['_badge'] = $badge;
        }
        $out[] = $p;
        if (count($out) >= (int) $limite) {
            break;
        }
    }
    return $out;
}

/** Productos por ranking de tendencias (métricas Ver Producto 48h). */
function improgyp_landing_tendencias($limite = 8) {
    $catalogo = improgyp_landing_dedup_catalogo(improgyp_landing_load_catalogo());
    $rank = improgyp_landing_ranking();
    $trends = $rank['tendencias'];
    if (empty($trends)) {
        return improgyp_landing_catalogo_fallback((int) $limite, 'tendencia');
    }
    usort($trends, static fn($a, $b) => (int) ($b['clics'] ?? 0) <=> (int) ($a['clics'] ?? 0));
    $ordered = [];
    $seen = [];
    foreach ($trends as $row) {
        $nombre = $row['nombre'] ?? '';
        if ($nombre === '') {
            continue;
        }
        foreach ($catalogo as $p) {
            if (($p['nombre'] ?? '') === $nombre) {
                $key = $p['codigo'] ?? $p['nombre'];
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $p['_badge'] = 'tendencia';
                    $p['_clics'] = (int) ($row['clics'] ?? 0);
                    $ordered[] = $p;
                }
                break;
            }
        }
        if (count($ordered) >= (int) $limite) {
            break;
        }
    }
    return array_slice($ordered, 0, (int) $limite);
}

/** Productos impulsados (TOP / más vendidos promocionados). */
function improgyp_landing_mas_vendidos($limite = 8) {
    $catalogo = improgyp_landing_dedup_catalogo(improgyp_landing_load_catalogo());
    $impulsados = improgyp_landing_ranking()['impulsados'];
    $ordered = [];
    foreach ($impulsados as $nombre) {
        foreach ($catalogo as $p) {
            if (($p['nombre'] ?? '') === $nombre) {
                $p['_badge'] = 'top';
                $ordered[] = $p;
                break;
            }
        }
        if (count($ordered) >= (int) $limite) {
            break;
        }
    }
    return array_slice($ordered, 0, (int) $limite);
}

/** @deprecated alias */
function improgyp_landing_destacados($limite = 8) {
    return improgyp_landing_mas_vendidos($limite);
}

function improgyp_landing_marcas($limite = 12) {
    $counts = [];
    foreach (improgyp_landing_load_catalogo() as $p) {
        $m = trim($p['marca'] ?? '');
        if ($m === '') {
            continue;
        }
        $counts[$m] = ($counts[$m] ?? 0) + 1;
    }
    arsort($counts);
    $out = [];
    foreach (array_slice(array_keys($counts), 0, (int) $limite) as $marca) {
        $out[] = [
            'nombre' => $marca,
            'count' => $counts[$marca],
            'href' => 'productos.php?q=' . rawurlencode($marca),
        ];
    }
    return $out;
}

function improgyp_landing_locales(): array {
    $path = dirname(__DIR__) . '/locales.json';
    if (!file_exists($path)) {
        return [];
    }
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function improgyp_landing_defaults() {
    return [
        'hero' => [
            'activo' => false,
            'badge' => 'Herramientas profesionales · Ecuador',
            'titulo_normal' => 'Herramientas profesionales para',
            'titulo_resaltado' => 'tu máximo nivel.',
            'subtitulo' => 'Catálogo técnico, asesoría IA y compra por WhatsApp en toda Ecuador.',
            'cta_tienda' => 'Explorar tienda',
            'cta_tienda_url' => 'productos.php',
            'cta_b2b' => 'Portal mayoristas',
            'cta_b2b_url' => 'b2b/',
            'imagen' => '',
        ],
        'secciones' => [
            ['tipo' => 'slider', 'activo' => true, 'autoplay' => true, 'intervalo_ms' => 6000, 'slides' => [
                ['etiqueta' => 'Novedades', 'titulo' => 'Herramientas que elevan tu obra', 'subtitulo' => 'Catálogo técnico, stock y asesoría en tienda y por WhatsApp.', 'imagen' => 'ads_media/banner_1775837301_1.webp', 'cta_texto' => 'Ver catálogo', 'cta_url' => 'productos.php'],
                ['etiqueta' => 'Mayoristas', 'titulo' => 'Portal B2B IMPROGYP', 'subtitulo' => 'Precios y cotizaciones para distribuidores.', 'imagen' => 'ads_media/banner_1775835420_2.webp', 'cta_texto' => 'Acceder B2B', 'cta_url' => 'b2b/'],
            ]],
            ['tipo' => 'categorias', 'titulo' => 'Explorar por categoría', 'subtitulo' => 'Acceso directo al catálogo filtrado.', 'limite' => 8, 'activo' => true],
            ['tipo' => 'tendencias', 'titulo' => 'Tendencias', 'subtitulo' => 'Lo más visto en las últimas 48 horas.', 'limite' => 8, 'activo' => true],
            ['tipo' => 'mas_vendidos', 'titulo' => 'Más vendidos', 'subtitulo' => 'Selección impulsada por nuestro equipo comercial.', 'limite' => 8, 'activo' => true],
            ['tipo' => 'cta', 'activo' => true, 'etiqueta' => 'Asesoría', 'titulo' => '¿Listo para tu próximo proyecto?', 'subtitulo' => 'Explora el catálogo con asesoría técnica y cotiza por WhatsApp en minutos.', 'cta_texto' => 'Ir a la tienda', 'cta_url' => 'productos.php'],
            ['tipo' => 'blog', 'titulo' => 'Desde el Blog', 'subtitulo' => 'Guías y novedades para profesionales.', 'activo' => true],
            ['tipo' => 'logos', 'titulo' => 'Marcas aliadas', 'subtitulo' => 'Distribuidores oficiales que respaldan tu obra.', 'limite' => 10, 'activo' => true],
            ['tipo' => 'locales', 'titulo' => 'Red de sucursales', 'subtitulo' => 'Atención técnica en todo Ecuador.', 'activo' => true],
        ],
    ];
}

function improgyp_landing_config() {
    $path = dirname(__DIR__) . '/config_landing.json';
    $defaults = improgyp_landing_defaults();
    if (!file_exists($path)) return $defaults;
    $raw = json_decode(file_get_contents($path), true);
    if (!is_array($raw)) return $defaults;
    $hero = array_merge($defaults['hero'], $raw['hero'] ?? []);
    if (!empty($raw['hero_titulo']) && empty($raw['hero']['titulo_normal'])) {
        $hero['titulo_normal'] = $raw['hero_titulo'];
        $hero['titulo_resaltado'] = '';
    }
    if (!empty($raw['hero_subtitulo'])) $hero['subtitulo'] = $raw['hero_subtitulo'];
    if (!empty($raw['hero_cta_tienda'])) $hero['cta_tienda'] = $raw['hero_cta_tienda'];
    if (!empty($raw['hero_cta_b2b'])) $hero['cta_b2b'] = $raw['hero_cta_b2b'];
    $secciones = $raw['secciones'] ?? $defaults['secciones'];
    foreach ($secciones as &$sec) {
        if (($sec['tipo'] ?? '') === 'destacados') {
            $sec['tipo'] = 'mas_vendidos';
        }
        if (empty($sec['titulo_normal']) && !empty($sec['titulo'])) {
            $sec['titulo_normal'] = $sec['titulo'];
        }
    }
    unset($sec);
    return ['hero' => $hero, 'secciones' => $secciones];
}
