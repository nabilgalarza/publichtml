/**
 * Showroom — helpers compartidos (home, modal tienda/footer).
 */
(function () {
    const SHOWROOM_PHOTOS = {
        Guayaquil: 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=800&q=70&fit=crop',
        Quito: 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=70&fit=crop',
        Durán: 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=800&q=70&fit=crop',
        Manta: 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&q=70&fit=crop',
        Ambato: 'https://images.unsplash.com/photo-1590644365607-1c59aaebca93?w=800&q=70&fit=crop',
        Loja: 'https://images.unsplash.com/photo-1565008576549-57569a49371d?w=800&q=70&fit=crop',
        default: 'https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=800&q=70&fit=crop'
    };

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s ?? '';
        return d.innerHTML;
    }

    function horario(loc) {
        const h = loc.horario && String(loc.horario).trim();
        return h || 'Lun–Vie 08:30 – 18:00';
    }

    function waLink(loc) {
        const wa = String(loc.whatsapp || '').replace(/\D/g, '');
        const msg = loc.whatsapp_msj ? encodeURIComponent(loc.whatsapp_msj) : '';
        return wa ? `https://wa.me/${wa}${msg ? '?text=' + msg : ''}` : '#';
    }

    function photoUrl(loc) {
        const custom = String(loc.imagen || '').trim().replace(/^\.\//, '');
        if (custom) {
            if (/^https?:\/\//i.test(custom)) {
                return custom;
            }
            return custom.startsWith('/') ? custom : custom;
        }
        const city = (loc.ciudad || '').trim();
        return SHOWROOM_PHOTOS[city] || SHOWROOM_PHOTOS.default;
    }

    function heroHtml(loc, options) {
        const opts = options || {};
        const dist = loc.distancia != null ? loc.distancia.toFixed(1) : null;
        const distBadge = dist
            ? `<span class="locales-showroom-dist">${dist} km</span>`
            : '';
        const badgeLabel = opts.badgeLabel || (opts.isNearest ? 'Tu sucursal' : esc(loc.ciudad || 'Sucursal'));
        const openAttr = opts.openModal
            ? ' role="button" tabindex="0" data-locales-open-modal="1"'
            : '';

        return `<div class="locales-showroom-hero"${openAttr}>
            <div class="locales-showroom-photo">
                <img src="${photoUrl(loc)}" alt="" loading="lazy" width="800" height="450">
                <span class="locales-showroom-badge"><i class="fa-solid fa-store" aria-hidden="true"></i> ${badgeLabel}</span>
            </div>
            <div class="locales-showroom-body">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="locales-showroom-name">${esc(loc.nombre)}</h3>
                    ${distBadge}
                </div>
                <p class="locales-showroom-addr">${esc(loc.direccion)}${loc.ciudad ? ' · ' + esc(loc.ciudad) : ''}</p>
                <div class="locales-showroom-hours"><i class="fa-solid fa-clock" aria-hidden="true"></i> ${esc(horario(loc))}</div>
                <div class="locales-showroom-actions">
                    <a href="${esc(loc.maps || '#')}" target="_blank" rel="noopener" class="locales-showroom-btn-primary" onclick="event.stopPropagation()"><i class="fa-solid fa-location-dot"></i> Cómo llegar</a>
                    <a href="${esc(waLink(loc))}" target="_blank" rel="noopener" class="locales-showroom-btn-wa" onclick="event.stopPropagation()" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>
        </div>`;
    }

    function stripHtml(locales, heroId, focusId) {
        const others = locales.filter((l) => l.id !== heroId).slice(0, 6);
        if (!others.length) return '';

        return others.map((l) => {
            const d = l.distancia != null ? `${l.distancia.toFixed(1)} km` : '';
            const active = focusId && l.id === focusId ? ' is-active' : '';
            return `<button type="button" class="locales-showroom-thumb${active}" data-locales-focus="${esc(l.id)}">
                <div class="locales-showroom-thumb-photo" style="background-image:url('${photoUrl(l)}')"></div>
                <div class="locales-showroom-thumb-body">
                    <p>${esc(l.nombre)}</p>
                    <small>${esc(l.ciudad || '')}${d ? ' · ' + d : ''}</small>
                </div>
            </button>`;
        }).join('');
    }

    function modalCardHtml(loc, isNearest) {
        const d = loc.distancia != null ? `${loc.distancia.toFixed(1)} km` : '';
        const nearBadge = isNearest
            ? '<span class="locales-showroom-badge"><i class="fa-solid fa-location-crosshairs"></i> Más cercana</span>'
            : '';
        return `<div class="locales-showroom-modal-card">
            <div class="locales-showroom-modal-card-photo">
                <img src="${photoUrl(loc)}" alt="" loading="lazy">
                ${nearBadge}
            </div>
            <div class="locales-showroom-modal-card-body">
                <h4>${esc(loc.nombre)}</h4>
                <p class="text-[10px] text-slate-500 m-0">${esc(loc.ciudad || '')}${d ? ' · ' + d : ''}</p>
                <p class="text-[10px] text-slate-600 mt-1 mb-0"><i class="fa-solid fa-clock text-[#0E75AE]"></i> ${esc(horario(loc))}</p>
                <div class="locales-showroom-modal-card-actions">
                    <a href="${esc(loc.maps || '#')}" target="_blank" rel="noopener" class="locales-showroom-btn-primary"><i class="fa-solid fa-location-dot"></i> Maps</a>
                    <a href="${esc(waLink(loc))}" target="_blank" rel="noopener" class="locales-showroom-btn-wa" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>
        </div>`;
    }

    function modalGridHtml(locales) {
        const nearestId = locales[0]?.id;
        return locales.map((l, i) => modalCardHtml(l, i === 0 && l.distancia != null && l.id === nearestId)).join('');
    }

    /** Tarjeta compacta — home móvil (<768px) */
    function compactCardHtml(loc, clickable) {
        const distText = loc.distancia != null
            ? `<span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full ml-auto shrink-0">A ${loc.distancia.toFixed(1)} km</span>`
            : '';
        const clickClass = clickable ? ' location-card--clickable location-card--featured' : ' location-card--featured';
        const clickAttr = clickable ? ' role="button" tabindex="0" data-locales-open-modal="1"' : '';
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

    window.ImprogypLocalesShowroom = {
        DESKTOP_MIN_WIDTH: 768,
        esc,
        horario,
        waLink,
        photoUrl,
        heroHtml,
        stripHtml,
        compactCardHtml,
        modalCardHtml,
        modalGridHtml
    };
})();
