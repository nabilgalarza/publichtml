<?php
/**
 * Demo Megamenú v2 — datos reales de producción + mejoras propuestas (no modifica config_header.json).
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/components/megamenu_config.php';

$headerPath = dirname(__DIR__) . '/config_header.json';
$headerRaw = file_exists($headerPath) ? (json_decode(file_get_contents($headerPath), true) ?? []) : [];
$megamenuStored = $headerRaw['megamenu'] ?? [];
$megamenuEmpty = !is_array($megamenuStored) || count($megamenuStored) === 0;
$megamenuActive = improgyp_normalize_megamenu($megamenuStored);
$megamenuJsMap = improgyp_megamenu_js_map($megamenuActive);
$megamenuFirstId = $megamenuActive[0]['id'] ?? 'drywall';

$nivel3 = $headerRaw['nivel3_menu'] ?? [];
if (!is_array($nivel3) || $nivel3 === []) {
    $nivel3 = improgyp_header_default_nivel3_menu();
}
$siteNav = improgyp_header_site_nav_items($nivel3);

$catalogoPath = dirname(__DIR__) . '/catalogo.json';
$catalogo = [];
if (file_exists($catalogoPath)) {
    $catalogo = json_decode(file_get_contents($catalogoPath), true) ?? [];
}
$categoriasReales = improgyp_megamenu_categorias_from_catalogo();

/** Conteo productos por categoría (igual criterio que tienda: campo categoria exacto). */
function demo_megamenu_cat_counts(array $catalogo): array
{
    $counts = [];
    foreach ($catalogo as $p) {
        $c = trim($p['categoria'] ?? '');
        if ($c === '') {
            continue;
        }
        $counts[$c] = ($counts[$c] ?? 0) + 1;
    }
    return $counts;
}

/** Todos los linkValue de tipo category en el menú. */
function demo_megamenu_linked_categories(array $divisions): array
{
    $linked = [];
    foreach ($divisions as $div) {
        foreach (['linksLeft', 'linksRight'] as $key) {
            foreach ($div[$key] ?? [] as $link) {
                if (($link['linkType'] ?? '') === 'search') {
                    continue;
                }
                $v = trim($link['linkValue'] ?? '');
                if ($v !== '') {
                    $linked[$v] = true;
                }
            }
        }
    }
    return array_keys($linked);
}

function demo_megamenu_audit_links(array $divisions, array $catCounts, array $categoriasReales): array
{
    $broken = [];
    $ok = [];
    foreach ($divisions as $div) {
        foreach (['linksLeft', 'linksRight'] as $key) {
            foreach ($div[$key] ?? [] as $link) {
                if (($link['linkType'] ?? '') === 'search') {
                    continue;
                }
                $v = trim($link['linkValue'] ?? '');
                $name = $link['name'] ?? $v;
                $row = [
                    'division' => $div['title'] ?? '',
                    'name' => $name,
                    'linkValue' => $v,
                    'count' => $catCounts[$v] ?? 0,
                ];
                if ($v === '' || !in_array($v, $categoriasReales, true)) {
                    $broken[] = $row;
                } else {
                    $ok[] = $row;
                }
            }
        }
    }
    return ['broken' => $broken, 'ok' => $ok];
}

$catCounts = demo_megamenu_cat_counts($catalogo);
$linkedCats = demo_megamenu_linked_categories($megamenuActive);
$orphanCats = array_values(array_diff($categoriasReales, $linkedCats));
$audit = demo_megamenu_audit_links($megamenuActive, $catCounts, $categoriasReales);

$bootstrap = [
    'megamenuEmpty' => $megamenuEmpty,
    'megamenuSource' => $megamenuEmpty ? 'defaults_php' : 'config_header.json',
    'divisions' => $megamenuActive,
    'jsMap' => $megamenuJsMap,
    'firstId' => $megamenuFirstId,
    'nivel3' => $nivel3,
    'siteNav' => $siteNav,
    'categoriasReales' => $categoriasReales,
    'catCounts' => $catCounts,
    'orphanCats' => $orphanCats,
    'audit' => $audit,
    'productCount' => count($catalogo),
    'defaults' => improgyp_megamenu_defaults(),
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEMO v2 — Megamenú IMPROGYP (producción + mejoras)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --brand: #3A86FF; --ink: #1B263B; --nav-h: 56px; }
        body { font-family: Inter, system-ui, sans-serif; background: #f1f5f9; }
        .demo-tab.active { background: var(--ink); color: #fff; border-color: var(--ink); }
        .phase-tag { font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 6px; background: #e0e7ff; color: #3730a3; }

        /* —— Megamenú contenido en marco (mejoras vs fixed global en producción) —— */
        .mega-frame {
            position: relative;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            border: 2px dashed #cbd5e1;
        }
        .mega-frame--desktop { min-height: 540px; }
        .mega-frame--mobile { width: 100%; max-width: 390px; margin: 0 auto; height: 700px; }

        .mf-nav {
            position: absolute; top: 0; left: 0; right: 0; z-index: 20;
            height: var(--nav-h); display: flex; align-items: center; gap: 8px;
            padding: 0 12px; background: rgba(255,255,255,.97); border-bottom: 1px solid #e2e8f0;
        }
        .mf-shell {
            position: absolute; left: 0; right: 0; top: var(--nav-h); bottom: 0;
            z-index: 30; pointer-events: none;
        }
        .mf-shell.open { pointer-events: auto; }
        .mf-backdrop {
            position: absolute; inset: 0; background: rgba(15,23,42,.45);
            opacity: 0; pointer-events: none; transition: opacity .2s;
        }
        .mf-shell.open .mf-backdrop { opacity: 1; pointer-events: auto; }
        .mega-frame--desktop .mf-backdrop { display: none; }

        .mf-panel {
            position: absolute; z-index: 2; left: 8px; right: 8px;
            display: none; flex-direction: column; background: #fff;
            border: 1px solid #e2e8f0; border-radius: 20px;
            box-shadow: 0 20px 40px rgba(15,23,42,.12);
            overflow: hidden; min-height: 0;
        }
        .mf-panel.open { display: flex; pointer-events: auto; }
        .mega-frame--desktop .mf-panel { top: 8px; height: min(400px, calc(100% - 20px)); max-height: calc(100% - 20px); }
        .mega-frame--mobile .mf-panel {
            left: 0; right: 0; bottom: 0; top: auto;
            height: 0; max-height: calc(100% - 8px);
            border-radius: 20px 20px 0 0;
            transform: translateY(100%);
            transition: transform .35s cubic-bezier(.16,1,.3,1), height .35s ease;
        }
        .mega-frame--mobile .mf-panel.open {
            transform: translateY(0);
            height: min(580px, calc(100% - 12px));
        }

        .mf-trigger.open {
            border-color: var(--brand) !important;
            box-shadow: 0 0 0 3px rgba(58,134,255,.28);
        }
        .mf-trigger .mf-chev { transition: transform .25s; }
        .mf-trigger.open .mf-chev { transform: rotate(180deg); }

        .mf-head { flex-shrink: 0; }
        .mf-scroll {
            flex: 1 1 auto; min-height: 0; overflow-y: auto;
            -webkit-overflow-scrolling: touch; overscroll-behavior: contain;
        }
        .mf-body { display: flex; flex-direction: column; }
        .mega-frame--desktop .mf-body {
            display: grid; grid-template-columns: minmax(0,1fr) minmax(0,2fr) minmax(0,1fr);
            min-height: 240px;
        }
        .mega-frame--desktop .mf-acc-wrap { display: none; }
        .mega-frame--desktop .mf-sidebar { display: flex; flex-direction: column; gap: 4px; padding: 12px; background: #f8fafc; border-right: 1px solid #e2e8f0; }
        .mega-frame--desktop .mf-center { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 16px; }
        .mega-frame--desktop .mf-aside-d { display: flex; flex-direction: column; justify-content: space-between; padding: 12px; background: #f8fafc; border-left: 1px solid #e2e8f0; font-size: 10px; }
        .mega-frame--mobile .mf-sidebar,
        .mega-frame--mobile .mf-center,
        .mega-frame--mobile .mf-aside-d { display: none !important; }
        .mega-frame--mobile .mf-acc-wrap { display: block; padding: 8px 12px; }
        .mega-frame--mobile .mf-aside-m { display: block; padding: 12px; border-top: 1px solid #e2e8f0; background: #f8fafc; }
        .mega-frame--desktop .mf-aside-m { display: none; }

        .mf-tab.active { background: #fff; color: var(--ink); box-shadow: 0 1px 3px rgba(0,0,0,.08); border-radius: 10px; }
        .mf-acc-item .mf-acc-panel { max-height: 0; overflow: hidden; opacity: 0; transition: max-height .3s ease, opacity .2s; }
        .mega-frame--mobile .mf-acc-item.open .mf-acc-panel { max-height: 800px; opacity: 1; overflow: visible; }
        .mf-acc-item.open .mf-acc-chev { transform: rotate(90deg); color: var(--brand); }
        .mf-acc-chev { transition: transform .25s; font-size: 10px; color: #94a3b8; }

        .mf-foot {
            flex-shrink: 0; border-top: 1px solid #e2e8f0; padding: 10px 12px;
            background: #f8fafc; display: flex; flex-direction: column; gap: 8px;
        }
        .mega-frame--mobile .mf-foot-nav { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .mega-frame--desktop .mf-foot-nav { display: flex; flex-wrap: wrap; gap: 10px; }

        .mf-dim {
            position: absolute; inset: var(--nav-h) 0 0; background: #f1f5f9;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; color: #94a3b8; font-weight: 700; text-align: center; padding: 12px;
        }
        .submenu-item { display: block; padding: 4px 0; cursor: pointer; }
        .toast-v2 {
            position: fixed; bottom: 20px; right: 20px; z-index: 99999;
            transform: translateY(120%); transition: transform .35s;
            max-width: 360px;
        }
        .toast-v2.show { transform: translateY(0); }
    </style>
</head>
<body class="text-slate-800 antialiased pb-16">
    <div class="bg-violet-600 text-white px-4 py-2.5 text-center text-xs font-bold sticky top-0 z-[99999]">
        <i class="fa-solid fa-flask"></i> DEMO v2 — Datos reales: <code class="bg-white/20 px-1 rounded">catalogo.json</code> + <code class="bg-white/20 px-1 rounded">config_header.json</code>
        · No escribe en producción
        <a href="megamenu-mejoras.html" class="underline ml-2 opacity-80">Demo v1</a>
        <a href="../dashboard.php?view=apariencia&sub=megamenu" class="underline ml-2">Admin real</a>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <header class="mb-6">
            <h1 class="text-2xl md:text-3xl font-black text-slate-900">Megamenú v2 — Producción + mejoras</h1>
            <p class="text-sm text-slate-500 mt-2" id="boot-summary"></p>
        </header>

        <nav class="flex flex-wrap gap-2 mb-6">
            <button type="button" class="demo-tab active text-xs font-black uppercase px-4 py-2 rounded-xl border bg-white" data-view="prod">1 · Producción</button>
            <button type="button" class="demo-tab text-xs font-black uppercase px-4 py-2 rounded-xl border bg-white" data-view="prop-desktop">2 · Propuesta desktop</button>
            <button type="button" class="demo-tab text-xs font-black uppercase px-4 py-2 rounded-xl border bg-white" data-view="prop-mobile">3 · Propuesta móvil</button>
            <button type="button" class="demo-tab text-xs font-black uppercase px-4 py-2 rounded-xl border bg-white" data-view="admin">4 · Admin propuesto</button>
            <button type="button" class="demo-tab text-xs font-black uppercase px-4 py-2 rounded-xl border bg-white" data-view="sync">5 · Sincronización</button>
            <button type="button" class="demo-tab text-xs font-black uppercase px-4 py-2 rounded-xl border bg-white" data-view="checklist">6 · Checklist</button>
        </nav>

        <!-- 1 Producción -->
        <section id="view-prod" class="view-panel">
            <div class="bg-white rounded-xl border p-4 mb-4 text-sm" id="prod-notice"></div>
            <iframe src="../productos.php" class="w-full h-[580px] rounded-xl border border-slate-300 bg-white" title="Tienda producción"></iframe>
            <p class="text-xs text-slate-500 mt-2">Comportamiento actual: menú <code>position: fixed</code> en viewport, <code>body overflow: hidden</code> en móvil al abrir.</p>
        </section>

        <!-- 2 Propuesta desktop -->
        <section id="view-prop-desktop" class="view-panel hidden">
            <div class="bg-white rounded-xl border p-4 mb-4 text-sm text-slate-600">
                <span class="phase-tag">F1.2</span> <kbd class="px-1 border rounded text-xs">Esc</kbd> cierra
                <span class="phase-tag ml-1">F3.5</span> trigger activo
                <span class="phase-tag ml-1">Mejora</span> menú dentro del marco (scroll correcto)
            </div>
            <div id="host-prop-desktop"></div>
        </section>

        <!-- 3 Propuesta móvil -->
        <section id="view-prop-mobile" class="view-panel hidden">
            <div class="bg-white rounded-xl border p-4 mb-4 text-sm text-slate-600 max-w-lg">
                Desliza <strong>dentro del menú blanco</strong> para ver asistencia y pie. <span class="phase-tag">F3.2</span> recuerda última división.
            </div>
            <div id="host-prop-mobile"></div>
        </section>

        <!-- 4 Admin -->
        <section id="view-admin" class="view-panel hidden">
            <div id="admin-banners" class="space-y-3 mb-4"></div>
            <div class="grid xl:grid-cols-12 gap-6">
                <div class="xl:col-span-7 space-y-4" id="admin-editor"></div>
                <div class="xl:col-span-5">
                    <div class="bg-white rounded-2xl border p-4 sticky top-16 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-xs font-black text-slate-500 uppercase"><span class="phase-tag">F3.1</span> Preview en vivo</p>
                            <button type="button" id="btn-preview-toggle" class="text-xs font-bold border rounded-lg px-3 py-1.5">Desktop</button>
                        </div>
                        <div id="admin-preview-host"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5 Sync -->
        <section id="view-sync" class="view-panel hidden">
            <div class="grid md:grid-cols-2 gap-4 mb-6" id="sync-cards"></div>
            <div class="bg-white rounded-2xl border p-5 mb-4">
                <h3 class="font-black mb-3"><span class="phase-tag">F4</span> Categorías en catálogo sin enlace en el menú</h3>
                <p class="text-xs text-slate-500 mb-3">Si agregas productos con categoría nueva en CSV/BD → aparecen aquí tras regenerar <code>catalogo.json</code>. El menú no se actualiza solo.</p>
                <ul id="orphan-list" class="text-sm space-y-1 max-h-48 overflow-y-auto"></ul>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl border p-5">
                    <h3 class="font-black text-emerald-800 mb-2">Enlaces OK</h3>
                    <div id="links-ok" class="text-xs space-y-2 max-h-64 overflow-y-auto"></div>
                </div>
                <div class="bg-white rounded-2xl border p-5">
                    <h3 class="font-black text-rose-800 mb-2">Enlaces rotos / 0 productos</h3>
                    <div id="links-broken" class="text-xs space-y-2 max-h-64 overflow-y-auto"></div>
                </div>
            </div>
        </section>

        <!-- 6 Checklist -->
        <section id="view-checklist" class="view-panel hidden">
            <div class="bg-white rounded-2xl border p-6 max-w-lg">
                <div id="checklist-host" class="space-y-4 text-sm"></div>
                <textarea id="notes" class="w-full mt-6 border rounded-xl p-3 text-sm" rows="4" placeholder="Notas para implementación en producción…"></textarea>
                <button type="button" id="btn-copy" class="mt-3 text-xs font-bold text-[#3A86FF]">Copiar resumen</button>
            </div>
        </section>
    </div>

    <div id="toast-v2" class="toast-v2 bg-[#1B263B] text-white rounded-xl px-5 py-4 shadow-2xl text-sm font-bold">
        <p class="text-emerald-300 text-[10px] uppercase mb-1" id="toast-title">Demo</p>
        <p id="toast-body"></p>
        <a href="../productos.php" id="toast-link" class="text-[#3A86FF] text-xs font-black underline mt-2 inline-block hidden">Ver en tienda →</a>
    </div>

    <script>
    const BOOT = <?= json_encode($bootstrap, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) ?>;

    let state = {
        divisions: JSON.parse(JSON.stringify(BOOT.divisions)),
        nivel3: JSON.parse(JSON.stringify(BOOT.nivel3)),
        megamenuEmpty: BOOT.megamenuEmpty,
        previewMode: 'desktop',
        activeId: BOOT.firstId,
    };

    const frames = {};
    let adminPreviewMode = 'desktop';

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
    }

    function showToast(title, body, showLink) {
        document.getElementById('toast-title').textContent = title;
        document.getElementById('toast-body').textContent = body;
        document.getElementById('toast-link').classList.toggle('hidden', !showLink);
        const t = document.getElementById('toast-v2');
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 5000);
    }

    function countForLink(link) {
        if (link.linkType === 'search') return null;
        return BOOT.catCounts[link.linkValue] ?? 0;
    }

    function linkBadge(link) {
        if (link.linkType === 'search') return '<span class="text-[9px] font-black text-sky-700 bg-sky-50 px-1.5 rounded">Búsqueda</span>';
        const c = countForLink(link);
        if (!BOOT.categoriasReales.includes(link.linkValue)) return '<span class="text-[9px] font-black text-rose-600 bg-rose-50 px-1.5 rounded">No existe en catálogo</span>';
        if (c === 0) return '<span class="text-[9px] font-black text-amber-700 bg-amber-50 px-1.5 rounded">0 productos</span>';
        return `<span class="text-[9px] font-black text-emerald-700 bg-emerald-50 px-1.5 rounded">~${c} prod.</span>`;
    }

    function getLinkedCategories(divisions) {
        const set = new Set();
        divisions.forEach(div => {
            ['linksLeft','linksRight'].forEach(key => {
                (div[key] || []).forEach(l => {
                    if (l.linkType !== 'search' && l.linkValue) set.add(l.linkValue);
                });
            });
        });
        return set;
    }

    function getOrphanCats(divisions) {
        const linked = getLinkedCategories(divisions);
        return BOOT.categoriasReales.filter(c => !linked.has(c));
    }

    function buildJsMap(divisions) {
        const map = {};
        divisions.forEach(d => {
            map[d.id] = {
                titleLeft: d.titleLeft,
                titleRight: d.titleRight,
                linksLeft: d.linksLeft,
                linksRight: d.linksRight,
            };
        });
        return map;
    }

    function filterMegaDemo(linkType, linkValue, event) {
        if (event) event.preventDefault();
        const type = linkType === 'search' ? 'search' : 'category';
        const value = (linkValue || '').trim();
        const url = type === 'category'
            ? '../productos.php?cat=' + encodeURIComponent(value)
            : '../productos.php?q=' + encodeURIComponent(value);
        showToast('Clic enlace (demo)', (type === 'category' ? 'Categoría: ' : 'Búsqueda: ') + value + '\n' + url, true);
        document.getElementById('toast-link').href = url;
        Object.values(frames).forEach(f => f.close());
    }

    function buildLinksHtml(links) {
        return (links || []).map(item => {
            const lt = item.linkType === 'search' ? 'search' : 'category';
            const lv = item.linkValue || item.name || '';
            return `<li><a href="#" class="submenu-item text-[11px] font-bold text-slate-500 hover:text-[#3A86FF]" data-lt="${lt}" data-lv="${esc(lv)}">${esc(item.name || lv)}</a></li>`;
        }).join('');
    }

    function buildCenterHtml(catId, map) {
        const m = map[catId];
        if (!m) return '';
        return `<div class="space-y-2"><h5 class="text-[10px] font-black uppercase border-b pb-1">${esc(m.titleLeft)}</h5><ul class="space-y-1">${buildLinksHtml(m.linksLeft)}</ul></div>`
            + `<div class="space-y-2"><h5 class="text-[10px] font-black uppercase border-b pb-1">${esc(m.titleRight)}</h5><ul class="space-y-1">${buildLinksHtml(m.linksRight)}</ul></div>`;
    }

    function createMegaFrame(container, mode) {
        const isMobile = mode === 'mobile';
        const storageKey = 'demo_v2_acc_' + mode;
        const el = document.createElement('div');
        el.className = 'mega-frame mega-frame--' + mode;
        el.innerHTML = `
            <nav class="mf-nav">
                <img src="../logo-oscuro.png" alt="" class="h-6" onerror="this.style.display='none'">
                <button type="button" class="mf-trigger flex items-center gap-1.5 px-2.5 py-1.5 border border-slate-200 rounded-lg text-[9px] font-black uppercase">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#3A86FF]"></span>
                    ${isMobile ? 'Explorar' : 'Explorar Divisiones'}
                    <i class="fa-solid fa-chevron-down mf-chev text-[7px] text-slate-400"></i>
                </button>
                ${isMobile ? '' : '<div class="flex-1 max-w-[160px] h-7 bg-slate-100 rounded-full"></div>'}
                <div class="ml-auto flex gap-1"><span class="w-7 h-7 rounded-full border"></span><span class="w-7 h-7 rounded-full bg-[#1B263B]"></span></div>
            </nav>
            <div class="mf-dim" data-dismiss>Clic aquí cierra el menú (demo)</div>
            <div class="mf-shell">
                <div class="mf-backdrop" data-dismiss></div>
                <div class="mf-panel" role="dialog">
                    <div class="mf-head flex justify-between items-center px-3 py-2 border-b">
                        <span class="text-[9px] font-black uppercase">Explorar Divisiones</span>
                        <button type="button" class="mf-close w-8 h-8 rounded-full bg-slate-100" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="mf-scroll" tabindex="0">
                        <div class="mf-body">
                            <div class="mf-acc-wrap"></div>
                            <div class="mf-sidebar"></div>
                            <div class="mf-center"></div>
                            <div class="mf-aside-d">
                                <div class="p-2 bg-blue-50 border border-blue-100 rounded-lg text-[#3A86FF] font-bold">Asesoría</div>
                                <div class="text-slate-500 mt-2">WhatsApp · Sucursales</div>
                                <div class="p-2 bg-slate-200 rounded text-center font-black mt-4">B2B</div>
                            </div>
                        </div>
                        <div class="mf-aside-m">
                            <p class="text-[8px] font-black text-slate-400 uppercase mb-2">Asistencia</p>
                            <div class="grid grid-cols-2 gap-2 text-[8px] font-black text-center">
                                <div class="p-2 border rounded-lg bg-white">WhatsApp</div>
                                <div class="p-2 border rounded-lg bg-white">Sucursales</div>
                            </div>
                            <div class="mt-2 p-2 bg-[#1B263B] text-white rounded-lg text-center text-[8px] font-black">Portal B2B</div>
                        </div>
                        <p class="text-[9px] text-center text-slate-400 font-bold py-2 mf-scroll-hint"><i class="fa-solid fa-arrows-up-down"></i> Desliza para ver más</p>
                    </div>
                    <footer class="mf-foot">
                        <a href="../productos.php" class="text-[9px] font-black text-[#1B263B] uppercase">Ver catálogo →</a>
                        <nav class="mf-foot-nav"></nav>
                    </footer>
                </div>
            </div>`;

        const shell = el.querySelector('.mf-shell');
        const panel = el.querySelector('.mf-panel');
        const trigger = el.querySelector('.mf-trigger');
        const scrollEl = el.querySelector('.mf-scroll');
        const accWrap = el.querySelector('.mf-acc-wrap');
        const sidebar = el.querySelector('.mf-sidebar');
        const center = el.querySelector('.mf-center');
        const footNav = el.querySelector('.mf-foot-nav');

        let divisions = [];
        let nivel3 = [];
        let activeId = '';
        let jsMap = {};

        function bindLinkClicks(root) {
            root.querySelectorAll('[data-lt]').forEach(a => {
                a.onclick = e => {
                    filterMegaDemo(a.getAttribute('data-lt'), a.getAttribute('data-lv'), e);
                };
            });
        }

        function renderCenter(id) {
            activeId = id;
            center.innerHTML = buildCenterHtml(id, jsMap);
            bindLinkClicks(center);
            el.querySelectorAll('.mf-tab').forEach(t => t.classList.toggle('active', t.dataset.id === id));
        }

        function render() {
            jsMap = buildJsMap(divisions);
            sidebar.innerHTML = '<p class="text-[8px] font-black text-slate-400 uppercase mb-1">Divisiones</p>' +
                divisions.map((d, i) => `<button type="button" class="mf-tab w-full text-left px-2 py-2 text-[10px] font-black text-slate-500 ${i===0?'active':''}" data-id="${esc(d.id)}"><i class="fa-solid ${d.icon} ${d.iconColor} mr-1"></i>${esc(d.title)}</button>`).join('');
            accWrap.innerHTML = '<p class="text-[8px] font-black text-slate-400 uppercase mb-2">Divisiones</p>' +
                divisions.map(d => `<div class="mf-acc-item border-b border-slate-100" data-id="${esc(d.id)}">
                    <button type="button" class="mf-acc-btn w-full flex justify-between py-2.5 text-[10px] font-black uppercase text-slate-600">
                        <span><i class="fa-solid ${d.icon} ${d.iconColor} mr-1"></i>${esc(d.title)}</span>
                        <i class="fa-solid fa-chevron-right mf-acc-chev"></i>
                    </button>
                    <div class="mf-acc-panel"><div class="mf-acc-inner pb-3 pl-4">${buildLinksHtml([...(d.linksLeft||[]),...(d.linksRight||[])])}</div></div>
                </div>`).join('');
            footNav.innerHTML = nivel3.map(n => `<a href="${esc(n.link)}" class="text-[9px] font-black uppercase text-slate-500">${esc(n.text)}</a>`).join('');

            el.querySelectorAll('.mf-tab').forEach(t => {
                t.onclick = () => renderCenter(t.dataset.id);
                t.onmouseenter = () => { if (!isMobile) renderCenter(t.dataset.id); };
            });
            el.querySelectorAll('.mf-acc-btn').forEach(btn => {
                btn.onclick = () => {
                    const item = btn.closest('.mf-acc-item');
                    const id = item.dataset.id;
                    const was = item.classList.contains('open');
                    el.querySelectorAll('.mf-acc-item').forEach(i => i.classList.remove('open'));
                    if (!was) {
                        item.classList.add('open');
                        sessionStorage.setItem(storageKey, id);
                    }
                    requestAnimationFrame(() => { scrollEl.scrollTop = scrollEl.scrollHeight; });
                };
            });
            bindLinkClicks(accWrap);

            const aid = activeId || divisions[0]?.id;
            if (aid) renderCenter(aid);
        }

        function open() {
            shell.classList.add('open');
            panel.classList.add('open');
            trigger.classList.add('open');
            if (isMobile) {
                const last = sessionStorage.getItem(storageKey);
                const id = last && divisions.some(d => d.id === last) ? last : divisions[0]?.id;
                const item = el.querySelector('.mf-acc-item[data-id="' + id + '"]');
                if (item) item.classList.add('open');
            } else {
                renderCenter(activeId || divisions[0]?.id);
            }
        }

        function close() {
            shell.classList.remove('open');
            panel.classList.remove('open');
            trigger.classList.remove('open');
            el.querySelectorAll('.mf-acc-item').forEach(i => i.classList.remove('open'));
        }

        trigger.onclick = () => panel.classList.contains('open') ? close() : open();
        el.querySelectorAll('.mf-close, [data-dismiss]').forEach(b => {
            b.addEventListener('click', e => {
                if (panel.classList.contains('open')) {
                    e.stopPropagation();
                    close();
                }
            });
        });
        panel.addEventListener('click', e => e.stopPropagation());

        const escH = e => {
            if (e.key === 'Escape' && panel.classList.contains('open') && document.body.contains(el)) close();
        };
        document.addEventListener('keydown', escH);

        return {
            mount(parent) { parent.appendChild(el); },
            setData(divs, n3) { divisions = divs; nivel3 = n3; render(); },
            setActive(id) { activeId = id; renderCenter(id); },
            open, close,
            destroy() { document.removeEventListener('keydown', escH); el.remove(); },
        };
    }

    function ensureFrame(hostId, mode) {
        if (!frames[hostId]) {
            const host = document.getElementById(hostId);
            frames[hostId] = createMegaFrame(host, mode);
            frames[hostId].mount(host);
            frames[hostId].setData(state.divisions, state.nivel3);
        } else {
            frames[hostId].setData(state.divisions, state.nivel3);
        }
        if (state.activeId) frames[hostId].setActive(state.activeId);
        return frames[hostId];
    }

    function syncAllFrames() {
        Object.values(frames).forEach(f => f.setData(state.divisions, state.nivel3));
        renderSyncPanel();
        renderAdminBanners();
    }

    // —— Tabs ——
    document.querySelectorAll('.demo-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.demo-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const v = btn.dataset.view;
            document.querySelectorAll('.view-panel').forEach(p => p.classList.add('hidden'));
            document.getElementById('view-' + v).classList.remove('hidden');
            if (v === 'prop-desktop') ensureFrame('host-prop-desktop', 'desktop');
            if (v === 'prop-mobile') ensureFrame('host-prop-mobile', 'mobile');
            if (v === 'admin') renderAdmin();
        });
    });

    // —— Boot summary ——
    document.getElementById('boot-summary').innerHTML =
        `<strong>${BOOT.productCount}</strong> productos en catálogo · <strong>${BOOT.categoriasReales.length}</strong> categorías · `
        + `Menú activo: <code>${esc(BOOT.megamenuSource)}</code> · <strong>${state.divisions.length}</strong> divisiones`;

    document.getElementById('prod-notice').innerHTML = BOOT.megamenuEmpty
        ? '<span class="phase-tag">Producción</span> <code>megamenu: []</code> → el header usa <strong>defaults PHP</strong> (no lo guardaste en dashboard). Comportamiento real en el iframe.'
        : '<span class="phase-tag">Producción</span> Menú cargado desde <code>config_header.json</code>.';

    // —— Admin ——
    function renderAdminBanners() {
        const el = document.getElementById('admin-banners');
        if (!el) return;
        let html = '';
        if (state.megamenuEmpty) {
            html += `<div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm">
                <p class="font-black text-amber-900"><span class="phase-tag">F1.1</span> En producción <code>megamenu</code> está vacío</p>
                <p class="text-xs text-amber-800 mt-1">El sitio muestra defaults. Pulsa «Guardar como menú oficial (demo)» para simular persistencia.</p>
                <button type="button" id="btn-use-defaults" class="mt-2 text-xs font-bold text-amber-900 underline">Cargar defaults en el editor</button>
            </div>`;
        }
        const orphans = getOrphanCats(state.divisions);
        if (orphans.length) {
            html += `<div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm">
                <p class="font-black text-sky-900"><span class="phase-tag">F4</span> ${orphans.length} categoría(s) en catálogo sin enlace en el menú</p>
                <p class="text-xs text-sky-800 mt-1">${esc(orphans.slice(0, 8).join(' · '))}${orphans.length > 8 ? '…' : ''}</p>
            </div>`;
        }
        el.innerHTML = html;
        document.getElementById('btn-use-defaults')?.addEventListener('click', () => {
            state.divisions = JSON.parse(JSON.stringify(BOOT.defaults));
            state.megamenuEmpty = false;
            renderAdmin();
            syncAllFrames();
        });
    }

    function renderAdmin() {
        renderAdminBanners();
        const editor = document.getElementById('admin-editor');
        const catOpts = v => BOOT.categoriasReales.map(c => `<option value="${esc(c)}" ${c===v?'selected':''}>${esc(c)}</option>`).join('');

        editor.innerHTML = `
            <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
                <div class="p-4 border-b bg-slate-50 flex flex-wrap gap-2 justify-between">
                    <p class="font-black">Editor (demo, no guarda en servidor)</p>
                    <div class="flex gap-2">
                        <button type="button" id="btn-save-demo" class="text-xs font-bold bg-emerald-500 text-white px-4 py-2 rounded-lg"><span class="phase-tag">F1.3</span> Guardar demo</button>
                    </div>
                </div>
                <p class="px-4 py-2 text-xs border-b bg-blue-50"><span class="phase-tag">F1.4</span> <code>linkValue</code> debe coincidir exacto con <code>categoria</code> en catalogo.json</p>
                <details class="border-b" open>
                    <summary class="px-4 py-3 font-black text-sm cursor-pointer bg-violet-50"><span class="phase-tag">F2.1</span> Pie del menú (nivel3)</summary>
                    <div class="p-4 space-y-2" id="nivel3-rows"></div>
                    <button type="button" id="nivel3-add" class="mx-4 mb-4 text-xs font-bold text-violet-700"><i class="fa-solid fa-plus"></i> Añadir</button>
                </details>
                <div id="div-editor" class="p-4 space-y-4"></div>
                <button type="button" id="add-div" class="mx-4 mb-4 w-[calc(100%-2rem)] py-3 border-2 border-dashed border-emerald-300 text-emerald-700 rounded-xl text-xs font-black">+ División</button>
            </div>`;

        function renderNivel3() {
            document.getElementById('nivel3-rows').innerHTML = state.nivel3.map((n, i) => `
                <div class="flex gap-2">
                    <input class="flex-1 border rounded-lg px-2 py-1.5 text-xs font-bold" value="${esc(n.text)}" data-n3="text" data-i="${i}">
                    <input class="flex-[2] border rounded-lg px-2 py-1.5 text-xs" value="${esc(n.link)}" data-n3="link" data-i="${i}">
                    <button type="button" class="text-rose-400 px-2" data-n3-del="${i}"><i class="fa-solid fa-trash-can"></i></button>
                </div>`).join('');
            document.querySelectorAll('[data-n3]').forEach(inp => {
                inp.onchange = () => {
                    state.nivel3[inp.dataset.i][inp.dataset.n3] = inp.value;
                    syncAllFrames();
                };
            });
            document.querySelectorAll('[data-n3-del]').forEach(btn => {
                btn.onclick = () => { state.nivel3.splice(+btn.getAttribute('data-n3-del'), 1); renderNivel3(); syncAllFrames(); };
            });
        }

        function renderDivs() {
            document.getElementById('div-editor').innerHTML = state.divisions.map((div, di) => {
                const col = (key, titleKey, label) => {
                    const links = div[key] || [];
                    return `<div class="bg-slate-50 rounded-xl p-3 space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase">${label}</label>
                        <input class="w-full text-xs font-black border rounded px-2 py-1 mb-2" value="${esc(div[titleKey])}" data-d="${di}" data-f="${titleKey}">
                        ${links.map((link, li) => `
                        <div class="bg-white border rounded-lg p-2 space-y-1">
                            <div class="flex flex-wrap gap-2 items-center">
                                <input class="flex-1 text-xs font-bold border-b outline-none" value="${esc(link.name)}" data-d="${di}" data-k="${key}" data-li="${li}" data-f="name">
                                ${linkBadge(link)}
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <select class="text-[10px] border rounded px-2" data-d="${di}" data-k="${key}" data-li="${li}" data-f="linkType">
                                    <option value="category" ${link.linkType==='category'?'selected':''}>Categoría</option>
                                    <option value="search" ${link.linkType==='search'?'selected':''}>Búsqueda</option>
                                </select>
                                ${link.linkType==='category'
                                    ? `<select class="text-xs border rounded flex-1" data-d="${di}" data-k="${key}" data-li="${li}" data-f="linkValue"><option value="">—</option>${catOpts(link.linkValue)}</select>`
                                    : `<input class="text-xs border rounded flex-1 px-2" value="${esc(link.linkValue)}" data-d="${di}" data-k="${key}" data-li="${li}" data-f="linkValue">`}
                                <button type="button" class="text-rose-400" data-del-link="1" data-di="${di}" data-key="${key}" data-li="${li}"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>`).join('')}
                        <button type="button" class="text-[10px] font-bold text-emerald-600" data-add-link="1" data-di="${di}" data-key="${key}">+ Enlace</button>
                    </div>`;
                };
                return `<article class="border rounded-xl overflow-hidden">
                    <div class="flex items-center gap-2 px-4 py-3 bg-slate-50 border-b">
                        <i class="fa-solid ${div.icon} ${div.iconColor}"></i>
                        <input class="flex-1 font-black text-sm bg-transparent" value="${esc(div.title)}" data-d="${di}" data-f="title">
                        <button type="button" class="text-slate-400 hover:text-rose-500" data-del-div="${di}"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                    <div class="p-3 grid md:grid-cols-2 gap-3">${col('linksLeft','titleLeft','Izquierda')}${col('linksRight','titleRight','Derecha')}</div>
                </article>`;
            }).join('');

            editor.querySelectorAll('[data-d]').forEach(inp => {
                if (inp.dataset.li !== undefined) {
                    inp.onchange = () => {
                        const l = state.divisions[inp.dataset.d][inp.dataset.k][inp.dataset.li];
                        l[inp.dataset.f] = inp.value;
                        if (inp.dataset.f === 'linkType') renderDivs();
                        syncAllFrames();
                    };
                } else {
                    inp.onchange = () => {
                        state.divisions[inp.dataset.d][inp.dataset.f] = inp.value;
                        syncAllFrames();
                    };
                }
            });
            document.querySelectorAll('[data-del-link]').forEach(btn => {
                btn.onclick = () => {
                    const di = btn.getAttribute('data-di');
                    const key = btn.getAttribute('data-key');
                    const li = btn.getAttribute('data-li');
                    state.divisions[di][key].splice(li, 1);
                    renderDivs(); syncAllFrames();
                };
            });
            document.querySelectorAll('[data-add-link]').forEach(btn => {
                btn.onclick = () => {
                    const di = btn.getAttribute('data-di');
                    const key = btn.getAttribute('data-key');
                    state.divisions[di][key].push({ name: 'Nuevo', linkType: 'category', linkValue: BOOT.categoriasReales[0] || '' });
                    renderDivs(); syncAllFrames();
                };
            });
            document.querySelectorAll('[data-del-div]').forEach(btn => {
                btn.onclick = () => {
                    state.divisions.splice(+btn.getAttribute('data-del-div'), 1);
                    renderDivs(); syncAllFrames();
                };
            });
        }

        renderNivel3();
        renderDivs();

        document.getElementById('nivel3-add').onclick = () => {
            state.nivel3.push({ text: 'Nuevo', link: '#' });
            renderNivel3(); syncAllFrames();
        };
        document.getElementById('add-div').onclick = () => {
            state.divisions.push({
                id: 'div_' + Date.now(),
                title: 'Nueva división',
                icon: 'fa-tag', iconColor: 'text-slate-400',
                titleLeft: 'GRUPO I', titleRight: 'GRUPO II',
                linksLeft: [{ name: 'Enlace', linkType: 'category', linkValue: BOOT.categoriasReales[0] || '' }],
                linksRight: [],
            });
            renderDivs(); syncAllFrames();
        };
        document.getElementById('btn-save-demo').onclick = () => {
            state.megamenuEmpty = false;
            sessionStorage.setItem('demo_v2_saved', JSON.stringify({ divisions: state.divisions, nivel3: state.nivel3 }));
            showToast('Guardado (demo)', state.divisions.length + ' divisiones · datos en sessionStorage del navegador', true);
            renderAdminBanners();
        };

        const prevHost = document.getElementById('admin-preview-host');
        prevHost.innerHTML = '';
        const wrap = document.createElement('div');
        prevHost.appendChild(wrap);
        if (frames['admin-preview']) frames['admin-preview'].destroy();
        frames['admin-preview'] = createMegaFrame(wrap, adminPreviewMode);
        frames['admin-preview'].mount(wrap);
        frames['admin-preview'].setData(state.divisions, state.nivel3);
    }

    document.getElementById('btn-preview-toggle').onclick = () => {
        adminPreviewMode = adminPreviewMode === 'desktop' ? 'mobile' : 'desktop';
        document.getElementById('btn-preview-toggle').textContent = adminPreviewMode === 'desktop' ? 'Desktop' : 'Móvil';
        renderAdmin();
    };

    // —— Sync panel ——
    function renderSyncPanel() {
        document.getElementById('sync-cards').innerHTML = `
            <div class="bg-white rounded-xl border p-4"><p class="text-[10px] font-black text-slate-400 uppercase">Catálogo</p><p class="text-2xl font-black">${BOOT.productCount}</p><p class="text-xs text-slate-500">productos en catalogo.json</p></div>
            <div class="bg-white rounded-xl border p-4"><p class="text-[10px] font-black text-slate-400 uppercase">Categorías</p><p class="text-2xl font-black">${BOOT.categoriasReales.length}</p><p class="text-xs text-slate-500">únicas en catálogo</p></div>
            <div class="bg-white rounded-xl border p-4"><p class="text-[10px] font-black text-slate-400 uppercase">Menú (editor demo)</p><p class="text-2xl font-black">${state.divisions.length}</p><p class="text-xs text-slate-500">divisiones · fuente prod: ${esc(BOOT.megamenuSource)}</p></div>
            <div class="bg-white rounded-xl border p-4"><p class="text-[10px] font-black text-slate-400 uppercase">Huérfanas</p><p class="text-2xl font-black text-sky-600">${getOrphanCats(state.divisions).length}</p><p class="text-xs text-slate-500">en catálogo, no en menú</p></div>`;

        const orphans = getOrphanCats(state.divisions);
        document.getElementById('orphan-list').innerHTML = orphans.length
            ? orphans.map(c => `<li class="flex justify-between gap-2 py-1 border-b border-slate-100"><span>${esc(c)}</span><span class="text-emerald-600 font-bold">${BOOT.catCounts[c]||0} prod.</span></li>`).join('')
            : '<li class="text-slate-400">Todas las categorías del catálogo tienen al menos un enlace en el menú (en este editor).</li>';

        const auditNow = { ok: [], broken: [] };
        state.divisions.forEach(div => {
            ['linksLeft','linksRight'].forEach(key => {
                (div[key]||[]).forEach(link => {
                    if (link.linkType === 'search') return;
                    const v = link.linkValue;
                    const row = { division: div.title, name: link.name, linkValue: v, count: BOOT.catCounts[v] ?? 0 };
                    if (!BOOT.categoriasReales.includes(v)) auditNow.broken.push(row);
                    else auditNow.ok.push(row);
                });
            });
        });
        document.getElementById('links-ok').innerHTML = auditNow.ok.map(r =>
            `<div class="p-2 bg-emerald-50 rounded border border-emerald-100"><strong>${esc(r.name)}</strong><br><span class="text-slate-500">${esc(r.linkValue)}</span> · ${r.count} prod.</div>`
        ).join('') || '<p class="text-slate-400">—</p>';
        document.getElementById('links-broken').innerHTML = auditNow.broken.map(r =>
            `<div class="p-2 bg-rose-50 rounded border border-rose-100"><strong>${esc(r.name)}</strong><br><span class="text-slate-500">${esc(r.linkValue || '(vacío)')}</span> · ${esc(r.division)}</div>`
        ).join('') || '<p class="text-slate-400">Ninguno en el menú actual del editor.</p>';
    }

    // —— Checklist ——
    const CHECKLIST = {
        'Fase 1': ['Aviso megamenu vacío', 'Escape cierra menú', 'Ver en tienda tras guardar', 'Ayuda categoría exacta'],
        'Fase 2': ['Editor nivel3_menu', 'Validación enlaces vs catálogo', 'Prellenar si JSON vacío'],
        'Fase 3': ['Preview desktop/móvil admin', 'Trigger activo', 'Scroll interno móvil'],
        'Fase 4': ['Lista categorías huérfanas', 'Select categorías reales', 'Conteo productos por enlace'],
    };
    const clHost = document.getElementById('checklist-host');
    Object.entries(CHECKLIST).forEach(([phase, items]) => {
        const d = document.createElement('div');
        d.innerHTML = `<p class="font-black">${phase}</p>` + items.map(t =>
            `<label class="flex gap-2 py-1"><input type="checkbox" class="rounded approval-cb" data-label="${esc(t)}"> ${esc(t)}</label>`
        ).join('');
        clHost.appendChild(d);
    });
    document.getElementById('btn-copy').onclick = () => {
        const ok = [...document.querySelectorAll('.approval-cb:checked')].map(c => c.dataset.label);
        const notes = document.getElementById('notes').value;
        navigator.clipboard.writeText('MEGAMENU V2 DEMO\n\nAprobado:\n' + (ok.length ? ok.map(x => '• ' + x).join('\n') : '(nada)') + '\n\nNotas:\n' + (notes || ''));
        alert('Copiado');
    };

    // Restore session demo if any
    try {
        const saved = sessionStorage.getItem('demo_v2_saved');
        if (saved) {
            const p = JSON.parse(saved);
            if (p.divisions) { state.divisions = p.divisions; state.megamenuEmpty = false; }
            if (p.nivel3) state.nivel3 = p.nivel3;
        }
    } catch (e) {}

    renderSyncPanel();
    renderAdminBanners();
    </script>
</body>
</html>
