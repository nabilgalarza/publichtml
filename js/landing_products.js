/**
 * Home: cards y modal idénticos a productos.php + botón "Ver en la tienda" en el modal.
 */
(function () {
    const CART_KEY = 'improgyp_carrito';
    const WISH_KEY = 'improgyp_wishlist';

    let catalogoLanding = [];
    let catalogoReady = false;
    let currentIdent = null;

    function getBaseUrl() {
        return typeof IMPROGYP_BASE_URL === 'string' ? IMPROGYP_BASE_URL : '';
    }

    function getAbsoluteImgUrl(ruta) {
        if (!ruta) return 'favicon-app.png?v=5';
        if (String(ruta).startsWith('http')) return ruta;
        const limpia = String(ruta).replace(/^\.\//, '').replace(/^\//, '');
        return getBaseUrl() + limpia;
    }

    function readJson(key) {
        try {
            return JSON.parse(localStorage.getItem(key)) || [];
        } catch (e) {
            return [];
        }
    }

    function writeCart(carrito) {
        localStorage.setItem(CART_KEY, JSON.stringify(carrito));
        if (typeof window.improgypOnCartUpdated === 'function') {
            window.improgypOnCartUpdated();
        }
        if (typeof window.syncCheckoutCartItems === 'function') {
            window.syncCheckoutCartItems(carrito);
        }
    }

    function findProduct(identificador) {
        if (!identificador) return null;
        return catalogoLanding.find(
            (p) => p.nombre === identificador || (p.codigo && p.codigo === identificador)
        ) || null;
    }

    function productIdent(prod) {
        return (prod.codigo && String(prod.codigo).trim()) || prod.nombre;
    }

    function buildProductosShopUrl(prod) {
        const ident = productIdent(prod);
        const params = new URLSearchParams();
        params.set('p', ident);
        if (prod.categoria) {
            params.set('cat', prod.categoria);
        }
        return 'productos.php?' + params.toString();
    }

    function formatModalPrice(precio) {
        if (precio === 'Consultar' || precio === null || precio === undefined) {
            return 'Consultar';
        }
        const s = String(precio);
        if (s.includes('$')) return s;
        return '$' + s;
    }

    function parsePrecio(pres) {
        if (!pres || !pres.precio) return 'Consultar';
        let p = String(pres.precio).split('|')[0].trim();
        if (!p || p === 'Consultar') return 'Consultar';
        return p;
    }

    function precioNumFromProd(prod) {
        if (prod.presentaciones && prod.presentaciones.length) {
            const raw = String(prod.presentaciones[0].precio || '').split('|')[0].trim().replace(/[^0-9.]/g, '');
            const n = parseFloat(raw);
            return isNaN(n) ? 0 : n;
        }
        return 0;
    }

    async function loadCatalogo() {
        if (catalogoReady) return catalogoLanding;
        try {
            const res = await fetch('catalogo.json?v=' + Date.now());
            const data = await res.json();
            catalogoLanding = Array.isArray(data) ? data : [];
        } catch (e) {
            catalogoLanding = [];
        }
        catalogoReady = true;
        refreshCardWishlistButtons();
        return catalogoLanding;
    }

    function refreshCardWishlistButtons() {
        const wishlist = readJson(WISH_KEY);
        document.querySelectorAll('.improgyp-home-wishlist-btn').forEach((btn) => {
            const id = btn.dataset.wishlistId;
            const prod = findProduct(id);
            if (!prod) return;
            const en = wishlist.some((w) => w.nombre === prod.nombre);
            btn.classList.toggle('active', en);
            btn.innerHTML = en
                ? '<i class="fa-solid fa-heart"></i>'
                : '<i class="fa-regular fa-heart"></i>';
        });
    }

    function setModalPrice(text) {
        const el = document.getElementById('modal-price');
        if (el) el.textContent = formatModalPrice(text);
    }

    window.cambiarPrecioModalLanding = function (precio, btn) {
        setModalPrice(precio);
        const wrap = document.getElementById('modal-presentations');
        if (!wrap) return;
        wrap.querySelectorAll('button').forEach((b) => {
            b.className = 'px-3 py-1.5 border rounded-lg text-[11px] font-bold transition-colors bg-white text-slate-500 border-slate-200 hover:border-[#1B263B] hover:text-[#1B263B]';
        });
        if (btn) {
            btn.className = 'px-3 py-1.5 border rounded-lg text-[11px] font-bold transition-colors bg-[#1B263B] text-white border-[#1B263B]';
        }
    };

    window.toggleRelacionadosLanding = function () {
        const content = document.getElementById('modal-related-content');
        const icon = document.getElementById('icon-toggle-related');
        if (!content || !icon) return;
        const isHidden = content.classList.contains('hidden');
        if (isHidden) {
            content.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    };

    function buildRelatedHTML(prod) {
        const relacionados = catalogoLanding
            .filter((p) => p.categoria === prod.categoria && p.nombre !== prod.nombre)
            .sort(() => 0.5 - Math.random())
            .slice(0, 3);

        if (!relacionados.length) return '';

        const items = relacionados.map((r) => {
            const rIdentificador = (r.codigo || r.nombre).replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const rImg = getAbsoluteImgUrl(r.imagen);
            return `
                <div class="group cursor-pointer" onclick="abrirModalProductoLanding('${rIdentificador}')">
                    <div class="aspect-square bg-slate-50 rounded-xl p-2 mb-2 border border-slate-100 group-hover:border-[#1B263B]/30 transition-all">
                        <img src="${rImg}" alt="" class="w-full h-full object-contain mix-blend-multiply group-hover:scale-110 transition-transform" onerror="this.onerror=null; this.src='favicon-app.png?v=5';">
                    </div>
                    <p class="text-[9px] font-bold text-slate-800 line-clamp-2 leading-tight">${r.nombre}</p>
                </div>`;
        }).join('');

        return `
            <div class="mt-6 pt-5 border-t border-slate-100">
                <button type="button" id="btn-toggle-related" onclick="toggleRelacionadosLanding()" class="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-slate-50 to-white border border-slate-200 text-[#1B263B] font-black text-[12px] flex items-center justify-between hover:border-[#1B263B]/30 hover:shadow-md transition-all group uppercase tracking-widest">
                    <span class="flex items-center gap-3"><span class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-600 flex items-center justify-center text-sm"><i class="fa-solid fa-wand-magic-sparkles"></i></span> Complementa tu kit</span>
                    <i id="icon-toggle-related" class="fa-solid fa-chevron-down text-slate-400 group-hover:text-[#1B263B] transition-transform"></i>
                </button>
                <div id="modal-related-content" class="hidden mt-5 fade-in">
                    <div class="grid grid-cols-3 gap-3">${items}</div>
                </div>
            </div>`;
    }

    function updateModalWishlistBtn(prod) {
        const btn = document.getElementById('modal-btn-wishlist');
        if (!btn) return;
        const wishlist = readJson(WISH_KEY);
        const en = wishlist.some((w) => w.nombre === prod.nombre);
        const ident = productIdent(prod).replace(/'/g, "\\'").replace(/"/g, '&quot;');
        if (en) {
            btn.className = 'w-10 h-10 rounded-lg flex items-center justify-center text-lg transition-colors border border-slate-200 shadow-sm text-rose-500 bg-rose-50';
            btn.innerHTML = '<i class="fa-solid fa-heart"></i>';
        } else {
            btn.className = 'w-10 h-10 rounded-lg flex items-center justify-center text-lg transition-colors border border-slate-200 shadow-sm text-slate-400 bg-white hover:text-rose-400 hover:border-rose-200';
            btn.innerHTML = '<i class="fa-regular fa-heart"></i>';
        }
        btn.onclick = () => toggleWishlistLanding(ident);
    }

    function updateModalAddBtn(identificador) {
        const wrapper = document.getElementById('modal-btn-add-wrapper');
        if (!wrapper) return;
        const carrito = readJson(CART_KEY);
        const item = carrito.find((c) => (c.codigo && c.codigo === identificador) || c.nombre === identificador);
        const safe = identificador.replace(/'/g, "\\'").replace(/"/g, '&quot;');

        if (item) {
            wrapper.innerHTML = `<div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl p-1 h-10 w-28 shadow-inner">
                <button type="button" onclick="improgypModificarCantidadLanding('${safe}', -1)" class="w-8 h-full rounded-lg bg-white text-slate-500 shadow-sm hover:text-rose-500 font-black text-base active:scale-95">-</button>
                <span class="font-black text-[14px] text-slate-800 flex-grow text-center select-none">${item.cantidad}</span>
                <button type="button" onclick="improgypModificarCantidadLanding('${safe}', 1)" class="w-8 h-full rounded-lg bg-white text-slate-500 shadow-sm hover:text-[#1B263B] font-black text-base active:scale-95">+</button>
            </div>`;
        } else {
            wrapper.innerHTML = `<button type="button" class="btn-IMPROGYP px-4 h-10 text-[13px] w-28" onclick="agregarAlCarritoLanding('${safe}')"><i class="fa-solid fa-cart-plus"></i> <span class="ml-1">Añadir</span></button>`;
        }
    }

    window.improgypModificarCantidadLanding = function (identificador, delta) {
        let carrito = readJson(CART_KEY);
        const idx = carrito.findIndex((c) => (c.codigo && c.codigo === identificador) || c.nombre === identificador);
        if (idx === -1) return;
        carrito[idx].cantidad += delta;
        if (carrito[idx].cantidad <= 0) carrito.splice(idx, 1);
        writeCart(carrito);
        updateModalAddBtn(identificador);
    };

    window.toggleWishlistLanding = function (identificador) {
        const prod = findProduct(identificador);
        if (!prod) return;
        let wishlist = readJson(WISH_KEY);
        const idx = wishlist.findIndex((w) => w.nombre === prod.nombre);
        if (idx > -1) {
            wishlist.splice(idx, 1);
        } else {
            wishlist.push({
                nombre: prod.nombre,
                codigo: prod.codigo || '',
                imagen: prod.imagen,
                categoria: prod.categoria,
                presentaciones: prod.presentaciones,
                precioNum: precioNumFromProd(prod),
            });
        }
        localStorage.setItem(WISH_KEY, JSON.stringify(wishlist));
        refreshCardWishlistButtons();
        if (currentIdent) {
            const p = findProduct(currentIdent);
            if (p) updateModalWishlistBtn(p);
        }
        if (typeof window.improgypHeaderRefreshWishlist === 'function') {
            window.improgypHeaderRefreshWishlist();
        }
    };

    window.agregarAlCarritoLanding = function (identificador) {
        const prod = findProduct(identificador || currentIdent);
        if (!prod) return;
        const id = productIdent(prod);
        let carrito = readJson(CART_KEY);
        const idx = carrito.findIndex((c) => (c.codigo && c.codigo === id) || c.nombre === id);
        if (idx > -1) {
            carrito[idx].cantidad += 1;
        } else {
            carrito.push({
                nombre: prod.nombre,
                codigo: prod.codigo || '',
                imagen: prod.imagen,
                precioNum: precioNumFromProd(prod),
                cantidad: 1,
            });
        }
        writeCart(carrito);
        const badge = document.getElementById('cart-badge');
        if (badge && badge.parentElement) {
            badge.parentElement.classList.add('scale-110', 'border-slate-900');
            setTimeout(() => badge.parentElement.classList.remove('scale-110', 'border-slate-900'), 200);
        }
        if (navigator.vibrate) navigator.vibrate(50);
        updateModalAddBtn(id);
    };

    function setupModalShare(prod, shopUrl) {
        const btnShare = document.getElementById('modal-btn-share');
        if (!btnShare) return;
        const fullShopUrl = window.location.origin + getBaseUrl().replace(/\/?$/, '/') + shopUrl;
        btnShare.onclick = () => {
            const data = {
                title: `${prod.nombre} | IMPROGYP`,
                text: `¡Mira esta herramienta en IMPROGYP: ${prod.nombre}!`,
                url: fullShopUrl,
            };
            if (navigator.share) {
                navigator.share(data).catch(() => {});
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(fullShopUrl);
            }
        };
    }

    window.abrirModalProductoLanding = async function (identificador) {
        await loadCatalogo();
        const prod = findProduct(identificador);
        if (!prod) {
            window.location.href = 'productos.php?p=' + encodeURIComponent(identificador);
            return;
        }

        const shopUrl = buildProductosShopUrl(prod);
        currentIdent = productIdent(prod);
        const modal = document.getElementById('product-modal');
        if (!modal) return;

        document.getElementById('modal-title').textContent = prod.nombre;
        document.getElementById('modal-cat').textContent = prod.categoria || '';
        document.getElementById('modal-marca-label').textContent = prod.marca || 'IMPROGYP';

        const skuEl = document.getElementById('modal-sku-label');
        if (prod.codigo) {
            skuEl.textContent = 'REF: ' + prod.codigo;
            skuEl.classList.remove('hidden');
        } else {
            skuEl.textContent = '';
            skuEl.classList.add('hidden');
        }

        document.getElementById('modal-img').src = getAbsoluteImgUrl(prod.imagen);

        const descEl = document.getElementById('modal-desc');
        if (descEl) {
            descEl.textContent = prod.desc_larga ? prod.desc_larga : 'Sin descripción adicional.';
        }

        let presHTML = '';
        let precioInicial = 'Consultar';
        if (prod.presentaciones && prod.presentaciones.length) {
            prod.presentaciones.forEach((pres, index) => {
                const precioLimpio = parsePrecio(pres);
                if (index === 0) precioInicial = precioLimpio;
                const activeClass = index === 0
                    ? 'bg-[#1B263B] text-white border-[#1B263B]'
                    : 'bg-white text-slate-500 border-slate-200 hover:border-[#1B263B] hover:text-[#1B263B]';
                const safePrecio = precioLimpio.replace(/'/g, "\\'");
                presHTML += `<button type="button" class="px-3 py-1.5 border rounded-lg text-[11px] font-bold transition-colors ${activeClass}" onclick="cambiarPrecioModalLanding('${safePrecio}', this)">${pres.opcion}</button>`;
            });
        } else {
            presHTML = '<span class="text-[11px] text-slate-400 italic">Presentación única</span>';
        }
        document.getElementById('modal-presentations').innerHTML = presHTML;
        setModalPrice(precioInicial);

        const shopBtn = document.getElementById('modal-btn-shop');
        if (shopBtn) {
            shopBtn.href = shopUrl;
        }

        setupModalShare(prod, shopUrl);
        updateModalWishlistBtn(prod);
        updateModalAddBtn(currentIdent);

        const relatedContainer = document.getElementById('modal-related-container');
        if (relatedContainer) {
            relatedContainer.innerHTML = buildRelatedHTML(prod);
        }

        modal.classList.remove('hidden');
        void modal.offsetWidth;
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    window.cerrarModalProductoLanding = function (e) {
        if (e && e.target && e.target.id !== 'product-modal') {
            const closeBtn = e.target.closest('button[aria-label="Cerrar"]');
            if (!closeBtn && e.target.innerHTML !== '×' && e.target.innerHTML !== '&times;') {
                return;
            }
        }
        const modal = document.getElementById('product-modal');
        if (!modal) return;
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
        currentIdent = null;
    };

    window.abrirModalProducto = window.abrirModalProductoLanding;
    window.cerrarModalProducto = window.cerrarModalProductoLanding;
    window.toggleRelacionados = window.toggleRelacionadosLanding;

    function bindHomeProductCards() {
        document.body.addEventListener('click', (e) => {
            const wishBtn = e.target.closest('.improgyp-home-wishlist-btn');
            if (wishBtn) return;

            const openEl = e.target.closest('[data-open-product]');
            if (!openEl) return;
            const id = openEl.getAttribute('data-open-product');
            if (!id) return;
            e.preventDefault();
            abrirModalProductoLanding(id);
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') cerrarModalProductoLanding();
    });

    document.addEventListener('DOMContentLoaded', async () => {
        bindHomeProductCards();
        await loadCatalogo();
        const openId = window.IMPROGYP_OPEN_PRODUCT;
        if (openId) {
            setTimeout(() => abrirModalProductoLanding(openId), 300);
        }
    });
})();
