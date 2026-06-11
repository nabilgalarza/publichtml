<?php
require_once __DIR__ . '/../lib/landing_helpers.php';
$limite = (int) ($sec['limite'] ?? 8);
$productos = improgyp_landing_tendencias($limite);
if (empty($productos)) {
    return;
}
?>
<section class="max-w-[1200px] mx-auto px-3 sm:px-6 pb-16">
    <?php $eyebrow = 'En tiempo real'; include __DIR__ . '/landing_section_heading.php'; ?>
    <div class="flex justify-end -mt-4 mb-6">
        <a href="productos.php" class="text-sm font-black text-[#0E75AE] hover:underline">Ver tienda →</a>
    </div>
    <?php
    $carousel_id = 'tendencias';
    include __DIR__ . '/landing_product_carousel.php';
    ?>
</section>
