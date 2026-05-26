<?php
/**
 * Grilla home — cards idénticas a productos.php; botón "Ver detalle" en lugar de "Añadir".
 * @var array $productos
 * @var string $base_url
 */
if (empty($productos)) {
    return;
}
?>
<div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
    <?php foreach ($productos as $prod):
        $ident = !empty($prod['codigo']) ? $prod['codigo'] : ($prod['nombre'] ?? '');
        $safeIdent = htmlspecialchars($ident, ENT_QUOTES, 'UTF-8');
        $safeJs = htmlspecialchars($ident, ENT_QUOTES, 'UTF-8');
        $img = improgyp_landing_img_url($prod['imagen'] ?? $prod['imagen_url'] ?? '', $base_url ?? '');
        $badge = $prod['_badge'] ?? '';
        $badgeHtml = improgyp_landing_card_badge_html($badge);
        $precioNum = 'Consultar';
        if (!empty($prod['presentaciones'][0]['precio'])) {
            $p = explode('|', $prod['presentaciones'][0]['precio'])[0];
            $p = trim($p);
            if ($p !== '' && $p !== 'Consultar') {
                $precioNum = strpos($p, '$') !== false ? $p : '$' . $p;
            }
        }
        $descCorta = 'Herramienta nutricional.';
        if (!empty($prod['desc_larga'])) {
            $snippet = function_exists('mb_substr')
                ? mb_substr($prod['desc_larga'], 0, 65)
                : substr($prod['desc_larga'], 0, 65);
            $descCorta = $snippet . '...';
        }
    ?>
    <article class="glass-card improgyp-home-product-card">
        <div class="product-img-wrapper" data-open-product="<?= $safeIdent ?>">
            <?= $badgeHtml ?>
            <?php if ($badgeHtml === '' && !empty($prod['categoria'])): ?>
            <span class="badge bg-white/90 text-slate-500 hidden sm:block"><?= htmlspecialchars($prod['categoria']) ?></span>
            <?php endif; ?>
            <button type="button" class="btn-wishlist improgyp-home-wishlist-btn" data-wishlist-id="<?= $safeIdent ?>" title="Añadir a deseos" onclick="event.stopPropagation(); typeof toggleWishlistLanding==='function'&&toggleWishlistLanding('<?= $safeJs ?>')"><i class="fa-regular fa-heart"></i></button>
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($prod['nombre'] ?? '') ?>" class="product-img cursor-pointer" loading="lazy" onerror="this.onerror=null;this.src='favicon-app.png?v=5';">
        </div>
        <div class="flex flex-col flex-grow cursor-pointer improgyp-home-card-body" data-open-product="<?= $safeIdent ?>">
            <span class="brand-label"><?= htmlspecialchars($prod['marca'] ?? 'IMPROGYP') ?></span>
            <h4 class="text-slate-900 font-bold mb-1 text-[14px] md:text-base leading-tight line-clamp-2 min-h-[36px] flex items-center md:h-auto"><?= htmlspecialchars($prod['nombre'] ?? '') ?></h4>
            <?php if (!empty($prod['codigo'])): ?>
            <span class="sku-label self-start mb-2"><?= htmlspecialchars($prod['codigo']) ?></span>
            <?php endif; ?>
            <p class="hidden md:block text-[14px] text-slate-500 mb-3 flex-grow leading-relaxed"><?= htmlspecialchars($descCorta, ENT_QUOTES, 'UTF-8') ?></p>
            <div class="flex justify-between items-end mt-auto mb-3">
                <div>
                    <p class="text-[9px] md:text-[10px] text-slate-400 font-bold uppercase mb-0.5">Precio</p>
                    <span class="text-[15px] md:text-[17px] font-black text-slate-800"><?= htmlspecialchars($precioNum) ?></span>
                </div>
            </div>
        </div>
        <button type="button" class="btn-IMPROGYP w-full text-[13px] md:text-[12px] py-2 h-10 md:h-9 mt-auto improgyp-home-card-detail-btn" data-open-product="<?= $safeIdent ?>">
            <i class="fa-solid fa-eye"></i> <span class="ml-1">Ver detalle</span>
        </button>
    </article>
    <?php endforeach; ?>
</div>
