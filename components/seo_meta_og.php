<?php
/**
 * Open Graph + Twitter (requiere core_init: $seo_titulo, $seo_desc, $seo_img absoluta).
 * Opcional: $url_actual, $seo_og_v (cache-bust de imagen).
 */
$og_url = htmlspecialchars((string) ($url_actual ?? ''), ENT_QUOTES, 'UTF-8');
$og_title = htmlspecialchars((string) ($seo_titulo ?? 'IMPROGYP'), ENT_QUOTES, 'UTF-8');
$og_desc = htmlspecialchars((string) ($seo_desc ?? ''), ENT_QUOTES, 'UTF-8');
$og_alt = $og_title;
$og_image_base = (string) ($seo_img ?? '');
$og_v = isset($seo_og_v) ? (int) $seo_og_v : time();
$og_image_sep = (strpos($og_image_base, '?') !== false) ? '&' : '?';
$og_image = htmlspecialchars($og_image_base . $og_image_sep . 'v=' . $og_v, ENT_QUOTES, 'UTF-8');
?>
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $og_url ?>">
    <meta property="og:title" content="<?= $og_title ?>">
    <meta property="og:description" content="<?= $og_desc ?>">
    <meta property="og:image" content="<?= $og_image ?>">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= $og_alt ?>">
    <meta property="og:site_name" content="IMPROGYP">
    <meta property="og:locale" content="es_EC">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= $og_url ?>">
    <meta name="twitter:title" content="<?= $og_title ?>">
    <meta name="twitter:description" content="<?= $og_desc ?>">
    <meta name="twitter:image" content="<?= $og_image ?>">
