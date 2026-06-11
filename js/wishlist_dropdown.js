/**
 * Dropdown "Mis Deseos" unificado (home, blog, tienda).
 */
(function () {
    const WISH_KEY = 'improgyp_wishlist';

    function readWishlist() {
        try {
            return JSON.parse(localStorage.getItem(WISH_KEY)) || [];
        } catch (e) {
            return [];
        }
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s ?? '';
        return d.innerHTML;
    }

    function escapeJsString(str) {
        return String(str || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }

    function getAbsoluteImgUrl(ruta) {
        if (!ruta) return 'favicon-app.png?v=5';
        if (String(ruta).startsWith('http')) return ruta;
        let rutaLimpia = String(ruta).replace(/^\.\//, '').replace(/^\//, '');
        const base = (window.IMPROGYP_BASE_URL || '').replace(/\/$/, '');
        return base ? base + '/' + rutaLimpia : rutaLimpia;
    }

    function getWishlistPrice(prod) {
        if (prod.precioNum != null && !isNaN(prod.precioNum)) {
            return '$' + Number(prod.precioNum).toFixed(2);
        }
        if (prod.presentaciones && prod.presentaciones.length) {
            const raw = String(prod.presentaciones[0].precio || '').split('|')[0].trim();
            if (raw) {
                return raw.includes('$') ? raw : '$' + raw.replace(/[^0-9.]/g, '');
            }
        }
        return 'Consultar';
    }

    function resolveWishlistHandlers() {
        return {
            open: typeof window.abrirModalProducto === 'function' ? 'abrirModalProducto'
                : (typeof window.abrirModalProductoLanding === 'function' ? 'abrirModalProductoLanding' : null),
            remove: typeof window.toggleWishlist === 'function' ? 'toggleWishlist'
                : (typeof window.toggleWishlistLanding === 'function' ? 'toggleWishlistLanding' : null),
            addCart: typeof window.agregarAlCarrito === 'function' ? 'agregarAlCarrito'
                : (typeof window.agregarAlCarritoLanding === 'function' ? 'agregarAlCarritoLanding' : null),
        };
    }

    function improgypRenderWishlistDropdown() {
        const badge = document.getElementById('wishlist-badge');
        const container = document.getElementById('wishlist-items-container');
        if (!container) return;

        const wishlist = readWishlist();
        const handlers = resolveWishlistHandlers();

        if (!wishlist.length) {
            if (badge) {
                badge.classList.add('hidden');
                badge.setAttribute('aria-hidden', 'true');
            }
            container.innerHTML = '<div class="wishlist-empty"><i class="fa-regular fa-heart text-3xl text-slate-200 mb-3 block"></i>Aún no tienes herramientas favoritos.</div>';
            return;
        }

        if (badge) {
            badge.textContent = wishlist.length > 99 ? '99+' : String(wishlist.length);
            badge.classList.remove('hidden');
            badge.setAttribute('aria-hidden', 'false');
        }

        let html = '';
        [...wishlist].reverse().forEach((prod) => {
            const ident = prod.codigo || prod.nombre || '';
            const safeIdent = escapeJsString(ident);
            const imgUrl = getAbsoluteImgUrl(prod.imagen);
            const price = getWishlistPrice(prod);
            const openClick = handlers.open
                ? `onclick="${handlers.open}('${safeIdent}')"`
                : `onclick="location.href='productos.php?p=${encodeURIComponent(ident)}'"`;
            const removeClick = handlers.remove
                ? `onclick="${handlers.remove}('${safeIdent}')"`
                : '';
            const cartClick = handlers.addCart
                ? `onclick="${handlers.addCart}('${safeIdent}')"`
                : `onclick="location.href='productos.php?p=${encodeURIComponent(ident)}'"`;

            html += `<div class="wishlist-item group">
                <img src="${escapeHtml(imgUrl)}" alt="${escapeHtml(prod.nombre)}" ${openClick} onerror="this.onerror=null; this.src='favicon-app.png?v=5';">
                <div class="wishlist-item-info" ${openClick}>
                    <div class="wishlist-item-title">${escapeHtml(prod.nombre)}</div>
                    ${prod.codigo ? `<div class="wishlist-item-ref">REF: ${escapeHtml(prod.codigo)}</div>` : ''}
                    <div class="wishlist-item-price">${escapeHtml(price)}</div>
                </div>
                <div class="wishlist-item-actions">
                    ${handlers.remove ? `<button type="button" ${removeClick} title="Eliminar"><i class="fa-solid fa-trash-can text-[10px]"></i></button>` : ''}
                    <button type="button" class="wishlist-add-cart" ${cartClick} title="Añadir a bolsa"><i class="fa-solid fa-cart-plus text-[10px]"></i></button>
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
            improgypRenderWishlistDropdown();
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        } else {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }
    }

    window.improgypRenderWishlistDropdown = improgypRenderWishlistDropdown;
    window.improgypHeaderRefreshWishlist = improgypRenderWishlistDropdown;
    window.toggleWishlistModal = toggleWishlistModal;

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
        improgypRenderWishlistDropdown();
    });

    window.addEventListener('storage', (e) => {
        if (e.key === WISH_KEY) improgypRenderWishlistDropdown();
    });
})();
