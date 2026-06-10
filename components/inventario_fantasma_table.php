<?php
/**
 * Tabla productos fantasma (Radar resumen o vista completa).
 *
 * @var list<array<string, mixed>> $productos_fantasma
 * @var bool $fantasma_compact
 */
$fantasma_compact = $fantasma_compact ?? false;
?>
<?php if (empty($productos_fantasma)): ?>
    <p class="text-sm text-slate-500 text-center py-6">No hay productos que coincidan con este criterio.</p>
<?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[520px]">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-100">
                    <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest w-14">Foto</th>
                    <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Producto</th>
                    <?php if (!$fantasma_compact): ?>
                    <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest w-36">Categoría</th>
                    <?php endif; ?>
                    <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right w-40">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($productos_fantasma as $pf): ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="w-10 h-10 rounded-lg bg-white border border-slate-100 flex items-center justify-center overflow-hidden">
                            <img src="<?= htmlspecialchars(getCleanImgUrl($pf['imagen_url'] ?? '')) ?>" alt="" class="max-w-full max-h-full object-contain" loading="lazy" onerror="this.src='favicon-app.png'">
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-black text-slate-900 leading-tight"><?= htmlspecialchars($pf['nombre']) ?></div>
                        <?php if ($fantasma_compact && !empty($pf['categoria'])): ?>
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mt-0.5"><?= htmlspecialchars($pf['categoria']) ?></div>
                        <?php endif; ?>
                    </td>
                    <?php if (!$fantasma_compact): ?>
                    <td class="px-4 py-3 text-xs font-bold text-slate-500"><?= htmlspecialchars($pf['categoria'] ?? '—') ?></td>
                    <?php endif; ?>
                    <td class="px-4 py-3 text-right">
                        <?php if (!empty($pf['impulsado'])): ?>
                            <span class="inline-flex items-center gap-1 bg-[#1B263B]/10 text-[#1B263B] text-[9px] font-black px-2.5 py-1.5 rounded-lg uppercase border border-[#1B263B]/20">
                                <i class="fa-solid fa-bolt-lightning"></i> Impulsado
                            </span>
                        <?php else: ?>
                            <div class="flex flex-col items-end gap-1.5">
                                <button type="button" class="btn-impulsar-fantasma bg-white hover:bg-[#1B263B] text-slate-500 hover:text-white text-[9px] font-black px-3 py-1.5 rounded-lg uppercase border border-slate-200 transition-all whitespace-nowrap" data-nombre="<?= htmlspecialchars($pf['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> Impulsar 24h
                                </button>
                                <span class="bg-rose-500/10 text-rose-500 text-[8px] font-black px-2 py-0.5 rounded uppercase">Fantasma</span>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
