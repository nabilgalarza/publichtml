<?php
$etiqueta = $sec['etiqueta'] ?? 'IMPROGYP';
$titulo = $sec['titulo'] ?? '¿Listo para tu próximo proyecto?';
$sub = $sec['subtitulo'] ?? '';
$ctaText = $sec['cta_texto'] ?? 'Ir a la tienda';
$ctaUrl = $sec['cta_url'] ?? 'productos.php';
$ctaImg = trim($sec['imagen'] ?? '');
$ctaImgUrl = $ctaImg !== '' ? improgyp_landing_img_url($ctaImg, $base_url ?? '') : '';
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-10 md:py-14">
    <a href="<?= htmlspecialchars($ctaUrl) ?>" class="rompetrafico no-underline text-white <?= $ctaImgUrl ? 'rompetrafico-has-image' : 'rompetrafico-text-only' ?>">
        <div class="rt-copy-panel">
            <div class="rt-content">
                <span class="rt-glass-pill"><i class="fa-solid fa-bolt"></i> <?= htmlspecialchars($etiqueta) ?></span>
                <h2 class="rt-title"><?= htmlspecialchars($titulo) ?></h2>
                <?php if ($sub): ?><p class="rt-desc"><?= htmlspecialchars($sub) ?></p><?php endif; ?>
                <span class="rt-home-cta-btn">
                    <?= htmlspecialchars($ctaText) ?> <i class="fa-solid fa-arrow-right"></i>
                </span>
            </div>
        </div>
        <?php if ($ctaImgUrl): ?>
            <div class="rt-media-panel" aria-hidden="true">
                <img src="<?= htmlspecialchars($ctaImgUrl) ?>" alt="" loading="lazy" onerror="this.onerror=null;this.src='favicon-app.png?v=5';">
            </div>
        <?php endif; ?>
    </a>
</section>
