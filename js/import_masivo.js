/**
 * Asistente de importación masiva — modelo: un código por medida.
 */
(function () {
    const LOTE = 12;
    const state = {
        csvFile: null,
        usarCsvSesion: false,
        preview: null,
        subiendoFotos: false,
        colaFotos: [],
        permitirId: false,
    };

    function csrf() {
        const el = document.querySelector('#import-wizard [name="csrf_token"]');
        return el ? el.value : '';
    }

    function qs(id) {
        return document.getElementById(id);
    }

    function setPaso(n) {
        document.querySelectorAll('[data-import-paso]').forEach(function (el) {
            el.classList.toggle('hidden', parseInt(el.dataset.importPaso, 10) !== n);
        });
        document.querySelectorAll('[data-import-step-ind]').forEach(function (el) {
            const step = parseInt(el.dataset.importStepInd, 10);
            el.classList.toggle('bg-[#1B263B]', step <= n);
            el.classList.toggle('text-white', step <= n);
            el.classList.toggle('bg-slate-100', step > n);
            el.classList.toggle('text-slate-400', step > n);
        });
        if (n === 2) {
            mostrarContinuarResumen();
        }
    }

    function mostrarContinuarResumen() {
        if (!state.csvFile && !state.usarCsvSesion) return;
        qs('btn-ir-resumen')?.classList.remove('hidden');
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function renderPreview(p) {
        const box = qs('import-preview-box');
        if (!box || !p) return;
        if (p.error) {
            box.innerHTML = '<p class="text-rose-600 font-bold text-sm">' + escapeHtml(p.error) + '</p>';
            box.classList.remove('hidden');
            return;
        }
        let html = '<ul class="text-sm space-y-2 text-slate-700">';
        html += '<li><strong>' + p.filas + '</strong> productos en el archivo</li>';
        html += '<li><strong class="text-emerald-700">' + p.nuevos + '</strong> nuevos · <strong class="text-[#3A86FF]">' + p.actualizados + '</strong> actualizaciones</li>';
        html += '<li><strong class="text-emerald-700">' + p.fotos_coinciden_csv + '</strong> con foto lista';
        if (p.fotos_faltan_csv > 0) {
            html += ' · <strong class="text-amber-700">' + p.fotos_faltan_csv + '</strong> sin foto</li>';
        } else {
            html += '</li>';
        }
        if (p.zip_fotos_ok) {
            html += '<li class="text-emerald-700">ZIP: ' + p.zip_fotos_ok + ' foto(s) cargadas';
            if (p.zip_fotos_skip) html += ', ' + p.zip_fotos_skip + ' omitidas';
            html += '</li>';
        }
        if (p.tiene_columna_id && !p.riesgo_id) {
            html += '<li class="text-slate-500">CSV con columna ID (desmarca actualizar por ID si son productos nuevos)</li>';
        }
        if (p.riesgo_id) {
            html += '<li class="text-amber-700 font-bold">Muchos ID en el CSV: puede pisar productos existentes. Confirma en el paso 3 si es intencional.</li>';
        }
        if (p.sin_precio > 0) {
            html += '<li class="text-amber-700">' + p.sin_precio + ' fila(s) sin precio en Unidad_Precios (ej. <code>Presentación Única: 1.20</code>)</li>';
        }
        if (p.sin_codigo > 0) {
            html += '<li class="text-rose-600">' + p.sin_codigo + ' fila(s) sin código</li>';
        }
        if (p.duplicados_csv && p.duplicados_csv.length) {
            html += '<li class="text-rose-600">Códigos duplicados: ' + escapeHtml(p.duplicados_csv.join(', ')) + '</li>';
        }
        if (p.codigos_sin_foto && p.codigos_sin_foto.length) {
            html += '<li class="text-xs text-slate-500">Sin foto (muestra): ' + escapeHtml(p.codigos_sin_foto.join(', ')) + '</li>';
        }
        html += '</ul>';
        html += '<p class="text-[10px] text-slate-400 mt-3 font-bold uppercase">Al importar, las fotos enlazadas pasan al catálogo visible en Inventario.</p>';
        box.innerHTML = html;
        box.classList.remove('hidden');
        qs('import-paso1-actions')?.classList.remove('hidden');
    }

    function aplicarPreview(data, nombreArchivo) {
        state.preview = data;
        if (data.csv_en_sesion) {
            state.usarCsvSesion = true;
        }
        if (nombreArchivo) {
            qs('import-csv-nombre').textContent = nombreArchivo;
        }
        renderPreview(data);
        mostrarContinuarResumen();
    }

    async function previewCsv(file) {
        const fd = new FormData();
        fd.append('csrf_token', csrf());
        fd.append('archivo_csv', file);
        const res = await fetch('dashboard.php?ajax=importacion_csv_preview', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
        });
        const data = await res.json();
        state.csvFile = file;
        state.usarCsvSesion = !!data.csv_en_sesion;
        aplicarPreview(data, file.name);
        return data;
    }

    async function procesarZip(file) {
        const label = qs('import-zip-nombre');
        const ayuda = qs('import-zip-ayuda');
        const input = qs('import-zip-input');
        const zipLabel = qs('import-zip-label');
        const maxBytes = 80 * 1024 * 1024;

        if (file.size > maxBytes) {
            alert('El ZIP es muy grande (máx. ~80 MB). Usa CSV + fotos por separado o reduce el paquete.');
            return;
        }

        const fd = new FormData();
        fd.append('csrf_token', csrf());
        fd.append('archivo_zip', file);

        if (input) input.disabled = true;
        if (zipLabel) zipLabel.classList.add('opacity-60', 'pointer-events-none');
        if (label) label.textContent = 'Procesando ZIP…';
        if (ayuda) {
            ayuda.textContent = 'Extrayendo CSV y fotos. No cierres esta página (1–3 min si hay muchas imágenes).';
            ayuda.classList.remove('hidden');
        }

        try {
            const res = await fetch('dashboard.php?ajax=importacion_zip', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
            });
            const raw = await res.text();
            let data;
            try {
                data = JSON.parse(raw);
            } catch (parseErr) {
                const snippet = raw.trim().slice(0, 120);
                throw new Error(
                    'El servidor no respondió correctamente' +
                    (res.status ? ' (HTTP ' + res.status + ')' : '') +
                    '. Suele ser timeout de PHP o ZIP demasiado pesado.' +
                    (snippet ? ' Respuesta: ' + snippet : '')
                );
            }
            if (!res.ok && !data.error) {
                throw new Error(data.error || 'Error del servidor (HTTP ' + res.status + ')');
            }
            if (data.error) {
                throw new Error(data.error);
            }
            state.csvFile = null;
            state.usarCsvSesion = true;
            if (label) label.textContent = file.name + ' ✓';
            if (ayuda) {
                ayuda.textContent = (data.zip_fotos_ok || 0) + ' foto(s) del ZIP · ' + (data.fotos_coinciden_csv || 0) + ' coinciden con el CSV';
                ayuda.classList.remove('hidden');
            }
            qs('import-csv-nombre').textContent = 'CSV incluido en el ZIP';
            aplicarPreview(data, null);
        } catch (err) {
            if (label) label.textContent = 'Error — selecciona el ZIP de nuevo';
            if (ayuda) {
                ayuda.textContent = err.message || 'Error al procesar el ZIP';
                ayuda.classList.remove('hidden');
            }
            alert(err.message || 'No se pudo procesar el ZIP.');
        } finally {
            if (input) input.disabled = false;
            if (zipLabel) zipLabel.classList.remove('opacity-60', 'pointer-events-none');
        }
    }

    async function subirLoteFotos(archivos) {
        const fd = new FormData();
        fd.append('csrf_token', csrf());
        for (let i = 0; i < archivos.length; i++) {
            fd.append('fotos[]', archivos[i]);
        }
        const res = await fetch('dashboard.php?ajax=importacion_foto_lote', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
        });
        const raw = await res.text();
        let data;
        try {
            data = JSON.parse(raw);
        } catch (e) {
            throw new Error('Error de servidor al subir fotos.');
        }
        if (data.error) throw new Error(data.error);
        return data;
    }

    async function refrescarPreview() {
        if (state.csvFile) {
            await previewCsv(state.csvFile);
            return;
        }
        if (state.usarCsvSesion) {
            const fd = new FormData();
            fd.append('csrf_token', csrf());
            const res = await fetch('dashboard.php?ajax=importacion_repreview', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
            });
            const data = await res.json();
            if (!data.error) {
                aplicarPreview(data, null);
            }
        }
    }

    async function procesarColaFotos() {
        if (state.subiendoFotos) return;
        state.subiendoFotos = true;
        const txt = qs('import-fotos-status');
        while (state.colaFotos.length > 0) {
            const lote = state.colaFotos.splice(0, LOTE);
            if (txt) txt.textContent = 'Subiendo fotos…';
            try {
                const data = await subirLoteFotos(lote);
                if (txt) {
                    txt.textContent = data.total_fotos + ' foto(s) en cola (se enlazan al importar)';
                }
                const warn = qs('import-fotos-warnings');
                if (warn && data.warnings && data.warnings.length) {
                    warn.classList.remove('hidden');
                    warn.innerHTML = data.warnings.slice(-5).map(function (w) {
                        return '<li>' + escapeHtml(w) + '</li>';
                    }).join('');
                }
                await refrescarPreview();
            } catch (err) {
                alert(err.message);
                state.colaFotos = lote.concat(state.colaFotos);
                break;
            }
        }
        state.subiendoFotos = false;
        mostrarContinuarResumen();
    }

    async function ejecutarImport() {
        if (!state.csvFile && !state.usarCsvSesion) {
            alert('Sube un CSV o un ZIP en el paso 1.');
            setPaso(1);
            return;
        }
        if (state.preview && state.preview.riesgo_id && !state.permitirId) {
            alert('Este CSV actualiza por ID. Marca la casilla de confirmación o quita la columna ID para productos nuevos.');
            setPaso(3);
            return;
        }
        if (!confirm('¿Importar estos productos al catálogo en vivo?')) {
            return;
        }

        const fd = new FormData();
        fd.append('csrf_token', csrf());
        if (state.usarCsvSesion) {
            fd.append('usar_csv_sesion', '1');
        } else if (state.csvFile) {
            fd.append('archivo_csv', state.csvFile);
        }
        if (state.permitirId) fd.append('permitir_id', '1');

        const btnImport = qs('btn-importar');
        const btnAhora = qs('btn-importar-ahora');
        if (btnImport) btnImport.disabled = true;
        if (btnAhora) btnAhora.disabled = true;

        const res = await fetch('dashboard.php?ajax=importacion_ejecutar', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
        });
        const data = await res.json();

        if (btnImport) btnImport.disabled = false;
        if (btnAhora) btnAhora.disabled = false;

        if (data.error) {
            alert(data.error);
            return;
        }

        const box = qs('import-resultado');
        if (box) {
            box.innerHTML =
                '<p class="font-black text-lg text-slate-900 mb-2">Importación completada</p>' +
                '<p class="text-sm text-slate-600">' +
                data.new + ' nuevos · ' + data.upd + ' actualizados</p>' +
                '<p class="text-sm text-emerald-700 font-bold mt-1">' +
                data.img_ok + ' productos con imagen en el catálogo · ' + data.img_sin + ' sin imagen</p>' +
                (data.err ? '<p class="text-rose-600 text-sm">' + data.err + ' errores</p>' : '') +
                '<p class="text-xs text-slate-500 mt-3">Abre Inventario → Catálogo para ver las fichas.</p>';
            box.classList.remove('hidden');
        }
        setPaso(4);
    }

    function init() {
        const wizard = qs('import-wizard');
        if (!wizard || wizard.dataset.bound) return;
        wizard.dataset.bound = '1';

        qs('import-csv-input')?.addEventListener('change', async function (e) {
            const file = e.target.files[0];
            if (!file) return;
            await previewCsv(file);
        });

        qs('import-zip-input')?.addEventListener('change', async function (e) {
            const file = e.target.files[0];
            if (!file) return;
            await procesarZip(file);
        });

        qs('import-fotos-input')?.addEventListener('change', function (e) {
            const files = e.target.files;
            if (!files || !files.length) return;
            for (let i = 0; i < files.length; i++) {
                state.colaFotos.push(files[i]);
            }
            e.target.value = '';
            procesarColaFotos();
        });

        qs('btn-ir-fotos')?.addEventListener('click', function () { setPaso(2); });
        qs('btn-importar-ahora')?.addEventListener('click', ejecutarImport);
        qs('btn-ir-resumen-directo')?.addEventListener('click', async function () {
            if (!state.csvFile && !state.usarCsvSesion) {
                alert('Sube un CSV o ZIP primero.');
                return;
            }
            await irAResumen();
        });
        qs('btn-ir-resumen')?.addEventListener('click', async function () {
            await irAResumen();
        });

        async function irAResumen() {
            await refrescarPreview();
            const resumen = qs('import-preview-box-resumen');
            const prev = qs('import-preview-box');
            if (resumen && prev && !prev.classList.contains('hidden')) {
                resumen.innerHTML = prev.innerHTML;
            }
            setPaso(3);
        }

        qs('btn-solo-fotos')?.addEventListener('click', async function () {
            if (!confirm('¿Actualizar solo las fotos de productos que ya existen (por código)?')) return;
            const res = await fetch('dashboard.php?ajax=importacion_solo_fotos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'csrf_token=' + encodeURIComponent(csrf()),
                credentials: 'same-origin',
            });
            const data = await res.json();
            if (data.error) {
                alert(data.error);
                return;
            }
            alert(data.ok + ' productos con foto en el catálogo.' + (data.sin ? ' ' + data.sin + ' fotos sin producto con ese código.' : ''));
        });

        qs('import-permitir-id')?.addEventListener('change', function (e) {
            state.permitirId = e.target.checked;
        });

        qs('btn-importar')?.addEventListener('click', ejecutarImport);

        qs('btn-nueva-import')?.addEventListener('click', function () {
            state.csvFile = null;
            state.usarCsvSesion = false;
            state.preview = null;
            state.colaFotos = [];
            qs('import-preview-box')?.classList.add('hidden');
            qs('import-resultado')?.classList.add('hidden');
            qs('import-csv-nombre').textContent = 'Seleccionar CSV';
            qs('import-zip-nombre').textContent = 'Seleccionar ZIP (CSV + fotos)';
            qs('import-zip-ayuda')?.classList.add('hidden');
            qs('import-fotos-status').textContent = 'Opcional: sube fotos aquí';
            qs('import-paso1-actions')?.classList.add('hidden');
            qs('btn-ir-resumen')?.classList.add('hidden');
            setPaso(1);
        });

        qs('btn-vaciar-fotos-cola')?.addEventListener('click', async function () {
            if (!confirm('¿Quitar todas las fotos en cola? (no borra productos del catálogo)')) return;
            await fetch('dashboard.php?ajax=importacion_vaciar_fotos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'csrf_token=' + encodeURIComponent(csrf()),
                credentials: 'same-origin',
            });
            qs('import-fotos-status').textContent = 'Opcional: sube fotos aquí';
            await refrescarPreview();
        });

        fetch('dashboard.php?ajax=importacion_staging_stats', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.total_fotos > 0 && qs('import-fotos-status')) {
                    qs('import-fotos-status').textContent = d.total_fotos + ' foto(s) en cola (se enlazan al importar)';
                }
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
