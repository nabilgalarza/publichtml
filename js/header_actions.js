/**
 * Header: badges de carrito y compartir (wishlist en wishlist_dropdown.js).
 */
(function () {
    const CART_KEY = 'improgyp_carrito';

    function readJson(key) {
        try {
            return JSON.parse(localStorage.getItem(key)) || [];
        } catch (e) {
            return [];
        }
    }

    function updateCartBadge() {
        const badge = document.getElementById('cart-badge');
        if (!badge) return;
        const carrito = readJson(CART_KEY);
        let qty = 0;
        carrito.forEach((i) => { qty += i.cantidad || 0; });
        if (qty > 0) {
            badge.textContent = qty > 99 ? '99+' : String(qty);
            badge.classList.remove('hidden');
            badge.removeAttribute('aria-hidden');
        } else {
            badge.classList.add('hidden');
            badge.setAttribute('aria-hidden', 'true');
        }
    }

    function compartirTienda() {
        const data = {
            title: 'IMPROGYP | Herramientas profesionales',
            text: '¡Mira el catálogo IMPROGYP! Cotiza fácil por WhatsApp.',
            url: window.location.origin + (window.IMPROGYP_BASE_URL || '') + '/productos.php'
        };
        if (navigator.share) {
            navigator.share(data).catch(() => {});
            return;
        }
        const url = data.url;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                if (typeof showToastNotification === 'function') {
                    showToastNotification('Enlace copiado al portapapeles.', 'success');
                } else {
                    alert('Enlace copiado al portapapeles');
                }
            }).catch(() => alert(url));
        } else {
            alert(url);
        }
    }

    window.compartirTienda = compartirTienda;

    document.addEventListener('DOMContentLoaded', () => {
        updateCartBadge();
        const nav = document.getElementById('main-nav');
        if (nav && nav.dataset.headerProfile === 'default') {
            nav.classList.remove('nav-hidden');
        }
    });

    window.addEventListener('storage', (e) => {
        if (e.key === CART_KEY) updateCartBadge();
    });
})();
