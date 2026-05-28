<?php
$improgyp_page = 'home';
require_once __DIR__ . '/core_init.php';
require_once __DIR__ . '/lib/landing_helpers.php';

improgyp_landing_ensure_ranking_cache();
$landing_cfg = improgyp_landing_config();
$hero = $landing_cfg['hero'];
$secciones = $landing_cfg['secciones'];
?>
<!DOCTYPE html>
<html lang="es" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($seo_titulo) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seo_desc) ?>">
    <?php include __DIR__ . '/components/seo_meta_og.php'; ?>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#1B263B">
    <link rel="icon" type="image/png" href="favicon-app.png?v=5">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>var IMPROGYP_BASE_URL = <?= json_encode($base_url ?? '') ?>;</script>
    <?php include __DIR__ . '/components/landing_styles.php'; ?>
</head>
<body class="antialiased landing-page">
<?php include __DIR__ . '/components/header.php'; ?>

<main class="pt-28 md:pt-36 pb-28 md:pb-0">
    <?php if (!empty($hero['activo'])): ?>
    <?php include __DIR__ . '/components/landing_hero.php'; ?>
    <?php endif; ?>
    <?php include __DIR__ . '/components/landing_sections.php'; ?>
</main>

<?php include __DIR__ . '/components/product_modal.php'; ?>
<?php include __DIR__ . '/components/footer.php'; ?>
<?php if (!empty($prod_compartido)):
    $openIdent = !empty($prod_compartido['codigo']) ? $prod_compartido['codigo'] : ($prod_compartido['nombre'] ?? '');
?>
<script>window.IMPROGYP_OPEN_PRODUCT = <?= json_encode($openIdent, JSON_UNESCAPED_UNICODE) ?>;</script>
<?php endif; ?>
<script>window.IMPROGYP_METRICS_PAGE = 'home';</script>
<script src="js/improgyp_metrics.js?v=<?= time() ?>"></script>
<script src="js/header_actions.js?v=<?= time() ?>"></script>
<script src="js/omnibar.js?v=<?= time() ?>"></script>
<script src="js/landing_header.js?v=<?= time() ?>"></script>
<script src="js/locales_showroom.js?v=<?= time() ?>"></script>
<script src="js/landing_home.js?v=<?= time() ?>"></script>
<script src="js/landing_products.js?v=<?= time() ?>"></script>
<script src="js/landing_carousel.js?v=<?= time() ?>"></script>
</body>
</html>
