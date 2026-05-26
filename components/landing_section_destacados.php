<?php
/** @var array $sec */
/** @var string $base_url */
$limite = (int) ($sec['limite'] ?? 8);
$titulo = $sec['titulo'] ?? 'Lo más buscado';
$sub = $sec['subtitulo'] ?? '';
$productos = improgyp_landing_destacados($limite);
if (empty($productos)) return;
?>
<section class="max-w-[1200px] mx-auto px-6 pb-20">
    <div class="text-center mb-10">
        <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-2"><?= htmlspecialchars($titulo) ?></h2>
        <?php if ($sub): ?><p class="text-slate-500 text-sm font-medium"><?= htmlspecialchars($sub) ?></p><?php endif; ?>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        <?php foreach ($productos as $prod):
            $ident = !empty($prod['codigo']) ? $prod['codigo'] : $prod['nombre'];
            $href = 'productos.php?p=' . rawurlencode($ident);
            $img = improgyp_landing_img_url($prod['imagen'] ?? '', $base_url);
            $precio = improgyp_landing_precio_display($prod);
        ?>
        <a href="<?= htmlspecialchars($href) ?>" class="glass-card-landing product-card-landing p-4 flex flex-col h-full">
            <div class="product-img-wrap flex-shrink-0">
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" loading="lazy" onerror="this.onerror=null;this.src='favicon-app.png?v=5';">
            </div>
            <span class="text-[9px] font-bold text-[#1B263B]/70 uppercase tracking-wide mb-1 truncate"><?= htmlspecialchars($prod['categoria'] ?? '') ?></span>
            <h3 class="text-[12px] font-black text-slate-800 leading-snug line-clamp-2 flex-grow mb-2"><?= htmlspecialchars($prod['nombre']) ?></h3>
            <div class="flex items-end justify-between mt-auto pt-2 border-t border-slate-50">
                <span class="text-[14px] font-black text-slate-900"><?= htmlspecialchars($precio) ?></span>
                <span class="text-[10px] font-black text-[#3A86FF] uppercase">Ver <i class="fa-solid fa-arrow-right text-[9px]"></i></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
