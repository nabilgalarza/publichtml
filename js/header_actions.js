/**
 * Header: badges de carrito/deseos y compartir (sin ocultar nav en home/blog).
 */
(function () {
    const WISH_KEY = 'improgyp_wishlist';
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

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s ?? '';
        return d.innerHTML;
    }

    function getWishlistPrice(prod) {
        if (prod.precioNum != null && !isNaN(prod.precioNum)) {
            return '$' + Number(prod.precioNum).toFixed(2);
        }
        if (prod.presentaciones && prod.presentaciones.length) {
            const p = String(prod.presentaciones[0].precio || '').split('|')[0].trim().replace(/[^0-9.]/g, '');
            if (p) return '$' + parseFloat(p).toFixed(2);
        }
        return 'Consultar';
    }

    function renderWishlistDropdown() {
        const badge = document.getElementById('wishlist-badge');
        const container = document.getElementById('wishlist-items-container');
        if (!container) return;

        const wishlist = readJson(WISH_KEY);
        if (!badge) return;

        if (!wishlist.length) {
            badge.classList.add('hidden');
            badge.setAttribute('aria-hidden', 'true');
            container.innerHTML = '<div class="wishlist-empty"><i class="fa-regular fa-heart text-3xl text-slate-200 mb-3 block"></i>Aún no tienes herramientas favoritas.</div>';
            return;
        }

        badge.textContent = wishlist.length > 99 ? '99+' : String(wishlist.length);
        badge.classList.remove('hidden');
        badge.removeAttribute('aria-hidden');

        let html = '';
        [...wishlist].reverse().forEach((prod) => {
            const id = (prod.codigo || prod.nombre || '').replace(/'/g, "\\'");
            const href = 'productos.php?p=' + encodeURIComponent(prod.codigo || prod.nombre);
            const price = getWishlistPrice(prod);
            const openModal = typeof abrirModalProductoLanding === 'function'
                ? 'abrirModalProductoLanding'
                : (typeof abrirModalProducto === 'function' ? 'abrirModalProducto' : null);
            const clickAttr = openModal
                ? `onclick="${openModal}('${id}'); toggleWishlistModal(event);"`
                : `onclick="location.href='${href}'"`;
            html += `<div class="wishlist-item group">
                <div class="wishlist-item-info" ${clickAttr}>
                    <div class="wishlist-item-title">${escapeHtml(prod.nombre)}</div>
                    <div class="wishlist-item-price">${escapeHtml(price)}</div>
                </div>
            </div>`;
        });
        container.innerHTML = html;
    }

    function toggleWishlistModal(e) {
        if (e) e.stopPropagation();
        const modal = document.getElementById('wishlist-modal');
        if (!modal) return;

        if (typeof window.hideMegaMenu === 'function') {
            window.hideMegaMenu();
        }

        const willOpen = !modal.classList.contains('show');
        if (willOpen) {
            renderWishlistDropdown();
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        } else {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
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

    window.toggleWishlistModal = toggleWishlistModal;
    window.compartirTienda = compartirTienda;
    window.improgypHeaderRefreshWishlist = renderWishlistDropdown;

    document.addEventListener('click', (event) => {
        const modal = document.getElementById('wishlist-modal');
        const btn = event.target.closest('button[onclick*="toggleWishlistModal"]');
        if (!modal || !modal.classList.contains('show')) return;
        if (!modal.contains(event.target) && !btn) {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        updateCartBadge();
        renderWishlistDropdown();
        const nav = document.getElementById('main-nav');
        if (nav && nav.dataset.headerProfile === 'default') {
            nav.classList.remove('nav-hidden');
        }
    });

    window.addEventListener('storage', (e) => {
        if (e.key === CART_KEY) updateCartBadge();
        if (e.key === WISH_KEY) renderWishlistDropdown();
    });
})();
