<?php
require_once __DIR__ . '/../lib/landing_helpers.php';
$limite = (int) ($sec['limite'] ?? 10);
$marcas = improgyp_landing_marcas($limite);
if (empty($marcas)) {
    return;
}
?>
<section class="max-w-[1200px] mx-auto px-6 py-12 md:py-16 border-t border-slate-100">
    <?php include __DIR__ . '/landing_section_heading.php'; ?>
    <div class="flex flex-wrap justify-center gap-3 md:gap-4">
        <?php foreach ($marcas as $m): ?>
        <a href="<?= htmlspecialchars($m['href']) ?>" class="glass-card-landing px-6 py-4 min-w-[120px] text-center hover:border-[#3A86FF]/40 transition-all no-underline">
            <span class="text-sm font-black text-slate-800 block"><?= htmlspecialchars($m['nombre']) ?></span>
            <span class="text-[10px] text-slate-400 font-bold"><?= (int) $m['count'] ?> productos</span>
        </a>
        <?php endforeach; ?>
    </div>
</section>
