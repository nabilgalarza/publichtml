<?php
require_once __DIR__ . '/../lib/landing_helpers.php';
$limite = (int) ($sec['limite'] ?? 8);
$productos = improgyp_landing_mas_vendidos($limite);
if (empty($productos)) {
    return;
}
?>
<section class="max-w-[1200px] mx-auto px-3 sm:px-6 pb-20">
    <div class="bg-slate-50/80 rounded-3xl py-8 px-2 sm:px-4 md:px-8 md:py-12">
        <?php $eyebrow = 'Selección comercial'; include __DIR__ . '/landing_section_heading.php'; ?>
        <div class="flex justify-end -mt-4 mb-6">
            <a href="productos.php" class="text-sm font-black text-[#0E75AE] hover:underline">Ver catálogo →</a>
        </div>
        <?php
        $carousel_id = 'mas-vendidos';
        include __DIR__ . '/landing_product_carousel.php';
        ?>
    </div>
</section>
