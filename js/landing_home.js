/**
 * Home: sucursales — Showroom desktop, tarjeta compacta móvil.
 */
(function () {
    const Showroom = window.ImprogypLocalesShowroom;
    const DESKTOP_MQ = '(min-width: 768px)';
    let listaLocales = [];
    let localesProcesados = [];
    let showroomFocusId = null;
    let mqDesktop = null;

    function isDesktopLocales() {
        return mqDesktop ? mqDesktop.matches : window.matchMedia(DESKTOP_MQ).matches;
    }

    function getHeroLoc() {
        if (!isDesktopLocales()) {
            return localesProcesados[0];
        }
        if (showroomFocusId) {
            return localesProcesados.find((l) => l.id === showroomFocusId) || localesProcesados[0];
        }
        return localesProcesados[0];
    }

    function renderHomeWidget() {
        const widget = document.getElementById('home-nearest-location-widget');
        const hero = getHeroLoc();
        if (!widget || !hero || !Showroom) return;

        if (isDesktopLocales()) {
            const nearest = localesProcesados[0];
            const isNearest = hero.id === nearest?.id && hero.distancia != null;
            const heroHtml = Showroom.heroHtml(hero, {
                isNearest,
                badgeLabel: isNearest && !showroomFocusId ? 'Tu sucursal' : undefined,
                openModal: true
            });
            const strip = Showroom.stripHtml(localesProcesados, hero.id, showroomFocusId);
            const stripWrap = strip
                ? `<div class="locales-showroom-strip" role="list" aria-label="Otras sucursales">${strip}</div>`
                : '';
            widget.innerHTML = heroHtml + stripWrap;
            widget.classList.remove('locales-showroom-widget--compact');
        } else {
            widget.innerHTML = Showroom.compactCardHtml(hero, true);
            widget.classList.add('locales-showroom-widget--compact');
        }
    }

    function renderizarLocales(userLat, userLng) {
        if (!listaLocales.length || !Showroom) return;

        localesProcesados = listaLocales.map((l) => ({ ...l }));
        if (userLat != null && userLng != null) {
            localesProcesados.forEach((l) => {
                if (l.lat != null && l.lng != null) {
                    l.distancia = distKm(userLat, userLng, l.lat, l.lng);
                }
            });
            localesProcesados.sort((a, b) => (a.distancia ?? 9999) - (b.distancia ?? 9999));
        }

        renderHomeWidget();

        const grid = document.getElementById('locations-grid');
        if (grid) {
            grid.innerHTML = Showroom.modalGridHtml(localesProcesados);
        }
    }

    function distKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2
            + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
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

    function bindShowroomInteractions() {
        document.addEventListener('click', (e) => {
            const thumb = e.target.closest('[data-locales-focus]');
            if (thumb && thumb.closest('#home-nearest-location-widget') && isDesktopLocales()) {
                showroomFocusId = thumb.dataset.localesFocus || null;
                renderHomeWidget();
                return;
            }

            const openCard = e.target.closest('[data-locales-open-modal]');
            if (openCard && openCard.closest('#home-nearest-location-widget')) {
                abrirModalLocales();
            }
        });

        document.addEventListener('keydown', (e) => {
            const openable = e.target.closest('[data-locales-open-modal]');
            if (openable && openable.closest('#home-nearest-location-widget') && (e.key === 'Enter' || e.key === ' ')) {
                e.preventDefault();
                abrirModalLocales();
            }
        });
    }

    function bindViewportLocales() {
        mqDesktop = window.matchMedia(DESKTOP_MQ);
        mqDesktop.addEventListener('change', () => {
            if (!localesProcesados.length) return;
            if (!isDesktopLocales()) {
                showroomFocusId = null;
            }
            renderizarLocales(renderizarLocales.lastLat ?? null, renderizarLocales.lastLng ?? null);
        });
    }

    async function initLocalesHome() {
        if (!Showroom) {
            console.warn('ImprogypLocalesShowroom no cargado');
            return;
        }

        bindShowroomInteractions();
        bindViewportLocales();

        try {
            const res = await fetch('locales.json?v=' + Date.now());
            listaLocales = await res.json();
            if (!Array.isArray(listaLocales)) listaLocales = [];

            const apply = (lat, lng) => {
                renderizarLocales.lastLat = lat;
                renderizarLocales.lastLng = lng;
                renderizarLocales(lat, lng);
            };

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => apply(pos.coords.latitude, pos.coords.longitude),
                    () => apply(null, null),
                    { timeout: 10000, maximumAge: 600000 }
                );
            } else {
                apply(null, null);
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
