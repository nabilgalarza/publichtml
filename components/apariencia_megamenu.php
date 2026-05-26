<?php
require_once __DIR__ . '/megamenu_config.php';

$header_config_path = __DIR__ . '/../config_header.json';
$header_data = [];
if (file_exists($header_config_path)) {
    $header_data = json_decode(file_get_contents($header_config_path), true) ?? [];
}

$megamenu_initial = improgyp_normalize_megamenu($header_data['megamenu'] ?? null);
$categorias_reales = improgyp_megamenu_categorias_from_catalogo();

$catalogo = [];
$cat_path = __DIR__ . '/../catalogo.json';
if (file_exists($cat_path)) {
    $catalogo = json_decode(file_get_contents($cat_path), true) ?? [];
}
?>
<style>
    #mm-preview .sidebar-tab.active { background: #fff; color: #1B263B; border-color: #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
</style>

<div class="max-w-[1600px] mx-auto space-y-6">
    <div class="flex flex-wrap justify-between items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Megamenú B2C</h2>
            <p class="text-slate-500 text-sm mt-1">Panel «Explorar Divisiones» del header público. Se guarda en <code class="text-xs bg-slate-100 px-1 rounded">config_header.json</code>.</p>
            <p id="catalog-status" class="text-[10px] font-bold text-emerald-700 mt-2">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                <?= count($catalogo) ?> productos · <?= count($categorias_reales) ?> categorías en catálogo
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="mmResetDefaults()" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl text-xs hover:bg-slate-50">
                <i class="fa-solid fa-arrow-rotate-left mr-1"></i> Restablecer
            </button>
            <button type="submit" form="form-megamenu" class="px-6 py-2.5 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 transition-all shadow-sm">
                <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar cambios
            </button>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'guardado'): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl text-sm font-bold flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i> Megamenú guardado. Los cambios ya están visibles en la tienda pública.
    </div>
    <?php endif; ?>

    <form id="form-megamenu" action="dashboard.php" method="POST" class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <input type="hidden" name="action" value="guardar_megamenu">
        <input type="hidden" name="megamenu_json" id="megamenu_json" value="">

        <div class="xl:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-xs text-slate-500 leading-relaxed">
                <strong class="text-slate-700">Pestaña</strong> = división del sidebar.
                <strong class="text-slate-700">Títulos de columna</strong> = solo texto visual.
                <strong class="text-slate-700">Enlaces</strong> = clic del cliente (categoría exacta o búsqueda).
            </div>
            <div id="divisiones-container" class="space-y-4"></div>
            <button type="button" onclick="mmAgregarDivision()" class="w-full py-3 border-2 border-dashed border-emerald-300 text-emerald-700 rounded-2xl font-bold text-xs bg-white hover:bg-emerald-50/30">
                <i class="fa-solid fa-plus mr-1"></i> Añadir pestaña (división)
            </button>
        </div>

        <div class="xl:col-span-5">
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm sticky top-24">
                <h3 class="text-sm font-black text-slate-800 mb-3">Vista previa</h3>
                <div id="mm-preview" class="bg-slate-50 rounded-2xl p-2 border border-slate-200">
                    <div class="bg-white/95 rounded-[18px] border border-slate-200 shadow-lg grid grid-cols-4 min-h-[340px] overflow-hidden text-left">
                        <div class="bg-slate-50/80 p-3 border-r border-slate-100 col-span-1 flex flex-col justify-between">
                            <div class="space-y-1" id="preview-tabs"></div>
                            <p class="text-[7px] font-black text-[#1B263B] uppercase mt-2 opacity-50 ml-1">Ver catálogo →</p>
                        </div>
                        <div class="col-span-2 p-4 grid grid-cols-2 gap-3" id="preview-center"></div>
                        <div class="bg-slate-50/50 p-3 border-l border-slate-100 col-span-1 flex flex-col justify-between text-[8px] text-slate-400 select-none">
                            <div class="space-y-1.5 opacity-80">
                                <div class="p-2 bg-[#3A86FF]/5 border border-[#3A86FF]/10 rounded-lg font-bold text-[#3A86FF]">Asesoría (fijo)</div>
                                <div class="p-1.5 border rounded bg-white">WhatsApp</div>
                                <div class="p-1.5 border rounded bg-white">Sucursales</div>
                            </div>
                            <div class="p-2 bg-slate-100 rounded text-center mt-2 font-black uppercase">Portal B2B</div>
                        </div>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 mt-2">Pasa el mouse sobre las pestañas para previsualizar columnas centrales.</p>
            </div>
        </div>
    </form>
</div>

<script>
(function() {
    const catalogo = <?= json_encode($catalogo, JSON_UNESCAPED_UNICODE) ?>;
    const categoriasReales = <?= json_encode($categorias_reales, JSON_UNESCAPED_UNICODE) ?>;
    const MEGAMENU_DEFAULTS = <?= json_encode(improgyp_megamenu_defaults(), JSON_UNESCAPED_UNICODE) ?>;

    let megamenuState = <?= json_encode($megamenu_initial, JSON_UNESCAPED_UNICODE) ?>;
    let previewActiveId = megamenuState[0]?.id || null;

    function normalize(str) {
        return (str || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    }
    function slugId(title) {
        return normalize(title).replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '') || 'division';
    }
    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
    }

    function countForLink(link) {
        if (!catalogo.length) return null;
        if (link.linkType === 'category') {
            return catalogo.filter(p => p.categoria === link.linkValue).length;
        }
        const q = normalize(link.linkValue);
        if (!q) return 0;
        return catalogo.filter(p =>
            normalize(p.nombre).includes(q) ||
            normalize(p.categoria).includes(q) ||
            normalize(p.marca || '').includes(q) ||
            normalize(p.codigo || '').includes(q)
        ).length;
    }

    function badgeHtml(count, link) {
        if (count === null) return '';
        if (link.linkType === 'category' && !categoriasReales.includes(link.linkValue)) {
            return '<span class="text-[9px] font-black text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded">Categoría no existe</span>';
        }
        if (count === 0) return '<span class="text-[9px] font-black text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">0 productos</span>';
        return `<span class="text-[9px] font-black text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">~${count} producto${count !== 1 ? 's' : ''}</span>`;
    }

    function iconColorOptions(selected) {
        const opts = [['text-[#3A86FF]','Azul'],['text-amber-500','Ámbar'],['text-slate-400','Gris'],['text-emerald-500','Verde'],['text-rose-500','Rojo']];
        return opts.map(([v,l]) => `<option value="${v}" ${selected===v?'selected':''}>${l}</option>`).join('');
    }

    function syncHiddenInput() {
        document.getElementById('megamenu_json').value = JSON.stringify(megamenuState);
    }

    window.mmResetDefaults = function() {
        if (!confirm('¿Restablecer el megamenú a los valores por defecto de IMPROGYP? Debes guardar para aplicar en la tienda.')) return;
        megamenuState = JSON.parse(JSON.stringify(MEGAMENU_DEFAULTS));
        previewActiveId = megamenuState[0]?.id;
        renderAll();
    };

    window.mmAgregarDivision = function() {
        const n = megamenuState.length + 1;
        megamenuState.push({
            id: slugId('division ' + n) + '_' + Date.now(),
            title: 'Nueva división ' + n,
            icon: 'fa-tag', iconColor: 'text-slate-400',
            titleLeft: 'GRUPO IZQUIERDO', titleRight: 'GRUPO DERECHO',
            linksLeft: [{ name: 'Nuevo enlace', linkType: 'category', linkValue: categoriasReales[0] || '' }],
            linksRight: []
        });
        renderAll();
    };

    function patchDiv(idx, field, value) {
        megamenuState[idx][field] = value;
        if (field === 'title') {
            const base = slugId(value);
            let id = base, n = 0;
            while (megamenuState.some((d, i) => i !== idx && d.id === id)) id = base + '_' + (++n);
            megamenuState[idx].id = id;
        }
        if (field === 'icon' || field === 'iconColor') {
            const el = document.getElementById('icon-prev-' + idx);
            if (el) {
                if (field === 'icon') { const ic = el.querySelector('i'); if (ic) ic.className = 'fa-solid ' + (value || 'fa-tag'); }
                else el.className = 'w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center ' + value;
            }
        }
        renderPreview();
        syncHiddenInput();
        if (field === 'title') renderEditor();
    }

    function patchLink(divIdx, key, linkIdx, field, value) {
        megamenuState[divIdx][key][linkIdx][field] = value;
        renderAll();
    }

    window.mmPatchLinkType = function(divIdx, key, linkIdx, type) {
        const link = megamenuState[divIdx][key][linkIdx];
        link.linkType = type;
        link.linkValue = type === 'category' ? (categoriasReales[0] || '') : ((link.name || '').split(' ')[0] || '');
        renderAll();
    };

    window.mmEliminarEnlace = function(divIdx, key, linkIdx) {
        megamenuState[divIdx][key].splice(linkIdx, 1);
        renderAll();
    };

    window.mmAgregarEnlace = function(divIdx, key) {
        megamenuState[divIdx][key].push({ name: 'Nuevo enlace', linkType: 'category', linkValue: categoriasReales[0] || '' });
        renderAll();
    };

    window.mmEliminarDivision = function(idx) {
        if (!confirm('¿Eliminar esta pestaña?')) return;
        const rid = megamenuState[idx].id;
        megamenuState.splice(idx, 1);
        if (previewActiveId === rid) previewActiveId = megamenuState[0]?.id;
        renderAll();
    };

    window.mmSetPreviewTab = function(id) { previewActiveId = id; renderPreview(); };

    function linkRowHtml(divIdx, key, linkIdx, link) {
        const count = countForLink(link);
        const catOpts = categoriasReales.map(c => `<option value="${esc(c)}" ${link.linkValue===c && link.linkType==='category'?'selected':''}>${esc(c)}</option>`).join('');
        const valField = link.linkType === 'category'
            ? `<select onchange="patchLink(${divIdx},'${key}',${linkIdx},'linkValue',this.value)" class="text-xs font-bold border border-slate-200 rounded-lg px-2 py-1.5 flex-grow max-w-[220px]"><option value="">— Categoría —</option>${catOpts}</select>`
            : `<input type="text" value="${esc(link.linkValue)}" placeholder="Palabra clave" oninput="patchLink(${divIdx},'${key}',${linkIdx},'linkValue',this.value)" class="text-xs font-bold border border-slate-200 rounded-lg px-2 py-1.5 flex-grow max-w-[220px]">`;

        return `<div class="bg-white rounded-xl border border-slate-200 p-3 space-y-2">
            <div class="flex flex-wrap items-center gap-2 justify-between">
                <input type="text" value="${esc(link.name)}" oninput="patchLink(${divIdx},'${key}',${linkIdx},'name',this.value)" class="text-xs font-black flex-grow min-w-[100px] border-b border-slate-100 outline-none">
                ${badgeHtml(count, link)}
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <select onchange="mmPatchLinkType(${divIdx},'${key}',${linkIdx},this.value)" class="text-[10px] font-black border border-slate-200 rounded-lg px-2 py-1.5">
                    <option value="category" ${link.linkType==='category'?'selected':''}>Abrir categoría</option>
                    <option value="search" ${link.linkType==='search'?'selected':''}>Buscar palabra</option>
                </select>
                ${valField}
                <button type="button" onclick="mmEliminarEnlace(${divIdx},'${key}',${linkIdx})" class="text-slate-300 hover:text-rose-500 p-1 ml-auto"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>`;
    }

    function columnEditor(divIdx, linksKey, titleKey, titleLabel, linksLabel, linksHtml) {
        return `<div class="bg-slate-50/80 rounded-xl border border-slate-100 p-4 space-y-3">
            <div>
                <label class="text-[9px] font-black text-slate-400 uppercase">${titleLabel}</label>
                <input type="text" value="${esc(megamenuState[divIdx][titleKey])}" oninput="patchDiv(${divIdx},'${titleKey}',this.value)" class="w-full mt-1 text-xs font-black border border-slate-200 rounded-lg px-3 py-2">
                <p class="text-[9px] text-slate-400 mt-1">Solo decoración; no filtra productos.</p>
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-400 uppercase">${linksLabel}</label>
                <div class="space-y-2 mt-2">${linksHtml}</div>
                <button type="button" onclick="mmAgregarEnlace(${divIdx},'${linksKey}')" class="mt-2 text-[10px] font-bold text-emerald-600"><i class="fa-solid fa-plus"></i> Añadir enlace</button>
            </div>
        </div>`;
    }

    function renderEditor() {
        const c = document.getElementById('divisiones-container');
        if (!megamenuState.length) {
            c.innerHTML = '<p class="text-sm text-slate-400 font-bold text-center py-8 bg-white rounded-2xl border">Sin divisiones.</p>';
            return;
        }
        c.innerHTML = megamenuState.map((div, idx) => {
            const lL = div.linksLeft.map((l,i) => linkRowHtml(idx,'linksLeft',i,l)).join('');
            const lR = div.linksRight.map((l,i) => linkRowHtml(idx,'linksRight',i,l)).join('');
            return `<article class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 px-5 py-4 border-b flex flex-wrap justify-between gap-3">
                    <div class="flex items-center gap-3 flex-grow">
                        <div class="w-10 h-10 rounded-xl bg-white border flex items-center justify-center ${div.iconColor}" id="icon-prev-${idx}"><i class="fa-solid ${div.icon}"></i></div>
                        <div class="flex-grow">
                            <label class="text-[9px] font-black text-slate-400 uppercase">Pestaña sidebar</label>
                            <input type="text" value="${esc(div.title)}" oninput="patchDiv(${idx},'title',this.value)" class="block w-full text-sm font-black border-b outline-none bg-transparent">
                        </div>
                    </div>
                    <button type="button" onclick="mmEliminarDivision(${idx})" class="text-slate-400 hover:text-rose-500 p-2"><i class="fa-solid fa-trash-can"></i></button>
                </div>
                <details class="border-b"><summary class="px-5 py-2 text-[10px] font-bold text-slate-400 cursor-pointer">Icono (avanzado)</summary>
                    <div class="px-5 pb-3 flex gap-3 flex-wrap">
                        <input type="text" value="${esc(div.icon)}" oninput="patchDiv(${idx},'icon',this.value)" placeholder="fa-bolt" class="text-xs font-mono border rounded-lg px-2 py-1 w-32">
                        <select onchange="patchDiv(${idx},'iconColor',this.value)" class="text-xs border rounded-lg px-2 py-1">${iconColorOptions(div.iconColor)}</select>
                    </div>
                </details>
                <div class="p-5 grid md:grid-cols-2 gap-4">
                    ${columnEditor(idx,'linksLeft','titleLeft','Título columna izquierda','Enlaces izquierda',lL)}
                    ${columnEditor(idx,'linksRight','titleRight','Título columna derecha','Enlaces derecha',lR)}
                </div>
            </article>`;
        }).join('');
    }

    function renderPreview() {
        const tabs = document.getElementById('preview-tabs');
        const center = document.getElementById('preview-center');
        if (!tabs || !center) return;
        tabs.innerHTML = megamenuState.map(div => {
            const act = div.id === previewActiveId;
            return `<button type="button" onmouseenter="mmSetPreviewTab('${div.id.replace(/'/g,"\\'")}')" class="sidebar-tab w-full text-left px-2 py-2 rounded-lg text-[9px] font-black flex justify-between border border-transparent ${act?'active text-slate-800':'text-slate-500'}">
                <span class="flex items-center gap-1 truncate"><i class="fa-solid ${div.icon} ${div.iconColor} text-[9px]"></i> ${esc(div.title)}</span>
            </button>`;
        }).join('');
        const active = megamenuState.find(d => d.id === previewActiveId) || megamenuState[0];
        if (!active) { center.innerHTML = ''; return; }
        const col = (title, links) => `<div><h5 class="text-[8px] font-black uppercase border-b pb-1 mb-1 text-[#1B263B]">${esc(title)}</h5><ul class="space-y-1">${(links||[]).map(l=>`<li class="text-[9px] text-slate-500 font-bold">${esc(l.name)}</li>`).join('')||'<li class="text-slate-300">—</li>'}</ul></div>`;
        center.innerHTML = col(active.titleLeft, active.linksLeft) + col(active.titleRight, active.linksRight);
    }

    function renderAll() {
        renderEditor();
        renderPreview();
        syncHiddenInput();
    }

    window.patchDiv = patchDiv;
    window.patchLink = patchLink;

    document.getElementById('form-megamenu').addEventListener('submit', function() {
        syncHiddenInput();
    });

    renderAll();
})();
</script>
