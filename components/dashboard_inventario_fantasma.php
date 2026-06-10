<?php
/**
 * @var list<array<string, mixed>> $productos_fantasma
 * @var int $productos_fantasma_total
 * @var int $fantasma_page
 * @var int $fantasma_pages
 * @var int $fantasma_per_page
 * @var string $fantasma_q
 */
$mostrando = count($productos_fantasma);
$desde = $productos_fantasma_total > 0 ? (($fantasma_page - 1) * $fantasma_per_page) + 1 : 0;
$hasta = min($fantasma_page * $fantasma_per_page, $productos_fantasma_total);

function fantasma_pagina_url(int $page, string $q): string
{
    $params = ['view' => 'inventario_fantasma', 'page' => max(1, $page)];
    if ($q !== '') {
        $params['q'] = $q;
    }
    return 'dashboard.php?' . http_build_query($params);
}
?>
<div class="mb-6 relative z-10">
    <a href="dashboard.php?view=radar" class="inline-flex items-center gap-2 text-xs font-black text-slate-500 hover:text-[#1B263B] uppercase tracking-wider mb-4">
        <i class="fa-solid fa-arrow-left"></i> Volver al Radar
    </a>
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 bg-rose-50 border border-rose-100 px-4 py-2 rounded-full text-rose-700 text-xs font-bold mb-3">
                <i class="fa-solid fa-ghost"></i>
                Sin vistas, carrito ni wishlist (histórico)
            </div>
            <p class="text-sm text-slate-500">
                <?php if ($productos_fantasma_total > 0): ?>
                    Mostrando <strong><?= (int) $desde ?>–<?= (int) $hasta ?></strong> de <strong><?= (int) $productos_fantasma_total ?></strong> productos · máx. 8 impulsos activos
                <?php else: ?>
                    No hay productos fantasma con los filtros actuales.
                <?php endif; ?>
            </p>
        </div>
        <form method="get" action="dashboard.php" class="flex flex-wrap gap-2 items-center">
            <input type="hidden" name="view" value="inventario_fantasma">
            <input type="search" name="q" value="<?= htmlspecialchars($fantasma_q) ?>" placeholder="Buscar nombre o categoría…" class="premium-input px-4 py-2.5 rounded-xl text-sm font-bold border border-slate-200 min-w-[220px]">
            <button type="submit" class="bg-[#1B263B] hover:bg-[#0E75AE] text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider">Buscar</button>
            <?php if ($fantasma_q !== ''): ?>
            <a href="dashboard.php?view=inventario_fantasma" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 border border-slate-200 hover:bg-slate-50">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="glass-card p-6 md:p-8 relative z-10">
    <?php $fantasma_compact = false; include __DIR__ . '/inventario_fantasma_table.php'; ?>

    <?php if ($fantasma_pages > 1): ?>
    <nav class="mt-8 pt-6 border-t border-slate-100 flex flex-wrap items-center justify-center gap-2" aria-label="Paginación">
        <?php if ($fantasma_page > 1): ?>
        <a href="<?= htmlspecialchars(fantasma_pagina_url($fantasma_page - 1, $fantasma_q)) ?>" class="px-3 py-2 rounded-lg text-xs font-black border border-slate-200 text-slate-600 hover:bg-slate-50">Anterior</a>
        <?php endif; ?>

        <?php
        $winStart = max(1, $fantasma_page - 2);
        $winEnd = min($fantasma_pages, $fantasma_page + 2);
        for ($p = $winStart; $p <= $winEnd; $p++):
        ?>
        <a href="<?= htmlspecialchars(fantasma_pagina_url($p, $fantasma_q)) ?>"
           class="min-w-[2.25rem] px-3 py-2 rounded-lg text-xs font-black text-center border transition-all <?= $p === $fantasma_page ? 'bg-[#1B263B] text-white border-[#1B263B]' : 'border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
            <?= (int) $p ?>
        </a>
        <?php endfor; ?>

        <?php if ($fantasma_page < $fantasma_pages): ?>
        <a href="<?= htmlspecialchars(fantasma_pagina_url($fantasma_page + 1, $fantasma_q)) ?>" class="px-3 py-2 rounded-lg text-xs font-black border border-slate-200 text-slate-600 hover:bg-slate-50">Siguiente</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</div>

