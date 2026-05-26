<?php
$etiqueta = $sec['etiqueta'] ?? 'IMPROGYP';
$titulo = $sec['titulo'] ?? '¿Listo para tu próximo proyecto?';
$sub = $sec['subtitulo'] ?? '';
$ctaText = $sec['cta_texto'] ?? 'Ir a la tienda';
$ctaUrl = $sec['cta_url'] ?? 'productos.php';
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-10 md:py-14">
    <a href="<?= htmlspecialchars($ctaUrl) ?>" class="rompetrafico block no-underline text-white">
        <div class="rt-content">
            <span class="rt-glass-pill"><i class="fa-solid fa-bolt"></i> <?= htmlspecialchars($etiqueta) ?></span>
            <h2 class="rt-title"><?= htmlspecialchars($titulo) ?></h2>
            <?php if ($sub): ?><p class="rt-desc"><?= htmlspecialchars($sub) ?></p><?php endif; ?>
            <span class="inline-flex mt-6 items-center gap-2 bg-white text-slate-900 font-black text-xs uppercase tracking-widest px-6 py-3 rounded-full">
                <?= htmlspecialchars($ctaText) ?> <i class="fa-solid fa-arrow-right"></i>
            </span>
        </div>
        <i class="fa-solid fa-chevron-right rt-chevron" aria-hidden="true"></i>
    </a>
</section>
