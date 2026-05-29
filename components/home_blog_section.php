<?php
/**
 * Sección blog en Home — vitrina grid (6) + modal con carga lazy.
 */
require_once __DIR__ . '/../lib/blog_layout_view.php';
require_once __DIR__ . '/../lib/landing_helpers.php';

$_bl_articulos = [];
$_bl_pdo = blog_get_pdo();
if ($_bl_pdo) {
    try {
        blog_ensure_table($_bl_pdo);
        blog_seed_if_empty($_bl_pdo);
        $_bl_articulos = blog_fetch_public((int) blog_home_fetch_limit(), false);
    } catch (Exception $e) {
        // silencioso
    }
}

$hBlog = improgyp_landing_section_heading($sec ?? []);
$blogNorm = $hBlog['normal'] !== '' ? htmlspecialchars($hBlog['normal']) : 'Desde el';
$blogRes = $hBlog['resalt'] !== '' ? htmlspecialchars($hBlog['resalt']) : 'Blog';
$headingHtml = $blogNorm . ' <span>' . $blogRes . '</span>';

$bl_prep = blog_layout_prepare($_bl_articulos, $base_url ?? '', [
    'heading_html' => $headingHtml,
    'show_view_all' => true,
    'open_in_modal' => true,
    'home_preview' => true,
    'section_id' => 'bl-home-section',
    'section_class_extra' => 'bl-section--home',
]);
if (!$bl_prep) {
    return;
}

$bl_accent = $bl_prep['accent'];
$bl_accent_rgb = $bl_prep['accent_rgb'];
$bl_font_css = $bl_prep['font_css'];
$bl_is_cyber = $bl_prep['is_cyber'];
$bl_heading_html = $bl_prep['heading_html'];
$bl_show_view_all = $bl_prep['show_view_all'];
$bl_section_id = $bl_prep['section_id'];
$bl_section_class_extra = $bl_prep['section_class_extra'];
$bl_js_cfg = $bl_prep['js_cfg'];
$bl_open_in_modal = $bl_prep['open_in_modal'];
$bl_stage_id = 'bl-stage';
$bl_pagination_id = 'bl-pagination';

include __DIR__ . '/blog_layout_shell.php';
