<?php
/**
 * Carrusel horizontal — primeros N productos fantasma (Radar).
 *
 * @var list<array<string, mixed>> $productos_fantasma_carrusel
 */
if (empty($productos_fantasma_carrusel)) {
    return;
}
?>
<div class="flex gap-4 overflow-x-auto pb-4 custom-scrollbar mb-8" aria-label="Vista rápida inventario fantasma">
    <?php foreach ($productos_fantasma_carrusel as $pf): ?>
        <div class="w-[160px] bg-slate-50 rounded-2xl p-4 border border-slate-100 text-center flex-shrink-0 flex flex-col items-center">
            <div class="w-20 h-20 bg-white rounded-xl p-2 mb-4 flex items-center justify-center border border-slate-100">
                <img src="<?= htmlspecialchars(getCleanImgUrl($pf['imagen_url'] ?? '')) ?>" class="max-w-full max-h-full object-contain" alt="" loading="lazy" onerror="this.src='favicon-app.png'">
            </div>
            <h4 class="text-[12px] font-black text-slate-900 line-clamp-2 mb-3 w-full leading-tight" title="<?= htmlspecialchars($pf['nombre']) ?>"><?= htmlspecialchars($pf['nombre']) ?></h4>
            <div class="mt-auto w-full flex flex-col gap-2">
                <?php if (!empty($pf['impulsado'])): ?>
                    <div class="bg-[#1B263B]/10 text-[#1B263B] text-[9px] font-black px-2 py-1 rounded-lg uppercase border border-[#1B263B]/20">
                        <i class="fa-solid fa-bolt-lightning"></i> Impulsado
                    </div>
                <?php else: ?>
                    <button type="button" class="btn-impulsar-fantasma bg-white hover:bg-[#1B263B] text-slate-500 hover:text-white text-[9px] font-black px-2 py-1.5 rounded-lg uppercase border border-slate-200 transition-all w-full" data-nombre="<?= htmlspecialchars($pf['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Impulsar 24h
                    </button>
                    <span class="bg-rose-500/10 text-rose-500 text-[8px] font-black px-2 py-0.5 rounded uppercase">Fantasma</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
