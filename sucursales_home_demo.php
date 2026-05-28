<?php
$seo_titulo = 'Demo sucursales home | IMPROGYP';
$seo_desc = 'Tres direcciones creativas: Concierge, Red logística y Showroom.';
require_once __DIR__ . '/core_init.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($seo_titulo, ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="favicon-app.png?v=5">
    <?php include __DIR__ . '/components/landing_styles.php'; ?>
    <link rel="stylesheet" href="css/sucursales_home_demo.css?v=<?= time() ?>">
</head>
<body class="suc-demo-page">
    <header class="suc-demo-header">
        <div>
            <h1>Demo · Red de sucursales</h1>
            <p>Las 3 direcciones creativas en una sola página · <a href="index.php">Home producción</a></p>
        </div>
    </header>

    <nav class="suc-demo-jump" aria-label="Ir a variante">
        <a href="#dir-concierge" class="suc-demo-jump-link is-active" data-jump="concierge">1 · Concierge</a>
        <a href="#dir-logistica" class="suc-demo-jump-link" data-jump="logistica">2 · Logística</a>
        <a href="#dir-showroom" class="suc-demo-jump-link" data-jump="showroom">3 · Showroom</a>
    </nav>

    <div class="suc-demo-stack">
        <div class="suc-demo-block" id="dir-concierge">
            <div class="suc-demo-block-label">
                <span class="suc-demo-block-num">01</span>
                <div>
                    <strong>Concierge B2B</strong>
                    <span>Mapa + card glass + CTA navy · Modal banda única</span>
                </div>
            </div>
            <div id="panel-concierge"></div>
        </div>

        <div class="suc-demo-block" id="dir-logistica">
            <div class="suc-demo-block-label suc-demo-block-label--log">
                <span class="suc-demo-block-num">02</span>
                <div>
                    <strong>Red logística</strong>
                    <span>Lista densa + filtros · Modal tabla</span>
                </div>
            </div>
            <div id="panel-logistica"></div>
        </div>

        <div class="suc-demo-block" id="dir-showroom">
            <div class="suc-demo-block-label suc-demo-block-label--show">
                <span class="suc-demo-block-num">03</span>
                <div>
                    <strong>Showroom local</strong>
                    <span>Foto + horario · Modal cards visuales</span>
                </div>
            </div>
            <div id="panel-showroom"></div>
        </div>

        <details class="suc-demo-ref">
            <summary>Referencia: diseño actual en producción (index.php)</summary>
            <div id="panel-actual"></div>
        </details>
    </div>

    <div class="suc-demo-modal-overlay hidden" id="suc-demo-modal" aria-hidden="true">
        <div class="suc-demo-modal" role="dialog" aria-modal="true" aria-labelledby="suc-demo-modal-title" onclick="event.stopPropagation()">
            <button type="button" class="suc-demo-modal-close" id="suc-demo-modal-close" aria-label="Cerrar">&times;</button>
            <div id="suc-demo-modal-body"></div>
        </div>
    </div>

    <script src="js/sucursales_home_demo.js?v=<?= time() ?>"></script>
</body>
</html>
