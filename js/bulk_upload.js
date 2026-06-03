/**
 * Actualización masiva — fotos por lotes (Ajax) + CSV sin multipart masivo.
 */
(function () {
    const LOTE = 12;
    let colaFotos = [];
    let subiendo = false;
    let totalStaging = 0;
    let allWarnings = [];

    function csrfToken() {
        const el = document.querySelector('#form-bulk-csv input[name="csrf_token"]');
        return el ? el.value : '';
    }

    function txtFotos() {
        return document.getElementById('txt-fotos');
    }

    function barraProgreso() {
        return document.getElementById('bulk-fotos-progress');
    }

    function warningsList() {
        return document.getElementById('bulk-fotos-warnings');
    }

    function btnResetStaging() {
        return document.getElementById('btn-bulk-staging-reset');
    }

    function stagingCountFromResponse(data) {
        if (typeof data.archivos_staging === 'number') return data.archivos_staging;
        if (typeof data.total_staging === 'number') return data.total_staging;
        return totalStaging;
    }

    function renderWarnings() {
        const ul = warningsList();
        if (!ul) return;
        if (allWarnings.length === 0) {
            ul.classList.add('hidden');
            ul.innerHTML = '';
            return;
        }
        ul.classList.remove('hidden');
        ul.innerHTML = allWarnings.slice(-8).map(function (w) {
            return '<li>' + escapeHtml(w) + '</li>';
        }).join('');
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function toggleResetButton() {
        const btn = btnResetStaging();
        if (btn) btn.classList.toggle('hidden', totalStaging <= 0);
    }

    function actualizarUiFotos(pendientes, subiendoAhora) {
        const txt = txtFotos();
        if (!txt) return;
        if (subiendoAhora || pendientes > 0) {
            const enCola = pendientes + (subiendoAhora ? Math.min(LOTE, pendientes + 1) : 0);
            txt.textContent = 'Subiendo… ' + totalStaging + ' guardadas · ' + enCola + ' en cola';
        } else if (totalStaging > 0) {
            txt.textContent = totalStaging + ' foto' + (totalStaging === 1 ? '' : 's') + ' listas (enlazan por código SKU)';
            txt.classList.remove('text-slate-400');
            txt.classList.add('text-[#1B263B]');
        } else {
            txt.textContent = 'Nombre de archivo = código SKU (ej. 20LAS04.webp). Máx. ' + LOTE + ' por tanda.';
            txt.classList.remove('text-[#1B263B]');
            txt.classList.add('text-slate-500');
        }
        const bar = barraProgreso();
        if (bar) {
            const busy = subiendoAhora || pendientes > 0;
            bar.classList.toggle('hidden', !busy);
        }
        toggleResetButton();
    }

    async function fetchStagingCount() {
        try {
            const res = await fetch('dashboard.php?ajax=bulk_staging_count', { credentials: 'same-origin' });
            const data = await res.json();
            if (data.status === 'success') {
                totalStaging = stagingCountFromResponse(data);
                actualizarUiFotos(0, false);
            }
        } catch (e) { /* ignore */ }
    }

    async function resetStaging(confirmar) {
        if (confirmar && totalStaging > 0 && !window.confirm('¿Vaciar las ' + totalStaging + ' fotos en cola de esta sesión?')) {
            return;
        }
        try {
            await fetch('dashboard.php?ajax=bulk_staging_reset', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
        } catch (e) { /* ignore */ }
        colaFotos = [];
        totalStaging = 0;
        allWarnings = [];
        renderWarnings();
        actualizarUiFotos(0, false);
    }

    function mergeWarnings(warnings) {
        if (!warnings || !warnings.length) return;
        warnings.forEach(function (w) {
            if (allWarnings.indexOf(w) === -1) allWarnings.push(w);
        });
        renderWarnings();
    }

    async function subirLote(archivos) {
        const fd = new FormData();
        fd.append('csrf_token', csrfToken());
        for (let i = 0; i < archivos.length; i++) {
            fd.append('fotos[]', archivos[i]);
        }
        const res = await fetch('dashboard.php?ajax=bulk_imagen_lote', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
        });
        const raw = await res.text();
        let data;
        try {
            data = JSON.parse(raw);
        } catch (e) {
            throw new Error('Respuesta inválida del servidor. ¿Sesión caducada? Recarga el panel.');
        }
        if (data.error) {
            throw new Error(data.error);
        }
        if (data.status === 'success' && data.ok === 0 && archivos.length > 0) {
            throw new Error('El lote no guardó ninguna imagen. Revisa formato o tamaño máximo en PHP.');
        }
        return data;
    }

    async function procesarCola() {
        if (subiendo) return;
        subiendo = true;
        const btn = document.getElementById('btn-bulk-sync');
        if (btn) btn.disabled = true;

        while (colaFotos.length > 0) {
            const lote = colaFotos.splice(0, LOTE);
            actualizarUiFotos(colaFotos.length, true);
            try {
                const data = await subirLote(lote);
                totalStaging = stagingCountFromResponse(data);
                mergeWarnings(data.warnings);
                actualizarUiFotos(colaFotos.length, true);
            } catch (err) {
                alert(err.message || 'Error al subir fotos');
                colaFotos = lote.concat(colaFotos);
                break;
            }
        }

        subiendo = false;
        if (btn) btn.disabled = false;
        actualizarUiFotos(0, false);
    }

    function encolarArchivos(fileList) {
        if (!fileList || !fileList.length) return;
        for (let i = 0; i < fileList.length; i++) {
            colaFotos.push(fileList[i]);
        }
        procesarCola();
    }

    function csvPareceTenerIds(text) {
        const lineas = text.split(/\r?\n/).filter(function (l) { return l.trim() !== ''; });
        if (lineas.length < 2) return false;
        const delim = lineas[0].indexOf(';') >= 0 ? ';' : ',';
        const headers = lineas[0].toLowerCase().split(delim).map(function (h) {
            return h.trim().replace(/^"|"$/g, '');
        });
        const idIdx = headers.indexOf('id');
        if (idIdx < 0) return false;
        let conId = 0;
        for (let i = 1; i < lineas.length && i < 200; i++) {
            const cols = lineas[i].split(delim);
            const idVal = parseInt(String(cols[idIdx] || '').replace(/[^\d]/g, ''), 10);
            if (idVal > 0) conId++;
        }
        return conId >= 5;
    }

    function setupCsvConfirmUi() {
        const wrap = document.getElementById('bulk-confirm-ids-wrap');
        const cb = document.getElementById('bulk-confirm-ids-cb');
        const hidden = document.getElementById('bulk-confirm-ids');
        if (!wrap || !cb || !hidden) return;

        const needsConfirm = new URLSearchParams(window.location.search).get('msg') === 'bulk_csv_confirm';
        if (needsConfirm) {
            wrap.classList.remove('hidden');
        }

        cb.addEventListener('change', function () {
            hidden.value = cb.checked ? '1' : '';
        });
    }

    function initBulkUpload() {
        const picker = document.getElementById('bulk-fotos-picker');
        if (picker && !picker.dataset.bound) {
            picker.dataset.bound = '1';
            picker.addEventListener('change', function () {
                encolarArchivos(picker.files);
                picker.value = '';
            });
        }

        const btnReset = btnResetStaging();
        if (btnReset && !btnReset.dataset.bound) {
            btnReset.dataset.bound = '1';
            btnReset.addEventListener('click', function () {
                resetStaging(true);
            });
        }

        const form = document.getElementById('form-bulk-csv');
        if (form && !form.dataset.bound) {
            form.dataset.bound = '1';
            form.addEventListener('submit', function (e) {
                if (form.dataset.bulkSkipGuard === '1') {
                    return;
                }
                e.preventDefault();
                if (subiendo) {
                    alert('Espera a que terminen de subirse las fotos.');
                    return;
                }
                const csv = form.querySelector('input[name="archivo_csv"]');
                if (!csv || !csv.files || !csv.files.length) {
                    alert('Selecciona el archivo CSV.');
                    return;
                }

                const hidden = document.getElementById('bulk-confirm-ids');
                const wrap = document.getElementById('bulk-confirm-ids-wrap');
                const cb = document.getElementById('bulk-confirm-ids-cb');
                const file = csv.files[0];

                function enviarFormulario() {
                    if (hidden) hidden.value = (cb && cb.checked) ? '1' : '';
                    form.dataset.bulkSkipGuard = '1';
                    HTMLFormElement.prototype.submit.call(form);
                }

                const reader = new FileReader();
                reader.onload = function () {
                    const tieneIds = csvPareceTenerIds(reader.result || '');
                    if (tieneIds) {
                        if (wrap) wrap.classList.remove('hidden');
                        if (!cb || !cb.checked) {
                            alert('Este CSV actualiza productos por ID. Marca la casilla de confirmación o quita la columna ID para altas nuevas por código.');
                            return;
                        }
                    }
                    enviarFormulario();
                };
                reader.onerror = function () {
                    enviarFormulario();
                };
                reader.readAsText(file.slice(0, 65536));
            });
        }

        setupCsvConfirmUi();
    }

    window.actualizarNombreCSV = function (input) {
        const txt = document.getElementById('txt-csv');
        if (txt && input.files && input.files.length > 0) {
            txt.textContent = input.files[0].name;
            txt.classList.remove('text-slate-400');
            txt.classList.add('text-[#1B263B]');
        }
    };

    window.abrirModalBulk = function () {
        const m = document.getElementById('modal-bulk');
        if (!m) return;
        initBulkUpload();
        fetchStagingCount();
        m.classList.remove('hidden');
        setTimeout(function () {
            m.classList.remove('opacity-0');
            const c = document.getElementById('modal-bulk-content');
            if (c) c.classList.remove('scale-95');
        }, 10);
        if (new URLSearchParams(window.location.search).get('msg') === 'bulk_csv_confirm') {
            const wrap = document.getElementById('bulk-confirm-ids-wrap');
            if (wrap) wrap.classList.remove('hidden');
        }
    };

    window.cerrarModalBulk = function () {
        if (totalStaging > 0 && !subiendo) {
            if (!window.confirm('Hay ' + totalStaging + ' foto(s) en cola. Si cierras sin sincronizar el CSV en esta pestaña, no se enlazarán. ¿Cerrar igual?')) {
                return;
            }
        }
        const m = document.getElementById('modal-bulk');
        if (!m) return;
        m.classList.add('opacity-0');
        const c = document.getElementById('modal-bulk-content');
        if (c) c.classList.add('scale-95');
        setTimeout(function () { m.classList.add('hidden'); }, 300);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBulkUpload);
    } else {
        initBulkUpload();
    }
})();
