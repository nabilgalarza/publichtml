<?php
/**
 * Demo Home — misma estructura que index.php, capa visual más color e imágenes.
 * URL: demo_home_pro.php
 */
$improgyp_page = 'home';
require_once __DIR__ . '/core_init.php';
require_once __DIR__ . '/lib/landing_helpers.php';

improgyp_landing_ensure_ranking_cache();
$landing_cfg = improgyp_landing_config();
$hero = $landing_cfg['hero'];
$secciones = $landing_cfg['secciones'];

// Más slides con imágenes reales (misma UI de slider)
foreach ($secciones as &$secDemo) {
    if (($secDemo['tipo'] ?? '') !== 'slider') {
        continue;
    }
    $slides = $secDemo['slides'] ?? [];
    if (count($slides) < 3) {
        $slides[] = [
            'etiqueta' => 'Asesoría',
            'titulo' => 'Tu proyecto con respaldo técnico',
            'subtitulo' => 'Pregunta al asesor IA en el catálogo y cotiza por WhatsApp.',
            'imagen' => 'ads_media/cta_1779978340.webp',
            'cta_texto' => 'Ir a la tienda',
            'cta_url' => 'productos.php',
        ];
    }
    $secDemo['slides'] = $slides;
    break;
}
unset($secDemo);
?>
<!DOCTYPE html>
<html lang="es" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Demo Home — IMPROGYP (más color)</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#1B263B">
    <link rel="icon" type="image/png" href="favicon-app.png?v=5">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>var IMPROGYP_BASE_URL = <?= json_encode($base_url ?? '') ?>;</script>
    <?php include __DIR__ . '/components/landing_styles.php'; ?>
    <?php include __DIR__ . '/components/demo_home_vivid_styles.php'; ?>
</head>
<body class="antialiased landing-page demo-home-vivid">
<div class="demo-home-vivid-banner" role="status">
    <span>Vista demo — mismo home que producción, con más color e imágenes</span>
    <a href="index.php">Ver producción</a>
</div>
<?php include __DIR__ . '/components/header.php'; ?>

<main class="pt-28 md:pt-36 pb-28 md:pb-0 demo-home-vivid-main">
    <?php if (!empty($hero['activo'])): ?>
    <?php include __DIR__ . '/components/landing_hero.php'; ?>
    <?php endif; ?>
    <?php include __DIR__ . '/components/landing_sections.php'; ?>
</main>

<?php include __DIR__ . '/components/product_modal.php'; ?>
<?php include __DIR__ . '/components/footer.php'; ?>
<script>window.IMPROGYP_METRICS_PAGE = 'home_demo';</script>
<script src="js/improgyp_metrics.js?v=<?= time() ?>"></script>
<script src="js/header_actions.js?v=<?= time() ?>"></script>
<script src="js/wishlist_dropdown.js?v=<?= time() ?>"></script>
<script src="js/omnibar.js?v=<?= time() ?>"></script>
<script src="js/landing_header.js?v=<?= time() ?>"></script>
<script src="js/locales_showroom.js?v=<?= time() ?>"></script>
<script src="js/landing_home.js?v=<?= time() ?>"></script>
<script src="js/landing_products.js?v=<?= time() ?>"></script>
<script src="js/landing_carousel.js?v=<?= time() ?>"></script>
</body>
</html>
