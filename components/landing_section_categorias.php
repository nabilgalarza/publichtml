<?php
/** @var array $sec */
/** @var string $base_url */
$limite = (int) ($sec['limite'] ?? 8);
$cats = improgyp_landing_categorias($limite);
if (empty($cats)) {
    return;
}
?>
<section class="max-w-[1200px] mx-auto px-6 pb-16">
    <?php include __DIR__ . '/landing_section_heading.php'; ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <?php foreach ($cats as $cat): ?>
        <a href="<?= htmlspecialchars($cat['href']) ?>" class="glass-card-landing cat-tile group">
            <i class="fa-solid <?= htmlspecialchars($cat['icon']) ?> group-hover:scale-110 transition-transform"></i>
            <span class="text-[12px] md:text-[13px] font-black text-slate-800 leading-tight line-clamp-2"><?= htmlspecialchars($cat['nombre']) ?></span>
            <span class="text-[10px] font-bold text-slate-400 mt-1"><?= (int) $cat['count'] ?> productos</span>
        </a>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-8">
        <a href="productos.php" class="inline-flex items-center gap-2 text-[13px] font-black text-[#1B263B] hover:text-[#0E75AE] transition-colors">
            Ver catálogo completo <i class="fa-solid fa-arrow-right-long"></i>
        </a>
    </div>
</section>
