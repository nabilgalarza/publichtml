<?php
/** @var array $sec */
/** @var string $base_url */
$slides = $sec['slides'] ?? [];
if (empty($slides)) return;
$autoplay = ($sec['autoplay'] ?? true) !== false;
$interval = max(3000, (int) ($sec['intervalo_ms'] ?? 6000));
?>
<section class="max-w-[1200px] mx-auto px-6 pb-16">
    <div class="relative rounded-[2rem] overflow-hidden shadow-2xl border border-slate-200/80 bg-[#0f172a] min-h-[280px] md:min-h-[360px]" id="landing-slider">
        <?php foreach ($slides as $i => $slide):
            $img = improgyp_landing_img_url($slide['imagen'] ?? '', $base_url);
            $ctaUrl = $slide['cta_url'] ?? 'productos.php';
            $ctaText = $slide['cta_texto'] ?? 'Ver más';
        ?>
        <div class="landing-slide absolute inset-0 transition-opacity duration-700 <?= $i === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' ?>" data-slide="<?= $i ?>">
            <div class="absolute inset-0 bg-gradient-to-r from-[#0f172a]/95 via-[#0f172a]/70 to-transparent z-10"></div>
            <?php if ($img): ?>
            <img src="<?= htmlspecialchars($img) ?>" alt="" class="absolute inset-0 w-full h-full object-cover" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
            <?php endif; ?>
            <div class="relative z-20 flex flex-col justify-center h-full min-h-[280px] md:min-h-[360px] p-8 md:p-14 max-w-xl">
                <?php if (!empty($slide['etiqueta'])): ?>
                <span class="inline-block text-[10px] font-black uppercase tracking-widest text-[#0E75AE] mb-3"><?= htmlspecialchars($slide['etiqueta']) ?></span>
                <?php endif; ?>
                <h2 class="text-2xl md:text-4xl font-black text-white leading-tight mb-3"><?= htmlspecialchars($slide['titulo'] ?? '') ?></h2>
                <?php if (!empty($slide['subtitulo'])): ?>
                <p class="text-slate-300 text-sm md:text-base mb-6 font-medium"><?= htmlspecialchars($slide['subtitulo']) ?></p>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($ctaUrl) ?>" class="inline-flex items-center gap-2 bg-[#0E75AE] hover:bg-white hover:text-[#1B263B] text-white font-black px-6 py-3 rounded-xl transition-colors text-sm w-fit">
                    <?= htmlspecialchars($ctaText) ?> <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (count($slides) > 1): ?>
        <div class="absolute bottom-4 left-0 right-0 z-30 flex justify-center gap-2" id="slider-dots">
            <?php foreach ($slides as $i => $slide): ?>
            <button type="button" class="w-2 h-2 rounded-full bg-white/40 hover:bg-white transition-all slider-dot <?= $i === 0 ? '!w-6 !bg-white' : '' ?>" data-goto="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php if (count($slides) > 1): ?>
<script>
(function(){
    const slides = document.querySelectorAll('#landing-slider .landing-slide');
    const dots = document.querySelectorAll('.slider-dot');
    if (!slides.length) return;
    let cur = 0;
    function show(i) {
        cur = (i + slides.length) % slides.length;
        slides.forEach((s, j) => {
            s.classList.toggle('opacity-100', j === cur);
            s.classList.toggle('z-10', j === cur);
            s.classList.toggle('opacity-0', j !== cur);
            s.classList.toggle('z-0', j !== cur);
        });
        dots.forEach((d, j) => {
            d.classList.toggle('!w-6', j === cur);
            d.classList.toggle('!bg-white', j === cur);
            d.classList.toggle('bg-white/40', j !== cur);
        });
    }
    dots.forEach(d => d.addEventListener('click', () => show(parseInt(d.dataset.goto, 10))));
    <?php if ($autoplay): ?>
    setInterval(() => show(cur + 1), <?= (int) $interval ?>);
    <?php endif; ?>
})();
</script>
<?php endif; ?>
