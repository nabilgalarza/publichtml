/**
 * Demo visual — Red de sucursales (3 direcciones creativas).
 * Solo sucursales_home_demo.php — no modifica index.php
 */
(function () {
    const DEMO_LAT = -2.15;
    const DEMO_LNG = -79.9;
    let locales = [];
    let logisticaFilter = 'todas';
    let logisticaSearch = '';
    let showroomFocusId = null;

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

    function distKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2
            + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function waLink(loc) {
        const wa = String(loc.whatsapp || '').replace(/\D/g, '');
        const msg = loc.whatsapp_msj ? encodeURIComponent(loc.whatsapp_msj) : '';
        return wa ? `https://wa.me/${wa}${msg ? '?text=' + msg : ''}` : '#';
    }

    function horario(loc) {
        const h = loc.horario && String(loc.horario).trim();
        return h || 'Lun–Vie 08:30 – 18:00';
    }

    function showroomPhotoUrl(loc) {
        const city = (loc.ciudad || '').trim();
        return SHOWROOM_PHOTOS[city] || SHOWROOM_PHOTOS.default;
    }

    function processLocales(list) {
        return list.map((l) => {
            const copy = { ...l };
            if (copy.lat != null && copy.lng != null) {
                copy.distancia = distKm(DEMO_LAT, DEMO_LNG, copy.lat, copy.lng);
            }
            return copy;
        }).sort((a, b) => (a.distancia ?? 9999) - (b.distancia ?? 9999));
    }

    function uniqueCities() {
        const set = new Set(locales.map((l) => (l.ciudad || '').trim()).filter(Boolean));
        return Array.from(set).sort();
    }

    function citiesList() {
        return ['todas', ...uniqueCities()];
    }

    function filteredLocales() {
        let list = locales;
        if (logisticaFilter !== 'todas') {
            list = list.filter((l) => (l.ciudad || '').toLowerCase() === logisticaFilter.toLowerCase());
        }
        const q = logisticaSearch.trim().toLowerCase();
        if (q) {
            list = list.filter((l) => {
                const blob = [l.nombre, l.ciudad, l.direccion, l.telefono].join(' ').toLowerCase();
                return blob.includes(q);
            });
        }
        return list;
    }

    function getShowroomHero() {
        if (showroomFocusId) {
            return locales.find((l) => l.id === showroomFocusId) || locales[0];
        }
        return locales[0];
    }

    function mapSvgHtml(highlightId) {
        const withCoords = locales.filter((l) => l.lat != null && l.lng != null);
        if (!withCoords.length) return '';

        const lats = withCoords.map((l) => l.lat);
        const lngs = withCoords.map((l) => l.lng);
        const minLat = Math.min(...lats);
        const maxLat = Math.max(...lats);
        const minLng = Math.min(...lngs);
        const maxLng = Math.max(...lngs);
        const latSpan = (maxLat - minLat) || 1;
        const lngSpan = (maxLng - minLng) || 1;

        const project = (lat, lng) => {
            const x = 40 + ((lng - minLng) / lngSpan) * 320;
            const y = 170 - ((lat - minLat) / latSpan) * 130;
            return { x, y };
        };

        const pins = withCoords.map((l) => {
            const { x, y } = project(l.lat, l.lng);
            const isNear = l.id === (highlightId || locales[0]?.id);
            const cls = isNear ? 'suc-map-pin suc-map-pin--nearest' : 'suc-map-pin';
            const fill = isNear ? '#3A86FF' : '#94a3b8';
            const r = isNear ? 7 : 5;
            return `<circle class="${cls}" cx="${x.toFixed(1)}" cy="${y.toFixed(1)}" r="${r}" fill="${fill}" data-pin-id="${esc(l.id)}" tabindex="0" role="button" aria-label="${esc(l.nombre)}"/>`;
        }).join('');

        return `<svg class="suc-concierge-map" viewBox="0 0 400 200" xmlns="http://www.w3.org/2000/svg" aria-label="Mapa de sucursales Ecuador">
            <defs>
                <linearGradient id="sucMapBg" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#e8eef7"/>
                    <stop offset="100%" stop-color="#dbeafe"/>
                </linearGradient>
            </defs>
            <rect fill="url(#sucMapBg)" width="400" height="200"/>
            <path fill="#cbd5e1" opacity="0.85" d="M60 50 Q140 30 220 55 T340 75 Q360 120 300 155 T180 175 Q100 165 60 130 T60 50"/>
            <text x="200" y="24" text-anchor="middle" fill="#64748b" font-size="9" font-weight="700">ECUADOR · DEMO</text>
            ${pins}
        </svg>`;
    }

    function asesoriaPanelHtml() {
        return `<div class="asesoria-premium-panel">
            <div class="asesoria-premium-panel__inner">
                <div class="asesoria-premium-panel__head">
                    <span class="asesoria-premium-badge" aria-hidden="true"><i class="fa-solid fa-headset"></i></span>
                    <div>
                        <h3 class="asesoria-premium-title">Asesoría técnica</h3>
                        <p class="asesoria-premium-lead">Cuéntanos tu proyecto y un especialista te contacta.</p>
                    </div>
                </div>
                <p class="asesoria-premium-trust">Respuesta en horario laboral · Sin compromiso de compra</p>
                <form class="asesoria-premium-form space-y-4" onsubmit="return false;">
                    <div>
                        <label class="asesoria-premium-label">Nombre</label>
                        <input type="text" class="asesoria-premium-input" placeholder="Tu nombre" disabled>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="asesoria-premium-label">Teléfono</label>
                            <input type="tel" class="asesoria-premium-input" placeholder="099…" disabled>
                        </div>
                        <div>
                            <label class="asesoria-premium-label">Email</label>
                            <input type="email" class="asesoria-premium-input" disabled>
                        </div>
                    </div>
                    <div>
                        <label class="asesoria-premium-label">Mensaje</label>
                        <textarea rows="3" class="asesoria-premium-input asesoria-premium-textarea" disabled></textarea>
                    </div>
                    <button type="button" class="asesoria-premium-submit" disabled>
                        <span class="asesoria-premium-submit__label">Enviar solicitud (demo)</span>
                    </button>
                </form>
            </div>
        </div>`;
    }

    function sectionShell(variantClass, leftHtml) {
        return `<section class="locales-premium-section suc-${variantClass}">
            <div class="max-w-[1200px] mx-auto px-6">
                <div class="locales-premium-shell">
                    <div class="locales-premium-grid">
                        <div class="locales-premium-locations suc-v-${variantClass.replace('v-', '')}">
                            <div class="locales-premium-heading mb-6">
                                <p class="suc-demo-eyebrow">Cerca de ti</p>
                                <h2 class="suc-demo-title">Red de <span class="laser-text">sucursales</span></h2>
                                <p class="suc-demo-sub">Atención técnica en todo Ecuador · ubicación demo Guayaquil.</p>
                            </div>
                            ${leftHtml}
                        </div>
                        ${asesoriaPanelHtml()}
                    </div>
                </div>
            </div>
        </section>`;
    }

    function renderConcierge(nearest) {
        const dist = nearest.distancia != null ? nearest.distancia.toFixed(1) : '—';
        const cities = uniqueCities().length;
        const left = `
            <div class="suc-concierge-stats">
                <div class="suc-concierge-stat"><strong>${locales.length}</strong><span>Puntos</span></div>
                <div class="suc-concierge-stat"><strong>${cities}</strong><span>Ciudades</span></div>
                <div class="suc-concierge-stat"><strong>${dist} km</strong><span>Más cercana</span></div>
            </div>
            <div class="suc-concierge-map-wrap">${mapSvgHtml(nearest.id)}</div>
            <div class="suc-concierge-card" role="button" tabindex="0" data-open-modal="concierge">
                <div class="suc-concierge-card-head">
                    <div>
                        <span class="suc-demo-chip suc-demo-chip--near"><i class="fa-solid fa-location-crosshairs"></i> Más cercana</span>
                        <h3 class="suc-concierge-name">${esc(nearest.nombre)}</h3>
                    </div>
                    <span class="suc-demo-chip suc-demo-chip--dist">${dist} km</span>
                </div>
                <p class="suc-concierge-addr">${esc(nearest.direccion)}${nearest.ciudad ? ' · ' + esc(nearest.ciudad) : ''}</p>
                <div class="suc-concierge-actions">
                    <a href="${esc(nearest.maps || '#')}" target="_blank" rel="noopener" class="suc-demo-btn-primary" onclick="event.stopPropagation()"><i class="fa-solid fa-location-dot"></i> Cómo llegar</a>
                    <a href="${esc(waLink(nearest))}" target="_blank" rel="noopener" class="suc-demo-btn-wa" onclick="event.stopPropagation()" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>
            <button type="button" class="suc-demo-btn-primary suc-concierge-cta-all" data-open-modal="concierge">
                <i class="fa-solid fa-map-location-dot"></i> Ver red completa · ${locales.length} puntos
            </button>`;
        document.getElementById('panel-concierge').innerHTML = sectionShell('v-concierge', left);
    }

    function logFilterButtons(attrPrefix) {
        return citiesList().map((c) => {
            const label = c === 'todas' ? 'Todas' : c;
            const active = c === logisticaFilter ? ' is-active' : '';
            const attr = attrPrefix === 'modal' ? 'data-modal-filter' : 'data-log-filter';
            return `<button type="button" class="suc-log-filter${active}" ${attr}="${esc(c)}">${esc(label)}</button>`;
        }).join('');
    }

    function logRowsHtml(list, limit) {
        const slice = limit ? list.slice(0, limit) : list;
        if (!slice.length) {
            return '<div class="suc-log-empty">No hay sucursales con ese filtro.</div>';
        }
        return slice.map((l) => {
            const d = l.distancia != null ? `${l.distancia.toFixed(1)} km` : '—';
            return `<div class="suc-log-row" data-open-modal="logistica">
                <div>
                    <p class="suc-log-row-name">${esc(l.nombre)}</p>
                    <p class="suc-log-row-meta">${esc(l.ciudad || '')} · ${d}</p>
                </div>
                <div class="suc-log-row-actions">
                    <a href="${esc(l.maps || '#')}" target="_blank" rel="noopener" class="suc-demo-btn-secondary" onclick="event.stopPropagation()"><i class="fa-solid fa-location-dot"></i></a>
                    <a href="${esc(waLink(l))}" target="_blank" rel="noopener" class="suc-demo-btn-wa" onclick="event.stopPropagation()" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>`;
        }).join('');
    }

    function renderLogistica(nearest) {
        const filtered = filteredLocales();
        const dist = nearest.distancia != null ? nearest.distancia.toFixed(1) : '—';
        const left = `
            <div class="suc-log-filters" id="suc-log-filters-panel">${logFilterButtons('panel')}</div>
            <div class="suc-log-nearest" data-open-modal="logistica">
                <span class="suc-log-rank">1</span>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-black text-[#3A86FF] uppercase tracking-wider mb-0.5">Recomendada · ${dist} km</p>
                    <p class="text-[13px] font-black text-slate-900 m-0">${esc(nearest.nombre)}</p>
                    <p class="text-[10px] text-slate-500 m-0 mt-0.5">${esc(nearest.direccion)}</p>
                </div>
                <i class="fa-solid fa-chevron-right text-slate-300"></i>
            </div>
            <div class="suc-log-list suc-log-list--scroll">${logRowsHtml(filtered, null)}</div>
            <button type="button" class="suc-demo-btn-primary suc-log-cta-all" data-open-modal="logistica">
                <i class="fa-solid fa-list"></i> Ver listado completo (${locales.length})
            </button>`;
        document.getElementById('panel-logistica').innerHTML = sectionShell('v-logistica', left);
    }

    function renderShowroom() {
        const hero = getShowroomHero();
        const dist = hero.distancia != null ? hero.distancia.toFixed(1) : '—';
        const others = locales.filter((l) => l.id !== hero.id).slice(0, 4);
        const thumbs = others.map((l) => {
            const d = l.distancia != null ? `${l.distancia.toFixed(1)} km` : '';
            return `<button type="button" class="suc-show-thumb" data-showroom-id="${esc(l.id)}">
                <div class="suc-show-thumb-photo" style="background-image:url('${showroomPhotoUrl(l)}')"></div>
                <div class="suc-show-thumb-body">
                    <p>${esc(l.nombre)}</p>
                    <small>${esc(l.ciudad || '')} · ${d}</small>
                </div>
            </button>`;
        }).join('');

        const left = `
            <div class="suc-show-hero" data-open-modal="showroom">
                <div class="suc-show-photo">
                    <img src="${showroomPhotoUrl(hero)}" alt="" loading="lazy">
                    <span class="suc-demo-chip suc-demo-chip--near suc-show-photo-badge"><i class="fa-solid fa-store"></i> ${hero.id === locales[0]?.id ? 'Tu sucursal' : esc(hero.ciudad || 'Sucursal')}</span>
                </div>
                <div class="suc-show-body">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-[15px] font-black text-slate-900 m-0">${esc(hero.nombre)}</h3>
                        <span class="suc-demo-chip suc-demo-chip--dist">${dist} km</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2 mb-0 leading-relaxed">${esc(hero.direccion)}</p>
                    <div class="suc-show-hours"><i class="fa-solid fa-clock"></i> ${esc(horario(hero))}</div>
                    <div class="suc-show-actions">
                        <a href="${esc(hero.maps || '#')}" target="_blank" rel="noopener" class="suc-demo-btn-primary flex-1" onclick="event.stopPropagation()"><i class="fa-solid fa-location-dot"></i> Cómo llegar</a>
                        <a href="${esc(waLink(hero))}" target="_blank" rel="noopener" class="suc-demo-btn-wa" onclick="event.stopPropagation()"><i class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            ${thumbs ? `<div class="suc-show-strip" aria-label="Otras sucursales">${thumbs}</div>` : ''}
            <button type="button" class="suc-demo-btn-primary suc-show-cta-all" data-open-modal="showroom">
                <i class="fa-solid fa-images"></i> Ver todas las sucursales
            </button>`;
        document.getElementById('panel-showroom').innerHTML = sectionShell('v-showroom', left);
    }

    function cardActualHtml(loc, clickable) {
        const distText = loc.distancia != null
            ? `<span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full ml-auto shrink-0">A ${loc.distancia.toFixed(1)} km</span>`
            : '';
        const clickClass = clickable ? ' location-card--clickable location-card--featured' : ' location-card--featured';
        const clickAttr = clickable ? ' data-open-modal="actual" role="button" tabindex="0"' : '';
        return `<div class="location-card${clickClass}"${clickAttr}>
            <div class="flex items-start justify-between gap-2 mb-3">
                <h4 class="text-[14px] font-black text-slate-800 leading-tight">${esc(loc.nombre)}</h4>
                ${distText}
            </div>
            <p class="text-[11px] text-slate-500 leading-relaxed mb-2">${esc(loc.direccion)}</p>
            ${loc.ciudad ? `<p class="text-[10px] font-bold text-slate-400 uppercase mb-3">${esc(loc.ciudad)}</p>` : ''}
            <div class="flex gap-2 flex-wrap">
                <a href="${esc(loc.maps || '#')}" target="_blank" rel="noopener" class="btn-location-action" onclick="event.stopPropagation()"><i class="fa-solid fa-location-dot"></i> Google Maps</a>
                ${loc.telefono ? `<span class="text-[11px] text-slate-500 font-medium flex items-center gap-1 px-2"><i class="fa-solid fa-phone text-[#1B263B]/40"></i>${esc(loc.telefono)}</span>` : ''}
            </div>
        </div>`;
    }

    function renderActual(nearest) {
        const left = `
            <div class="locales-premium-widget mb-5">${cardActualHtml(nearest, true)}</div>
            <button type="button" class="locales-premium-ghost-btn w-full" data-open-modal="actual">
                <i class="fa-solid fa-map-location-dot"></i>
                <span>Ver todas las sucursales</span>
            </button>`;
        document.getElementById('panel-actual').innerHTML = sectionShell('v-actual', left);
    }

    function modalConciergeHtml() {
        const nearest = locales[0];
        const rows = locales.map((l) => {
            const d = l.distancia != null ? `${l.distancia.toFixed(1)} km` : '';
            return `<div class="suc-modal-list-row">
                <div>
                    <strong class="text-[13px] text-slate-900">${esc(l.nombre)}</strong>
                    <p class="text-[11px] text-slate-500 m-0">${esc(l.ciudad || '')} · ${d}</p>
                </div>
                <div class="flex gap-2">
                    <a href="${esc(l.maps || '#')}" target="_blank" rel="noopener" class="suc-demo-btn-secondary"><i class="fa-solid fa-location-dot"></i></a>
                    <a href="${esc(waLink(l))}" target="_blank" rel="noopener" class="suc-demo-btn-wa"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>`;
        }).join('');

        return `<div class="suc-modal-concierge">
            <h2 class="suc-demo-modal-title" id="suc-demo-modal-title">Nuestras sucursales</h2>
            <p class="suc-demo-modal-sub">Encuentra el punto IMPROGYP más cercano a tu obra.</p>
            <div class="suc-modal-split">
                <div class="suc-modal-map-mini">${mapSvgHtml(nearest.id)}</div>
                <div>
                    <div class="suc-modal-hero">
                        <span class="suc-demo-chip suc-demo-chip--near" style="margin-bottom:8px"><i class="fa-solid fa-location-crosshairs"></i> Más cercana</span>
                        <h3>${esc(nearest.nombre)} · ${nearest.distancia != null ? nearest.distancia.toFixed(1) + ' km' : ''}</h3>
                        <p>${esc(nearest.direccion)}</p>
                        <div class="suc-modal-hero-actions">
                            <a href="${esc(nearest.maps || '#')}" target="_blank" rel="noopener" class="suc-demo-btn-primary"><i class="fa-solid fa-location-dot"></i> Cómo llegar</a>
                            <a href="${esc(waLink(nearest))}" target="_blank" rel="noopener" class="suc-demo-btn-secondary" style="color:#fff;border-color:rgba(255,255,255,.3);background:transparent"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 mt-4">Todas las sucursales</p>
            ${rows}
        </div>`;
    }

    function modalLogisticaHtml() {
        const filtered = filteredLocales();
        const rows = filtered.map((l) => {
            const d = l.distancia != null ? l.distancia.toFixed(1) : '—';
            return `<tr>
                <td><strong>${esc(l.nombre)}</strong><br><span class="text-slate-500">${esc(l.ciudad || '')}</span></td>
                <td>${d} km</td>
                <td class="text-slate-600">${esc(l.telefono || '')}</td>
                <td>
                    <div class="flex gap-1 justify-end">
                        <a href="${esc(l.maps || '#')}" target="_blank" rel="noopener" class="suc-demo-btn-secondary" style="padding:6px 10px"><i class="fa-solid fa-location-dot"></i></a>
                        <a href="${esc(waLink(l))}" target="_blank" rel="noopener" class="suc-demo-btn-wa" style="width:34px;height:34px;font-size:14px"><i class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </td>
            </tr>`;
        }).join('');

        return `<div class="suc-modal-logistica">
            <h2 class="suc-demo-modal-title" id="suc-demo-modal-title">Red de suministros</h2>
            <p class="suc-demo-modal-sub">Listado ordenado por distancia (demo Guayaquil).</p>
            <div class="suc-modal-toolbar">
                <input type="search" class="suc-modal-search" id="suc-modal-search" placeholder="Buscar sucursal o ciudad…" value="${esc(logisticaSearch)}">
                <div class="suc-log-filters" style="margin:0" id="suc-modal-filters">${logFilterButtons('modal')}</div>
            </div>
            <div style="overflow-x:auto">
                <table>
                    <thead><tr><th>Sucursal</th><th>Dist.</th><th>Teléfono</th><th></th></tr></thead>
                    <tbody>${rows || '<tr><td colspan="4" class="text-center text-slate-400 py-6">Sin resultados</td></tr>'}</tbody>
                </table>
            </div>
        </div>`;
    }

    function modalShowroomHtml() {
        const cards = locales.map((l) => {
            const d = l.distancia != null ? `${l.distancia.toFixed(1)} km` : '';
            const img = showroomPhotoUrl(l);
            return `<div class="suc-modal-card">
                <div class="suc-modal-card-photo">
                    <img src="${img}" alt="" loading="lazy">
                </div>
                <div class="suc-modal-card-body">
                    <h4>${esc(l.nombre)}</h4>
                    <p class="text-[10px] text-slate-500 m-0">${esc(l.ciudad || '')} · ${d}</p>
                    <p class="text-[10px] text-slate-600 mt-1 mb-0"><i class="fa-solid fa-clock text-[#3A86FF]"></i> ${esc(horario(l))}</p>
                    <div class="suc-modal-card-actions">
                        <a href="${esc(l.maps || '#')}" target="_blank" rel="noopener" class="suc-demo-btn-primary" style="flex:1;padding:8px"><i class="fa-solid fa-location-dot"></i> Maps</a>
                        <a href="${esc(waLink(l))}" target="_blank" rel="noopener" class="suc-demo-btn-wa"><i class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>`;
        }).join('');

        return `<div class="suc-modal-showroom">
            <h2 class="suc-demo-modal-title" id="suc-demo-modal-title">Nuestras sucursales</h2>
            <p class="suc-demo-modal-sub">Puntos de venta y asesoría técnica.</p>
            <div class="suc-modal-grid">${cards}</div>
        </div>`;
    }

    function modalActualHtml() {
        const nearest = locales[0];
        const cards = locales.map((l) => {
            const wa = waLink(l);
            const cobertura = Array.isArray(l.cobertura) && l.cobertura.length
                ? `<div class="flex items-start gap-2 text-[11px] text-slate-600 font-medium"><i class="fa-solid fa-truck text-[#1B263B]/40 w-4 mt-0.5"></i><span>Domicilio: ${esc(l.cobertura.join(', '))}</span></div>`
                : '';
            return `<div class="location-card group">
                <div class="flex items-center gap-2 mb-3 flex-wrap">
                    <span class="location-dot"></span>
                    <h4 class="text-[15px] font-black text-slate-900">${esc(l.nombre)}</h4>
                    ${l.distancia != null ? `<span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full">A ${l.distancia.toFixed(1)} km</span>` : ''}
                </div>
                <p class="text-[12px] text-slate-500 mb-3 leading-relaxed">${esc(l.direccion)}</p>
                <div class="space-y-2 mb-5">
                    <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium"><i class="fa-solid fa-phone text-[#1B263B]/40 w-4"></i> ${esc(l.telefono || '')}</div>
                    <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium"><i class="fa-solid fa-envelope text-[#1B263B]/40 w-4"></i> ${esc(l.email || '')}</div>
                    <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium"><i class="fa-solid fa-clock text-[#1B263B]/40 w-4"></i> ${esc(horario(l))}</div>
                    ${cobertura}
                </div>
                <div class="flex gap-2">
                    <a href="${esc(l.maps || '#')}" target="_blank" rel="noopener" class="btn-location-action flex-grow"><i class="fa-solid fa-location-dot"></i> Cómo llegar</a>
                    <a href="${wa}" target="_blank" rel="noopener" class="bg-emerald-500 text-white px-5 rounded-xl flex items-center justify-center gap-2 hover:bg-emerald-600 text-[11px] font-bold"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                </div>
            </div>`;
        }).join('');

        return `<div class="suc-modal-actual">
            <span class="inline-block bg-[#1B263B]/10 text-[#1B263B] text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest mb-2 border border-[#1B263B]/20">Red de Suministros</span>
            <h2 class="suc-demo-modal-title" id="suc-demo-modal-title">Nuestras Sucursales</h2>
            <p class="suc-demo-modal-sub">Encuentra el punto IMPROGYP más cercano a tu obra.</p>
            <div class="bg-emerald-50/50 border border-emerald-100 rounded-3xl p-5 mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="bg-emerald-500 text-white text-[9px] font-black px-2 py-0.5 rounded uppercase">Cercano</span>
                    <p class="text-[10px] font-black text-emerald-600 uppercase">Tu mejor opción ahora mismo</p>
                </div>
                ${cardActualHtml(nearest, false)}
            </div>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest px-1 mb-3">Listado completo</p>
            <div class="modal-location-grid">${cards}</div>
        </div>`;
    }

    function openModal(variant) {
        const body = document.getElementById('suc-demo-modal-body');
        const overlay = document.getElementById('suc-demo-modal');
        if (!body || !overlay) return;

        if (variant === 'concierge') body.innerHTML = modalConciergeHtml();
        else if (variant === 'logistica') body.innerHTML = modalLogisticaHtml();
        else if (variant === 'showroom') body.innerHTML = modalShowroomHtml();
        else body.innerHTML = modalActualHtml();

        overlay.classList.remove('hidden');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        const search = document.getElementById('suc-modal-search');
        if (search) {
            search.focus();
            search.addEventListener('input', () => {
                logisticaSearch = search.value;
                openModal('logistica');
            });
        }
    }

    function closeModal() {
        const overlay = document.getElementById('suc-demo-modal');
        if (!overlay) return;
        overlay.classList.add('hidden');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function renderAll() {
        if (!locales.length) return;
        const nearest = locales[0];
        renderConcierge(nearest);
        renderLogistica(nearest);
        renderShowroom();
        renderActual(nearest);
    }

    function bindGlobal() {
        document.addEventListener('click', (e) => {
            const filterBtn = e.target.closest('[data-log-filter]');
            if (filterBtn) {
                logisticaFilter = filterBtn.dataset.logFilter || 'todas';
                renderLogistica(locales[0]);
                return;
            }

            const modalFilter = e.target.closest('[data-modal-filter]');
            if (modalFilter) {
                logisticaFilter = modalFilter.dataset.modalFilter || 'todas';
                openModal('logistica');
                return;
            }

            const thumb = e.target.closest('[data-showroom-id]');
            if (thumb) {
                showroomFocusId = thumb.dataset.showroomId || null;
                renderShowroom();
                return;
            }

            const open = e.target.closest('[data-open-modal]');
            if (open) {
                openModal(open.dataset.openModal || 'concierge');
            }
        });

        document.getElementById('suc-demo-modal-close')?.addEventListener('click', closeModal);
        document.getElementById('suc-demo-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'suc-demo-modal') closeModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });

        document.querySelectorAll('.suc-demo-jump-link').forEach((link) => {
            link.addEventListener('click', () => {
                document.querySelectorAll('.suc-demo-jump-link').forEach((l) => l.classList.remove('is-active'));
                link.classList.add('is-active');
            });
        });

        const blocks = [
            { id: 'dir-concierge', jump: 'concierge' },
            { id: 'dir-logistica', jump: 'logistica' },
            { id: 'dir-showroom', jump: 'showroom' }
        ];
        if ('IntersectionObserver' in window) {
            const obs = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    const match = blocks.find((b) => b.id === entry.target.id);
                    if (!match) return;
                    document.querySelectorAll('.suc-demo-jump-link').forEach((l) => {
                        l.classList.toggle('is-active', l.dataset.jump === match.jump);
                    });
                });
            }, { rootMargin: '-40% 0px -50% 0px', threshold: 0 });
            blocks.forEach((b) => {
                const el = document.getElementById(b.id);
                if (el) obs.observe(el);
            });
        }
    }

    async function init() {
        bindGlobal();
        try {
            const res = await fetch('locales.json?v=' + Date.now());
            const data = await res.json();
            locales = processLocales(Array.isArray(data) ? data : []);
        } catch (e) {
            locales = [];
        }
        if (!locales.length) {
            ['panel-concierge', 'panel-logistica', 'panel-showroom'].forEach((id) => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = '<p class="p-8 text-slate-500">No se pudo cargar locales.json</p>';
            });
            return;
        }
        renderAll();
    }

    init();
})();
