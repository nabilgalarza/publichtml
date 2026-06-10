<?php
/** @var string $periodo */
/** @var string $periodo_label */
$dow_labels = improgyp_radar_dow_labels();
$radar_tabs = [
    '7d' => '7 días',
    '30d' => '30 días',
    'todo' => 'Todo',
];
$funnel_base = max(1, (int) ($funnel['visita'] ?? 0));
?>
<div class="mb-6 relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
    <div>
        <div class="inline-flex items-center gap-2 bg-[#1B263B]/10 border border-[#1B263B]/30 px-4 py-2 rounded-full text-[#1B263B] text-xs font-bold mb-3">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Sensores en inicio, tienda y blog · <?= (int) $total_eventos ?> eventos (<?= htmlspecialchars($periodo_label) ?>)
        </div>
        <?php if (!empty($radar_error)): ?>
            <p class="text-xs text-rose-500 mt-1"><?= htmlspecialchars($radar_error) ?></p>
        <?php endif; ?>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <?php foreach ($radar_tabs as $key => $label): ?>
            <a href="dashboard.php?view=radar&periodo=<?= urlencode($key) ?>"
               class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider border transition-all <?= $periodo === $key ? 'bg-[#1B263B] text-white border-[#1B263B]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#1B263B]/40' ?>">
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
        <a href="dashboard.php?view=distribuidores" class="px-4 py-2 rounded-xl text-xs font-bold text-[#1B263B] border border-[#1B263B]/30 hover:bg-[#1B263B]/5">
            <i class="fa-solid fa-handshake"></i> B2B
        </a>
        <a href="dashboard.php?view=pedidos_publicos" class="px-4 py-2 rounded-xl text-xs font-bold text-emerald-700 border border-emerald-200 hover:bg-emerald-50">
            <i class="fa-brands fa-whatsapp"></i> Pedidos WA
        </a>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 relative z-10 mb-8">
    <div class="glass-card p-5 border-l-4 border-l-[#0E75AE]">
        <p class="text-[10px] uppercase font-black text-slate-400 tracking-widest">Visitantes</p>
        <p class="text-2xl font-black text-slate-900 mt-1"><?= number_format((int) $visitantes_unicos) ?></p>
        <p class="text-[10px] text-slate-500 mt-1">únicos (cookie / IP)</p>
    </div>
    <div class="glass-card p-5 border-l-4 border-l-indigo-400">
        <p class="text-[10px] uppercase font-black text-slate-400 tracking-widest">Páginas vistas</p>
        <p class="text-2xl font-black text-slate-900 mt-1"><?= number_format((int) $paginas_vistas) ?></p>
        <p class="text-[10px] text-slate-500 mt-1">1 por pestaña / página</p>
    </div>
    <div class="glass-card p-5 border-l-4 border-l-orange-400">
        <p class="text-[10px] uppercase font-black text-slate-400 tracking-widest">Eventos</p>
        <p class="text-2xl font-black text-slate-900 mt-1"><?= number_format((int) $total_eventos) ?></p>
        <p class="text-[10px] text-slate-500 mt-1">clics y búsquedas</p>
    </div>
    <div class="glass-card p-5 border-l-4 border-l-emerald-500">
        <p class="text-[10px] uppercase font-black text-slate-400 tracking-widest">Checkouts</p>
        <p class="text-2xl font-black text-slate-900 mt-1"><?= number_format((int) $checkouts) ?></p>
        <p class="text-[10px] text-slate-500 mt-1">bolsa abierta</p>
    </div>
    <div class="glass-card p-5 border-l-4 border-l-rose-400 col-span-2 md:col-span-1">
        <p class="text-[10px] uppercase font-black text-slate-400 tracking-widest">Pedidos WA</p>
        <p class="text-2xl font-black text-slate-900 mt-1"><?= number_format((int) ($pedidos_publicos_count ?? 0)) ?></p>
        <p class="text-[10px] text-slate-500 mt-1">en el periodo</p>
    </div>
</div>

<div class="glass-card p-6 mb-8 relative z-10">
    <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight mb-4 flex items-center gap-2">
        <i class="fa-solid fa-filter text-[#1B263B]"></i> Embudo B2C (periodo seleccionado)
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php
        $funnel_steps = [
            ['key' => 'visita', 'label' => 'Visitas', 'color' => 'bg-[#0E75AE]'],
            ['key' => 'ver_producto', 'label' => 'Ver producto', 'color' => 'bg-indigo-500'],
            ['key' => 'carrito', 'label' => 'Añadir carrito', 'color' => 'bg-emerald-500'],
            ['key' => 'checkout', 'label' => 'Checkout', 'color' => 'bg-orange-500'],
        ];
        foreach ($funnel_steps as $step):
            $n = (int) ($funnel[$step['key']] ?? 0);
            $pct = min(100, round(($n / $funnel_base) * 100));
        ?>
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="font-bold text-slate-600"><?= htmlspecialchars($step['label']) ?></span>
                    <span class="font-black text-slate-900"><?= $n ?> <span class="text-slate-400 font-normal">(<?= $pct ?>%)</span></span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="<?= $step['color'] ?> h-2 rounded-full transition-all" style="width: <?= max(2, $pct) ?>%"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 relative z-10">
    <?php
    $tops = [
        ['items' => $top_carrito, 'title' => 'Intención de compra', 'sub' => 'TOP 5 · bolsa', 'icon' => 'fa-bag-shopping', 'icon_bg' => 'bg-emerald-50 text-emerald-600', 'bar' => 'bg-emerald-500', 'num' => 'text-emerald-600', 'quote' => false],
        ['items' => $top_wishlist, 'title' => 'Interés a futuro', 'sub' => 'TOP 5 · deseados', 'icon' => 'fa-heart', 'icon_bg' => 'bg-rose-50 text-rose-600', 'bar' => 'bg-rose-500', 'num' => 'text-rose-600', 'quote' => false],
        ['items' => $top_vistas, 'title' => 'Fichas abiertas', 'sub' => 'TOP 5 · ver producto', 'icon' => 'fa-eye', 'icon_bg' => 'bg-sky-50 text-sky-600', 'bar' => 'bg-sky-500', 'num' => 'text-sky-600', 'quote' => false],
        ['items' => $top_ia, 'title' => 'Mente del cliente', 'sub' => 'TOP 5 · IA + omnibar', 'icon' => 'fa-brain', 'icon_bg' => 'bg-indigo-50 text-indigo-600', 'bar' => 'bg-indigo-500', 'num' => 'text-indigo-600', 'quote' => true],
    ];
    foreach ($tops as $box):
        $list = $box['items'];
    ?>
        <div class="glass-card p-6 flex flex-col">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-full <?= $box['icon_bg'] ?> flex items-center justify-center text-lg">
                    <i class="fa-solid <?= $box['icon'] ?>"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-slate-900 leading-tight"><?= htmlspecialchars($box['title']) ?></h2>
                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest"><?= htmlspecialchars($box['sub']) ?></p>
                </div>
            </div>
            <div class="flex-1 flex flex-col gap-3">
                <?php if (empty($list)): ?>
                    <p class="text-sm text-slate-500 text-center py-6">Sin datos en este periodo</p>
                <?php else: ?>
                    <?php foreach ($list as $idx => $item):
                        $w = max(20, ($item['total'] / $list[0]['total']) * 100);
                        $label = $box['quote'] ? '"' . $item['valor'] . '"' : $item['valor'];
                    ?>
                        <div>
                            <div class="flex justify-between text-xs mb-1 text-slate-600">
                                <span class="font-bold truncate pr-2"><?= $idx + 1 ?>. <?= htmlspecialchars($label) ?></span>
                                <span class="font-black <?= $box['num'] ?>"><?= (int) $item['total'] ?></span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="<?= $box['bar'] ?> h-2 rounded-full" style="width: <?= $w ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 relative z-10 mt-8">
    <div class="glass-card p-8 flex flex-col">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl border border-orange-100">
                <i class="fa-solid fa-clock text-orange-500"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-slate-900 uppercase tracking-tighter">Actividad por hora</h2>
                <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest"><?= htmlspecialchars($periodo_label) ?></p>
            </div>
        </div>
        <div class="flex items-end gap-1 flex-1 min-h-[140px] pt-2">
            <?php foreach ($heatmap as $hora => $valor):
                $height = $max_heat > 0 ? ($valor / $max_heat) * 100 : 0;
                $is_peak = $valor === $max_heat && $valor > 0;
                $bg = $is_peak ? 'bg-orange-500' : ($valor > 0 ? 'bg-[#1B263B]' : 'bg-slate-100');
            ?>
                <div class="flex-1 flex flex-col items-center group relative h-full justify-end">
                    <div class="w-full rounded-t-sm transition-all duration-500 <?= $bg ?>" style="height: <?= max(2, $height) ?>%; min-height: 2px;"></div>
                    <span class="text-[8px] text-slate-400 mt-1 font-mono hidden sm:block"><?= str_pad((string) $hora, 2, '0', STR_PAD_LEFT) ?></span>
                    <div class="absolute bottom-full mb-1 hidden group-hover:block bg-slate-900 text-white text-[10px] py-1 px-2 rounded z-20 whitespace-nowrap">
                        <?= str_pad((string) $hora, 2, '0', STR_PAD_LEFT) ?>:00 — <?= (int) $valor ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="glass-card p-8 flex flex-col">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl border border-violet-100">
                <i class="fa-solid fa-calendar-week"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-slate-900 uppercase tracking-tighter">Día de la semana</h2>
                <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest">volumen de eventos</p>
            </div>
        </div>
        <div class="flex items-end gap-2 flex-1 min-h-[140px] pt-2">
            <?php for ($d = 1; $d <= 7; $d++):
                $valor = (int) ($heatmap_dow[$d] ?? 0);
                $height = $max_heat_dow > 0 ? ($valor / $max_heat_dow) * 100 : 0;
                $is_peak = $valor === $max_heat_dow && $valor > 0;
                $bg = $is_peak ? 'bg-violet-500' : ($valor > 0 ? 'bg-[#1B263B]/80' : 'bg-slate-100');
            ?>
                <div class="flex-1 flex flex-col items-center group relative h-full justify-end">
                    <div class="w-full max-w-[48px] mx-auto rounded-t-lg <?= $bg ?>" style="height: <?= max(4, $height) ?>%; min-height: 4px;"></div>
                    <span class="text-[10px] text-slate-500 mt-2 font-bold"><?= htmlspecialchars($dow_labels[$d] ?? '') ?></span>
                    <span class="text-[9px] text-slate-400"><?= $valor ?></span>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10 mt-8">
    <div class="glass-card p-8 flex flex-col">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-[#1B263B]/10 text-[#1B263B] flex items-center justify-center text-xl border border-[#1B263B]/20">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-slate-900 uppercase tracking-tighter">Interés por categoría</h2>
                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest"><?= htmlspecialchars($periodo_label) ?></p>
            </div>
        </div>
        <div class="flex-1 flex flex-col gap-4">
            <?php if (empty($top_categorias)): ?>
                <p class="text-sm text-slate-500 text-center py-8 italic">Sin categorías en el periodo</p>
            <?php else: ?>
                <?php foreach ($top_categorias as $cat):
                    $w = max(5, ($cat['total'] / $top_categorias[0]['total']) * 100);
                ?>
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[13px] font-black text-slate-800 uppercase tracking-tight"><?= htmlspecialchars($cat['categoria']) ?></span>
                            <span class="text-xs font-black text-[#1B263B]"><?= (int) $cat['total'] ?></span>
                        </div>
                        <div class="w-full bg-slate-50 border border-slate-100 rounded-xl h-3 overflow-hidden">
                            <div class="h-full rounded-lg bg-gradient-to-r from-[#1B263B] to-[#0E75AE]" style="width: <?= $w ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="glass-card p-8 flex flex-col">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl border border-blue-100">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-slate-900 uppercase tracking-tighter">Hotspots geográficos</h2>
                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">por región detectada</p>
            </div>
        </div>
        <?php if (empty($top_regiones)): ?>
            <p class="text-sm text-slate-500 text-center py-8 italic">Sin geolocalización en el periodo</p>
        <?php else: ?>
            <div class="space-y-3 overflow-y-auto custom-scrollbar max-h-[280px] pr-2">
                <?php foreach ($top_regiones as $idx => $reg): ?>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-xs text-blue-500 border font-bold"><?= $idx + 1 ?></div>
                            <div>
                                <p class="text-[13px] font-black text-slate-900"><?= htmlspecialchars((string) ($reg['region'] ?? 'Desconocida')) ?></p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">muestra IP <?= htmlspecialchars((string) ($reg['ip'] ?? '—')) ?></p>
                            </div>
                        </div>
                        <span class="text-blue-600 font-black text-sm"><?= (int) $reg['total'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="glass-card p-8 mt-8 relative z-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl border border-rose-100">
                <i class="fa-solid fa-ghost"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-slate-900 uppercase tracking-tighter">Limpieza de inventario</h2>
                <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest">
                    Sin vistas, carrito ni wishlist (histórico) · máx. 8 impulsos activos
                    <?php if (($productos_fantasma_total ?? 0) > 0): ?>
                        · <?= (int) count($productos_fantasma) ?> de <?= (int) $productos_fantasma_total ?> mostrados
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php if (($productos_fantasma_total ?? 0) > count($productos_fantasma)): ?>
        <a href="dashboard.php?view=inventario_fantasma" class="inline-flex items-center gap-2 bg-[#1B263B] hover:bg-[#0E75AE] text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shrink-0">
            Ver todos (<?= (int) $productos_fantasma_total ?>)
            <i class="fa-solid fa-arrow-right"></i>
        </a>
        <?php elseif (($productos_fantasma_total ?? 0) > 0): ?>
        <a href="dashboard.php?view=inventario_fantasma" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-wider text-[#1B263B] border border-[#1B263B]/30 px-4 py-2 rounded-xl hover:bg-[#1B263B]/5 shrink-0">
            Lista completa <i class="fa-solid fa-list"></i>
        </a>
        <?php endif; ?>
    </div>

    <?php if (empty($productos_fantasma) && ($productos_fantasma_total ?? 0) === 0): ?>
        <p class="text-sm text-slate-500 text-center py-4">Todos los productos publicados tienen al menos una interacción registrada.</p>
    <?php else: ?>
        <?php
        $productos_fantasma_carrusel = array_slice($productos_fantasma, 0, 10);
        include __DIR__ . '/inventario_fantasma_carousel.php';
        ?>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Listado detallado</p>
        <?php $fantasma_compact = true; include __DIR__ . '/inventario_fantasma_table.php'; ?>
    <?php endif; ?>
</div>
