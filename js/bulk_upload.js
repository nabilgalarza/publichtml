/**
 * Actualización masiva — solo CSV (modal en catálogo).
 */
(function () {
    function setupCsvConfirmUi() {
        const wrap = document.getElementById('bulk-confirm-ids-wrap');
        const cb = document.getElementById('bulk-confirm-ids-cb');
        const hidden = document.getElementById('bulk-confirm-ids');
        if (!wrap || !cb || !hidden) return;

        if (new URLSearchParams(window.location.search).get('msg') === 'bulk_csv_confirm') {
            wrap.classList.remove('hidden');
        }

        cb.addEventListener('change', function () {
            hidden.value = cb.checked ? '1' : '';
        });
    }

    function csvPareceTenerIds(texto) {
        const lineas = String(texto || '').split(/\r?\n/).filter(function (l) {
            return l.trim() !== '';
        });
        if (lineas.length < 2) return false;
        const delim = lineas[0].indexOf(';') >= 0 ? ';' : ',';
        const headers = lineas[0].split(delim).map(function (h) {
            return h.trim().toLowerCase().replace(/^\ufeff/, '');
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

    function initBulkUpload() {
        const form = document.getElementById('form-bulk-csv');
        if (!form || form.dataset.bound) return;
        form.dataset.bound = '1';

        form.addEventListener('submit', function (e) {
            if (form.dataset.bulkSkipGuard === '1') {
                return;
            }
            e.preventDefault();

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
                if (hidden) hidden.value = cb && cb.checked ? '1' : '';
                form.dataset.bulkSkipGuard = '1';
                HTMLFormElement.prototype.submit.call(form);
            }

            const reader = new FileReader();
            reader.onload = function () {
                if (csvPareceTenerIds(reader.result || '')) {
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
        const m = document.getElementById('modal-bulk');
        if (!m) return;
        m.classList.add('opacity-0');
        const c = document.getElementById('modal-bulk-content');
        if (c) c.classList.add('scale-95');
        setTimeout(function () {
            m.classList.add('hidden');
        }, 300);
    };

    function onReady() {
        initBulkUpload();
        if (new URLSearchParams(window.location.search).get('msg') === 'bulk_csv_confirm') {
            window.abrirModalBulk();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }
})();
