<?php
/** @var array $sec */
/** @var string $eyebrow opcional */
$h = improgyp_landing_section_heading($sec);
if ($h['normal'] === '' && $h['resalt'] === '') {
    return;
}
?>
<div class="text-center mb-12">
    <?php if (!empty($eyebrow)): ?>
    <p class="text-[10px] font-black uppercase tracking-[0.25em] text-[#0E75AE] mb-3"><?= htmlspecialchars($eyebrow) ?></p>
    <?php endif; ?>
    <h2 class="text-3xl md:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight leading-tight">
        <?= htmlspecialchars($h['normal']) ?>
        <?php if ($h['resalt'] !== ''): ?>
        <span class="laser-text block sm:inline"><?= htmlspecialchars($h['resalt']) ?></span>
        <?php endif; ?>
    </h2>
    <?php if ($h['sub'] !== ''): ?>
    <p class="text-slate-500 text-sm md:text-base mt-3 max-w-2xl mx-auto font-medium leading-relaxed"><?= htmlspecialchars($h['sub']) ?></p>
    <?php endif; ?>
</div>
