<?php
/**
 * Grilla home (legacy) — cards idénticas a productos.php.
 * @var array $productos
 * @var string $base_url
 */
if (empty($productos)) {
    return;
}
?>
<div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
    <?php foreach ($productos as $prod): ?>
        <?php include __DIR__ . '/landing_product_card.php'; ?>
    <?php endforeach; ?>
</div>
