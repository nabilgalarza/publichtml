<?php
/**
 * Persistencia de config_landing.json desde el dashboard.
 */
require_once __DIR__ . '/landing_helpers.php';

/**
 * Aplica titulo_normal, titulo_resaltado y subtitulo desde arrays POST (editor home).
 */
function improgyp_landing_apply_encabezado_post(array $sec, string $key, array $titulosN, array $titulosR, array $subs): array
{
    if (isset($titulosN[$key])) {
        $sec['titulo_normal'] = trim((string) $titulosN[$key]);
        $sec['titulo'] = $sec['titulo_normal'];
    }
    if (isset($titulosR[$key])) {
        $sec['titulo_resaltado'] = trim((string) $titulosR[$key]);
    }
    if (isset($subs[$key])) {
        $sec['subtitulo'] = trim((string) $subs[$key]);
    }
    return $sec;
}

/**
 * Portada: estructura; opcionalmente encabezados láser si $applyEncabezados.
 *
 * @return array{hero: array, secciones: array}
 */
function improgyp_landing_build_payload_from_post(array $post, array $files, bool $applyEncabezados = false): array
{
    $landingPrev = improgyp_landing_config();
    $prevSecciones = $landingPrev['secciones'];
    $secciones = [];

    $titulosN = $post['titulo_normal'] ?? [];
    $titulosR = $post['titulo_resaltado'] ?? [];
    $subs = $post['subtitulo'] ?? [];

    $mergeLaser = static function (?array $prev, array $updates, string $key) use ($applyEncabezados, $titulosN, $titulosR, $subs): array {
        $merged = improgyp_landing_merge_portada_section($prev, $updates);
        if ($applyEncabezados) {
            $merged = improgyp_landing_apply_encabezado_post($merged, $key, $titulosN, $titulosR, $subs);
        }
        return $merged;
    };

    if (isset($post['sec_slider_activo'])) {
        $slides = [];
        for ($si = 1; $si <= 3; $si++) {
            $tit = trim($post["slider_{$si}_titulo"] ?? '');
            if ($tit === '') {
                continue;
            }
            $slides[] = [
                'etiqueta' => trim($post["slider_{$si}_etiqueta"] ?? ''),
                'titulo' => $tit,
                'subtitulo' => trim($post["slider_{$si}_subtitulo"] ?? ''),
                'imagen' => trim($post["slider_{$si}_imagen"] ?? ''),
                'cta_texto' => trim($post["slider_{$si}_cta_texto"] ?? 'Ver más'),
                'cta_url' => trim($post["slider_{$si}_cta_url"] ?? 'productos.php'),
            ];
        }
        if ($slides) {
            $secciones[] = [
                'tipo' => 'slider',
                'activo' => true,
                'slides' => $slides,
                'autoplay' => isset($post['slider_autoplay']),
                'intervalo_ms' => max(3000, (int) ($post['slider_intervalo_ms'] ?? 6000)),
            ];
        }
    }

    $secciones[] = $mergeLaser(
        improgyp_landing_find_section($prevSecciones, 'categorias'),
        [
            'tipo' => 'categorias',
            'activo' => isset($post['sec_categorias_activo']),
            'limite' => max(4, min(12, (int) ($post['sec_categorias_limite'] ?? 8))),
        ],
        'categorias'
    );
    $secciones[] = $mergeLaser(
        improgyp_landing_find_section($prevSecciones, 'tendencias'),
        [
            'tipo' => 'tendencias',
            'activo' => isset($post['sec_tendencias_activo']),
            'limite' => max(4, min(12, (int) ($post['sec_tendencias_limite'] ?? 8))),
        ],
        'tendencias'
    );
    $secciones[] = $mergeLaser(
        improgyp_landing_find_section($prevSecciones, 'mas_vendidos'),
        [
            'tipo' => 'mas_vendidos',
            'activo' => isset($post['sec_mas_vendidos_activo']),
            'limite' => max(4, min(12, (int) ($post['sec_mas_vendidos_limite'] ?? 8))),
        ],
        'mas_vendidos'
    );

    if (isset($post['sec_cta_activo'])) {
        $ctaImgUrl = trim($post['sec_cta_img_url_actual'] ?? '');
        if (isset($files['sec_cta_imagen']) && ($files['sec_cta_imagen']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $tmpName = $files['sec_cta_imagen']['tmp_name'];
            $imgGd = @imagecreatefromstring(file_get_contents($tmpName));
            if ($imgGd !== false) {
                $dir = dirname(__DIR__) . '/ads_media';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $nombreArchivo = 'cta_' . time() . '.webp';
                if (imagewebp($imgGd, $dir . '/' . $nombreArchivo, 85)) {
                    if ($ctaImgUrl !== '' && function_exists('borrarFotoFisica')) {
                        borrarFotoFisica($ctaImgUrl);
                    }
                    $ctaImgUrl = 'ads_media/' . $nombreArchivo;
                }
                imagedestroy($imgGd);
            }
        }
        $secciones[] = [
            'tipo' => 'cta',
            'activo' => true,
            'etiqueta' => trim($post['sec_cta_etiqueta'] ?? 'Asesoría'),
            'titulo' => trim($post['sec_cta_titulo'] ?? ''),
            'subtitulo' => trim($post['sec_cta_subtitulo'] ?? ''),
            'cta_texto' => trim($post['sec_cta_texto'] ?? 'Ir a la tienda'),
            'cta_url' => trim($post['sec_cta_url'] ?? 'productos.php'),
            'imagen' => $ctaImgUrl,
        ];
    }

    $secciones[] = $mergeLaser(
        improgyp_landing_find_section($prevSecciones, 'blog'),
        [
            'tipo' => 'blog',
            'activo' => isset($post['sec_blog_activo']),
        ],
        'blog'
    );
    $secciones[] = $mergeLaser(
        improgyp_landing_find_section($prevSecciones, 'logos'),
        [
            'tipo' => 'logos',
            'activo' => isset($post['sec_logos_activo']),
            'limite' => max(4, min(20, (int) ($post['sec_logos_limite'] ?? 10))),
        ],
        'logos'
    );

    $localesPrev = improgyp_landing_find_section($prevSecciones, 'locales') ?? ['tipo' => 'locales'];
    $secciones[] = array_merge($localesPrev, [
        'tipo' => 'locales',
        'titulo' => trim($post['sec_locales_titulo'] ?? $localesPrev['titulo'] ?? 'Red de sucursales'),
        'subtitulo' => trim($post['sec_locales_subtitulo'] ?? $localesPrev['subtitulo'] ?? ''),
        'activo' => isset($post['sec_locales_activo']),
    ]);

    return [
        'hero' => [
            'activo' => isset($post['hero_activo']),
            'badge' => trim($post['hero_badge'] ?? ''),
            'titulo_normal' => trim($post['hero_titulo_normal'] ?? ''),
            'titulo_resaltado' => trim($post['hero_titulo_resaltado'] ?? ''),
            'subtitulo' => trim($post['hero_subtitulo'] ?? ''),
            'cta_tienda' => trim($post['hero_cta_tienda'] ?? 'Explorar tienda'),
            'cta_tienda_url' => 'productos.php',
            'cta_b2b' => trim($post['hero_cta_b2b'] ?? 'Portal mayoristas'),
            'cta_b2b_url' => 'b2b/',
            'imagen' => trim($post['hero_imagen'] ?? ''),
        ],
        'secciones' => $secciones,
    ];
}

function improgyp_landing_write_config(array $payload): void
{
    $path = dirname(__DIR__) . '/config_landing.json';
    file_put_contents(
        $path,
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
}

/** Solo encabezados láser sobre el JSON actual (compat. formulario antiguo). */
function improgyp_landing_save_encabezados_only(array $post): void
{
    $landing = improgyp_landing_config();
    $titulosN = $post['titulo_normal'] ?? [];
    $titulosR = $post['titulo_resaltado'] ?? [];
    $subs = $post['subtitulo'] ?? [];

    foreach ($landing['secciones'] as &$sec) {
        $tipo = $sec['tipo'] ?? '';
        if ($tipo === 'destacados') {
            $tipo = 'mas_vendidos';
        }
        if (!in_array($tipo, improgyp_landing_tipos_encabezado_secciones(), true) && !isset($titulosN[$tipo])) {
            continue;
        }
        $sec = improgyp_landing_apply_encabezado_post($sec, $tipo, $titulosN, $titulosR, $subs);
    }
    unset($sec);
    improgyp_landing_write_config($landing);
}
