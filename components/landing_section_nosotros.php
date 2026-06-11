<?php
/** @var array $sec */
/** @var string $base_url */
$eyebrow = trim($sec['eyebrow'] ?? '');
$tNormal = trim($sec['titulo_normal'] ?? '');
$tResalt = trim($sec['titulo_resaltado'] ?? '');
$texto = trim($sec['texto'] ?? '');
$s1v = trim($sec['stat1_valor'] ?? '');
$s1l = trim($sec['stat1_etiqueta'] ?? '');
$s2v = trim($sec['stat2_valor'] ?? '');
$s2l = trim($sec['stat2_etiqueta'] ?? '');
$img = trim($sec['imagen'] ?? '');
$imgUrl = $img !== '' ? improgyp_landing_img_url($img, $base_url ?? '') : '';
$hasStats = ($s1v !== '' && $s1l !== '') || ($s2v !== '' && $s2l !== '');
if ($tNormal === '' && $tResalt === '' && $texto === '') {
    return;
}
?>
<section id="nosotros" class="landing-nosotros-section max-w-[1200px] mx-auto px-6 py-14 md:py-20 scroll-mt-24">
    <div class="landing-nosotros-grid">
        <div class="landing-nosotros-copy">
            <?php if ($eyebrow !== ''): ?>
            <p class="text-[10px] font-black uppercase tracking-[0.25em] text-[#0E75AE] mb-4"><?= htmlspecialchars($eyebrow) ?></p>
            <?php endif; ?>
            <?php if ($tNormal !== '' || $tResalt !== ''): ?>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-[#1B263B] tracking-tight leading-[1.12] mb-5 uppercase">
                <?php if ($tNormal !== ''): ?>
                <span class="block sm:inline"><?= htmlspecialchars($tNormal) ?></span>
                <?php endif; ?>
                <?php if ($tResalt !== ''): ?>
                <span class="block sm:inline <?= $tNormal !== '' ? 'sm:ml-1' : '' ?>"><span class="laser-text normal-case"><?= htmlspecialchars($tResalt) ?></span></span>
                <?php endif; ?>
            </h2>
            <?php endif; ?>
            <?php if ($texto !== ''): ?>
            <p class="text-slate-600 text-sm md:text-base leading-relaxed font-medium max-w-xl"><?= nl2br(htmlspecialchars($texto)) ?></p>
            <?php endif; ?>
            <?php if ($hasStats): ?>
            <div class="landing-nosotros-stats mt-8 md:mt-10">
                <?php if ($s1v !== '' && $s1l !== ''): ?>
                <div class="landing-nosotros-stat">
                    <p class="text-2xl md:text-3xl font-black text-[#1B263B] leading-none"><?= htmlspecialchars($s1v) ?></p>
                    <p class="text-[11px] md:text-xs font-bold text-slate-500 mt-1.5 uppercase tracking-wide"><?= htmlspecialchars($s1l) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($s2v !== '' && $s2l !== ''): ?>
                <div class="landing-nosotros-stat">
                    <p class="text-2xl md:text-3xl font-black text-[#1B263B] leading-none"><?= htmlspecialchars($s2v) ?></p>
                    <p class="text-[11px] md:text-xs font-bold text-slate-500 mt-1.5 uppercase tracking-wide"><?= htmlspecialchars($s2l) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php if ($imgUrl): ?>
        <div class="landing-nosotros-media">
            <div class="landing-nosotros-media-frame">
                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="IMPROGYP — Quiénes somos" loading="lazy" class="landing-nosotros-img" onerror="this.onerror=null;this.src='favicon-app.png?v=5';">
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
