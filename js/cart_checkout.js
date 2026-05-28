/**
 * Bolsa global (drawer) — localStorage improgyp_carrito.
 * En productos.php, tienda_scripts sigue siendo la fuente principal del render con catálogo.
 */
(function () {
    const STORAGE_KEY = 'improgyp_carrito';

    function getBaseUrl() {
        return typeof IMPROGYP_BASE_URL === 'string' ? IMPROGYP_BASE_URL : '';
    }

    function getAbsoluteImgUrl(ruta) {
        if (!ruta) return 'favicon-app.png?v=5';
        if (ruta.startsWith('http')) return ruta;
        const limpia = ruta.replace(/^\.\//, '').replace(/^\//, '');
        return getBaseUrl() + limpia;
    }

    function readCart() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
        } catch (e) {
            return [];
        }
    }

    function writeCart(carrito) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(carrito));
        if (typeof window.syncCheckoutCartItems === 'function') {
            window.syncCheckoutCartItems(carrito);
        }
    }

    function updateBadge(carrito) {
        const badge = document.getElementById('cart-badge');
        if (!badge) return;
        let qty = 0;
        (carrito || []).forEach((i) => { qty += i.cantidad || 0; });
        if (qty > 0) {
            badge.textContent = qty > 99 ? '99+' : String(qty);
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function itemKey(item) {
        return item.codigo || item.nombre;
    }

    function renderDrawerFromStorage() {
        const container = document.getElementById('cart-items-container');
        const totalEl = document.getElementById('cart-subtotal');
        if (!container || !totalEl) return;

        const carrito = readCart();
        updateBadge(carrito);

        if (!carrito.length) {
            container.innerHTML = '<div class="h-full flex flex-col items-center justify-center p-6 text-center text-slate-400"><i class="fa-solid fa-box-open text-4xl mb-3 text-slate-200"></i><p class="text-[13px] font-medium">Tu bolsa está vacía.</p><a href="productos.php" class="mt-3 text-[12px] font-bold text-[#3A86FF]">Ir a la tienda</a></div>';
            totalEl.textContent = '$0.00';
            return;
        }

        let html = '';
        let subtotal = 0;
        let totalQty = 0;
        [...carrito].reverse().forEach((item) => {
            totalQty += item.cantidad;
            subtotal += item.cantidad * item.precioNum;
            const imgUrl = getAbsoluteImgUrl(item.imagen);
            const safeId = String(itemKey(item)).replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const onTienda = typeof modificarCantidad === 'function';
            const qtyControls = onTienda
                ? `<div class="flex items-center gap-1 bg-slate-50 border border-slate-100 rounded-md p-1">
                        <button type="button" class="cart-qty-btn" onclick="modificarCantidad('${safeId}', -1)"><i class="fa-solid fa-minus text-[9px]"></i></button>
                        <span class="text-[11px] font-black text-slate-700 w-3 text-center">${item.cantidad}</span>
                        <button type="button" class="cart-qty-btn" onclick="modificarCantidad('${safeId}', 1)"><i class="fa-solid fa-plus text-[9px]"></i></button>
                   </div>
                   <button type="button" class="w-7 h-7 rounded-md text-slate-400 hover:text-rose-500 hover:bg-rose-50 flex items-center justify-center ml-1" onclick="eliminarDelCarrito('${safeId}')" title="Eliminar"><i class="fa-solid fa-trash-can text-[11px]"></i></button>`
                : `<span class="text-[11px] font-black text-slate-500">×${item.cantidad}</span>`;

            const imgClick = onTienda && typeof abrirModalProducto === 'function'
                ? `onclick="abrirModalProducto('${safeId}'); toggleCartDrawer();"`
                : '';

            html += `<div class="cart-item bg-white rounded-xl mb-2 mx-2 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                <img src="${imgUrl}" class="cart-item-img" alt="" onerror="this.onerror=null;this.src='favicon-app.png?v=5';" ${imgClick}>
                <div class="flex-grow min-w-0 pr-2">
                    <h4 class="text-[11px] font-bold text-slate-800 leading-tight mb-0.5 truncate">${item.nombre}</h4>
                    <div class="text-[11px] text-[#1B263B] font-black">$${item.precioNum.toFixed(2)}</div>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">${qtyControls}</div>
            </div>`;
        });

        container.innerHTML = html;
        totalEl.textContent = '$' + subtotal.toFixed(2);
        const badge = document.getElementById('cart-badge');
        if (badge) badge.textContent = totalQty > 99 ? '99+' : String(totalQty);
    }

    window.toggleCartDrawer = function toggleCartDrawer() {
        const drawer = document.getElementById('cart-drawer-overlay');
        if (!drawer) {
            window.location.href = 'productos.php';
            return;
        }
        drawer.classList.toggle('show');
        document.body.style.overflow = drawer.classList.contains('show') ? 'hidden' : '';
        if (drawer.classList.contains('show')) {
            if (typeof actualizarUICarrito === 'function') {
                actualizarUICarrito();
            } else {
                renderDrawerFromStorage();
            }
        }
    };

    window.improgypModificarCantidadLite = function (identificador, delta) {
        let carrito = readCart();
        const idx = carrito.findIndex((c) => itemKey(c) === identificador);
        if (idx === -1) return;
        carrito[idx].cantidad += delta;
        if (carrito[idx].cantidad <= 0) carrito.splice(idx, 1);
        writeCart(carrito);
        renderDrawerFromStorage();
    };

    window.improgypEliminarDelCarritoLite = function (identificador) {
        let carrito = readCart().filter((c) => itemKey(c) !== identificador);
        writeCart(carrito);
        renderDrawerFromStorage();
        if (typeof renderCheckoutList === 'function') renderCheckoutList();
    };

    window.improgypOnCartUpdated = function () {
        updateBadge(readCart());
        if (typeof window.syncCheckoutCartItems === 'function') {
            window.syncCheckoutCartItems(readCart());
        }
        if (typeof renderCheckoutList === 'function') renderCheckoutList();
    };

    document.addEventListener('DOMContentLoaded', function () {
        updateBadge(readCart());
        if (document.getElementById('cart-items-container') && typeof actualizarUICarrito !== 'function') {
            renderDrawerFromStorage();
        }
    });

    window.addEventListener('storage', function (e) {
        if (e.key === STORAGE_KEY) renderDrawerFromStorage();
    });
})();
