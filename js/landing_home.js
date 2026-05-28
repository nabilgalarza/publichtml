/**
 * Home: sucursal cercana + modal con todas las sucursales (paridad con productos.php).
 */
(function () {
    let listaLocales = [];

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s ?? '';
        return d.innerHTML;
    }

    function distKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2
            + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function cardCercanaHtml(loc, clickable) {
        const distText = loc.distancia != null
            ? `<span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full ml-auto shrink-0">A ${loc.distancia.toFixed(1)} km</span>`
            : '';
        const clickClass = clickable ? ' location-card--clickable location-card--featured' : ' location-card--featured';
        const clickAttr = clickable ? ' role="button" tabindex="0" onclick="abrirModalLocales()"' : '';
        const maps = loc.maps || '#';
        return `<div class="location-card${clickClass}"${clickAttr}>
            <div class="flex items-start justify-between gap-2 mb-3">
                <h4 class="text-[14px] font-black text-slate-800 leading-tight">${esc(loc.nombre)}</h4>
                ${distText}
            </div>
            <p class="text-[11px] text-slate-500 leading-relaxed mb-2">${esc(loc.direccion)}</p>
            ${loc.ciudad ? `<p class="text-[10px] font-bold text-slate-400 uppercase mb-3">${esc(loc.ciudad)}</p>` : ''}
            <div class="flex gap-2 flex-wrap">
                <a href="${esc(maps)}" target="_blank" rel="noopener" class="btn-location-action" onclick="event.stopPropagation()"><i class="fa-solid fa-location-dot"></i> Google Maps</a>
                ${loc.telefono ? `<span class="text-[11px] text-slate-500 font-medium flex items-center gap-1 px-2"><i class="fa-solid fa-phone text-[#1B263B]/40"></i>${esc(loc.telefono)}</span>` : ''}
            </div>
        </div>`;
    }

    function cardModalHtml(l) {
        const wa = String(l.whatsapp || '').replace(/\D/g, '');
        const waMsg = l.whatsapp_msj ? encodeURIComponent(l.whatsapp_msj) : '';
        const waLink = wa ? `https://wa.me/${wa}${waMsg ? '?text=' + waMsg : ''}` : '#';
        const horario = l.horario && String(l.horario).trim() !== '' ? l.horario : '08:30 - 18:00';
        const cobertura = Array.isArray(l.cobertura) && l.cobertura.length
            ? `<div class="flex items-start gap-2 text-[11px] text-slate-600 font-medium">
                <i class="fa-solid fa-truck text-[#1B263B]/40 w-4 mt-0.5"></i>
                <span>Domicilio: ${esc(l.cobertura.join(', '))}</span>
               </div>`
            : '';

        return `<div class="location-card group">
            <div class="flex items-center gap-2 mb-3 flex-wrap">
                <span class="location-dot"></span>
                <h4 class="text-[15px] font-black text-slate-900">${esc(l.nombre)}</h4>
                ${l.distancia != null ? `<span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full">A ${l.distancia.toFixed(1)} km</span>` : ''}
            </div>
            <p class="text-[12px] text-slate-500 mb-3 leading-relaxed">${esc(l.direccion)}</p>
            <div class="space-y-2 mb-5">
                <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium">
                    <i class="fa-solid fa-phone text-[#1B263B]/40 w-4"></i> ${esc(l.telefono || '')}
                </div>
                <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium">
                    <i class="fa-solid fa-envelope text-[#1B263B]/40 w-4"></i> ${esc(l.email || '')}
                </div>
                <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium">
                    <i class="fa-solid fa-clock text-[#1B263B]/40 w-4"></i> ${esc(horario)}
                </div>
                ${cobertura}
            </div>
            <div class="flex gap-2">
                <a href="${esc(l.maps || '#')}" target="_blank" rel="noopener" class="btn-location-action flex-grow hover:bg-[#1B263B] hover:text-white transition-all"><i class="fa-solid fa-location-dot"></i> Cómo llegar</a>
                <a href="${waLink}" target="_blank" rel="noopener" class="bg-emerald-500 text-white px-5 rounded-xl flex items-center justify-center gap-2 hover:bg-emerald-600 transition-colors shadow-lg shadow-emerald-500/20 text-[11px] font-bold">
                    <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp
                </a>
            </div>
        </div>`;
    }

    function renderizarLocales(userLat, userLng) {
        if (!listaLocales.length) return;

        let localesProcesados = listaLocales.map((l) => ({ ...l }));
        if (userLat != null && userLng != null) {
            localesProcesados.forEach((l) => {
                if (l.lat != null && l.lng != null) {
                    l.distancia = distKm(userLat, userLng, l.lat, l.lng);
                }
            });
            localesProcesados.sort((a, b) => (a.distancia ?? 9999) - (b.distancia ?? 9999));
        }

        const masCercano = localesProcesados[0];
        const widget = document.getElementById('home-nearest-location-widget');
        const modalNearest = document.getElementById('modal-nearest-location');
        const grid = document.getElementById('locations-grid');

        if (masCercano && widget) {
            widget.innerHTML = cardCercanaHtml(masCercano, true);
        }

        if (modalNearest && masCercano && masCercano.distancia != null) {
            modalNearest.innerHTML = `
                <div class="bg-emerald-50/50 border border-emerald-100 rounded-3xl p-5 mb-2">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="bg-emerald-500 text-white text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest">Cercano</span>
                        <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Tu mejor opción ahora mismo</p>
                    </div>
                    ${cardCercanaHtml(masCercano, false)}
                </div>`;
            modalNearest.classList.remove('hidden');
        } else if (modalNearest) {
            modalNearest.classList.add('hidden');
            modalNearest.innerHTML = '';
        }

        if (grid) {
            grid.innerHTML = localesProcesados.map(cardModalHtml).join('');
        }
    }

    window.abrirModalLocales = function () {
        const m = document.getElementById('locations-modal');
        if (!m) return;
        m.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    window.cerrarModalLocales = function (e) {
        if (e && e.target && !e.target.classList.contains('modal-overlay') && e.target.innerHTML !== '×' && e.target.innerHTML !== '&times;') {
            const content = e.target.closest('.product-modal-content');
            if (content) return;
        }
        const m = document.getElementById('locations-modal');
        if (!m) return;
        m.classList.remove('show');
        document.body.style.overflow = '';
    };

    async function initLocalesHome() {
        try {
            const res = await fetch('locales.json?v=' + Date.now());
            listaLocales = await res.json();
            if (!Array.isArray(listaLocales)) listaLocales = [];

            const onPos = (lat, lng) => renderizarLocales(lat, lng);
            const onFail = () => renderizarLocales(null, null);

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => onPos(pos.coords.latitude, pos.coords.longitude),
                    onFail,
                    { timeout: 10000, maximumAge: 600000 }
                );
            } else {
                onFail();
            }
        } catch (e) {
            const widget = document.getElementById('home-nearest-location-widget');
            if (widget) widget.innerHTML = '<p class="text-sm text-red-500">No se pudieron cargar las sucursales.</p>';
        }
    }

    function initAsesoriaForm() {
        const form = document.getElementById('form-asesoria-home');
        if (!form) return;
        const msg = document.getElementById('asesoria-home-msg');
        const submitBtn = document.getElementById('asesoria-submit-btn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('is-loading');
            }
            msg.classList.add('hidden');
            msg.classList.remove('text-success', 'text-error');

            const fd = new FormData(form);
            try {
                const res = await fetch('api_asesoria.php', { method: 'POST', body: fd });
                const data = await res.json();
                msg.classList.remove('hidden');
                if (data.status === 'success') {
                    msg.classList.add('text-success');
                    msg.textContent = data.message || 'Solicitud enviada. Te contactaremos pronto.';
                    form.reset();
                } else {
                    msg.classList.add('text-error');
                    msg.textContent = data.message || 'No se pudo enviar.';
                }
            } catch (err) {
                msg.classList.remove('hidden');
                msg.classList.add('text-error');
                msg.textContent = 'Error de conexión.';
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('is-loading');
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initLocalesHome();
        initAsesoriaForm();
    });
})();
