<?php
require_once __DIR__ . '/megamenu_config.php';

$header_config_path = __DIR__ . '/../config_header.json';
$header_data = [];
if (file_exists($header_config_path)) {
    $header_data = json_decode(file_get_contents($header_config_path), true) ?? [];
}

$megamenu_stored_raw = $header_data['megamenu'] ?? null;
$megamenu_stored_empty = improgyp_megamenu_is_stored_empty($megamenu_stored_raw);
$megamenu_initial = improgyp_normalize_megamenu($megamenu_stored_raw);
$nivel3_initial = improgyp_normalize_nivel3_menu($header_data['nivel3_menu'] ?? null);
$orphan_cats_initial = improgyp_megamenu_orphan_categories($megamenu_initial, improgyp_megamenu_categorias_from_catalogo());
$categorias_reales = improgyp_megamenu_categorias_from_catalogo();
improgyp_megamenu_refresh_orphan_session();

$catalogo = [];
$cat_path = __DIR__ . '/../catalogo.json';
if (file_exists($cat_path)) {
    $catalogo = json_decode(file_get_contents($cat_path), true) ?? [];
}
?>
<style>
    #mm-preview .sidebar-tab.active { background: #fff; color: #1B263B; border-color: #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
    #mm-preview.mm-preview-mobile .mm-preview-desktop { display: none !important; }
    #mm-preview.mm-preview-mobile .mm-preview-mobile-panel { display: block !important; }
    #mm-preview:not(.mm-preview-mobile) .mm-preview-mobile-panel { display: none !important; }
    .mm-preview-mode-btn.active { background: #1B263B; color: #fff; border-color: #1B263B; }
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
        <div class="flex flex-wrap items-center gap-2">
            <a href="../productos.php" target="_blank" rel="noopener" class="px-4 py-2 border border-[#0E75AE]/30 text-[#0E75AE] font-bold rounded-xl text-xs hover:bg-blue-50">
                <i class="fa-solid fa-store mr-1"></i> Ver en tienda
            </a>
            <button type="button" onclick="mmResetDefaults()" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl text-xs hover:bg-slate-50">
                <i class="fa-solid fa-arrow-rotate-left mr-1"></i> Restablecer
            </button>
            <button type="submit" form="form-megamenu" class="px-6 py-2.5 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 transition-all shadow-sm">
                <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar cambios
            </button>
        </div>
    </div>

    <?php if ($megamenu_stored_empty): ?>
    <div class="bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-xl text-sm flex gap-3 items-start">
        <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
        <div>
            <p class="font-black">Megamenú no guardado en JSON</p>
            <p class="text-xs mt-1 text-amber-800/90">En <code class="bg-amber-100/80 px-1 rounded">config_header.json</code> el array <code>megamenu</code> está vacío. La tienda muestra los valores por defecto hasta que pulses <strong>Guardar cambios</strong>.</p>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'guardado'): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl text-sm font-bold flex flex-wrap items-center gap-3">
        <span class="flex items-center gap-2"><i class="fa-solid fa-circle-check"></i> Megamenú guardado. Los cambios ya están visibles en la tienda pública.</span>
        <a href="../productos.php" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wide text-emerald-800 underline hover:text-emerald-950">
            Ver en tienda <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
        </a>
    </div>
    <?php
        $orphans_after_save = (int) ($_SESSION['megamenu_orphan_count'] ?? 0);
        if ($orphans_after_save > 0):
    ?>
    <div class="bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-xl text-xs font-medium">
        Aún hay <strong><?= $orphans_after_save ?></strong> categoría<?= $orphans_after_save === 1 ? '' : 's' ?> del catálogo sin enlace en el menú.
        <a href="#mm-orphans-panel" class="font-black underline text-amber-950 ml-1">Sincronizar aquí</a>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <form id="form-megamenu" action="dashboard.php" method="POST" class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
        <input type="hidden" name="action" value="guardar_megamenu">
        <input type="hidden" name="megamenu_json" id="megamenu_json" value="">
        <input type="hidden" name="nivel3_json" id="nivel3_json" value="">

        <div class="xl:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm text-xs text-slate-500 leading-relaxed">
                <strong class="text-slate-700">Pestaña</strong> = división del sidebar.
                <strong class="text-slate-700">Títulos de columna</strong> = solo texto visual.
                <strong class="text-slate-700">Enlaces</strong> = clic del cliente (categoría exacta o búsqueda).
                <span class="block mt-2 text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2 font-bold">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    En «Abrir categoría», el valor debe coincidir <em>literalmente</em> con el nombre en el catálogo (mayúsculas, tildes y espacios incluidos).
                </span>
            </div>

            <div id="mm-orphans-panel" class="bg-white rounded-2xl border border-amber-200 p-4 shadow-sm scroll-mt-24 <?= empty($orphan_cats_initial) ? 'hidden' : '' ?>">
                <h3 class="text-xs font-black text-slate-700 uppercase tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-link-slash text-amber-500"></i> Categorías sin enlace en el menú
                    <span id="mm-orphans-count" class="ml-auto text-[10px] font-black text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full"><?= count($orphan_cats_initial) ?></span>
                </h3>
                <p class="text-[10px] text-slate-500 mt-1">Existen en el catálogo pero no aparecen en «Explorar Divisiones». Añádelas aquí y pulsa <strong>Guardar cambios</strong> para publicar.</p>
                <ul id="mm-orphans-list" class="mt-3 flex flex-wrap gap-2 text-[10px] font-bold text-amber-800"></ul>
                <div id="mm-orphans-actions" class="mt-4 flex flex-wrap gap-2">
                    <button type="button" onclick="mmOpenOrphanModal()" class="px-4 py-2 bg-[#1B263B] text-white font-black text-[10px] uppercase tracking-wide rounded-xl hover:bg-[#0E75AE] transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Añadir al menú…
                    </button>
                    <button type="button" onclick="mmAutoDistributeOrphans()" class="px-4 py-2 border border-amber-300 text-amber-900 font-black text-[10px] uppercase tracking-wide rounded-xl hover:bg-amber-50 transition-colors">
                        <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Distribuir automáticamente
                    </button>
                </div>
            </div>

            <div id="mm-orphan-modal" class="fixed inset-0 z-[200] hidden items-center justify-center p-4" aria-hidden="true">
                <div class="absolute inset-0 bg-slate-900/50" onclick="mmCloseOrphanModal()"></div>
                <div class="relative bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg p-6 z-10">
                    <h3 class="text-sm font-black text-slate-900">Añadir categorías al megamenú</h3>
                    <p class="text-[11px] text-slate-500 mt-1">El texto del enlace será el nombre exacto de la categoría (filtro en tienda).</p>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[9px] font-black text-slate-400 uppercase">División (pestaña)</label>
                            <select id="mm-orphan-division" class="w-full mt-1 text-xs font-bold border border-slate-200 rounded-lg px-3 py-2"></select>
                        </div>
                        <div>
                            <label class="text-[9px] font-black text-slate-400 uppercase">Columna</label>
                            <select id="mm-orphan-column" class="w-full mt-1 text-xs font-bold border border-slate-200 rounded-lg px-3 py-2">
                                <option value="left">Izquierda</option>
                                <option value="right">Derecha</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 max-h-48 overflow-y-auto custom-scrollbar border border-slate-100 rounded-xl p-3 space-y-2" id="mm-orphan-checkboxes"></div>
                    <div class="mt-4 flex flex-wrap justify-end gap-2">
                        <button type="button" onclick="mmCloseOrphanModal()" class="px-4 py-2 text-slate-500 font-bold text-xs rounded-xl hover:bg-slate-50">Cancelar</button>
                        <button type="button" onclick="mmApplyOrphanModal()" class="px-5 py-2 bg-emerald-500 text-white font-black text-xs rounded-xl hover:bg-emerald-600">Añadir seleccionadas</button>
                    </div>
                </div>
            </div>

            <details class="bg-white rounded-2xl border border-violet-200 shadow-sm overflow-hidden">
                <summary class="px-5 py-4 font-black text-sm text-slate-800 cursor-pointer bg-violet-50/80">
                    <i class="fa-solid fa-bars-staggered text-violet-500 mr-2"></i> Pie del megamenú (nivel 3)
                </summary>
                <div class="p-5 border-t border-violet-100 space-y-2" id="nivel3-container"></div>
                <div class="px-5 pb-5">
                    <button type="button" onclick="mmAgregarNivel3()" class="text-[10px] font-bold text-violet-600"><i class="fa-solid fa-plus"></i> Añadir enlace</button>
                </div>
            </details>

            <div id="divisiones-container" class="space-y-4"></div>
            <button type="button" onclick="mmAgregarDivision()" class="w-full py-3 border-2 border-dashed border-emerald-300 text-emerald-700 rounded-2xl font-bold text-xs bg-white hover:bg-emerald-50/30">
                <i class="fa-solid fa-plus mr-1"></i> Añadir pestaña (división)
            </button>
        </div>

        <div class="xl:col-span-5">
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm sticky top-24">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <h3 class="text-sm font-black text-slate-800">Vista previa</h3>
                    <div class="flex rounded-lg border border-slate-200 overflow-hidden text-[10px] font-black uppercase">
                        <button type="button" id="mm-preview-desktop-btn" class="mm-preview-mode-btn active px-3 py-1.5 bg-white text-slate-600" onclick="mmSetPreviewMode('desktop')">Desktop</button>
                        <button type="button" id="mm-preview-mobile-btn" class="mm-preview-mode-btn px-3 py-1.5 bg-white text-slate-600" onclick="mmSetPreviewMode('mobile')">Móvil</button>
                    </div>
                </div>
                <div id="mm-preview" class="bg-slate-50 rounded-2xl p-2 border border-slate-200">
                    <div class="mm-preview-desktop bg-white/95 rounded-[18px] border border-slate-200 shadow-lg grid grid-cols-4 min-h-[340px] overflow-hidden text-left">
                        <div class="bg-slate-50/80 p-3 border-r border-slate-100 col-span-1 flex flex-col justify-between">
                            <div class="space-y-1" id="preview-tabs"></div>
                            <p class="text-[7px] font-black text-[#1B263B] uppercase mt-2 opacity-50 ml-1">Ver catálogo →</p>
                        </div>
                        <div class="col-span-2 p-4 grid grid-cols-2 gap-3" id="preview-center"></div>
                        <div class="bg-slate-50/50 p-3 border-l border-slate-100 col-span-1 flex flex-col justify-between text-[8px] text-slate-400 select-none">
                            <div class="space-y-1.5 opacity-80">
                                <div class="p-2 bg-[#0E75AE]/5 border border-[#0E75AE]/10 rounded-lg font-bold text-[#0E75AE]">Asesoría (fijo)</div>
                                <div class="p-1.5 border rounded bg-white">WhatsApp</div>
                                <div class="p-1.5 border rounded bg-white">Sucursales</div>
                            </div>
                            <div class="p-2 bg-slate-100 rounded text-center mt-2 font-black uppercase">Portal B2B</div>
                        </div>
                    </div>
                    <div class="mm-preview-mobile-panel mt-2 max-w-[280px] mx-auto">
                        <div class="bg-white rounded-t-2xl border border-slate-200 shadow-lg overflow-hidden text-left max-h-[360px] flex flex-col">
                            <div class="px-3 py-2 border-b text-[9px] font-black uppercase text-slate-500">Explorar (móvil)</div>
                            <div class="overflow-y-auto flex-1 p-2 space-y-1" id="preview-mobile-acc"></div>
                            <div class="px-3 py-2 border-t bg-slate-50 flex flex-wrap gap-2" id="preview-mobile-footer"></div>
                        </div>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 mt-2">Desktop: pasa el mouse sobre las pestañas. Móvil: acordeón simplificado + pie nivel 3.</p>
            </div>
        </div>
    </form>
</div>

<script>
(function() {
    const catalogo = <?= json_encode($catalogo, JSON_UNESCAPED_UNICODE) ?>;
    const categoriasReales = <?= json_encode($categorias_reales, JSON_UNESCAPED_UNICODE) ?>;
    const MEGAMENU_DEFAULTS = <?= json_encode(improgyp_megamenu_defaults(), JSON_UNESCAPED_UNICODE) ?>;
    const NIVEL3_DEFAULTS = <?= json_encode(improgyp_header_default_nivel3_menu(), JSON_UNESCAPED_UNICODE) ?>;

    let megamenuState = <?= json_encode($megamenu_initial, JSON_UNESCAPED_UNICODE) ?>;
    let nivel3State = <?= json_encode($nivel3_initial, JSON_UNESCAPED_UNICODE) ?>;
    let previewActiveId = megamenuState[0]?.id || null;
    let previewMode = 'desktop';

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
        const opts = [['text-[#0E75AE]','Azul'],['text-amber-500','Ámbar'],['text-slate-400','Gris'],['text-emerald-500','Verde'],['text-rose-500','Rojo']];
        return opts.map(([v,l]) => `<option value="${v}" ${selected===v?'selected':''}>${l}</option>`).join('');
    }

    function syncHiddenInput() {
        document.getElementById('megamenu_json').value = JSON.stringify(megamenuState);
        document.getElementById('nivel3_json').value = JSON.stringify(nivel3State);
    }

    /** Actualiza solo el badge de un enlace sin re-renderizar el formulario (evita perder foco al escribir). */
    function updateLinkBadge(divIdx, key, linkIdx) {
        const wrap = document.querySelector(`[data-mm-link-badge="${divIdx}-${key}-${linkIdx}"]`);
        if (!wrap) return;
        const link = megamenuState[divIdx][key][linkIdx];
        wrap.innerHTML = badgeHtml(countForLink(link), link);
    }

    function collectLinkedCategories() {
        const set = new Set();
        megamenuState.forEach(div => {
            ['linksLeft', 'linksRight'].forEach(key => {
                (div[key] || []).forEach(link => {
                    if (link.linkType === 'category' && link.linkValue) set.add(link.linkValue);
                });
            });
        });
        return set;
    }

    function getOrphanCategories() {
        const linked = collectLinkedCategories();
        return categoriasReales.filter(c => !linked.has(c));
    }

    function guessDivisionId(categoria) {
        const n = normalize(categoria);
        const rules = [
            ['drywall', ['lijador', 'lija', 'drywall', 'yeso', 'gypsum', 'atornill', 'zanco', 'panel de yeso', 'herramientas drywall', 'revestimiento']],
            ['potencia', ['taladro', 'amoladora', 'mezclador', 'concreto', 'cortadora', 'sierra', 'rotomart', 'percutor', 'esmeril', 'demolicion']],
            ['aplicacion', ['pulveriz', 'silicona', 'pistola', 'soplador', 'aspiradora', 'limpieza', 'vacio', 'aire caliente', 'estirado']],
            ['accesorios', ['accesorio', 'abrasivo', 'consumible', 'bater', 'cargador', 'tornillo', 'kit', 'repuesto']],
        ];
        for (const [id, needles] of rules) {
            if (needles.some(needle => n.includes(needle))) return id;
        }
        return 'accesorios';
    }

    function divisionIndexById(divId) {
        return megamenuState.findIndex(d => d.id === divId);
    }

    function appendCategoriesToDivision(divId, column, categories) {
        let idx = divisionIndexById(divId);
        if (idx < 0 && megamenuState.length) {
            idx = 0;
        }
        if (idx < 0) return 0;
        const key = column === 'right' ? 'linksRight' : 'linksLeft';
        const linked = collectLinkedCategories();
        let added = 0;
        categories.forEach(cat => {
            if (!cat || linked.has(cat)) return;
            megamenuState[idx][key].push({ name: cat, linkType: 'category', linkValue: cat });
            linked.add(cat);
            added++;
        });
        return added;
    }

    function renderOrphans() {
        const orphans = getOrphanCategories();
        const panel = document.getElementById('mm-orphans-panel');
        const list = document.getElementById('mm-orphans-list');
        const countEl = document.getElementById('mm-orphans-count');
        const actions = document.getElementById('mm-orphans-actions');
        if (!panel || !list) return;
        if (!orphans.length) {
            panel.classList.add('hidden');
            list.innerHTML = '';
            if (actions) actions.classList.add('hidden');
            return;
        }
        panel.classList.remove('hidden');
        if (actions) actions.classList.remove('hidden');
        if (countEl) countEl.textContent = String(orphans.length);
        list.innerHTML = orphans.map(c => `<li class="bg-amber-50 border border-amber-100 px-2 py-1 rounded-lg">${esc(c)}</li>`).join('');
    }

    window.mmOpenOrphanModal = function() {
        const orphans = getOrphanCategories();
        if (!orphans.length) {
            alert('No hay categorías huérfanas.');
            return;
        }
        const sel = document.getElementById('mm-orphan-division');
        const box = document.getElementById('mm-orphan-checkboxes');
        const modal = document.getElementById('mm-orphan-modal');
        if (!sel || !box || !modal) return;
        sel.innerHTML = megamenuState.map(d => `<option value="${esc(d.id)}">${esc(d.title)}</option>`).join('');
        const firstGuess = guessDivisionId(orphans[0]);
        if (divisionIndexById(firstGuess) >= 0) sel.value = firstGuess;
        box.innerHTML = orphans.map(c => `
            <label class="flex items-start gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                <input type="checkbox" class="mm-orphan-cb mt-0.5 rounded border-slate-300" value="${esc(c)}" checked>
                <span>${esc(c)} <span class="text-[9px] text-slate-400 font-medium">→ ${esc(guessDivisionId(c))}</span></span>
            </label>`).join('');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
    };

    window.mmCloseOrphanModal = function() {
        const modal = document.getElementById('mm-orphan-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
    };

    window.mmApplyOrphanModal = function() {
        const divId = document.getElementById('mm-orphan-division')?.value;
        const col = document.getElementById('mm-orphan-column')?.value || 'left';
        const checked = [...document.querySelectorAll('.mm-orphan-cb:checked')].map(el => el.value).filter(Boolean);
        if (!divId || !checked.length) {
            alert('Elige al menos una categoría.');
            return;
        }
        const n = appendCategoriesToDivision(divId, col, checked);
        mmCloseOrphanModal();
        renderAll();
        if (n > 0) {
            alert(`Se añadieron ${n} enlace${n === 1 ? '' : 's'}. Pulsa «Guardar cambios» para publicar en la tienda.`);
        } else {
            alert('No se añadió ningún enlace (ya existían o la división no es válida).');
        }
    };

    window.mmAutoDistributeOrphans = function() {
        const orphans = getOrphanCategories();
        if (!orphans.length) {
            alert('Todas las categorías del catálogo ya están enlazadas en el menú.');
            return;
        }
        if (!confirm(`¿Distribuir ${orphans.length} categoría(s) en las divisiones sugeridas (columna izquierda)? Podrás revisar antes de guardar.`)) return;
        const byDiv = {};
        orphans.forEach(cat => {
            const id = guessDivisionId(cat);
            if (!byDiv[id]) byDiv[id] = [];
            byDiv[id].push(cat);
        });
        let total = 0;
        Object.keys(byDiv).forEach(divId => {
            total += appendCategoriesToDivision(divId, 'left', byDiv[divId]);
        });
        renderAll();
        alert(total > 0
            ? `Se añadieron ${total} enlace${total === 1 ? '' : 's'}. Revisa la vista previa y pulsa «Guardar cambios».`
            : 'No se pudo añadir ningún enlace (revisa que existan las divisiones drywall, potencia, aplicacion, accesorios).');
    };

    window.mmSetPreviewMode = function(mode) {
        previewMode = mode === 'mobile' ? 'mobile' : 'desktop';
        const wrap = document.getElementById('mm-preview');
        const dBtn = document.getElementById('mm-preview-desktop-btn');
        const mBtn = document.getElementById('mm-preview-mobile-btn');
        if (wrap) wrap.classList.toggle('mm-preview-mobile', previewMode === 'mobile');
        if (dBtn) dBtn.classList.toggle('active', previewMode === 'desktop');
        if (mBtn) mBtn.classList.toggle('active', previewMode === 'mobile');
    };

    window.mmAgregarNivel3 = function() {
        nivel3State.push({ text: 'Nuevo', link: 'index.php' });
        renderNivel3();
    };

    window.mmEliminarNivel3 = function(idx) {
        nivel3State.splice(idx, 1);
        renderNivel3();
    };

    function patchNivel3(idx, field, value) {
        nivel3State[idx][field] = value;
        syncHiddenInput();
        renderPreview();
    }

    function renderNivel3() {
        const c = document.getElementById('nivel3-container');
        if (!c) return;
        if (!nivel3State.length) {
            c.innerHTML = '<p class="text-xs text-slate-400">Sin enlaces. Añade al menos uno.</p>';
            syncHiddenInput();
            renderPreview();
            return;
        }
        c.innerHTML = nivel3State.map((item, idx) => `
            <div class="flex flex-wrap gap-2 items-center bg-slate-50 rounded-xl p-3 border border-slate-100">
                <input type="text" value="${esc(item.text)}" placeholder="Texto" oninput="patchNivel3(${idx},'text',this.value)" class="text-xs font-black border border-slate-200 rounded-lg px-2 py-1.5 flex-1 min-w-[100px]">
                <input type="text" value="${esc(item.link)}" placeholder="URL (ej. blog.php)" oninput="patchNivel3(${idx},'link',this.value)" class="text-xs font-bold border border-slate-200 rounded-lg px-2 py-1.5 flex-[2] min-w-[140px]">
                <button type="button" onclick="mmEliminarNivel3(${idx})" class="text-slate-300 hover:text-rose-500 p-1"><i class="fa-solid fa-trash-can"></i></button>
            </div>`).join('');
        syncHiddenInput();
        renderPreview();
    }

    window.patchNivel3 = patchNivel3;

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
    }

    /**
     * @param {boolean} fullRender true = reemplazar todo el editor (añadir/borrar, cambiar tipo)
     */
    function patchLink(divIdx, key, linkIdx, field, value, fullRender) {
        megamenuState[divIdx][key][linkIdx][field] = value;
        if (fullRender) {
            renderAll();
            return;
        }
        syncHiddenInput();
        updateLinkBadge(divIdx, key, linkIdx);
        if (field === 'name' || field === 'linkValue') {
            renderPreview();
        }
        if (field === 'linkValue') {
            renderOrphans();
        }
    }

    /** Escritura en inputs de texto: no destruye el DOM del formulario. */
    window.patchLinkInput = function(divIdx, key, linkIdx, field, value) {
        patchLink(divIdx, key, linkIdx, field, value, false);
    };

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
            ? `<select onchange="patchLink(${divIdx},'${key}',${linkIdx},'linkValue',this.value,false)" class="text-xs font-bold border border-slate-200 rounded-lg px-2 py-1.5 flex-grow max-w-[220px]"><option value="">— Categoría —</option>${catOpts}</select>`
            : `<input type="text" value="${esc(link.linkValue)}" placeholder="Palabra clave" oninput="patchLinkInput(${divIdx},'${key}',${linkIdx},'linkValue',this.value)" class="text-xs font-bold border border-slate-200 rounded-lg px-2 py-1.5 flex-grow max-w-[220px]">`;

        return `<div class="bg-white rounded-xl border border-slate-200 p-3 space-y-2" data-mm-link-row="${divIdx}-${key}-${linkIdx}">
            <div class="flex flex-wrap items-center gap-2 justify-between">
                <input type="text" value="${esc(link.name)}" oninput="patchLinkInput(${divIdx},'${key}',${linkIdx},'name',this.value)" class="text-xs font-black flex-grow min-w-[100px] border-b border-slate-100 outline-none">
                <span data-mm-link-badge="${divIdx}-${key}-${linkIdx}">${badgeHtml(count, link)}</span>
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
        const mobAcc = document.getElementById('preview-mobile-acc');
        const mobFoot = document.getElementById('preview-mobile-footer');
        if (mobAcc) {
            mobAcc.innerHTML = megamenuState.map(div => `
                <div class="border border-slate-100 rounded-lg px-2 py-1.5">
                    <p class="text-[9px] font-black text-slate-600 flex items-center gap-1"><i class="fa-solid ${div.icon} ${div.iconColor} text-[8px]"></i> ${esc(div.title)}</p>
                    <p class="text-[8px] text-slate-400 mt-0.5">${(div.linksLeft||[]).length + (div.linksRight||[]).length} enlaces</p>
                </div>`).join('') || '<p class="text-[9px] text-slate-300">Sin divisiones</p>';
        }
        if (mobFoot) {
            mobFoot.innerHTML = nivel3State.map(n => `<span class="text-[8px] font-black uppercase text-slate-500">${esc(n.text)}</span>`).join('');
        }
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
        renderNivel3();
        renderOrphans();
        renderPreview();
        syncHiddenInput();
    }

    window.patchDiv = patchDiv;
    window.patchLink = patchLink;

    document.getElementById('form-megamenu').addEventListener('submit', function() {
        syncHiddenInput();
    });

    renderAll();

    if (window.location.hash === '#mm-orphans-panel') {
        const orphanPanel = document.getElementById('mm-orphans-panel');
        if (orphanPanel) {
            setTimeout(() => orphanPanel.scrollIntoView({ behavior: 'smooth', block: 'start' }), 150);
        }
    }
})();
</script>
