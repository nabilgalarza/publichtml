<?php
/** @var array $sec */
/** @var string $eyebrow opcional */
$h = improgyp_landing_section_heading($sec);
if ($h['normal'] === '' && $h['resalt'] === '') {
    return;
}
?>
<div class="text-center mb-10">
    <?php if (!empty($eyebrow)): ?>
    <p class="text-[10px] font-black uppercase tracking-[0.25em] text-[#3A86FF] mb-2"><?= htmlspecialchars($eyebrow) ?></p>
    <?php endif; ?>
    <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight leading-tight">
        <?= htmlspecialchars($h['normal']) ?>
        <?php if ($h['resalt'] !== ''): ?>
        <span class="laser-text block sm:inline"><?= htmlspecialchars($h['resalt']) ?></span>
        <?php endif; ?>
    </h2>
    <?php if ($h['sub'] !== ''): ?>
    <p class="text-slate-500 text-sm mt-2 max-w-xl mx-auto font-medium"><?= htmlspecialchars($h['sub']) ?></p>
    <?php endif; ?>
</div>
