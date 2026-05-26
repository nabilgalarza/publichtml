<?php
require_once __DIR__ . '/../lib/landing_helpers.php';

$landing = improgyp_landing_config();
$hero = $landing['hero'];
$secciones = $landing['secciones'];

function improgyp_portada_sec($secciones, $tipo) {
    foreach ($secciones as $s) {
        if (($s['tipo'] ?? '') === $tipo) {
            return $s;
        }
    }
    return ['tipo' => $tipo, 'activo' => false];
}
$secSlider = improgyp_portada_sec($secciones, 'slider');
$secCat = improgyp_portada_sec($secciones, 'categorias');
$secTrend = improgyp_portada_sec($secciones, 'tendencias');
$secTop = improgyp_portada_sec($secciones, 'mas_vendidos');
if (empty($secTop['activo']) && ($d = improgyp_portada_sec($secciones, 'destacados'))['activo'] ?? false) {
    $secTop = $d;
}
$secCta = improgyp_portada_sec($secciones, 'cta');
$secBlog = improgyp_portada_sec($secciones, 'blog');
$secLogos = improgyp_portada_sec($secciones, 'logos');
$secLocales = improgyp_portada_sec($secciones, 'locales');
$slidesCfg = $secSlider['slides'] ?? [['titulo'=>'','subtitulo'=>'','imagen'=>'','cta_texto'=>'Ver más','cta_url'=>'productos.php','etiqueta'=>'']];
while (count($slidesCfg) < 3) {
    $slidesCfg[] = ['titulo'=>'','subtitulo'=>'','imagen'=>'','cta_texto'=>'Ver más','cta_url'=>'productos.php','etiqueta'=>''];
}
?>
<div class="max-w-[900px] mx-auto">
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'portada_guardada'): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 p-4 rounded-xl mb-6 text-sm font-bold">
        <i class="fa-solid fa-circle-check"></i> Portada actualizada. Recarga el home para ver cambios.
    </div>
    <?php endif; ?>

    <div class="mb-6">
        <h2 class="text-xl font-black text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-house text-[#3A86FF]"></i> Portada (Home)
        </h2>
        <p class="text-sm text-slate-500 mt-1">Orden: slider → categorías → tendencias → más vendidos → CTA → blog → marcas → sucursales. Encabezados con láser en <a href="?view=apariencia&sub=secciones" class="text-[#3A86FF] font-bold underline">Secciones Home</a>.</p>
    </div>

    <form method="POST" action="dashboard.php?view=apariencia&sub=portada" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <input type="hidden" name="action" value="guardar_landing">

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="hero_activo" value="1" <?= !empty($hero['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                <span class="font-bold text-sm text-slate-700">Mostrar hero encima del slider (opcional)</span>
            </label>
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Hero</h3>
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Badge</label>
                <input type="text" name="hero_badge" value="<?= htmlspecialchars($hero['badge'] ?? '') ?>" class="w-full premium-input rounded-xl px-4 py-3 text-sm font-bold border border-slate-100">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Título (línea 1)</label>
                    <input type="text" name="hero_titulo_normal" value="<?= htmlspecialchars($hero['titulo_normal'] ?? '') ?>" class="w-full premium-input rounded-xl px-4 py-3 text-sm font-bold border border-slate-100">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Título resaltado</label>
                    <input type="text" name="hero_titulo_resaltado" value="<?= htmlspecialchars($hero['titulo_resaltado'] ?? '') ?>" class="w-full premium-input rounded-xl px-4 py-3 text-sm font-bold border border-slate-100">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Subtítulo</label>
                <textarea name="hero_subtitulo" rows="2" class="w-full premium-input rounded-xl px-4 py-3 text-sm font-bold border border-slate-100"><?= htmlspecialchars($hero['subtitulo'] ?? '') ?></textarea>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <input type="text" name="hero_cta_tienda" value="<?= htmlspecialchars($hero['cta_tienda'] ?? '') ?>" placeholder="CTA tienda" class="premium-input rounded-xl px-4 py-3 text-sm border border-slate-100">
                <input type="text" name="hero_cta_b2b" value="<?= htmlspecialchars($hero['cta_b2b'] ?? '') ?>" placeholder="CTA B2B" class="premium-input rounded-xl px-4 py-3 text-sm border border-slate-100">
            </div>
            <input type="text" name="hero_imagen" value="<?= htmlspecialchars($hero['imagen'] ?? '') ?>" placeholder="Imagen hero (ruta)" class="w-full premium-input rounded-xl px-4 py-3 text-sm border border-slate-100">
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Secciones</h3>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="sec_slider_activo" value="1" <?= !empty($secSlider['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                <span class="font-bold text-sm text-slate-700">1. Slider</span>
            </label>
            <div class="pl-6 space-y-4 border-l-2 border-slate-100 ml-2">
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

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="sec_categorias_activo" value="1" <?= !empty($secCat['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                <span class="font-bold text-sm text-slate-700">2. Categorías</span>
            </label>
            <div class="grid sm:grid-cols-3 gap-4 pl-6">
                <input type="text" name="sec_categorias_titulo" value="<?= htmlspecialchars($secCat['titulo'] ?? '') ?>" placeholder="Título" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                <input type="text" name="sec_categorias_subtitulo" value="<?= htmlspecialchars($secCat['subtitulo'] ?? '') ?>" placeholder="Subtítulo" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100 sm:col-span-2">
                <input type="number" name="sec_categorias_limite" value="<?= (int)($secCat['limite'] ?? 8) ?>" min="4" max="12" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="sec_tendencias_activo" value="1" <?= !empty($secTrend['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                <span class="font-bold text-sm text-slate-700">3. Tendencias (ranking 48h)</span>
            </label>
            <div class="grid sm:grid-cols-3 gap-4 pl-6">
                <input type="text" name="sec_tendencias_titulo" value="<?= htmlspecialchars($secTrend['titulo'] ?? 'Tendencias') ?>" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                <input type="text" name="sec_tendencias_subtitulo" value="<?= htmlspecialchars($secTrend['subtitulo'] ?? '') ?>" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100 sm:col-span-2">
                <input type="number" name="sec_tendencias_limite" value="<?= (int)($secTrend['limite'] ?? 8) ?>" min="4" max="12" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="sec_mas_vendidos_activo" value="1" <?= !empty($secTop['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                <span class="font-bold text-sm text-slate-700">4. Más vendidos (impulsados)</span>
            </label>
            <div class="grid sm:grid-cols-3 gap-4 pl-6">
                <input type="text" name="sec_mas_vendidos_titulo" value="<?= htmlspecialchars($secTop['titulo'] ?? 'Más vendidos') ?>" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                <input type="text" name="sec_mas_vendidos_subtitulo" value="<?= htmlspecialchars($secTop['subtitulo'] ?? '') ?>" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100 sm:col-span-2">
                <input type="number" name="sec_mas_vendidos_limite" value="<?= (int)($secTop['limite'] ?? 8) ?>" min="4" max="12" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="sec_cta_activo" value="1" <?= !empty($secCta['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                <span class="font-bold text-sm text-slate-700">5. CTA rompetráfico</span>
            </label>
            <div class="pl-6 grid sm:grid-cols-2 gap-4">
                <input type="text" name="sec_cta_etiqueta" value="<?= htmlspecialchars($secCta['etiqueta'] ?? 'Asesoría') ?>" placeholder="Etiqueta" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                <input type="text" name="sec_cta_titulo" value="<?= htmlspecialchars($secCta['titulo'] ?? '') ?>" placeholder="Título" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                <input type="text" name="sec_cta_subtitulo" value="<?= htmlspecialchars($secCta['subtitulo'] ?? '') ?>" placeholder="Subtítulo" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100 sm:col-span-2">
                <input type="text" name="sec_cta_texto" value="<?= htmlspecialchars($secCta['cta_texto'] ?? 'Ir a la tienda') ?>" placeholder="Botón" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                <input type="text" name="sec_cta_url" value="<?= htmlspecialchars($secCta['cta_url'] ?? 'productos.php') ?>" placeholder="URL" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="sec_blog_activo" value="1" <?= !empty($secBlog['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                <span class="font-bold text-sm text-slate-700">6. Blog</span>
            </label>
            <div class="grid sm:grid-cols-2 gap-4 pl-6">
                <input type="text" name="sec_blog_titulo" value="<?= htmlspecialchars($secBlog['titulo'] ?? 'Desde el Blog') ?>" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                <input type="text" name="sec_blog_subtitulo" value="<?= htmlspecialchars($secBlog['subtitulo'] ?? '') ?>" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="sec_logos_activo" value="1" <?= !empty($secLogos['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                <span class="font-bold text-sm text-slate-700">7. Marcas aliadas</span>
            </label>
            <div class="grid sm:grid-cols-3 gap-4 pl-6">
                <input type="text" name="sec_logos_titulo" value="<?= htmlspecialchars($secLogos['titulo'] ?? 'Marcas aliadas') ?>" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                <input type="text" name="sec_logos_subtitulo" value="<?= htmlspecialchars($secLogos['subtitulo'] ?? '') ?>" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100 sm:col-span-2">
                <input type="number" name="sec_logos_limite" value="<?= (int)($secLogos['limite'] ?? 10) ?>" min="4" max="20" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="sec_locales_activo" value="1" <?= !empty($secLocales['activo']) ? 'checked' : '' ?> class="rounded border-slate-300">
                <span class="font-bold text-sm text-slate-700">8. Sucursales + asesoría</span>
            </label>
            <div class="grid sm:grid-cols-2 gap-4 pl-6">
                <input type="text" name="sec_locales_titulo" value="<?= htmlspecialchars($secLocales['titulo'] ?? 'Red de sucursales') ?>" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                <input type="text" name="sec_locales_subtitulo" value="<?= htmlspecialchars($secLocales['subtitulo'] ?? '') ?>" class="premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
            </div>
        </div>

        <button type="submit" class="w-full bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-4 rounded-xl transition-colors uppercase tracking-widest text-xs">
            Guardar portada
        </button>
    </form>

    <p class="text-center mt-6">
        <a href="index.php" target="_blank" class="text-[#3A86FF] font-bold text-sm hover:underline">Ver home en nueva pestaña →</a>
    </p>
</div>
