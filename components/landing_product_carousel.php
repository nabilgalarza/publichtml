<?php
/**
 * Carrusel horizontal de productos (home).
 * @var array $productos
 * @var string $carousel_id  id único (tendencias, mas-vendidos)
 * @var string $base_url
 */
if (empty($productos)) {
    return;
}
$carousel_id = preg_replace('/[^a-z0-9_-]/i', '', (string) ($carousel_id ?? 'productos'));
?>
<div class="improgyp-product-carousel" data-carousel="<?= htmlspecialchars($carousel_id, ENT_QUOTES, 'UTF-8') ?>">
    <button type="button" class="improgyp-carousel-btn improgyp-carousel-prev" aria-label="Productos anteriores" aria-controls="carousel-viewport-<?= htmlspecialchars($carousel_id, ENT_QUOTES, 'UTF-8') ?>">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
    </button>
    <div class="improgyp-carousel-viewport" id="carousel-viewport-<?= htmlspecialchars($carousel_id, ENT_QUOTES, 'UTF-8') ?>" role="region" aria-roledescription="carrusel" aria-label="Productos">
        <?php foreach ($productos as $prod): ?>
        <div class="improgyp-carousel-slide">
            <?php include __DIR__ . '/landing_product_card.php'; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="improgyp-carousel-btn improgyp-carousel-next" aria-label="Productos siguientes" aria-controls="carousel-viewport-<?= htmlspecialchars($carousel_id, ENT_QUOTES, 'UTF-8') ?>">
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    </button>
</div>
