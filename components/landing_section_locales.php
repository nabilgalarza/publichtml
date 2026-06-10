<?php
require_once __DIR__ . '/../lib/landing_helpers.php';
$titulo = $sec['titulo'] ?? 'Red de sucursales';
$sub = $sec['subtitulo'] ?? '';
$h = improgyp_landing_section_heading($sec);
?>
<section class="locales-premium-section" id="sucursales-home">
    <div class="max-w-[1200px] mx-auto px-6">
        <div class="locales-premium-shell">
            <div class="locales-premium-grid">
                <div class="locales-premium-locations locales-showroom">
                    <?php if ($h['normal'] !== '' || $h['resalt'] !== ''): ?>
                    <div class="locales-premium-heading mb-8">
                        <p class="locales-premium-eyebrow">Cerca de ti</p>
                        <h2 class="text-3xl md:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight leading-tight">
                            <?= htmlspecialchars($h['normal']) ?>
                            <?php if ($h['resalt'] !== ''): ?>
                            <span class="laser-text block sm:inline"><?= htmlspecialchars($h['resalt']) ?></span>
                            <?php endif; ?>
                        </h2>
                        <?php if ($h['sub'] !== ''): ?>
                        <p class="text-slate-500 text-sm md:text-base mt-3 leading-relaxed"><?= htmlspecialchars($h['sub']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="locales-premium-heading mb-8">
                        <p class="locales-premium-eyebrow">Cerca de ti</p>
                        <h2 class="text-3xl md:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight leading-tight"><?= htmlspecialchars($titulo) ?></h2>
                        <?php if ($sub): ?>
                        <p class="text-slate-500 text-sm md:text-base mt-3 leading-relaxed"><?= htmlspecialchars($sub) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div id="home-nearest-location-widget" class="locales-premium-widget locales-showroom-widget min-h-[120px]">
                        <p class="text-sm text-slate-400 font-medium">Cargando sucursal más cercana…</p>
                    </div>

                    <button type="button" onclick="typeof abrirModalLocales==='function'&&abrirModalLocales()" class="locales-premium-ghost-btn w-full locales-home-cta locales-home-cta--mobile">
                        <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
                        <span>Ver todas las sucursales</span>
                    </button>
                    <button type="button" onclick="typeof abrirModalLocales==='function'&&abrirModalLocales()" class="locales-showroom-cta locales-home-cta locales-home-cta--desktop">
                        <i class="fa-solid fa-images" aria-hidden="true"></i>
                        <span>Ver todas las sucursales</span>
                    </button>
                </div>

                <div class="asesoria-premium-panel">
                    <div class="asesoria-premium-panel__inner">
                        <div class="asesoria-premium-panel__head">
                            <span class="asesoria-premium-badge" aria-hidden="true"><i class="fa-solid fa-headset"></i></span>
                            <div>
                                <h3 class="asesoria-premium-title">Asesoría técnica</h3>
                                <p class="asesoria-premium-lead">Cuéntanos tu proyecto y un especialista te contacta.</p>
                            </div>
                        </div>
                        <p class="asesoria-premium-trust">Respuesta en horario laboral · Sin compromiso de compra</p>

                        <form id="form-asesoria-home" class="asesoria-premium-form space-y-4">
                            <div>
                                <label class="asesoria-premium-label" for="asesoria-nombre">Nombre</label>
                                <input type="text" id="asesoria-nombre" name="nombre" required class="asesoria-premium-input" placeholder="Tu nombre" autocomplete="name">
                            </div>
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="asesoria-premium-label" for="asesoria-telefono">Teléfono</label>
                                    <input type="tel" id="asesoria-telefono" name="telefono" required class="asesoria-premium-input" autocomplete="tel">
                                </div>
                                <div>
                                    <label class="asesoria-premium-label" for="asesoria-email">Email</label>
                                    <input type="email" id="asesoria-email" name="email" class="asesoria-premium-input" autocomplete="email">
                                </div>
                            </div>
                            <div>
                                <label class="asesoria-premium-label" for="asesoria-mensaje">Mensaje</label>
                                <textarea id="asesoria-mensaje" name="mensaje" rows="3" required class="asesoria-premium-input asesoria-premium-textarea" placeholder="Tipo de obra, materiales que necesitas…"></textarea>
                            </div>
                            <p id="asesoria-home-msg" class="asesoria-premium-msg hidden" role="status"></p>
                            <button type="submit" id="asesoria-submit-btn" class="asesoria-premium-submit">
                                <span class="asesoria-premium-submit__label">Enviar solicitud</span>
                                <i class="fa-solid fa-paper-plane asesoria-premium-submit__icon" aria-hidden="true"></i>
                                <span class="asesoria-premium-submit__spinner hidden" aria-hidden="true"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
if (!defined('IMPROGYP_LOCALES_MODAL')) {
    define('IMPROGYP_LOCALES_MODAL', true);
    include __DIR__ . '/locales_modal.php';
}
