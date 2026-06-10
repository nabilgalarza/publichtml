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
        return '<div class="absolute top-[10px] left-[10px] bg-rose-500/90 backdrop-blur-md text-white text-[10px] font-black px-2 py-1 rounded-md shadow-lg z-10 flex items-center gap-1 border border-rose-400"><i class="fa-solid fa-fire text-yellow-300"></i> TENDENCIA</div>';
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

/** Iconos por defecto si no hay override en config_landing (retrocompat). */
function improgyp_landing_categoria_icon_defaults(): array
{
    return [
        'Accesorios' => 'fa-gears',
        'Herramientas Drywall' => 'fa-trowel-bricks',
        'Taladros Percutores' => 'fa-screwdriver-wrench',
        'Amoladoras' => 'fa-circle-notch',
        'Lijadoras de Paneles de Yeso' => 'fa-sheet-plastic',
        'Pulverizadores de Pintura' => 'fa-spray-can',
    ];
}

function improgyp_landing_normalize_icon_class(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return 'fa-tag';
    }
    if (preg_match('/^fa-[a-z0-9\-]+$/i', $raw)) {
        return strtolower($raw);
    }
    if (preg_match('/^[a-z0-9\-]+$/i', $raw)) {
        return 'fa-' . strtolower($raw);
    }
    return 'fa-tag';
}

/** @return array<string, int> categoría canónica => cantidad de productos */
function improgyp_landing_categorias_catalog_counts(): array
{
    $counts = [];
    foreach (improgyp_landing_load_catalogo() as $p) {
        $cat = trim($p['categoria'] ?? '');
        if ($cat === '') {
            continue;
        }
        $counts[$cat] = ($counts[$cat] ?? 0) + 1;
    }
    arsort($counts);
    return $counts;
}

/**
 * Tarjetas de categorías para el home (vitrina).
 * $sec = bloque categorias de config_landing (limite, overrides).
 *
 * @return list<array{nombre: string, nombre_canonico: string, count: int, icon: string, href: string}>
 */
function improgyp_landing_categorias(array $sec = []): array
{
    $limite = max(4, min(12, (int) ($sec['limite'] ?? 8)));
    $counts = improgyp_landing_categorias_catalog_counts();
    $overrides = is_array($sec['overrides'] ?? null) ? $sec['overrides'] : [];
    $defaults = improgyp_landing_categoria_icon_defaults();

    $candidates = [];
    foreach ($counts as $cat => $count) {
        $ov = is_array($overrides[$cat] ?? null) ? $overrides[$cat] : [];
        if (array_key_exists('visible', $ov) && $ov['visible'] === false) {
            continue;
        }
        $nombreVisible = trim((string) ($ov['nombre_visible'] ?? ''));
        $iconRaw = trim((string) ($ov['icono'] ?? ''));
        $icon = $iconRaw !== ''
            ? improgyp_landing_normalize_icon_class($iconRaw)
            : ($defaults[$cat] ?? 'fa-tag');
        $orden = isset($ov['orden']) && $ov['orden'] !== '' ? (int) $ov['orden'] : null;

        $candidates[] = [
            'nombre' => $nombreVisible !== '' ? $nombreVisible : $cat,
            'nombre_canonico' => $cat,
            'count' => $count,
            'icon' => $icon,
            'href' => 'productos.php?cat=' . rawurlencode($cat),
            '_orden' => $orden,
            '_count_sort' => $count,
        ];
    }

    usort($candidates, static function (array $a, array $b): int {
        $ao = $a['_orden'];
        $bo = $b['_orden'];
        if ($ao !== null && $bo !== null && $ao !== $bo) {
            return $ao <=> $bo;
        }
        if ($ao !== null && $bo === null) {
            return -1;
        }
        if ($ao === null && $bo !== null) {
            return 1;
        }
        return $b['_count_sort'] <=> $a['_count_sort'];
    });

    $result = array_slice($candidates, 0, $limite);
    foreach ($result as &$row) {
        unset($row['_orden'], $row['_count_sort']);
    }
    unset($row);

    return $result;
}

/** Datos para el editor del dashboard (todas las categorías del catálogo + huérfanos). */
function improgyp_landing_categorias_for_editor(): array
{
    $counts = improgyp_landing_categorias_catalog_counts();
    $sec = improgyp_landing_find_section(improgyp_landing_config()['secciones'], 'categorias') ?? [];
    $overrides = is_array($sec['overrides'] ?? null) ? $sec['overrides'] : [];
    $defaults = improgyp_landing_categoria_icon_defaults();

    $orphans = [];
    foreach (array_keys($overrides) as $key) {
        if (!isset($counts[$key])) {
            $orphans[] = $key;
        }
    }

    $rows = [];
    foreach ($counts as $cat => $count) {
        $ov = is_array($overrides[$cat] ?? null) ? $overrides[$cat] : [];
        $rows[] = [
            'canonico' => $cat,
            'count' => $count,
            'nombre_visible' => (string) ($ov['nombre_visible'] ?? ''),
            'icono' => (string) ($ov['icono'] ?? ($defaults[$cat] ?? '')),
            'visible' => !array_key_exists('visible', $ov) || $ov['visible'] !== false,
            'orden' => isset($ov['orden']) ? (string) $ov['orden'] : '',
        ];
    }

    usort($rows, static fn(array $a, array $b): int => $b['count'] <=> $a['count']);

    return ['rows' => $rows, 'orphans' => $orphans];
}

/** Persiste overrides de vitrina desde POST del editor home (no modifica inventario). */
function improgyp_landing_build_categorias_overrides_from_post(array $post): array
{
    $canonicos = $post['cat_canonico'] ?? [];
    if (!is_array($canonicos)) {
        return [];
    }

    $nombres = is_array($post['cat_nombre_visible'] ?? null) ? $post['cat_nombre_visible'] : [];
    $iconos = is_array($post['cat_icono'] ?? null) ? $post['cat_icono'] : [];
    $ordenes = is_array($post['cat_orden'] ?? null) ? $post['cat_orden'] : [];
    $visibles = is_array($post['cat_visible'] ?? null) ? $post['cat_visible'] : [];

    $overrides = [];
    foreach ($canonicos as $i => $cat) {
        $cat = trim((string) $cat);
        if ($cat === '') {
            continue;
        }

        $nombreVisible = trim((string) ($nombres[$i] ?? ''));
        $icono = trim((string) ($iconos[$i] ?? ''));
        $ordenRaw = trim((string) ($ordenes[$i] ?? ''));
        $visible = isset($visibles[$cat]);

        $entry = [];
        if ($nombreVisible !== '') {
            $entry['nombre_visible'] = $nombreVisible;
        }
        if ($icono !== '') {
            $entry['icono'] = improgyp_landing_normalize_icon_class($icono);
        }
        if ($ordenRaw !== '' && is_numeric($ordenRaw)) {
            $entry['orden'] = (int) $ordenRaw;
        }
        if (!$visible) {
            $entry['visible'] = false;
        }

        if ($entry !== []) {
            $overrides[$cat] = $entry;
        }
    }

    return $overrides;
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

/** Tipos con encabezados láser en el Editor del Home (Apariencia). */
function improgyp_landing_tipos_encabezado_secciones(): array
{
    return ['categorias', 'tendencias', 'mas_vendidos', 'logos', 'blog'];
}

function improgyp_landing_find_section(array $secciones, string $tipo): ?array
{
    foreach ($secciones as $s) {
        $t = $s['tipo'] ?? '';
        if ($t === $tipo || ($tipo === 'mas_vendidos' && $t === 'destacados')) {
            return $s;
        }
    }
    return null;
}

/**
 * Portada: actualiza activo/límite/etc. sin pisar titulo_normal, titulo_resaltado ni subtitulo.
 */
function improgyp_landing_merge_portada_section(?array $prev, array $updates): array
{
    $tipo = $updates['tipo'] ?? '';
    $base = is_array($prev) ? $prev : ['tipo' => $tipo];
    $base['tipo'] = $tipo;

    if (in_array($tipo, improgyp_landing_tipos_encabezado_secciones(), true)) {
        if (array_key_exists('activo', $updates)) {
            $base['activo'] = (bool) $updates['activo'];
        }
        if (isset($updates['limite'])) {
            $base['limite'] = $updates['limite'];
        }
        if (isset($updates['marquee_seg'])) {
            $base['marquee_seg'] = $updates['marquee_seg'];
        }
        if (($tipo ?? '') === 'categorias') {
            if (array_key_exists('overrides', $updates) && is_array($updates['overrides'])) {
                $base['overrides'] = $updates['overrides'];
            }
            if (isset($updates['modo'])) {
                $base['modo'] = $updates['modo'];
            }
        }
        return $base;
    }

    return array_merge($base, $updates);
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

/**
 * Logos de fabricantes aliados (archivos en /logos_marcas).
 * @return list<array{src: string, alt: string}>
 */
function improgyp_landing_marcas_logos(): array
{
    $dir = dirname(__DIR__) . '/logos_marcas';
    if (!is_dir($dir)) {
        return [];
    }
    $extOk = ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif'];
    $found = [];
    foreach (scandir($dir) ?: [] as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, $extOk, true)) {
            continue;
        }
        if (preg_match('/^c7-.+/i', $file) && is_file($dir . '/c7.png')) {
            continue;
        }
        $found[] = $file;
    }
    usort($found, 'strnatcasecmp');
    $out = [];
    foreach ($found as $file) {
        $base = pathinfo($file, PATHINFO_FILENAME);
        $out[] = [
            'src' => 'logos_marcas/' . $file,
            'alt' => preg_replace('/^c(\d+)$/i', 'Marca aliada $1', $base) ?: $base,
        ];
    }
    return $out;
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
            ['tipo' => 'cta', 'activo' => true, 'etiqueta' => 'Asesoría', 'titulo' => '¿Listo para tu próximo proyecto?', 'subtitulo' => 'Explora el catálogo con asesoría técnica y cotiza por WhatsApp en minutos.', 'cta_texto' => 'Ir a la tienda', 'cta_url' => 'productos.php', 'imagen' => ''],
            ['tipo' => 'blog', 'titulo' => 'Desde el Blog', 'subtitulo' => 'Guías y novedades para profesionales.', 'activo' => true],
            ['tipo' => 'logos', 'titulo' => 'Marcas aliadas', 'subtitulo' => 'Distribuidores oficiales que respaldan tu obra.', 'limite' => 10, 'marquee_seg' => 50, 'activo' => true],
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
