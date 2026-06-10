<?php
require_once __DIR__ . '/../lib/landing_helpers.php';

$landing = improgyp_landing_config();
$hero = $landing['hero'];
$secciones = $landing['secciones'];

function improgyp_home_sec(array $secciones, string $tipo): array
{
    foreach ($secciones as $s) {
        if (($s['tipo'] ?? '') === $tipo) {
            return $s;
        }
    }
    return ['tipo' => $tipo, 'activo' => false];
}

$secSlider = improgyp_home_sec($secciones, 'slider');
$secCat = improgyp_home_sec($secciones, 'categorias');
$secTrend = improgyp_home_sec($secciones, 'tendencias');
$secTop = improgyp_home_sec($secciones, 'mas_vendidos');
if (empty($secTop['activo']) && (improgyp_home_sec($secciones, 'destacados')['activo'] ?? false)) {
    $secTop = improgyp_home_sec($secciones, 'destacados');
}
$secCta = improgyp_home_sec($secciones, 'cta');
$secBlog = improgyp_home_sec($secciones, 'blog');
$secLogos = improgyp_home_sec($secciones, 'logos');
$secLocales = improgyp_home_sec($secciones, 'locales');

$slidesCfg = $secSlider['slides'] ?? [['titulo' => '', 'subtitulo' => '', 'imagen' => '', 'cta_texto' => 'Ver más', 'cta_url' => 'productos.php', 'etiqueta' => '']];
while (count($slidesCfg) < 3) {
    $slidesCfg[] = ['titulo' => '', 'subtitulo' => '', 'imagen' => '', 'cta_texto' => 'Ver más', 'cta_url' => 'productos.php', 'etiqueta' => ''];
}

$bloquesLaserAntesCta = [
    ['key' => 'categorias', 'orden' => 2, 'label' => 'Categorías de equipos', 'sec' => $secCat, 'limite' => true, 'max' => 12],
    ['key' => 'tendencias', 'orden' => 3, 'label' => 'Tendencias (ranking 48h)', 'sec' => $secTrend, 'limite' => true, 'max' => 12],
    ['key' => 'mas_vendidos', 'orden' => 4, 'label' => 'Más vendidos (impulsados)', 'sec' => $secTop, 'limite' => true, 'max' => 12],
];
$bloquesLaserDespuesCta = [
    ['key' => 'blog', 'orden' => 6, 'label' => 'Bloque blog', 'sec' => $secBlog, 'limite' => false, 'max' => 12],
    ['key' => 'logos', 'orden' => 7, 'label' => 'Marcas aliadas', 'sec' => $secLogos, 'limite' => true, 'max' => 20],
];

function improgyp_home_preview_heading(array $sec): string
{
    $h = improgyp_landing_section_heading($sec);
    $parts = array_filter([$h['normal'], $h['resalt']]);
    if ($parts) {
        return implode(' · ', $parts);
    }
    return 'Sin título configurado';
}
?>
<style>
.home-accordion > summary { list-style: none; cursor: pointer; }
.home-accordion > summary::-webkit-details-marker { display: none; }
.home-accordion[open] > summary .home-acc-chevron { transform: rotate(180deg); }
.home-accordion > summary .home-acc-chevron { transition: transform 0.2s ease; }
</style>
<div class="max-w-[920px] mx-auto">
    <?php
    $msg = $_GET['msg'] ?? '';
    if ($msg === 'home_guardado' || $msg === 'portada_guardada' || $msg === 'secciones_guardadas'):
    ?>
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 p-4 rounded-xl mb-6 text-sm font-bold">
        <i class="fa-solid fa-circle-check"></i> Home guardado. Recarga <code class="text-xs">index.php</code> para ver los cambios.
    </div>
    <?php endif; ?>

    <div class="mb-6">
        <h2 class="text-xl font-black text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-house-chimney text-[#0E75AE]"></i> Editor del Home
        </h2>
        <p class="text-sm text-slate-500 mt-1">Un solo lugar para <strong>index.php</strong>: enciende bloques, límites y títulos láser por sección. Un guardado actualiza todo <code class="text-xs bg-slate-100 px-1 rounded">config_landing.json</code>.</p>
        <?php include __DIR__ . '/gemini_status_badge.php'; ?>
    </div>

    <div class="mb-6 p-4 rounded-2xl border border-violet-200 bg-violet-50/80 text-[12px] text-slate-700 leading-relaxed">
        <p class="font-black text-violet-900 mb-2 uppercase tracking-wider text-[10px]">¿Dónde edito qué?</p>
        <ul class="space-y-1 list-disc list-inside">
            <li><strong>Home (index):</strong> esta pantalla</li>
            <li><strong>Catálogo (productos.php):</strong> <a href="?view=marketing" class="text-[#0E75AE] font-bold underline">Marketing IA</a> → <code class="text-[10px] bg-white/80 px-1 rounded">textos_tienda.json</code></li>
            <li><strong>Banners en catálogo:</strong> Gestor de Pautas → <code class="text-[10px] bg-white/80 px-1 rounded">ads.json</code></li>
            <li><strong>Artículos del blog:</strong> <a href="?view=blog" class="text-[#0E75AE] font-bold underline">Gestor de Blog</a></li>
        </ul>
    </div>

    <form method="POST" action="dashboard.php?view=apariencia&sub=home" enctype="multipart/form-data" class="space-y-3">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <input type="hidden" name="action" value="guardar_home_landing">

        <details class="home-accordion bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" open>
            <summary class="flex items-center justify-between gap-3 px-5 py-4 hover:bg-slate-50/80">
                <span class="flex items-center gap-3 min-w-0">
                    <span class="flex-shrink-0 w-7 h-7 rounded-lg bg-[#0E75AE]/10 text-[#0E75AE] text-xs font-black flex items-center justify-center">H</span>
                    <span class="min-w-0">
                        <span class="block font-black text-slate-800 text-sm">Hero (opcional, encima del slider)</span>
                        <span class="block text-[11px] text-slate-400 truncate"><?= htmlspecialchars(improgyp_home_preview_heading($hero)) ?></span>
                    </span>
                </span>
                <i class="fa-solid fa-chevron-down home-acc-chevron text-slate-400 text-xs"></i>
            </summary>
            <div class="px-5 pb-5 pt-0 border-t border-slate-100 space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="hero_activo" value="1" <?= !empty($hero['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                    <span class="font-bold text-sm text-slate-700">Mostrar hero</span>
                </label>
                <input type="text" name="hero_badge" value="<?= htmlspecialchars($hero['badge'] ?? '') ?>" placeholder="Badge" class="w-full premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                <div class="grid sm:grid-cols-2 gap-4">
                    <input type="text" name="hero_titulo_normal" value="<?= htmlspecialchars($hero['titulo_normal'] ?? '') ?>" placeholder="Título línea 1" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                    <input type="text" name="hero_titulo_resaltado" value="<?= htmlspecialchars($hero['titulo_resaltado'] ?? '') ?>" placeholder="Título resaltado" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                </div>
                <textarea name="hero_subtitulo" rows="2" placeholder="Subtítulo" class="w-full premium-input rounded-xl px-4 py-2 text-sm border border-slate-100"><?= htmlspecialchars($hero['subtitulo'] ?? '') ?></textarea>
                <div class="grid sm:grid-cols-2 gap-4">
                    <input type="text" name="hero_cta_tienda" value="<?= htmlspecialchars($hero['cta_tienda'] ?? '') ?>" placeholder="CTA tienda" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                    <input type="text" name="hero_cta_b2b" value="<?= htmlspecialchars($hero['cta_b2b'] ?? '') ?>" placeholder="CTA B2B" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                </div>
                <input type="text" name="hero_imagen" value="<?= htmlspecialchars($hero['imagen'] ?? '') ?>" placeholder="Imagen hero (ruta)" class="w-full premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
            </div>
        </details>

        <details id="bloque-slider" class="home-accordion bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <summary class="flex items-center justify-between gap-3 px-5 py-4 hover:bg-slate-50/80">
                <span class="flex items-center gap-3 min-w-0">
                    <span class="flex-shrink-0 w-7 h-7 rounded-lg bg-slate-100 text-slate-600 text-xs font-black flex items-center justify-center">1</span>
                    <span class="min-w-0">
                        <span class="block font-black text-slate-800 text-sm">Slider principal</span>
                        <span class="block text-[11px] text-slate-400"><?= !empty($secSlider['activo']) ? 'Activo' : 'Apagado' ?> · <?= count(array_filter($slidesCfg, fn($s) => trim($s['titulo'] ?? '') !== '')) ?> slides</span>
                    </span>
                </span>
                <i class="fa-solid fa-chevron-down home-acc-chevron text-slate-400 text-xs"></i>
            </summary>
            <div class="px-5 pb-5 border-t border-slate-100 space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="sec_slider_activo" value="1" <?= !empty($secSlider['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                    <span class="font-bold text-sm text-slate-700">Mostrar slider</span>
                </label>
                <label class="flex items-center gap-2 text-xs font-bold text-slate-500">
                    <input type="checkbox" name="slider_autoplay" value="1" <?= ($secSlider['autoplay'] ?? true) ? 'checked' : '' ?> class="rounded"> Autoplay
                </label>
                <?php for ($si = 1; $si <= 3; $si++):
                    $sl = $slidesCfg[$si - 1] ?? [];
                ?>
                <div class="p-4 bg-slate-50 rounded-xl space-y-2">
                    <p class="text-[10px] font-black uppercase text-slate-400">Slide <?= $si ?></p>
                    <input type="text" name="slider_<?= $si ?>_etiqueta" value="<?= htmlspecialchars($sl['etiqueta'] ?? '') ?>" placeholder="Etiqueta" class="w-full premium-input rounded-lg px-3 py-2 text-xs border border-slate-100">
                    <input type="text" name="slider_<?= $si ?>_titulo" value="<?= htmlspecialchars($sl['titulo'] ?? '') ?>" placeholder="Título" class="w-full premium-input rounded-lg px-3 py-2 text-xs border border-slate-100">
                    <input type="text" name="slider_<?= $si ?>_subtitulo" value="<?= htmlspecialchars($sl['subtitulo'] ?? '') ?>" placeholder="Subtítulo" class="w-full premium-input rounded-lg px-3 py-2 text-xs border border-slate-100">
                    <input type="text" name="slider_<?= $si ?>_imagen" value="<?= htmlspecialchars($sl['imagen'] ?? '') ?>" placeholder="Imagen" class="w-full premium-input rounded-lg px-3 py-2 text-xs border border-slate-100">
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="slider_<?= $si ?>_cta_texto" value="<?= htmlspecialchars($sl['cta_texto'] ?? 'Ver más') ?>" placeholder="CTA texto" class="premium-input rounded-lg px-3 py-2 text-xs border border-slate-100">
                        <input type="text" name="slider_<?= $si ?>_cta_url" value="<?= htmlspecialchars($sl['cta_url'] ?? 'productos.php') ?>" placeholder="CTA URL" class="premium-input rounded-lg px-3 py-2 text-xs border border-slate-100">
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </details>

        <?php
        $renderBloqueLaser = static function (array $bloque) {
            $bk = $bloque['key'];
            $bs = $bloque['sec'];
            $h = improgyp_landing_section_heading($bs);
            $maxLim = (int) ($bloque['max'] ?? 12);
            $orden = (int) $bloque['orden'];
            ?>
        <details id="bloque-<?= htmlspecialchars($bk) ?>" class="home-accordion bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden scroll-mt-24">
            <summary class="flex items-center justify-between gap-3 px-5 py-4 hover:bg-slate-50/80">
                <span class="flex items-center gap-3 min-w-0">
                    <span class="flex-shrink-0 w-7 h-7 rounded-lg bg-slate-100 text-slate-600 text-xs font-black flex items-center justify-center"><?= $orden ?></span>
                    <span class="min-w-0">
                        <span class="block font-black text-slate-800 text-sm"><?= htmlspecialchars($bloque['label']) ?></span>
                        <span class="block text-[11px] text-slate-400 truncate">
                            <?= !empty($bs['activo']) ? 'Activo' : 'Apagado' ?>
                            <?php if ($bloque['limite']): ?> · máx. <?= (int) ($bs['limite'] ?? 8) ?><?php endif; ?>
                            · <?= htmlspecialchars(improgyp_home_preview_heading($bs)) ?>
                        </span>
                    </span>
                </span>
                <i class="fa-solid fa-chevron-down home-acc-chevron text-slate-400 text-xs"></i>
            </summary>
            <div class="px-5 pb-5 border-t border-slate-100 space-y-5">
                <div class="pt-4 flex flex-wrap items-center gap-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="sec_<?= $bk ?>_activo" value="1" <?= !empty($bs['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                        <span class="font-bold text-sm text-slate-700">Visible en el home</span>
                    </label>
                    <?php if ($bloque['limite']): ?>
                    <div class="flex items-center gap-2">
                        <label class="text-[10px] font-bold text-slate-500 uppercase">Máx. ítems</label>
                        <input type="number" name="sec_<?= $bk ?>_limite" value="<?= (int) ($bs['limite'] ?? 8) ?>" min="4" max="<?= $maxLim ?>" class="w-20 premium-input rounded-lg px-3 py-2 text-sm border border-slate-100">
                    </div>
                    <?php endif; ?>
                </div>
                <div class="rounded-xl border border-[#0E75AE]/20 bg-[#0E75AE]/5 p-4 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#0E75AE]">Título visible (efecto láser)</p>
                        <button type="button" onclick="generarCopySeccionIA('<?= $bk ?>', 'tit_norm_<?= $bk ?>', 'tit_resal_<?= $bk ?>', 'sub_<?= $bk ?>', this)" class="btn-copy-ia bg-white hover:bg-[#1B263B] text-[#1B263B] hover:text-white border border-[#1B263B]/20 rounded-lg py-1.5 px-4 text-xs font-black transition-colors flex items-center justify-center gap-2">
                            <i class="fa-solid fa-robot"></i> <span class="btn-text">Generar con IA</span>
                        </button>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Título (línea 1)</label>
                            <input type="text" id="tit_norm_<?= $bk ?>_input" name="titulo_normal[<?= $bk ?>]" value="<?= htmlspecialchars($h['normal']) ?>" class="w-full premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Título láser</label>
                            <input type="text" id="tit_resal_<?= $bk ?>" name="titulo_resaltado[<?= $bk ?>]" value="<?= htmlspecialchars($h['resalt']) ?>" class="w-full premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Subtítulo</label>
                        <input type="text" id="sub_<?= $bk ?>" name="subtitulo[<?= $bk ?>]" value="<?= htmlspecialchars($h['sub']) ?>" class="w-full premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                    </div>
                </div>
            </div>
        </details>
        <?php
        };
        foreach ($bloquesLaserAntesCta as $bloque) {
            $renderBloqueLaser($bloque);
        }
        ?>

        <details id="bloque-cta" class="home-accordion bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden scroll-mt-24">
            <summary class="flex items-center justify-between gap-3 px-5 py-4 hover:bg-slate-50/80">
                <span class="flex items-center gap-3 min-w-0">
                    <span class="flex-shrink-0 w-7 h-7 rounded-lg bg-slate-100 text-slate-600 text-xs font-black flex items-center justify-center">5</span>
                    <span class="min-w-0">
                        <span class="block font-black text-slate-800 text-sm">CTA rompetráfico (home)</span>
                        <span class="block text-[11px] text-slate-400 truncate"><?= !empty($secCta['activo']) ? 'Activo' : 'Apagado' ?> · <?= htmlspecialchars($secCta['titulo'] ?? '—') ?></span>
                    </span>
                </span>
                <i class="fa-solid fa-chevron-down home-acc-chevron text-slate-400 text-xs"></i>
            </summary>
            <div class="px-5 pb-5 border-t border-slate-100 space-y-4 pt-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="sec_cta_activo" value="1" <?= !empty($secCta['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                    <span class="font-bold text-sm text-slate-700">Mostrar bloque CTA</span>
                </label>
                <input type="hidden" name="sec_cta_img_url_actual" value="<?= htmlspecialchars($secCta['imagen'] ?? '') ?>">
                <div class="grid sm:grid-cols-2 gap-4">
                    <input type="text" name="sec_cta_etiqueta" value="<?= htmlspecialchars($secCta['etiqueta'] ?? 'Asesoría') ?>" placeholder="Etiqueta" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                    <input type="text" name="sec_cta_titulo" value="<?= htmlspecialchars($secCta['titulo'] ?? '') ?>" placeholder="Título" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                    <input type="text" name="sec_cta_subtitulo" value="<?= htmlspecialchars($secCta['subtitulo'] ?? '') ?>" placeholder="Subtítulo" class="premium-input rounded-xl px-4 py-2 text-sm sm:col-span-2 border border-slate-100">
                    <input type="text" name="sec_cta_texto" value="<?= htmlspecialchars($secCta['cta_texto'] ?? 'Ir a la tienda') ?>" placeholder="Botón" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                    <input type="text" name="sec_cta_url" value="<?= htmlspecialchars($secCta['cta_url'] ?? 'productos.php') ?>" placeholder="URL" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Imagen CTA</label>
                    <input type="file" name="sec_cta_imagen" accept="image/*" class="w-full premium-input rounded-xl px-3 py-2 text-xs border border-slate-100">
                    <?php if (!empty($secCta['imagen'])): ?>
                    <p class="text-[10px] text-emerald-600 mt-1 font-bold"><i class="fa-solid fa-check"></i> Imagen cargada</p>
                    <?php endif; ?>
                </div>
            </div>
        </details>

        <?php foreach ($bloquesLaserDespuesCta as $bloque):
            $renderBloqueLaser($bloque);
        endforeach; ?>

        <details id="bloque-locales" class="home-accordion bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <summary class="flex items-center justify-between gap-3 px-5 py-4 hover:bg-slate-50/80">
                <span class="flex items-center gap-3 min-w-0">
                    <span class="flex-shrink-0 w-7 h-7 rounded-lg bg-slate-100 text-slate-600 text-xs font-black flex items-center justify-center">8</span>
                    <span class="min-w-0">
                        <span class="block font-black text-slate-800 text-sm">Sucursales + asesoría</span>
                        <span class="block text-[11px] text-slate-400 truncate"><?= htmlspecialchars($secLocales['titulo'] ?? 'Red de sucursales') ?></span>
                    </span>
                </span>
                <i class="fa-solid fa-chevron-down home-acc-chevron text-slate-400 text-xs"></i>
            </summary>
            <div class="px-5 pb-5 border-t border-slate-100 space-y-4 pt-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="sec_locales_activo" value="1" <?= !empty($secLocales['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                    <span class="font-bold text-sm text-slate-700">Mostrar bloque</span>
                </label>
                <div class="grid sm:grid-cols-2 gap-4">
                    <input type="text" name="sec_locales_titulo" value="<?= htmlspecialchars($secLocales['titulo'] ?? 'Red de sucursales') ?>" placeholder="Título (texto plano)" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                    <input type="text" name="sec_locales_subtitulo" value="<?= htmlspecialchars($secLocales['subtitulo'] ?? '') ?>" placeholder="Subtítulo" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                </div>
                <p class="text-[10px] text-slate-400">El listado de locales se edita en <a href="?view=locales" class="text-[#0E75AE] font-bold underline">Red de Sucursales</a>.</p>
            </div>
        </details>

        <div class="mt-6 pt-2 border-t border-slate-200">
            <button type="submit" class="w-full bg-[#1B263B] hover:bg-[#0E75AE] text-white font-black py-4 rounded-xl transition-colors uppercase tracking-widest text-xs">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Guardar home
            </button>
        </div>
    </form>

    <p class="text-center mt-6 pb-8">
        <a href="index.php" target="_blank" class="text-[#0E75AE] font-bold text-sm hover:underline">Ver home en nueva pestaña →</a>
    </p>
</div>

<script>
window.iaCopyEnCurso = window.iaCopyEnCurso || false;

function setEstadoBotonesCopyIA(disabled) {
    document.querySelectorAll('.btn-copy-ia').forEach(btn => {
        btn.disabled = disabled;
        btn.classList.toggle('opacity-40', disabled);
        btn.classList.toggle('pointer-events-none', disabled);
    });
}

async function generarCopySeccionIA(seccion, idNorm, idResal, idSub, btnElement) {
    if (window.iaCopyEnCurso) {
        alert('Espera a que termine la generación anterior (un IA a la vez).');
        return;
    }
    window.iaCopyEnCurso = true;
    setEstadoBotonesCopyIA(true);

    const normEl = document.getElementById(idNorm + '_input') || document.getElementById(idNorm);
    const resalEl = document.getElementById(idResal);
    const subEl = document.getElementById(idSub);

    const icon = btnElement.querySelector('i');
    const textSpan = btnElement.querySelector('.btn-text');
    if (icon) icon.className = 'fa-solid fa-circle-notch fa-spin';
    if (textSpan) textSpan.innerText = '...';
    btnElement.classList.add('bg-[#1B263B]', 'text-white');

    try {
        const response = await fetch('dashboard.php?ajax=generar_copy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                seccion: seccion,
                tit_actual: normEl?.value || '',
                resal_actual: resalEl?.value || '',
                sub_actual: subEl?.value || '',
                regenerar: true
            })
        });
        const raw = await response.text();
        let data;
        try { data = JSON.parse(raw); } catch (e) { throw new Error('Respuesta no válida'); }
        if (data.error) {
            alert('Error IA: ' + data.error);
        } else if (data.tit_normal !== undefined) {
            if (normEl) normEl.value = data.tit_normal;
            if (resalEl) resalEl.value = data.tit_resaltado || '';
            if (subEl) subEl.value = data.sub || '';
            if (icon) icon.className = 'fa-solid fa-check';
            if (textSpan) textSpan.innerText = 'Listo';
        }
    } catch (err) {
        alert(err.message || 'Error de conexión');
    } finally {
        setTimeout(() => {
            if (icon) icon.className = 'fa-solid fa-robot';
            if (textSpan) textSpan.innerText = 'Generar con IA';
            btnElement.classList.remove('bg-[#1B263B]', 'text-white');
            window.iaCopyEnCurso = false;
            setEstadoBotonesCopyIA(false);
        }, 1200);
    }
}

(function () {
    var hash = window.location.hash.replace(/^#/, '');
    if (!hash) return;
    if (hash.indexOf('tit_norm_') === 0) {
        hash = 'bloque-' + hash.slice('tit_norm_'.length);
    }
    var el = document.getElementById(hash) || document.getElementById('bloque-' + hash);
    if (el && el.tagName === 'DETAILS') {
        el.open = true;
        setTimeout(function () { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
    }
})();
</script>
