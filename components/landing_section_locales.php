<?php
require_once __DIR__ . '/../lib/landing_helpers.php';
$titulo = $sec['titulo'] ?? 'Red de sucursales';
$sub = $sec['subtitulo'] ?? '';
$h = improgyp_landing_section_heading($sec);
?>
<section class="max-w-[1200px] mx-auto px-6 py-12 md:py-20" id="sucursales-home">
    <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-start">
        <div>
            <?php if ($h['normal'] !== '' || $h['resalt'] !== ''): ?>
            <div class="mb-6">
                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-[#3A86FF] mb-2">Cerca de ti</p>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight leading-tight">
                    <?= htmlspecialchars($h['normal']) ?>
                    <?php if ($h['resalt'] !== ''): ?>
                    <span class="laser-text block sm:inline"><?= htmlspecialchars($h['resalt']) ?></span>
                    <?php endif; ?>
                </h2>
                <?php if ($h['sub'] !== ''): ?>
                <p class="text-slate-500 text-sm mt-2"><?= htmlspecialchars($h['sub']) ?></p>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <p class="text-[10px] font-black uppercase tracking-[0.25em] text-[#3A86FF] mb-2">Cerca de ti</p>
            <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-2"><?= htmlspecialchars($titulo) ?></h2>
            <?php if ($sub): ?><p class="text-slate-500 text-sm mb-6"><?= htmlspecialchars($sub) ?></p><?php endif; ?>
            <?php endif; ?>

            <div id="home-nearest-location-widget" class="mb-4 min-h-[120px]">
                <p class="text-sm text-slate-400">Cargando sucursal más cercana…</p>
            </div>

            <button type="button" onclick="typeof abrirModalLocales==='function'&&abrirModalLocales()" class="w-full text-[12px] font-black text-[#3A86FF] hover:text-[#1B263B] transition-colors flex items-center justify-center gap-2 py-3 border border-[#3A86FF]/20 rounded-xl bg-[#3A86FF]/5 hover:bg-[#3A86FF]/10">
                <i class="fa-solid fa-map-location-dot"></i> Ver todas las sucursales
            </button>
        </div>

        <div class="glass-card-landing p-6 md:p-8">
            <h3 class="text-lg font-black text-slate-900 mb-1">Asesoría técnica</h3>
            <p class="text-sm text-slate-500 mb-6">Cuéntanos tu proyecto y un especialista te contacta.</p>
            <form id="form-asesoria-home" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Nombre</label>
                    <input type="text" name="nombre" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold" placeholder="Tu nombre">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Teléfono</label>
                        <input type="tel" name="telefono" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Email</label>
                        <input type="email" name="email" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Mensaje</label>
                    <textarea name="mensaje" rows="3" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold" placeholder="Tipo de obra, materiales que necesitas…"></textarea>
                </div>
                <p id="asesoria-home-msg" class="text-sm font-bold hidden"></p>
                <button type="submit" class="w-full bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-4 rounded-xl text-xs uppercase tracking-widest transition-colors">
                    Enviar solicitud
                </button>
            </form>
        </div>
    </div>
</section>

<?php
if (!defined('IMPROGYP_LOCALES_MODAL')) {
    define('IMPROGYP_LOCALES_MODAL', true);
    include __DIR__ . '/locales_modal.php';
}
?>
