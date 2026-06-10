/**
 * Métricas ligeras IMPROGYP (Visita + helper para eventos).
 * 1 beacon por página y sesión de pestaña.
 */
(function () {
    const COOKIE_NAME = 'improgyp_vid';
    const COOKIE_DAYS = 365;

    function getVisitorId() {
        const match = document.cookie.match(new RegExp('(?:^|; )' + COOKIE_NAME + '=([^;]*)'));
        if (match) {
            return decodeURIComponent(match[1]);
        }
        const id = (typeof crypto !== 'undefined' && crypto.randomUUID)
            ? crypto.randomUUID()
            : 'v' + Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
        const secure = location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = COOKIE_NAME + '=' + encodeURIComponent(id) + '; path=/; max-age=' + (COOKIE_DAYS * 86400) + '; SameSite=Lax' + secure;
        return id;
    }

    function sendPayload(payload) {
        if (!navigator.sendBeacon) {
            return;
        }
        try {
            navigator.sendBeacon('api_metricas.php', JSON.stringify(payload));
        } catch (e) { /* ignore */ }
    }

    window.improgypTrackEvent = function (evento, valor, categoria) {
        if (!evento || valor === undefined || valor === null || String(valor).trim() === '') {
            return;
        }
        sendPayload({
            e: String(evento).slice(0, 50),
            v: String(valor).slice(0, 150),
            c: String(categoria || 'General').slice(0, 50),
            vid: getVisitorId(),
        });
    };

    function trackPageView() {
        const page = typeof IMPROGYP_METRICS_PAGE === 'string' ? IMPROGYP_METRICS_PAGE : 'unknown';
        const key = 'improgyp_pv_' + page;
        try {
            if (sessionStorage.getItem(key)) {
                return;
            }
            sessionStorage.setItem(key, '1');
        } catch (e) { /* private mode */ }

        sendPayload({
            e: 'Visita',
            v: page,
            c: 'Pageview',
            vid: getVisitorId(),
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', trackPageView);
    } else {
        trackPageView();
    }
})();
