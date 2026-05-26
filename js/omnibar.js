/**
 * Omnibar global — búsqueda en vivo + asesor IA.
 * En productos.php delega a tienda_scripts; en home/blog redirige a la tienda.
 */
(function () {
    const SHOP_PATH = /productos\.php/i;
    let liveNavTimer = null;

    function isShopPage() {
        return SHOP_PATH.test(window.location.pathname || '');
    }

    function shopReady() {
        return window.improgypOmniShopHandlers && window.improgypOmniShopHandlers.ready === true;
    }

    function allOmniInputs() {
        return document.querySelectorAll('.omni-input-field');
    }

    function syncOmniInputs(value, exceptEl) {
        allOmniInputs().forEach((el) => {
            if (el !== exceptEl && el.value !== value) {
                el.value = value;
            }
        });
    }

    function setSendLoading(loading) {
        document.querySelectorAll('.btn-send').forEach((btn) => {
            btn.disabled = loading;
        });
        document.querySelectorAll('.btn-send-icon').forEach((icon) => {
            icon.className = loading
                ? 'btn-send-icon fa-solid fa-circle-notch fa-spin' + (icon.classList.contains('text-[10px]') ? ' text-[10px]' : ' text-sm')
                : 'btn-send-icon fa-solid fa-paper-plane' + (icon.classList.contains('text-[10px]') ? ' text-[10px]' : ' text-sm');
        });
    }

    function goToShopQuery(q, extraParams) {
        const base = 'productos.php';
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (extraParams) {
            Object.keys(extraParams).forEach((k) => params.set(k, extraParams[k]));
        }
        const qs = params.toString();
        window.location.href = qs ? base + '?' + qs : base;
    }

    function landingLiveSearch(query) {
        const q = (query || '').trim();
        if (liveNavTimer) clearTimeout(liveNavTimer);
        if (!q) return;
        if (q.length < 3) return;
        liveNavTimer = setTimeout(() => {
            if (allOmniInputs()[0] && allOmniInputs()[0].value.trim() === q) {
                goToShopQuery(q);
            }
        }, 700);
    }

    function filtrarPorTexto(query, sourceEl) {
        syncOmniInputs(query, sourceEl);

        if (shopReady()) {
            window.improgypOmniShopHandlers.filtrarPorTexto(query);
            return;
        }

        if (!isShopPage()) {
            const q = (query || '').trim();
            if (!q) {
                if (liveNavTimer) clearTimeout(liveNavTimer);
                return;
            }
            landingLiveSearch(q);
        }
    }

    async function buscarConIALanding(mensajeUsuario) {
        sessionStorage.setItem('improgyp_pending_ia', mensajeUsuario);
        sessionStorage.setItem('improgyp_ai_cat', '');
        goToShopQuery('', { iaq: mensajeUsuario });
    }

    async function buscarConIA(queryDirecto, specificInput) {
        const inputField = specificInput || document.getElementById('omni-input-field') || document.querySelector('.omni-input-field');
        const mensajeUsuario = (queryDirecto != null ? String(queryDirecto) : (inputField && inputField.value.trim())) || '';
        if (!mensajeUsuario) return;

        if (shopReady()) {
            return window.improgypOmniShopHandlers.buscarConIA(queryDirecto, specificInput);
        }

        if (!isShopPage()) {
            await buscarConIALanding(mensajeUsuario);
            return;
        }

        /* Catálogo aún cargando en tienda */
        const bubble = document.getElementById('ai-bubble');
        const bubbleText = document.getElementById('ai-bubble-text');
        if (bubble && bubbleText) {
            bubble.classList.add('show');
            bubble.setAttribute('aria-hidden', 'false');
            bubbleText.innerHTML = '<span class="text-slate-400"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Cargando catálogo…</span>';
        }
        const waitStart = Date.now();
        const poll = setInterval(() => {
            if (shopReady()) {
                clearInterval(poll);
                window.improgypOmniShopHandlers.buscarConIA(mensajeUsuario, inputField);
            } else if (Date.now() - waitStart > 12000) {
                clearInterval(poll);
                buscarConIALanding(mensajeUsuario);
            }
        }, 200);
    }

    function cerrarBurbujaIA() {
        if (shopReady() && typeof window.improgypOmniShopHandlers.cerrarBurbujaIA === 'function') {
            window.improgypOmniShopHandlers.cerrarBurbujaIA();
            return;
        }
        const bubble = document.getElementById('ai-bubble');
        if (!bubble) return;
        bubble.classList.remove('show');
        bubble.setAttribute('aria-hidden', 'true');
    }

    function onOmniInput(el) {
        filtrarPorTexto(el.value, el);
    }

    function onOmniKeydown(e, el) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarConIA(null, el);
        }
    }

    function bindOmniInputs() {
        document.querySelectorAll('.omni-input-wrapper').forEach((wrap) => {
            const input = wrap.querySelector('.omni-input-field');
            const btn = wrap.querySelector('.btn-send');
            if (!input || input.dataset.omniBound === '1') return;
            input.dataset.omniBound = '1';
            input.addEventListener('input', () => onOmniInput(input));
            input.addEventListener('keydown', (e) => onOmniKeydown(e, input));
            if (btn) {
                btn.addEventListener('click', () => buscarConIA(null, input));
            }
        });
    }

    window.filtrarPorTexto = filtrarPorTexto;
    window.buscarConIA = buscarConIA;
    window.cerrarBurbujaIA = cerrarBurbujaIA;
    window.improgypOmniSetLoading = setSendLoading;

    document.addEventListener('DOMContentLoaded', () => {
        bindOmniInputs();
        const params = new URLSearchParams(window.location.search);
        const q = params.get('q');
        if (q && isShopPage()) {
            allOmniInputs().forEach((el) => { el.value = decodeURIComponent(q); });
        }
    });

    /* Por si el header se hidrata después */
    if (document.readyState !== 'loading') {
        bindOmniInputs();
    }
})();
