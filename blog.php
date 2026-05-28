<?php
$improgyp_page = 'blog';
require_once __DIR__ . '/core_init.php';
require_once __DIR__ . '/lib/blog_helpers.php';
require_once __DIR__ . '/lib/blog_layout_view.php';

$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['slug'])) : '';
$articulo = $slug !== '' ? blog_fetch_by_slug($slug) : null;

$cfg = blog_layout_load_config();
$accent = $cfg['accent'] ?? '#3A86FF';

$pdo = blog_get_pdo();
if ($pdo) {
    blog_ensure_table($pdo);
    blog_seed_if_empty($pdo);
}

$articulos_lista = $articulo ? [] : blog_fetch_public(50);
$relacionados = [];
if ($articulo && $pdo) {
    $stmt = $pdo->prepare(
        "SELECT id, titulo, slug, categoria, tiempo_lectura, resumen, portada, fecha, visitas
         FROM improgyp_blog WHERE borrador = 0 AND id != ? ORDER BY id DESC LIMIT 12"
    );
    $stmt->execute([(int) $articulo['id']]);
    $relacionados = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $articulo ? htmlspecialchars($articulo['titulo']) . ' | ' : '' ?>Blog | IMPROGYP</title>
    <meta name="description" content="<?= htmlspecialchars($articulo ? ($articulo['resumen'] ?? '') : 'Noticias y guías técnicas IMPROGYP') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body{font-family:Inter,system-ui,sans-serif;background:#f8fafc}</style>
    <?php include __DIR__ . '/components/landing_styles.php'; ?>
</head>
<body class="antialiased">
<?php include __DIR__ . '/components/header.php'; ?>

<?php if ($articulo): ?>
<main class="pt-28 max-w-[900px] mx-auto px-6 pb-28 md:pb-8">
    <a href="blog.php" class="text-sm font-bold text-slate-400 hover:text-[#3A86FF] mb-6 inline-block">← Volver al blog</a>
    <article class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-sm mb-4">
        <?php $img = blog_img_url($articulo['portada'] ?? '', $base_url ?? ''); ?>
        <img src="<?= htmlspecialchars($img) ?>" alt="" class="w-full h-56 md:h-72 object-cover bg-slate-100">
        <div class="p-8 md:p-12">
            <span class="text-[10px] font-black uppercase tracking-widest" style="color:<?= htmlspecialchars($accent) ?>"><?= htmlspecialchars($articulo['categoria'] ?? '') ?></span>
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 mt-2 mb-4"><?= htmlspecialchars($articulo['titulo']) ?></h1>
            <p class="text-xs text-slate-400 font-bold mb-8">
                <?= htmlspecialchars($articulo['tiempo_lectura'] ?? '') ?>
                · <?= date('d M Y', strtotime($articulo['fecha'])) ?>
                · <?= (int) ($articulo['visitas'] ?? 0) ?> visitas
            </p>
            <?php if (!empty($articulo['resumen'])): ?>
            <p class="text-lg text-slate-600 font-medium mb-8 border-l-4 pl-4" style="border-color:<?= htmlspecialchars($accent) ?>"><?= htmlspecialchars($articulo['resumen']) ?></p>
            <?php endif; ?>
            <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed">
                <?= $articulo['contenido'] ?? '' ?>
            </div>
        </div>
    </article>
</main>
<?php
    $bl_prep = blog_layout_prepare($relacionados, $base_url ?? '', [
        'heading_html' => 'También te <span>interesa</span>',
        'show_view_all' => true,
        'section_id' => 'bl-related-section',
        'section_class_extra' => '',
    ]);
    if ($bl_prep) {
        $bl_accent = $bl_prep['accent'];
        $bl_accent_rgb = $bl_prep['accent_rgb'];
        $bl_font_css = $bl_prep['font_css'];
        $bl_is_cyber = $bl_prep['is_cyber'];
        $bl_heading_html = $bl_prep['heading_html'];
        $bl_show_view_all = $bl_prep['show_view_all'];
        $bl_section_id = $bl_prep['section_id'];
        $bl_section_class_extra = $bl_prep['section_class_extra'];
        $bl_js_cfg = $bl_prep['js_cfg'];
        $bl_stage_id = 'bl-rel-stage';
        $bl_pagination_id = 'bl-rel-pagination';
        include __DIR__ . '/components/blog_layout_shell.php';
    }
?>
<?php else: ?>
<?php
    $bl_prep = blog_layout_prepare($articulos_lista, $base_url ?? '', [
        'heading_html' => 'Blog <span>IMPROGYP</span>',
        'show_view_all' => false,
        'section_id' => 'bl-page-section',
        'section_class_extra' => 'bl-section--page',
    ]);
?>
<main class="pt-20 pb-28 md:pb-8">
    <?php if (!$bl_prep): ?>
    <div class="max-w-[900px] mx-auto px-6 pb-16 pt-12">
        <div class="bg-white rounded-3xl border border-slate-100 p-12 text-center text-slate-500">
            <i class="fa-solid fa-pen-nib text-3xl text-slate-200 mb-4 block"></i>
            <p class="font-bold">Próximamente publicaciones.</p>
            <p class="text-sm mt-2">Crea artículos en el <a href="dashboard.php?view=blog" class="text-[#3A86FF] font-bold">Gestor de Blog</a>.</p>
            <a href="productos.php" class="inline-block mt-6 text-[#3A86FF] font-black text-sm">Ir a la tienda →</a>
        </div>
    </div>
    <?php else:
        $bl_accent = $bl_prep['accent'];
        $bl_accent_rgb = $bl_prep['accent_rgb'];
        $bl_font_css = $bl_prep['font_css'];
        $bl_is_cyber = $bl_prep['is_cyber'];
        $bl_heading_html = $bl_prep['heading_html'];
        $bl_show_view_all = $bl_prep['show_view_all'];
        $bl_section_id = $bl_prep['section_id'];
        $bl_section_class_extra = $bl_prep['section_class_extra'];
        $bl_js_cfg = $bl_prep['js_cfg'];
        $bl_stage_id = 'bl-page-stage';
        $bl_pagination_id = 'bl-page-pagination';
        include __DIR__ . '/components/blog_layout_shell.php';
    endif; ?>
</main>
<?php endif; ?>

<?php include __DIR__ . '/components/footer.php'; ?>
<script>window.IMPROGYP_METRICS_PAGE = 'blog';</script>
<script src="js/improgyp_metrics.js?v=<?= time() ?>"></script>
<script src="js/header_actions.js?v=<?= time() ?>"></script>
<script src="js/omnibar.js?v=<?= time() ?>"></script>
<script src="js/landing_header.js?v=<?= time() ?>"></script>
<script src="js/landing_home.js?v=<?= time() ?>"></script>
<script src="js/cart_checkout.js?v=<?= time() ?>"></script>
<script src="js/checkout_wa.js?v=<?= time() ?>"></script>
</body>
</html>
