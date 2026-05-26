<?php
require_once __DIR__ . '/../lib/landing_helpers.php';

$landing = improgyp_landing_config();
$secciones = $landing['secciones'];

$iaKeys = [
    'categorias' => 'Categorías',
    'tendencias' => 'Tendencias',
    'mas_vendidos' => 'Más vendidos',
    'logos' => 'Marcas aliadas',
];

function improgyp_seccion_ia_data($secciones, $tipo) {
    foreach ($secciones as $s) {
        if (($s['tipo'] ?? '') === $tipo || ($tipo === 'mas_vendidos' && ($s['tipo'] ?? '') === 'destacados')) {
            $h = improgyp_landing_section_heading($s);
            return $h;
        }
    }
    return ['normal' => '', 'resalt' => '', 'sub' => ''];
}
?>
<div class="max-w-[900px] mx-auto">
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'secciones_guardadas'): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 p-4 rounded-xl mb-6 text-sm font-bold">
        <i class="fa-solid fa-circle-check"></i> Encabezados del home guardados.
    </div>
    <?php endif; ?>

    <div class="mb-6">
        <h2 class="text-xl font-black text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-layer-group text-[#3A86FF]"></i> Secciones del Home
        </h2>
        <p class="text-sm text-slate-500 mt-1">Títulos con efecto láser para categorías, tendencias, más vendidos y marcas. Slider y CTA en <a href="?view=apariencia&sub=portada" class="text-[#3A86FF] font-bold underline">Portada</a>.</p>
        <p class="text-[11px] text-slate-400 mt-2">Un solo botón IA a la vez.</p>
    </div>

    <form method="POST" action="dashboard.php?view=apariencia&sub=secciones" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <input type="hidden" name="action" value="guardar_secciones_landing">

        <?php foreach ($iaKeys as $key => $label):
            $h = improgyp_seccion_ia_data($secciones, $key);
        ?>
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h3 class="font-black text-slate-800"><?= htmlspecialchars($label) ?></h3>
                <button type="button" onclick="generarCopySeccionIA('<?= $key ?>', 'tit_norm_<?= $key ?>', 'tit_resal_<?= $key ?>', 'sub_<?= $key ?>', this)" class="btn-copy-ia bg-[#1B263B]/10 hover:bg-[#1B263B] text-[#1B263B] hover:text-white border border-[#1B263B]/20 rounded-lg py-1.5 px-4 text-xs font-black transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-robot"></i> <span class="btn-text">IA</span>
                </button>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Título (línea 1)</label>
                    <input type="text" id="tit_norm_<?= $key ?>" name="titulo_normal[<?= $key ?>]" value="<?= htmlspecialchars($h['normal']) ?>" class="w-full premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Título láser</label>
                    <input type="text" id="tit_resal_<?= $key ?>" name="titulo_resaltado[<?= $key ?>]" value="<?= htmlspecialchars($h['resalt']) ?>" class="w-full premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Subtítulo</label>
                <input type="text" id="sub_<?= $key ?>" name="subtitulo[<?= $key ?>]" value="<?= htmlspecialchars($h['sub']) ?>" class="w-full premium-input rounded-xl px-4 py-2 text-sm border border-slate-100">
            </div>
        </div>
        <?php endforeach; ?>

        <button type="submit" class="w-full bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-4 rounded-xl uppercase tracking-widest text-xs">
            Guardar encabezados
        </button>
    </form>
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
                tit_actual: document.getElementById(idNorm)?.value || '',
                resal_actual: document.getElementById(idResal)?.value || '',
                sub_actual: document.getElementById(idSub)?.value || '',
                regenerar: true
            })
        });
        const raw = await response.text();
        let data;
        try { data = JSON.parse(raw); } catch (e) { throw new Error('Respuesta no válida'); }
        if (data.error) {
            alert('Error IA: ' + data.error);
        } else if (data.tit_normal !== undefined) {
            document.getElementById(idNorm).value = data.tit_normal;
            document.getElementById(idResal).value = data.tit_resaltado || '';
            document.getElementById(idSub).value = data.sub || '';
            if (icon) icon.className = 'fa-solid fa-check';
            if (textSpan) textSpan.innerText = 'Ok';
        }
    } catch (err) {
        alert(err.message || 'Error de conexión');
    } finally {
        setTimeout(() => {
            if (icon) icon.className = 'fa-solid fa-robot';
            if (textSpan) textSpan.innerText = 'IA';
            btnElement.classList.remove('bg-[#1B263B]', 'text-white');
            window.iaCopyEnCurso = false;
            setEstadoBotonesCopyIA(false);
        }, 1200);
    }
}
</script>
