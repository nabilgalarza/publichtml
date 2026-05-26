<?php
/** @var array $hero */
/** @var string $base_url */
$badge = $hero['badge'] ?? '';
$tNormal = $hero['titulo_normal'] ?? 'Herramientas profesionales para';
$tResalt = $hero['titulo_resaltado'] ?? 'tu máximo nivel.';
$sub = $hero['subtitulo'] ?? '';
$cta1 = $hero['cta_tienda'] ?? 'Explorar tienda';
$cta1url = $hero['cta_tienda_url'] ?? 'productos.php';
$cta2 = $hero['cta_b2b'] ?? 'Portal mayoristas';
$cta2url = $hero['cta_b2b_url'] ?? 'b2b/';
$heroImg = trim($hero['imagen'] ?? '');
?>
<section class="relative text-center py-14 md:py-24 px-4 max-w-[1100px] mx-auto">
    <div class="landing-hero-glow" aria-hidden="true"></div>
    <?php if ($badge): ?>
    <span class="relative z-10 inline-flex items-center gap-2 bg-white/80 border border-slate-200/80 text-[10px] font-black uppercase tracking-widest text-[#1B263B] px-4 py-2 rounded-full mb-6 shadow-sm">
        <span class="w-1.5 h-1.5 rounded-full bg-[#3A86FF] animate-pulse"></span>
        <?= htmlspecialchars($badge) ?>
    </span>
    <?php endif; ?>
    <h1 class="relative z-10 text-3xl md:text-5xl lg:text-6xl font-black text-slate-900 leading-[1.1] mb-5 tracking-tight">
        <?= htmlspecialchars($tNormal) ?>
        <?php if ($tResalt !== ''): ?>
        <br class="hidden sm:block"><span class="laser-text"><?= htmlspecialchars($tResalt) ?></span>
        <?php endif; ?>
    </h1>
    <?php if ($sub): ?>
    <p class="relative z-10 text-slate-500 font-medium text-sm md:text-lg max-w-2xl mx-auto mb-10 leading-relaxed"><?= htmlspecialchars($sub) ?></p>
    <?php endif; ?>
    <div class="relative z-10 flex flex-col sm:flex-row gap-3 justify-center mb-10">
        <a href="<?= htmlspecialchars($cta1url) ?>" class="inline-flex items-center justify-center gap-2 bg-[#1B263B] text-white font-black px-8 py-4 rounded-2xl hover:bg-[#3A86FF] transition-colors shadow-lg shadow-[#1B263B]/20">
            <i class="fa-solid fa-store"></i> <?= htmlspecialchars($cta1) ?>
        </a>
        <a href="<?= htmlspecialchars($cta2url) ?>" class="inline-flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-700 font-black px-8 py-4 rounded-2xl hover:border-[#1B263B] transition-colors shadow-sm">
            <i class="fa-solid fa-briefcase"></i> <?= htmlspecialchars($cta2) ?>
        </a>
    </div>
    <?php if ($heroImg): ?>
    <div class="relative z-10 max-w-2xl mx-auto rounded-3xl overflow-hidden border border-slate-100 shadow-xl bg-white p-4">
        <img src="<?= htmlspecialchars(improgyp_landing_img_url($heroImg, $base_url)) ?>" alt="IMPROGYP" class="w-full h-auto max-h-64 object-contain mx-auto">
    </div>
    <?php endif; ?>
</section>
