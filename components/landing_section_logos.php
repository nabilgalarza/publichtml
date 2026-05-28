<?php
require_once __DIR__ . '/../lib/landing_helpers.php';

$logos = improgyp_landing_marcas_logos();
if (empty($logos)) {
    return;
}

$marqueeSeg = max(15, min(120, (int) ($sec['marquee_seg'] ?? 50)));
?>
<section class="marcas-marquee-section max-w-[1200px] mx-auto px-6 py-12 md:py-16 border-t border-slate-100" aria-label="Marcas aliadas">
    <?php include __DIR__ . '/landing_section_heading.php'; ?>

    <div class="marcas-marquee-wrap" style="--marcas-marquee-duration: <?= (int) $marqueeSeg ?>s;">
        <div class="marcas-marquee-fade marcas-marquee-fade--left" aria-hidden="true"></div>
        <div class="marcas-marquee-fade marcas-marquee-fade--right" aria-hidden="true"></div>
        <div class="marcas-marquee-viewport">
            <div class="marcas-marquee-track">
                <?php foreach ([1, 2] as $copy): ?>
                <div class="marcas-marquee-group" <?= $copy === 2 ? 'aria-hidden="true"' : '' ?>>
                    <?php foreach ($logos as $logo): ?>
                    <div class="marcas-marquee-item">
                        <img
                            src="<?= htmlspecialchars($logo['src']) ?>"
                            alt="<?= htmlspecialchars($logo['alt']) ?>"
                            class="marcas-marquee-logo"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
