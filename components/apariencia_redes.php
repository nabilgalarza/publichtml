<?php
/**
 * Apariencia → Redes sociales (footer público)
 */
require_once __DIR__ . '/../lib/footer_social_helpers.php';

$cfg = improgyp_footer_config_read();
$redes = $cfg['redes_sociales'];
$presets = improgyp_footer_social_presets();
$msg = $_GET['msg'] ?? '';
?>
<?php if ($msg === 'redes_guardado'): ?>
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl mb-6 flex items-center gap-3 text-sm font-bold relative z-10">
    <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i> Redes sociales guardadas. Recarga la tienda para ver los iconos en el pie de página.
</div>
<?php endif; ?>

<div class="glass-card p-7 relative z-10 max-w-4xl">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-black text-slate-900 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-share-nodes text-violet-500"></i> Redes sociales
            </h2>
            <p class="text-xs text-slate-500 font-medium leading-relaxed max-w-xl">
                Iconos del <strong>footer</strong> (columna del logo). Usa enlaces completos (<code class="text-[10px] bg-slate-100 px-1 rounded">https://…</code>).
                Iconos de marca: <code class="text-[10px] bg-slate-100 px-1 rounded">fa-instagram</code>, <code class="text-[10px] bg-slate-100 px-1 rounded">fa-facebook</code>, etc.
            </p>
        </div>
        <a href="index.php#contacto" target="_blank" rel="noopener" class="shrink-0 inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-wide text-[#0E75AE] hover:text-[#1B263B] transition-colors">
            Ver en tienda <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
        </a>
    </div>

    <form method="POST" action="dashboard.php?view=apariencia&sub=redes" id="form-redes-sociales">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <input type="hidden" name="action" value="guardar_redes_sociales">
        <input type="hidden" name="redes_json" id="redes-json" value="">

        <div class="overflow-x-auto rounded-2xl border border-slate-100 mb-4">
            <table class="w-full text-left text-sm min-w-[640px]">
                <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-3 py-3 w-[140px]">Red</th>
                        <th class="px-3 py-3">URL</th>
                        <th class="px-3 py-3 w-[160px]">Icono</th>
                        <th class="px-3 py-3 w-14 text-center">Ord.</th>
                        <th class="px-3 py-3 w-12 text-center">On</th>
                        <th class="px-3 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody id="redes-tbody"></tbody>
            </table>
        </div>

        <button type="button" id="btn-add-red" class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-wide text-emerald-600 hover:text-emerald-700 mb-6">
            <i class="fa-solid fa-plus"></i> Añadir red
        </button>

        <div class="flex flex-wrap gap-3 pt-4 border-t border-slate-100">
            <button type="submit" class="inline-flex items-center gap-2 bg-[#1B263B] hover:bg-[#0E75AE] text-white font-black text-[11px] uppercase tracking-widest px-6 py-3 rounded-xl transition-colors">
                <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
            </button>
        </div>
    </form>

    <p class="text-[10px] text-slate-400 mt-4 leading-relaxed">
        Familia automática: marcas conocidas usan <code class="bg-white px-1 rounded">fa-brands</code>; otros usan <code class="bg-white px-1 rounded">fa-solid</code>.
    </p>
</div>

<script>
(function () {
    const PRESETS = <?= json_encode($presets, JSON_UNESCAPED_UNICODE) ?>;
    const BRAND_ICONS = <?= json_encode(improgyp_footer_social_brand_icons(), JSON_UNESCAPED_UNICODE) ?>;
    let rows = <?= json_encode($redes, JSON_UNESCAPED_UNICODE) ?>;

    const tbody = document.getElementById('redes-tbody');
    const form = document.getElementById('form-redes-sociales');
    const hiddenJson = document.getElementById('redes-json');

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function normalizeIcon(raw) {
        let v = String(raw || '').trim().toLowerCase();
        if (!v) return 'fa-link';
        if (!v.startsWith('fa-')) v = 'fa-' + v;
        if (!/^fa-[a-z0-9\-]+$/.test(v)) return 'fa-link';
        return v;
    }

    function iconFamily(icon) {
        return BRAND_ICONS.indexOf(icon) >= 0 ? 'fa-brands' : 'fa-solid';
    }

    function presetOptions(selectedKey) {
        return Object.keys(PRESETS).map(function (key) {
            const p = PRESETS[key];
            const label = key === 'custom' ? 'Personalizado…' : p.etiqueta;
            const sel = key === selectedKey ? ' selected' : '';
            return '<option value="' + esc(key) + '"' + sel + '>' + esc(label) + '</option>';
        }).join('');
    }

    function detectPreset(row) {
        const icon = normalizeIcon(row.icono);
        for (const key of Object.keys(PRESETS)) {
            if (key === 'custom') continue;
            if (PRESETS[key].icono === icon) return key;
        }
        return 'custom';
    }

    function render() {
        if (!rows.length) {
            rows.push({
                id: 'red-1',
                etiqueta: 'WhatsApp',
                url: 'https://wa.me/593991754887',
                icono: 'fa-whatsapp',
                orden: 1,
                activo: true
            });
        }
        tbody.innerHTML = rows.map(function (row, idx) {
            const preset = detectPreset(row);
            const icon = normalizeIcon(row.icono);
            const fam = iconFamily(icon);
            return '<tr class="border-t border-slate-50 redes-row" data-idx="' + idx + '">' +
                '<td class="px-3 py-2.5 align-middle">' +
                    '<select class="red-preset w-full premium-input rounded-lg px-2 py-1.5 text-xs border border-slate-100">' +
                        presetOptions(preset) +
                    '</select>' +
                    '<input type="text" class="red-etiqueta mt-1 w-full premium-input rounded-lg px-2 py-1.5 text-xs border border-slate-100" value="' + esc(row.etiqueta || '') + '" placeholder="Etiqueta">' +
                '</td>' +
                '<td class="px-3 py-2.5 align-middle">' +
                    '<input type="url" class="red-url w-full premium-input rounded-lg px-2 py-1.5 text-xs border border-slate-100 font-mono" value="' + esc(row.url || '') + '" placeholder="https://instagram.com/…">' +
                '</td>' +
                '<td class="px-3 py-2.5 align-middle">' +
                    '<div class="flex items-center gap-2">' +
                        '<span class="red-icon-preview w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-slate-600">' +
                            '<i class="' + fam + ' ' + esc(icon) + ' text-sm" aria-hidden="true"></i>' +
                        '</span>' +
                        '<input type="text" class="red-icono flex-1 premium-input rounded-lg px-2 py-1.5 text-xs border border-slate-100 font-mono" value="' + esc(icon.replace(/^fa-/, '')) + '" placeholder="instagram">' +
                    '</div>' +
                '</td>' +
                '<td class="px-3 py-2.5 align-middle">' +
                    '<input type="number" min="1" max="99" class="red-orden w-full premium-input rounded-lg px-2 py-1.5 text-xs border border-slate-100 text-center" value="' + esc(row.orden || (idx + 1)) + '">' +
                '</td>' +
                '<td class="px-3 py-2.5 align-middle text-center">' +
                    '<input type="checkbox" class="red-activo rounded border-slate-300" ' + (row.activo !== false ? 'checked' : '') + ' title="Visible en footer">' +
                '</td>' +
                '<td class="px-3 py-2.5 align-middle text-center">' +
                    '<button type="button" class="red-remove text-slate-300 hover:text-rose-500 p-1" title="Eliminar"><i class="fa-solid fa-trash-can"></i></button>' +
                '</td>' +
            '</tr>';
        }).join('');
        bindRows();
    }

    function syncRowFromDom(tr, idx) {
        const preset = tr.querySelector('.red-preset').value;
        let etiqueta = tr.querySelector('.red-etiqueta').value.trim();
        let icono = normalizeIcon(tr.querySelector('.red-icono').value);
        if (preset !== 'custom' && PRESETS[preset]) {
            if (!etiqueta) etiqueta = PRESETS[preset].etiqueta;
            if (!tr.querySelector('.red-icono').dataset.touched) {
                icono = PRESETS[preset].icono;
            }
        }
        rows[idx] = {
            id: rows[idx] && rows[idx].id ? rows[idx].id : ('red-' + (idx + 1)),
            etiqueta: etiqueta || 'Enlace',
            url: tr.querySelector('.red-url').value.trim(),
            icono: icono,
            orden: parseInt(tr.querySelector('.red-orden').value, 10) || (idx + 1),
            activo: tr.querySelector('.red-activo').checked
        };
    }

    function updateIconPreview(tr) {
        const icon = normalizeIcon(tr.querySelector('.red-icono').value);
        const fam = iconFamily(icon);
        const prev = tr.querySelector('.red-icon-preview');
        if (prev) prev.innerHTML = '<i class="' + fam + ' ' + esc(icon) + ' text-sm" aria-hidden="true"></i>';
    }

    function bindRows() {
        tbody.querySelectorAll('.redes-row').forEach(function (tr) {
            const idx = parseInt(tr.dataset.idx, 10);

            tr.querySelector('.red-preset').addEventListener('change', function () {
                const key = this.value;
                if (key !== 'custom' && PRESETS[key]) {
                    tr.querySelector('.red-etiqueta').value = PRESETS[key].etiqueta;
                    tr.querySelector('.red-icono').value = PRESETS[key].icono.replace(/^fa-/, '');
                    tr.querySelector('.red-icono').dataset.touched = '';
                }
                updateIconPreview(tr);
                syncRowFromDom(tr, idx);
            });

            tr.querySelector('.red-icono').addEventListener('input', function () {
                this.dataset.touched = '1';
                updateIconPreview(tr);
            });

            tr.querySelector('.red-remove').addEventListener('click', function () {
                rows.splice(idx, 1);
                render();
            });

            ['red-etiqueta', 'red-url', 'red-icono', 'red-orden', 'red-activo'].forEach(function (cls) {
                const el = tr.querySelector('.' + cls);
                if (!el) return;
                const ev = cls === 'red-activo' ? 'change' : 'input';
                el.addEventListener(ev, function () {
                    syncRowFromDom(tr, idx);
                    if (cls === 'red-icono') updateIconPreview(tr);
                });
            });
        });
    }

    document.getElementById('btn-add-red').addEventListener('click', function () {
        rows.push({
            id: 'red-' + (rows.length + 1),
            etiqueta: '',
            url: '',
            icono: 'fa-link',
            orden: rows.length + 1,
            activo: true
        });
        render();
    });

    form.addEventListener('submit', function () {
        const out = [];
        tbody.querySelectorAll('.redes-row').forEach(function (tr, idx) {
            const preset = tr.querySelector('.red-preset').value;
            let etiqueta = tr.querySelector('.red-etiqueta').value.trim();
            let icono = normalizeIcon(tr.querySelector('.red-icono').value);
            if (preset !== 'custom' && PRESETS[preset] && !tr.querySelector('.red-icono').dataset.touched) {
                icono = PRESETS[preset].icono;
                if (!etiqueta) etiqueta = PRESETS[preset].etiqueta;
            }
            out.push({
                id: (rows[idx] && rows[idx].id) ? rows[idx].id : ('red-' + (idx + 1)),
                etiqueta: etiqueta || 'Enlace',
                url: tr.querySelector('.red-url').value.trim(),
                icono: icono,
                orden: parseInt(tr.querySelector('.red-orden').value, 10) || (idx + 1),
                activo: tr.querySelector('.red-activo').checked
            });
        });
        hiddenJson.value = JSON.stringify(out);
    });

    render();
})();
</script>
