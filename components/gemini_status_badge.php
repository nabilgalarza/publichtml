<?php
/** Badge de estado Gemini (requiere $env del dashboard). */
if (!function_exists('improgyp_gemini_esta_configurado')) {
    require_once __DIR__ . '/../lib/copy_ia_helpers.php';
}
$envGemini = is_array($env ?? null) ? $env : [];
$gemini_ok = improgyp_gemini_esta_configurado($envGemini);
$gemini_model_label = improgyp_gemini_model($envGemini);
?>
<div class="flex flex-wrap items-center gap-2 mb-4 p-3 rounded-xl border text-[11px] font-bold <?= $gemini_ok ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-900' ?>">
    <span class="inline-flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full <?= $gemini_ok ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' ?>"></span>
        <?= $gemini_ok ? 'Gemini conectada' : 'Gemini sin API key' ?>
    </span>
    <span class="text-slate-500 font-medium">· Modelo: <code class="text-[10px] bg-white/80 px-1.5 py-0.5 rounded"><?= htmlspecialchars($gemini_model_label) ?></code></span>
    <?php if (!$gemini_ok): ?>
        <span class="w-full text-[10px] font-medium text-amber-800/90">Añade <code>GEMINI_API_KEY</code> en <code>.env</code> (copia desde <code>.env.example</code>).</span>
    <?php endif; ?>
</div>
