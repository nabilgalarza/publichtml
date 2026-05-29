<?php
$improgyp_page = 'blog';
require_once __DIR__ . '/core_init.php';
require_once __DIR__ . '/lib/blog_helpers.php';
require_once __DIR__ . '/lib/blog_layout_view.php';
require_once __DIR__ . '/lib/blog_seo.php';

$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['slug'])) : '';
$articulo = $slug !== '' ? blog_fetch_by_slug($slug) : null;

$cfg = blog_layout_load_config();
$accent = $cfg['accent'] ?? '#3A86FF';
$showDate = !empty($cfg['showDate']);
$showReadTime = !empty($cfg['showReadTime']);
$showViews = !empty($cfg['showViews']);
$fontMap = [
    'sans'  => "'Outfit', 'Plus Jakarta Sans', system-ui, sans-serif",
    'serif' => "'Merriweather', Georgia, serif",
    'mono'  => "'Courier New', monospace",
];
$blogFont = $fontMap[$cfg['font'] ?? 'sans'] ?? $fontMap['sans'];

$pdo = blog_get_pdo();
if ($pdo) {
    blog_ensure_table($pdo);
    blog_seed_if_empty($pdo);
}

$articulos_lista = $articulo ? [] : blog_fetch_public(60, false);
$relacionados = [];
if ($articulo && $pdo) {
    $stmt = $pdo->prepare(
        "SELECT id, titulo, slug, categoria, tiempo_lectura, resumen, portada, fecha, visitas
         FROM improgyp_blog WHERE borrador = 0 AND id != ? ORDER BY id DESC LIMIT 12"
    );
    $stmt->execute([(int) $articulo['id']]);
    $relacionados = $stmt->fetchAll();
}

$articleImg = $articulo ? blog_img_url($articulo['portada'] ?? '', $base_url ?? '') : '';
$articleCanonical = $articulo ? blog_seo_canonical_url($articulo['slug'], $base_url ?? '') : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($articulo): ?>
        <?php blog_seo_render_article_head($articulo, $base_url ?? ''); ?>
    <?php else: ?>
        <title>Blog | IMPROGYP</title>
        <meta name="description" content="Noticias, guías técnicas y novedades de herramientas y drywall — IMPROGYP Ecuador.">
        <link rel="canonical" href="<?= htmlspecialchars(blog_seo_absolute_url('blog.php', $base_url ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;900&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <script>var IMPROGYP_BASE_URL = <?= json_encode($base_url ?? '', JSON_UNESCAPED_SLASHES) ?>;</script>
    <style>
        body { font-family: <?= $blogFont ?>; background: #f8fafc; }
        .blog-article-share { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; }
        .blog-article-share a, .blog-article-share button {
            display: inline-flex; align-items: center; gap: 8px; font-size: .8rem; font-weight: 800;
            border-radius: 12px; padding: 10px 16px; border: none; cursor: pointer; text-decoration: none;
        }
        .blog-share-wa { background: #25d366; color: #fff; }
        .blog-share-wa:hover { background: #1ebe57; color: #fff; }
        .blog-share-copy { background: #f1f5f9; color: #475569; }
        .blog-share-copy:hover { background: #e2e8f0; }
    </style>
    <?php include __DIR__ . '/components/landing_styles.php'; ?>
</head>
<body class="antialiased">
<?php include __DIR__ . '/components/header.php'; ?>

<?php if ($articulo): ?>
<main class="pt-28 max-w-[900px] mx-auto px-6 pb-28 md:pb-8">
    <nav class="text-sm font-bold text-slate-400 mb-6 flex flex-wrap items-center gap-2" aria-label="Migas de pan">
        <a href="index.php" class="hover:text-[#3A86FF]">Inicio</a>
        <span aria-hidden="true">›</span>
        <a href="blog.php" class="hover:text-[#3A86FF]">Blog</a>
        <span aria-hidden="true">›</span>
        <span class="text-slate-600 truncate max-w-[200px] md:max-w-none"><?= htmlspecialchars($articulo['titulo']) ?></span>
    </nav>
    <a href="blog.php" id="blog-back-link" class="text-sm font-bold text-slate-400 hover:text-[#3A86FF] mb-4 inline-block">← Volver al blog</a>
    <article class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-sm mb-4" itemscope itemtype="https://schema.org/Article">
        <img src="<?= htmlspecialchars($articleImg) ?>" alt="<?= htmlspecialchars($articulo['titulo']) ?>" class="w-full h-56 md:h-72 object-cover bg-slate-100" itemprop="image">
        <div class="p-8 md:p-12">
            <span class="text-[10px] font-black uppercase tracking-widest" style="color:<?= htmlspecialchars($accent) ?>"><?= htmlspecialchars($articulo['categoria'] ?? '') ?></span>
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 mt-2 mb-4" itemprop="headline"><?= htmlspecialchars($articulo['titulo']) ?></h1>
            <p class="text-xs text-slate-400 font-bold mb-8 flex flex-wrap gap-x-2 gap-y-1">
                <?php if ($showReadTime && !empty($articulo['tiempo_lectura'])): ?>
                    <span><?= htmlspecialchars($articulo['tiempo_lectura']) ?></span>
                <?php endif; ?>
                <?php if ($showDate): ?>
                    <span>· <?= date('d M Y', strtotime($articulo['fecha'])) ?></span>
                <?php endif; ?>
                <?php if ($showViews): ?>
                    <span>· <?= (int) ($articulo['visitas'] ?? 0) ?> visitas</span>
                <?php endif; ?>
            </p>
            <?php if (!empty($articulo['resumen'])): ?>
            <p class="text-lg text-slate-600 font-medium mb-8 border-l-4 pl-4" style="border-color:<?= htmlspecialchars($accent) ?>" itemprop="description"><?= htmlspecialchars($articulo['resumen']) ?></p>
            <?php endif; ?>
            <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed" itemprop="articleBody">
                <?= $articulo['contenido'] ?? '' ?>
            </div>
            <div class="blog-article-share">
                <button type="button" class="blog-share-wa" id="blog-article-wa" data-slug="<?= htmlspecialchars($articulo['slug']) ?>" data-title="<?= htmlspecialchars($articulo['titulo']) ?>" data-resumen="<?= htmlspecialchars($articulo['resumen'] ?? '') ?>">
                    <i class="fa-brands fa-whatsapp"></i> Compartir en WhatsApp
                </button>
                <button type="button" class="blog-share-copy" id="blog-article-copy" data-url="<?= htmlspecialchars($articleCanonical) ?>">
                    <i class="fa-solid fa-link"></i> Copiar enlace
                </button>
            </div>
        </div>
    </article>
</main>
<script>
(function () {
    const back = document.getElementById('blog-back-link');
    if (back) {
        try {
            const ref = document.referrer || '';
            if (ref && (/\/index\.php/i.test(ref) || /publichtml\/?$/i.test(ref) || ref.indexOf('index.php') !== -1)) {
                back.href = 'index.php#bl-home-section';
                back.textContent = '← Volver al inicio';
            }
        } catch (e) { /* ignore */ }
    }
    const wa = document.getElementById('blog-article-wa');
    if (wa) {
        wa.addEventListener('click', () => {
            const slug = wa.dataset.slug || '';
            const title = wa.dataset.title || 'IMPROGYP';
            const resumen = wa.dataset.resumen || '';
            const url = new URL('blog.php', window.location.href);
            url.searchParams.set('slug', slug);
            const text = title + '\n' + (resumen ? resumen.substring(0, 140) + '\n' : '') + url.toString();
            window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank', 'noopener,noreferrer');
        });
    }
    const copyBtn = document.getElementById('blog-article-copy');
    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            const url = copyBtn.dataset.url || window.location.href;
            const done = () => {
                copyBtn.innerHTML = '<i class="fa-solid fa-check"></i> Copiado';
                setTimeout(() => { copyBtn.innerHTML = '<i class="fa-solid fa-link"></i> Copiar enlace'; }, 2200);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done).catch(() => {});
            } else {
                window.prompt('Copia este enlace:', url);
            }
        });
    }
})();
</script>
<?php
    $bl_prep = blog_layout_prepare($relacionados, $base_url ?? '', [
        'heading_html' => 'También te <span>interesa</span>',
        'show_view_all' => true,
        'archive_mode' => true,
        'per_page' => 3,
        'open_in_modal' => false,
        'section_id' => 'bl-related-section',
        'section_class_extra' => 'bl-section--archive',
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
        'archive_mode' => true,
        'per_page' => blog_archive_per_page(),
        'open_in_modal' => false,
        'section_id' => 'bl-page-section',
        'section_class_extra' => 'bl-section--page bl-section--archive',
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
<script src="js/locales_showroom.js?v=<?= time() ?>"></script>
<script src="js/landing_home.js?v=<?= time() ?>"></script>
</body>
</html>
