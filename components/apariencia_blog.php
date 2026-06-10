<?php
/**
 * components/apariencia_blog.php
 * Personalizador de Apariencia del Blog — 5 layouts + preview con paginación
 */

require_once __DIR__ . '/../lib/blog_layout_slots.php';

$cfg_path = __DIR__ . '/../config_blog.json';
$cfg = file_exists($cfg_path) ? (json_decode(file_get_contents($cfg_path), true) ?: []) : [];

$layout_actual = blog_layout_normalize($cfg['layout'] ?? 'editorial');
$accent_actual = $cfg['accent']       ?? '#0e75ae';
$font_actual   = $cfg['font']         ?? 'sans';
$showDate      = !empty($cfg['showDate']);
$showReadTime  = !empty($cfg['showReadTime']);
$showViews     = !empty($cfg['showViews']);

$layouts = blog_layout_admin_options();
$msg = $_GET['msg'] ?? '';
$per_page_actual = blog_layout_per_page($layout_actual);
?>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&family=Plus+Jakarta+Sans:wght@300;400;600;700;800&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
<style>
.layout-card { transition: all .25s; cursor: pointer; }
.layout-card:hover { transform: translateY(-3px); }
.blog-live-canvas { background: #f8fafc; border-radius: 20px; overflow: hidden; min-height: 300px; }
.lc-pagination { display: flex; justify-content: center; gap: 6px; margin-top: 12px; padding-bottom: 12px; }
.lc-dot { height: 8px; width: 8px; border-radius: 4px; border: none; padding: 0; cursor: pointer; transition: width .25s, opacity .25s; }
.lc-dot.is-active { width: 24px; opacity: 1; }
</style>

<?php if ($msg === 'blog_guardado'): ?>
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl mb-6 flex items-center gap-3 text-sm font-bold relative z-10">
    <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i> Apariencia del blog guardada exitosamente.
</div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-5 gap-8 relative z-10">

    <div class="xl:col-span-2 space-y-6">
        <div class="glass-card p-7">
            <h2 class="text-xl font-black text-slate-900 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-square-rss text-orange-400"></i> Apariencia de Blog
            </h2>
            <p class="text-xs text-slate-400 font-medium mb-6">Aplica al <strong>Home</strong> y a la página <strong>blog.php</strong> (color, tipografía, metadatos y diseño de cuadrícula).</p>

            <form id="blog-appearance-form" method="POST" action="dashboard.php?view=apariencia&sub=blog">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="guardar_apariencia_blog">

                <div class="mb-6">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-3">Diseño de Cuadrícula</label>
                    <div class="grid grid-cols-2 gap-2" id="layout-grid">
                        <?php foreach ($layouts as $key => $info): ?>
                        <div class="layout-card p-3 border-2 rounded-2xl flex items-center gap-3 <?= $layout_actual === $key ? 'border-orange-400 bg-orange-50' : 'border-slate-200 hover:border-slate-300 bg-white' ?>"
                             onclick="selectLayout('<?= $key ?>')" data-key="<?= $key ?>">
                            <div class="w-9 h-9 bg-gradient-to-br <?= $info['color'] ?> rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid <?= $info['icon'] ?> text-white text-xs"></i>
                            </div>
                            <span class="text-xs font-black text-slate-700 leading-tight"><?= $info['label'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="f-layout" name="layout" value="<?= htmlspecialchars($layout_actual) ?>">
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-3">Color de Énfasis</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="f-accent" name="accent" value="<?= htmlspecialchars($accent_actual) ?>"
                            class="w-12 h-12 rounded-xl border-2 border-slate-200 cursor-pointer p-1 bg-white"
                            oninput="updateAccentPreview(this.value)">
                        <div class="flex gap-2 flex-wrap">
                            <?php foreach (['#0e75ae','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4'] as $c): ?>
                            <button type="button" onclick="document.getElementById('f-accent').value='<?= $c ?>'; updateAccentPreview('<?= $c ?>')"
                                class="w-7 h-7 rounded-full border-2 border-white shadow-md hover:scale-110 transition-transform"
                                style="background:<?= $c ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-3">Familia Tipográfica</label>
                    <div class="grid grid-cols-3 gap-2">
                        <?php foreach (['sans'=>'Outfit (Sans)','serif'=>'Merriweather (Serif)','mono'=>'Monoespaciado'] as $fk => $fl): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="font" value="<?= $fk ?>" <?= $font_actual === $fk ? 'checked' : '' ?> class="hidden peer" onchange="updateFontPreview('<?= $fk ?>')">
                            <div class="peer-checked:border-orange-400 peer-checked:bg-orange-50 border-2 border-slate-200 rounded-xl p-3 text-center hover:border-slate-300 transition-all">
                                <span class="text-xs font-black text-slate-700 leading-tight block"><?= $fl ?></span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-3">Metadatos Visibles</label>
                    <div class="space-y-3">
                        <?php foreach ([
                            ['showDate','1','fa-calendar','Mostrar Fecha', $showDate],
                            ['showReadTime','1','fa-clock','Mostrar Tiempo de Lectura', $showReadTime],
                            ['showViews','1','fa-eye','Mostrar Visitas', $showViews],
                        ] as [$tn,$tv,$ic,$lbl,$checked]): ?>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" name="<?= $tn ?>" value="<?= $tv ?>" <?= $checked ? 'checked' : '' ?>
                                    class="sr-only peer" onchange="updateToggle()">
                                <div class="w-10 h-5 bg-slate-200 peer-checked:bg-orange-400 rounded-full transition-colors"></div>
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow-sm peer-checked:translate-x-5 transition-transform"></div>
                            </div>
                            <span class="text-sm font-medium text-slate-700 flex items-center gap-2">
                                <i class="fa-solid <?= $ic ?> text-slate-400 w-4"></i> <?= $lbl ?>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-orange-200 transition-all active:scale-95">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Apariencia
                </button>
            </form>
        </div>
    </div>

    <div class="xl:col-span-3">
        <div class="glass-card p-6 h-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-black text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-display text-orange-400"></i> Previsualización en Vivo
                </h3>
                <span id="lc-layout-label" class="text-xs bg-orange-100 text-orange-700 px-3 py-1 rounded-full font-black">
                    <?= $layouts[$layout_actual]['label'] ?? 'Editorial' ?>
                </span>
            </div>
            <p id="lc-slot-hint" class="text-xs text-slate-500 font-bold mb-3"></p>

            <div id="blog-live-canvas" class="blog-live-canvas border border-slate-200" style="--accent:<?= htmlspecialchars($accent_actual) ?>"></div>
            <div id="lc-pagination" class="lc-pagination"></div>

            <p class="text-xs text-slate-400 font-medium mt-4 text-center">
                <i class="fa-solid fa-circle-info mr-1"></i> Misma lógica que el Home: cupos fijos, paginación clicable (carrusel: arrastre + puntos).
            </p>
        </div>
    </div>
</div>

<script>
let canvasState = {
    layout: <?= json_encode($layout_actual) ?>,
    accent: <?= json_encode($accent_actual) ?>,
    page: 0,
    showDate: <?= $showDate ? 'true' : 'false' ?>,
    showReadTime: <?= $showReadTime ? 'true' : 'false' ?>,
    showViews: <?= $showViews ? 'true' : 'false' ?>,
};

const layoutPerPage = <?= json_encode(array_combine(
    blog_layouts_allowed(),
    array_map('blog_layout_per_page', blog_layouts_allowed())
), JSON_UNESCAPED_UNICODE) ?>;

const layoutLabels = <?= json_encode(array_map(fn($v) => $v['label'], $layouts), JSON_UNESCAPED_UNICODE) ?>;

const samplePosts = [
    { titulo: 'Guía Definitiva: Drywall en Zonas Húmedas', cat: 'Drywall' },
    { titulo: '5 Herramientas Esenciales para Drywall 2026', cat: 'Herramientas' },
    { titulo: 'Cómo elegir el mejor taladro percutor', cat: 'Taladros' },
    { titulo: 'Normativas ISO para construcción en seco', cat: 'Normativas' },
    { titulo: 'Tendencias de interiorismo con Drywall', cat: 'Tendencias' },
    { titulo: 'Aislamiento acústico con placas de yeso', cat: 'Acústica' },
    { titulo: 'Mantenimiento de herramientas eléctricas', cat: 'Mantenimiento' },
    { titulo: 'Selladores para juntas en baños', cat: 'Selladores' },
    { titulo: 'Comparativa de tornillos para drywall', cat: 'Comparativas' },
];

function perPage() { return layoutPerPage[canvasState.layout] || 3; }
function pageCount() { return Math.max(1, Math.ceil(samplePosts.length / perPage())); }
function pageSlice(page) {
    const n = perPage();
    return samplePosts.slice(page * n, page * n + n);
}

function updateSlotHint() {
    const n = perPage();
    const pages = pageCount();
    const el = document.getElementById('lc-slot-hint');
    const paginated = canvasState.layout !== 'carousel';
    if (el) {
        el.textContent = paginated
            ? `${n} artículos por página · ${pages} páginas de ejemplo (clic en los puntos)`
            : `3 visibles · arrastre para ver ${samplePosts.length} de ejemplo · puntos clicables`;
    }
}

function buildLcPagination(active, total, onSelect) {
    const wrap = document.getElementById('lc-pagination');
    if (!wrap) return;
    if (total <= 1 && canvasState.layout !== 'carousel') {
        wrap.innerHTML = '';
        return;
    }
    wrap.innerHTML = '';
    for (let i = 0; i < total; i++) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'lc-dot' + (i === active ? ' is-active' : '');
        btn.style.background = canvasState.accent;
        btn.style.opacity = i === active ? '1' : '0.25';
        if (i !== active) btn.style.width = '8px';
        else btn.style.width = '24px';
        btn.setAttribute('aria-label', 'Página ' + (i + 1));
        btn.onclick = () => onSelect(i);
        wrap.appendChild(btn);
    }
}

function cardBase(p, extra = '') {
    const meta = [
        canvasState.showDate ? '<span>Jun 2026</span>' : '',
        canvasState.showReadTime ? '<span>5 min</span>' : '',
        canvasState.showViews ? '<span>1.2K</span>' : '',
    ].filter(Boolean).join(' · ');
    return `<div style="background:white;border-radius:10px;overflow:hidden;${extra}">
        <div style="height:72px;background:linear-gradient(135deg,#e2e8f0,#cbd5e1);position:relative">
            <span style="position:absolute;top:6px;left:8px;background:${canvasState.accent};color:white;padding:2px 8px;border-radius:20px;font-size:9px;font-weight:900">${p.cat}</span>
        </div>
        <div style="padding:8px">
            <h4 style="font-size:10px;font-weight:900;color:#1e293b;line-height:1.35;margin:0 0 4px">${p.titulo.substring(0,42)}${p.titulo.length > 42 ? '…' : ''}</h4>
            <div style="font-size:8px;color:#94a3b8">${meta || '—'}</div>
        </div>
    </div>`;
}

function cyberCard(p) {
    return `<div style="background:#12122a;border:1px solid ${canvasState.accent};border-radius:10px;overflow:hidden">
        <div style="height:60px;background:linear-gradient(135deg,#1a0533,#3b0764)">
            <span style="display:inline-block;margin:6px 8px;background:${canvasState.accent};color:white;padding:2px 8px;border-radius:20px;font-size:9px;font-weight:900">${p.cat}</span>
        </div>
        <div style="padding:8px"><h4 style="font-size:10px;font-weight:900;color:white;margin:0">${p.titulo.substring(0,36)}…</h4></div>
    </div>`;
}

function renderLayoutBody(slice) {
    const s = slice;
    switch (canvasState.layout) {
        case 'editorial':
            return `<div style="display:grid;grid-template-columns:1fr 1fr;gap:1px;background:#e2e8f0;padding:0">
                ${s[0] ? `<div style="grid-row:span 2">${cardBase(s[0], 'border-radius:0;min-height:160px')}</div>` : ''}
                <div style="display:flex;flex-direction:column;gap:1px">
                    ${(s.slice(1, 3)).map(p => cardBase(p, 'border-radius:0')).join('')}
                </div>
            </div>`;
        case 'duo50':
            return `<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:10px;background:#f8fafc">
                ${s[0] ? `<div>${cardBase(s[0])}</div>` : ''}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                    ${(s.slice(1, 3)).map(p => cardBase(p)).join('')}
                </div>
            </div>`;
        case 'grid3':
            return `<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:12px;background:#f8fafc">
                ${s.map(p => cardBase(p)).join('')}
            </div>`;
        case 'cyberneon':
            return `<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;padding:12px;background:#0a0a1a">
                ${s.map(p => cyberCard(p)).join('')}
            </div>`;
        case 'carousel': {
            const vis = 3;
            const shown = samplePosts.slice(0, Math.min(samplePosts.length, 7));
            return `<div style="display:flex;gap:8px;padding:12px;background:#f8fafc;overflow-x:auto;scroll-snap-type:x mandatory" id="lc-carousel-track">
                ${shown.map(p => `<div style="min-width:32%;flex-shrink:0;scroll-snap-align:start">${cardBase(p)}</div>`).join('')}
            </div>`;
        }
        default:
            return cardBase(s[0] || samplePosts[0]);
    }
}

function renderCanvas() {
    const el = document.getElementById('blog-live-canvas');
    if (!el) return;

    if (canvasState.layout === 'carousel') {
        el.innerHTML = renderLayoutBody([]);
        const pages = Math.ceil(samplePosts.length / 3);
        buildLcPagination(0, pages, (i) => {
            const track = document.getElementById('lc-carousel-track');
            if (!track) return;
            const children = track.children;
            const target = children[Math.min(i * 3, children.length - 1)];
            if (target) track.scrollTo({ left: target.offsetLeft - track.offsetLeft, behavior: 'smooth' });
            buildLcPagination(i, pages, arguments.callee);
        });
        updateSlotHint();
        return;
    }

    const slice = pageSlice(canvasState.page);
    el.innerHTML = renderLayoutBody(slice);
    buildLcPagination(canvasState.page, pageCount(), (i) => {
        canvasState.page = i;
        renderCanvas();
    });
    updateSlotHint();
}

function selectLayout(key) {
    canvasState.layout = key;
    canvasState.page = 0;
    document.getElementById('f-layout').value = key;
    document.querySelectorAll('#layout-grid .layout-card').forEach(c => {
        c.classList.toggle('border-orange-400', c.dataset.key === key);
        c.classList.toggle('bg-orange-50', c.dataset.key === key);
        c.classList.toggle('border-slate-200', c.dataset.key !== key);
    });
    document.getElementById('lc-layout-label').textContent = layoutLabels[key] || key;
    renderCanvas();
}

function updateAccentPreview(val) {
    canvasState.accent = val;
    document.getElementById('blog-live-canvas').style.setProperty('--accent', val);
    renderCanvas();
}
function updateFontPreview() { renderCanvas(); }
function updateToggle() {
    canvasState.showDate = document.querySelector('[name="showDate"]').checked;
    canvasState.showReadTime = document.querySelector('[name="showReadTime"]').checked;
    canvasState.showViews = document.querySelector('[name="showViews"]').checked;
    renderCanvas();
}

renderCanvas();

document.getElementById('blog-appearance-form').addEventListener('submit', function () {
    ['showDate','showReadTime','showViews'].forEach(name => {
        const cb = this.querySelector(`[name="${name}"]`);
        if (!cb.checked) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = name;
            hidden.value = '0';
            this.appendChild(hidden);
            cb.name = name + '_cb';
        }
    });
});
</script>
