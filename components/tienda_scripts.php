    <script>
        let catalogoCompleto = [];
        let adData = {};
        let textosMarketing = {};
        let listaLocales = [];
        let carrito = [];
        let wishlist = [];
        let datosRanking = { impulsados: [], tendencias: [] };
        let marcaActiva = 'Todas';
        let ultimosProductosRenderizados = [];
        let improgypGridMode = 'catalog';
        let improgypModalIdent = null;

        try { carrito = JSON.parse(localStorage.getItem('improgyp_carrito')) || []; } catch(e) { console.error("Error cargando carrito"); }
        try { wishlist = JSON.parse(localStorage.getItem('improgyp_wishlist')) || []; } catch(e) { console.error("Error cargando wishlist"); }

        function getAbsoluteImgUrl(ruta) {
    // 1. Si no hay ruta, mostrar el favicon por defecto
    if (!ruta) return 'favicon-app.png?v=5';
    
    // 2. Si la ruta ya es absoluta (http...), no tocar nada
    if (ruta.startsWith('http')) return ruta;
    
    // 3. Limpiar la ruta de ./ o / al inicio
    let rutaLimpia = ruta.replace(/^\.\//, '').replace(/^\//, ''); 
    
    // 4. Retornar la unión (si IMPROGYP_BASE_URL es '', solo queda la ruta limpia)
    return IMPROGYP_BASE_URL + rutaLimpia;
}
        // =====================================================================

        (function initCatalogHeaderScroll() {
            const navBar = document.getElementById('main-nav');
            const stickyBar = document.getElementById('sticky-cat-bar');
            if (!navBar || !stickyBar || navBar.dataset.headerProfile !== 'catalog') return;

            let lastScrollY = window.scrollY;
            let tickingScroll = false;
            const navHeight = () => navBar.offsetHeight || 72;

            const setCatBarCollapsed = (collapsed) => {
                if (collapsed) {
                    stickyBar.classList.add('cat-bar-up');
                    stickyBar.style.top = '0px';
                } else {
                    stickyBar.classList.remove('cat-bar-up');
                    stickyBar.style.top = navHeight() + 'px';
                }
            };

            window.addEventListener('scroll', () => {
                if (!tickingScroll) {
                    window.requestAnimationFrame(() => {
                        if (window.scrollY > 80) {
                            if (window.scrollY > lastScrollY) {
                                navBar.classList.add('nav-hidden');
                                setCatBarCollapsed(true);
                            } else {
                                navBar.classList.remove('nav-hidden');
                                setCatBarCollapsed(false);
                            }
                        } else {
                            navBar.classList.remove('nav-hidden');
                            setCatBarCollapsed(false);
                        }
                        lastScrollY = window.scrollY;
                        tickingScroll = false;
                    });
                    tickingScroll = true;
                }
            }, { passive: true });

            setCatBarCollapsed(false);
        })();

        function radarNinja(evento, valor, categoria = 'General') {
            if (typeof window.improgypTrackEvent === 'function') {
                window.improgypTrackEvent(evento, valor, categoria);
                return;
            }
            if (navigator.sendBeacon) {
                navigator.sendBeacon('api_metricas.php', JSON.stringify({ e: evento, v: valor, c: categoria }));
            }
        }

        function getSafeId(str) { return 'item-' + String(str || '').replace(/[^a-zA-Z0-9]/g, '-'); }

        function productIdent(prod) {
            if (!prod) return '';
            return (prod.codigo && String(prod.codigo).trim()) || prod.nombre || '';
        }

        function productUniqueKey(prod) {
            return productIdent(prod);
        }

        function normalizeIdent(str) {
            return String(str || '').trim();
        }

        /** Busca producto por código; por nombre solo si hay un único match en catálogo. */
        function findProductByIdent(identificador) {
            const id = normalizeIdent(identificador);
            if (!id) return null;
            const byCodigo = catalogoCompleto.find((p) => p.codigo && normalizeIdent(p.codigo) === id);
            if (byCodigo) return byCodigo;
            const byNombre = catalogoCompleto.filter((p) => p.nombre === id);
            if (byNombre.length === 1) return byNombre[0];
            return null;
        }

        function cartMatchesIdent(cartItem, identificador) {
            const id = normalizeIdent(identificador);
            if (!id || !cartItem) return false;
            if (cartItem.codigo && normalizeIdent(cartItem.codigo) === id) return true;
            if (!cartItem.codigo && cartItem.nombre === id) return true;
            return false;
        }

        function cartMatchesProduct(cartItem, prod) {
            return cartMatchesIdent(cartItem, productIdent(prod));
        }

        function wishlistMatchesProduct(wishItem, prod) {
            if (!wishItem || !prod) return false;
            const pc = prod.codigo && normalizeIdent(prod.codigo);
            const wc = wishItem.codigo && normalizeIdent(wishItem.codigo);
            if (pc && wc) return pc === wc;
            if (!pc && !wc && wishItem.nombre === prod.nombre) return true;
            return false;
        }

        function wishlistMatchesIdent(wishItem, identificador) {
            const prod = findProductByIdent(identificador);
            if (prod) return wishlistMatchesProduct(wishItem, prod);
            const id = normalizeIdent(identificador);
            if (!id || !wishItem) return false;
            if (wishItem.codigo && normalizeIdent(wishItem.codigo) === id) return true;
            if (!wishItem.codigo && wishItem.nombre === id) return true;
            return false;
        }

        function migrateCartAndWishlistIds() {
            if (!catalogoCompleto.length) return;
            let cartChanged = false;
            let wishChanged = false;
            carrito = carrito.map((item) => {
                if (item.codigo) return item;
                const matches = catalogoCompleto.filter((p) => p.nombre === item.nombre);
                if (matches.length === 1) {
                    cartChanged = true;
                    return { ...item, codigo: matches[0].codigo || '' };
                }
                return item;
            });
            wishlist = wishlist.map((item) => {
                if (item.codigo) return item;
                const matches = catalogoCompleto.filter((p) => p.nombre === item.nombre);
                if (matches.length === 1) {
                    wishChanged = true;
                    return { ...item, codigo: matches[0].codigo || '' };
                }
                return item;
            });
            if (cartChanged) localStorage.setItem('improgyp_carrito', JSON.stringify(carrito));
            if (wishChanged) localStorage.setItem('improgyp_wishlist', JSON.stringify(wishlist));
        }

        function escapeJsString(str) {
            return String(str || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }

        function escapeHtml(str) {
            return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function parsePrecioPresentacion(pres) {
            if (!pres || pres.precio == null || pres.precio === '') return 'Consultar';
            const p = String(pres.precio).split('|')[0].trim();
            return p || 'Consultar';
        }

        function scrollToProductInGrid(identificador) {
            if (!identificador) return;
            const grid = document.getElementById('grid-productos');
            if (!grid) return;
            const cards = grid.querySelectorAll('article[data-product-ident]');
            const prod = findProductByIdent(identificador);
            const keys = new Set([identificador]);
            if (prod) keys.add(productIdent(prod));
            for (const card of cards) {
                const attr = card.getAttribute('data-product-ident');
                if (attr && keys.has(attr)) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
            }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            try {
                // 1. CARGA PRIORITARIA: Catálogo primero para mostrar productos de inmediato
                const catRes = await fetch('catalogo.json?v=' + Date.now()).then(r => r.ok ? r.json() : []).catch(() => []);
                catalogoCompleto = catRes;
                migrateCartAndWishlistIds();

                if (Array.isArray(catalogoCompleto) && catalogoCompleto.length > 0) {
                    const catMemoria = localStorage.getItem('improgyp_ai_cat') || localStorage.getItem('improgyp_memoria_cat') || 'Todos';
                    filtrarCategoria(catMemoria); // RENDERIZADO INICIAL RÁPIDO
                }
                window.improgypOmniShopHandlers = {
                    ready: true,
                    filtrarPorTexto: filtrarPorTexto,
                    buscarConIA: buscarConIA,
                    cerrarBurbujaIA: cerrarBurbujaIA
                };

                // 2. CARGA DE LOCALES
                initLocales();

                // 3. CARGA DE APOYO: Rankings, Textos y Ads en segundo plano (sin bloquear)
                Promise.all([
                    fetch('api_ranking.php?v=' + Date.now()).then(r => r.ok ? r.json() : {}).catch(() => ({})),
                    fetch('ads.json?v=' + Date.now()).then(r => r.ok ? r.json() : {}).catch(() => ({})),
                    fetch('textos_tienda.json?v=' + Date.now()).then(r => r.ok ? r.json() : {}).catch(() => ({}))
                ]).then(([rankRes, adsRes, textosRes]) => {
                    adData = adsRes;
                    textosMarketing = textosRes;
                    if (rankRes && typeof rankRes === 'object') {
                        datosRanking = {
                            impulsados: Array.isArray(rankRes.impulsados) ? rankRes.impulsados : [],
                            tendencias: Array.isArray(rankRes.tendencias) ? rankRes.tendencias : []
                        };
                    }
                    
                    // Actualizar nombres de categorías y marcas si es necesario
                    inicializarCategoriasYMarcas();
                    
                    // Re-renderizar si ya hay rankings o ads para añadir badges/pautas
                    if (datosRanking.impulsados.length > 0 || Object.keys(adData).length > 0) {
                        const currentCat = document.querySelector('.cat-pill.active')?.dataset.cat || 'Todos';
                        filtrarCategoria(currentCat);
                    }
                });

                // Funcionalidades secundarias
                try { actualizarUIWishlist(); } catch(e) {}
                try { actualizarUICarrito(); } catch(e) {}
                
                // Deep Linking
                const urlParams = new URLSearchParams(window.location.search);
                const prodName = urlParams.get('p');
                const catDeep = urlParams.get('cat');
                const qDeep = urlParams.get('q');
                const iaqDeep = urlParams.get('iaq') || sessionStorage.getItem('improgyp_pending_ia');
                const isWishlistDeep = urlParams.get('wishlist');
                if (catDeep && prodName) {
                    const catDec = decodeURIComponent(catDeep);
                    const prodDec = decodeURIComponent(prodName);
                    setTimeout(() => {
                        filtrarCategoria(catDec);
                        setTimeout(() => {
                            const existe = findProductByIdent(prodDec);
                            if (existe) {
                                scrollToProductInGrid(productIdent(existe));
                                abrirModalProducto(productIdent(existe));
                            }
                        }, 500);
                    }, 300);
                } else if (catDeep) {
                    setTimeout(() => filtrarCategoria(decodeURIComponent(catDeep)), 300);
                } else if (qDeep) {
                    setTimeout(() => {
                        document.querySelectorAll('.omni-input-field').forEach(el => { el.value = decodeURIComponent(qDeep); });
                        filtrarPorTexto(decodeURIComponent(qDeep));
                    }, 300);
                } else if (iaqDeep) {
                    sessionStorage.removeItem('improgyp_pending_ia');
                    setTimeout(() => buscarConIA(decodeURIComponent(iaqDeep)), 500);
                } else if (isWishlistDeep === '1') {
                    setTimeout(() => { if (typeof mostrarWishlistCompleta === 'function') mostrarWishlistCompleta(); }, 400);
                } else if (prodName) {
                    const prodDec = decodeURIComponent(prodName);
                    setTimeout(() => {
                        const existe = findProductByIdent(prodDec);
                        if (!existe) return;
                        const prodIdentDeep = productIdent(existe);
                        const abrirYScroll = () => {
                            scrollToProductInGrid(prodIdentDeep);
                            abrirModalProducto(prodIdentDeep);
                        };
                        if (existe.categoria && existe.categoria !== 'Todos') {
                            filtrarCategoria(existe.categoria);
                            setTimeout(abrirYScroll, 500);
                        } else {
                            abrirYScroll();
                        }
                    }, 500);
                }
                // Habilitar Draggable en Escritorio
                makeDraggable('category-pills');
            } catch(e) { 
                console.error("Error global de inicialización:", e);
                const grid = document.getElementById('grid-productos');
                if (grid) grid.innerHTML = `<div class="col-span-full text-center py-10 bg-rose-50 text-rose-600 font-bold">Error de sincronización. Por favor recarga la página.</div>`; 
            }
        });

        function inicializarCategoriasYMarcas() {
            const categoriasUnicas = [...new Set(catalogoCompleto.map(item => item.categoria))].filter(Boolean);
            const pillsContainer = document.getElementById('category-pills'); 
            const sheetContainer = document.getElementById('mobile-category-list');
            const catMemoria = localStorage.getItem('improgyp_ai_cat') || localStorage.getItem('improgyp_memoria_cat') || 'Todos';

            let pillsHTML = `<button data-cat="Todos" class="cat-pill ${catMemoria==='Todos'?'active':''} px-4 py-1.5 rounded-full text-[13px] font-bold cursor-pointer transition-all border border-transparent bg-white text-slate-500 hover:border-[#1B263B] hover:text-[#1B263B] whitespace-nowrap" onclick="filtrarCategoria('Todos')">Todos</button>`;
            let sheetHTML = `<button data-cat="Todos" class="bs-cat-btn ${catMemoria==='Todos'?'active':''}" onclick="filtrarCategoria('Todos')"><span>Todos los productos</span><i class="fa-solid fa-check bs-icon"></i></button>`;

            categoriasUnicas.forEach(cat => {
                const isActive = cat === catMemoria;
                const catJs = escapeJsString(cat);
                const catHtml = escapeHtml(cat);
                pillsHTML += `<button data-cat="${catHtml}" class="cat-pill ${isActive?'active':''} px-4 py-1.5 rounded-full text-[13px] font-bold cursor-pointer transition-all border border-slate-200 bg-white text-slate-500 hover:border-[#1B263B] hover:text-[#1B263B] whitespace-nowrap" onclick="filtrarCategoria('${catJs}')">${catHtml}</button>`;
                sheetHTML += `<button data-cat="${catHtml}" class="bs-cat-btn ${isActive?'active':''}" onclick="filtrarCategoria('${catJs}')"><span>${catHtml}</span><i class="fa-solid fa-check bs-icon"></i></button>`;
            });
            
            if(pillsContainer) pillsContainer.innerHTML = pillsHTML; 
            if(sheetContainer) sheetContainer.innerHTML = sheetHTML;

            const marcasUnicas = [...new Set(catalogoCompleto.map(item => item.marca))].filter(Boolean).sort();
            const sidebarBrandPillsContainer = document.getElementById('sidebar-brand-pills');
            const mobileBrandPillsContainer = document.getElementById('mobile-brands-list');
            const mobileBrandStrip = document.getElementById('mobile-brand-pills');
            
            if (marcasUnicas.length > 0) {
                let brandHTML = `<button type="button" onclick="filtrarMarca('Todas')" class="brand-pill active" data-brand="Todas">Todas</button>`;
                marcasUnicas.forEach(m => {
                    const mJs = escapeJsString(m);
                    const mHtml = escapeHtml(m);
                    brandHTML += `<button type="button" onclick="filtrarMarca('${mJs}')" class="brand-pill" data-brand="${mHtml}">${mHtml}</button>`;
                });
                if(sidebarBrandPillsContainer) sidebarBrandPillsContainer.innerHTML = brandHTML;
                if(mobileBrandPillsContainer) mobileBrandPillsContainer.innerHTML = brandHTML;
                if(mobileBrandStrip) mobileBrandStrip.innerHTML = brandHTML;
            }
        }

        /* LÓGICA DE LOCALES Y GEOLOCALIZACIÓN */
        async function initLocales() {
            try {
                const res = await fetch('locales.json?v=' + Date.now());
                listaLocales = await res.json();
                
                // 1. Intentar obtener ubicación del navegador (Precisión)
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            renderizarLocalesCercanos(pos.coords.latitude, pos.coords.longitude);
                        },
                        () => {
                            // Fallback: Si el usuario deniega, usar ciudad por IP si es posible
                            renderizarLocalesCercanos(null, null);
                        }
                    );
                } else {
                    renderizarLocalesCercanos(null, null);
                }
            } catch(e) { console.error("Error cargando locales:", e); }
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radio de la Tierra en km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        function renderizarLocalesCercanos(userLat, userLng) {
            if (!listaLocales.length) return;

            let localesProcesados = [...listaLocales];

            if (userLat && userLng) {
                localesProcesados.forEach(l => {
                    l.distancia = calculateDistance(userLat, userLng, l.lat, l.lng);
                });
                localesProcesados.sort((a, b) => a.distancia - b.distancia);
            }

            const masCercano = localesProcesados[0];
            const widget = document.getElementById('nearest-location-widget');
            const container = document.getElementById('sidebar-location-container');

            if (masCercano) {
                const distText = masCercano.distancia ? `<span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full ml-auto">A ${masCercano.distancia.toFixed(1)} km</span>` : '';
                const cardHTML = `
                    <div class="location-card border-[#1B263B]/20" onclick="abrirModalLocales()">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="text-[14px] font-black text-slate-800 leading-tight">${masCercano.nombre}</h4>
                            ${distText}
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed mb-4">${masCercano.direccion}</p>
                        <div class="flex gap-2">
                            <a href="${masCercano.maps}" target="_blank" class="btn-location-action w-full" onclick="event.stopPropagation()"><i class="fa-solid fa-location-dot"></i> Ver en Google Maps</a>
                        </div>
                    </div>
                `;
                
                if (widget) widget.innerHTML = cardHTML;
                if (container) container.classList.remove('hidden');

            }

            const grid = document.getElementById('locations-grid');
            const Showroom = window.ImprogypLocalesShowroom;
            if (grid && Showroom) {
                grid.innerHTML = Showroom.modalGridHtml(localesProcesados);
            }
        }

        function abrirModalLocales() { document.getElementById('locations-modal').classList.add('show'); document.body.style.overflow = 'hidden'; }
        function cerrarModalLocales(e) { if(!e || e.target.classList.contains('modal-overlay') || e.target.innerHTML === '&times;') { document.getElementById('locations-modal').classList.remove('show'); document.body.style.overflow = 'auto'; } }

        const bsPanel = document.getElementById('bs-panel'); const bsOverlay = document.getElementById('category-bottom-sheet');
        let startY = 0, currentY = 0, deltaY = 0, isDragging = false;
        bsPanel.addEventListener('touchstart', (e) => { const scrollArea = e.target.closest('#mobile-category-list'); if(scrollArea && scrollArea.scrollTop > 0) return; startY = e.touches[0].clientY; isDragging = true; bsPanel.style.transition = 'none'; }, {passive: true});
        bsPanel.addEventListener('touchmove', (e) => { if(!isDragging) return; currentY = e.touches[0].clientY; deltaY = currentY - startY; if(deltaY > 0) { bsPanel.style.transform = `translateY(${deltaY}px)`; } }, {passive: true});
        bsPanel.addEventListener('touchend', (e) => { if(!isDragging) return; isDragging = false; bsPanel.style.transition = 'transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)'; if(deltaY > 80) { toggleBottomSheet(); setTimeout(() => bsPanel.style.transform = '', 300); } else { bsPanel.style.transform = ''; } deltaY = 0; });
        function toggleBottomSheet() { bsOverlay.classList.toggle('show'); document.body.style.overflow = bsOverlay.classList.contains('show') ? 'hidden' : 'auto'; }
        function toggleCartDrawer() { const drawer = document.getElementById('cart-drawer-overlay'); drawer.classList.toggle('show'); document.body.style.overflow = drawer.classList.contains('show') ? 'hidden' : 'auto'; if(drawer.classList.contains('show')) actualizarUICarrito(); }
        
        function makeDraggable(containerId) {
            const slider = document.getElementById(containerId);
            if (!slider) return;

            let isDown = false;
            let startX;
            let scrollLeft;

            slider.addEventListener('mousedown', (e) => {
                isDown = true;
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });

            slider.addEventListener('mouseleave', () => { isDown = false; });
            slider.addEventListener('mouseup', () => { isDown = false; });

            slider.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 2; 
                slider.scrollLeft = scrollLeft - walk;
            });
        }

        function actualizarBotonesGrid() {
            // OPTIMIZACIÓN: Solo iterar sobre los productos que están actualmente en el grid
            const targetList = ultimosProductosRenderizados.length > 0 ? ultimosProductosRenderizados : catalogoCompleto;
            targetList.forEach(prod => {
                const actionContainer = document.getElementById(getSafeId(productIdent(prod))); if(!actionContainer) return; 
                const pid = productIdent(prod);
                const itemEnCarrito = carrito.find((c) => cartMatchesProduct(c, prod)); 
                const safeIdentificador = escapeJsString(pid);
                if(itemEnCarrito) {
                    actionContainer.innerHTML = `<div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl p-1 h-10 md:h-9 mt-auto shadow-inner" onclick="event.stopPropagation()"><button onclick="modificarCantidad('${safeIdentificador}', -1)" class="w-9 md:w-8 h-full rounded-lg bg-white text-slate-500 shadow-sm hover:text-rose-500 font-black text-base active:scale-95">-</button><span class="font-black text-[13px] text-slate-800 w-6 text-center select-none">${itemEnCarrito.cantidad}</span><button onclick="modificarCantidad('${safeIdentificador}', 1)" class="w-9 md:w-8 h-full rounded-lg bg-white text-slate-500 shadow-sm hover:text-[#1B263B] font-black text-base active:scale-95">+</button></div>`;
                } else { actionContainer.innerHTML = `<button class="btn-IMPROGYP w-full text-[13px] md:text-[12px] py-2 h-10 md:h-9 mt-auto" onclick="agregarAlCarrito('${safeIdentificador}'); event.stopPropagation();"><i class="fa-solid fa-cart-plus"></i> <span class="ml-1">Añadir</span></button>`; }
            });
            if (improgypModalIdent) {
                const identificadorModal = improgypModalIdent;
                const itemModal = carrito.find((c) => cartMatchesIdent(c, identificadorModal));
                const btnModalWrapper = document.getElementById('modal-btn-add-wrapper'); 
                const safeIdentificadorModal = identificadorModal.replace(/'/g, "\\'");
                if(btnModalWrapper) {
                    if(itemModal) { btnModalWrapper.innerHTML = `<div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl p-1 h-10 w-28 shadow-inner"><button onclick="modificarCantidad('${safeIdentificadorModal}', -1)" class="w-8 h-full rounded-lg bg-white text-slate-500 shadow-sm hover:text-rose-500 font-black text-base active:scale-95">-</button><span class="font-black text-[14px] text-slate-800 flex-grow text-center select-none">${itemModal.cantidad}</span><button onclick="modificarCantidad('${safeIdentificadorModal}', 1)" class="w-8 h-full rounded-lg bg-white text-slate-500 shadow-sm hover:text-[#1B263B] font-black text-base active:scale-95">+</button></div>`;
                    } else { btnModalWrapper.innerHTML = `<button class="btn-IMPROGYP px-4 h-10 text-[13px] w-28" onclick="agregarAlCarrito('${safeIdentificadorModal}')"><i class="fa-solid fa-cart-plus"></i> <span class="ml-1">Añadir</span></button>`; }
                }
            }
        }

        function agregarAlCarrito(identificador) {
            const prodIndex = carrito.findIndex((c) => cartMatchesIdent(c, identificador));
            if (prodIndex > -1) { carrito[prodIndex].cantidad += 1; } 
            else {
                const prodReal = findProductByIdent(identificador);
                if (prodReal) { 
                    let precioBase = "0.00"; 
                    if(prodReal.presentaciones && prodReal.presentaciones.length > 0) { 
                        const raw = parsePrecioPresentacion(prodReal.presentaciones[0]);
                        precioBase = raw !== 'Consultar' ? String(raw).replace(/[^0-9.]/g, '') : '0.00';
                    }
                    carrito.push({ 
                        nombre: prodReal.nombre, 
                        codigo: prodReal.codigo || '',
                        imagen: prodReal.imagen, 
                        precioNum: parseFloat(precioBase) || 0, 
                        cantidad: 1 
                    }); 
                    radarNinja('Añadir a Carrito', prodReal.nombre, prodReal.categoria); 
                }
            }
            localStorage.setItem('improgyp_carrito', JSON.stringify(carrito)); actualizarUICarrito();
            const badge = document.getElementById('cart-badge'); badge.parentElement.classList.add('scale-110', 'border-slate-900'); setTimeout(() => { badge.parentElement.classList.remove('scale-110', 'border-slate-900'); }, 200);
            if (navigator.vibrate) navigator.vibrate(50);
        }

        function modificarCantidad(identificador, delta) { 
            const prodIndex = carrito.findIndex((c) => cartMatchesIdent(c, identificador)); 
            if (prodIndex > -1) { 
                carrito[prodIndex].cantidad += delta; 
                if(carrito[prodIndex].cantidad <= 0) carrito.splice(prodIndex, 1); 
                localStorage.setItem('improgyp_carrito', JSON.stringify(carrito)); 
                actualizarUICarrito(); 
            } 
        }
        function eliminarDelCarrito(identificador) { 
            const prodIndex = carrito.findIndex((c) => cartMatchesIdent(c, identificador)); 
            if (prodIndex > -1) { 
                carrito.splice(prodIndex, 1); 
                localStorage.setItem('improgyp_carrito', JSON.stringify(carrito)); 
                actualizarUICarrito(); 
            } 
        }

        function actualizarUICarrito() {
            const container = document.getElementById('cart-items-container'); const badge = document.getElementById('cart-badge'); const totalElement = document.getElementById('cart-subtotal');
            let html = ''; let totalQty = 0; let subtotal = 0;
            if(carrito.length === 0) { badge.classList.add('hidden'); container.innerHTML = `<div class="h-full flex flex-col items-center justify-center p-6 text-center text-slate-400"><i class="fa-solid fa-box-open text-4xl mb-3 text-slate-200"></i><p class="text-[13px] font-medium">Tu bolsa de compras está vacía.</p></div>`; totalElement.innerText = "$0.00"; } 
            else { badge.classList.remove('hidden');
                [...carrito].reverse().forEach(item => {
                    totalQty += item.cantidad; subtotal += (item.precioNum * item.cantidad); 
                    
                    // --- IMPLEMENTACIÓN DE DOMINIO ABSOLUTO ---
                    const imgUrl = getAbsoluteImgUrl(item.imagen); 
                    const safeIdentificador = (item.codigo || item.nombre).replace(/'/g, "\\'").replace(/"/g, "&quot;");
                    html += `
                    <div class="cart-item bg-white rounded-xl mb-2 mx-2 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                        <img src="${imgUrl}" class="cart-item-img" onerror="this.onerror=null; this.src='favicon-app.png?v=5';" onclick="abrirModalProducto('${safeIdentificador}'); toggleCartDrawer();">
                        <div class="flex-grow min-w-0 pr-2">
                            <h4 class="text-[11px] font-bold text-slate-800 leading-tight mb-0.5 truncate">${item.nombre}</h4>
                            <div class="text-[9px] text-slate-400 font-bold mb-1">${item.codigo ? 'REF: ' + item.codigo : ''}</div>
                            <div class="text-[11px] text-[#1B263B] font-black">$${item.precioNum.toFixed(2)}</div>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <div class="flex items-center gap-1 bg-slate-50 border border-slate-100 rounded-md p-1">
                                <button class="cart-qty-btn h-6 w-6" onclick="modificarCantidad('${safeIdentificador}', -1)"><i class="fa-solid fa-minus text-[9px]"></i></button>
                                <span class="text-[11px] font-black text-slate-700 w-3 text-center">${item.cantidad}</span>
                                <button class="cart-qty-btn h-6 w-6" onclick="modificarCantidad('${safeIdentificador}', 1)"><i class="fa-solid fa-plus text-[9px]"></i></button>
                            </div>
                            <button class="w-7 h-7 rounded-md text-slate-400 hover:text-rose-500 hover:bg-rose-50 flex items-center justify-center transition-colors ml-1" onclick="eliminarDelCarrito('${safeIdentificador}')" title="Eliminar"><i class="fa-solid fa-trash-can text-[11px]"></i></button>
                        </div>
                    </div>`;
                }); badge.innerText = totalQty; container.innerHTML = html; totalElement.innerText = `$${subtotal.toFixed(2)}`;
            }
            if (typeof window.syncCheckoutCartItems === 'function') window.syncCheckoutCartItems(carrito);
            if (typeof window.improgypOnCartUpdated === 'function') window.improgypOnCartUpdated();
            else if (typeof renderCheckoutList === 'function') renderCheckoutList();
            actualizarBotonesGrid();
        }

        async function enviarPedidoWhatsApp() {
            if (typeof toggleCheckoutModal === 'function') { toggleCheckoutModal(); return; }
            if(carrito.length === 0) return alert("Agrega herramientas para iniciar la compra.");
            let texto = "Hola IMPROGYP, deseo realizar el siguiente pedido:\n\n"; let subtotal = 0;
            const itemsSimplificados = carrito.map(item => {
                subtotal += (item.cantidad * item.precioNum);
                texto += `🔹 ${item.cantidad}x ${item.nombre} ($${item.precioNum.toFixed(2)})\n`;
                return { nombre: item.nombre, cantidad: item.cantidad, precio: item.precioNum };
            });
            texto += `\n*TOTAL ESTIMADO:* $${subtotal.toFixed(2)}\n_Solicito asistencia para coordinar el pago y envío._`;
            const catPrincipal = carrito.length > 0 ? carrito[0].categoria : 'General';
            
            // Registro silencioso del pedido en la base de datos
            try {
                const source = window.lastSearchSource || 'directo';
                fetch('api_pedido_publico.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        total: subtotal,
                        items: itemsSimplificados,
                        source: source
                    })
                });
            } catch(e) { console.error("Error al registrar métrica de pedido:", e); }

            radarNinja('Checkout Iniciado', `$${subtotal.toFixed(2)}`, catPrincipal);
            const url = `https://wa.me/593991754887?text=${encodeURIComponent(texto)}`;
            window.open(url, '_blank');
        }

        function compartirTienda() {
            const data = {
                title: 'IMPROGYP | E-commerce Inteligente',
                text: '¡Mira la mejor selección de herramientas en IMPROGYP! Compra fácil por WhatsApp.',
                url: window.location.origin + '/productos.php'
            };
            if (navigator.share) {
                navigator.share(data).catch(() => {});
            } else {
                navigator.clipboard.writeText(data.url);
                alert("Enlace copiado al portapapeles");
            }
        }

        function compartirProducto(identificador, categoria, nombreDisplay) {
            const baseUrl = window.location.origin + window.location.pathname.replace(/[^/]+$/, '') + 'productos.php';
            const params = new URLSearchParams();
            params.set('p', identificador);
            if (categoria) params.set('cat', categoria);
            const shareUrl = `${baseUrl}?${params.toString()}`;
            const titulo = nombreDisplay || identificador;
            const data = {
                title: `${titulo} | IMPROGYP`,
                text: `¡Mira esta herramienta en IMPROGYP: ${titulo}!`,
                url: shareUrl
            };
            if (navigator.share) {
                navigator.share(data).catch(() => {});
            } else {
                navigator.clipboard.writeText(shareUrl);
                alert("Enlace del producto copiado");
            }
        }

        function filtrarPorTexto(query) {
            improgypGridMode = 'catalog';
            const normalize = (str) => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            const q = normalize(query.trim());
            
            // Sincronizar el valor en el otro buscador
            document.querySelectorAll('.omni-input-field').forEach(el => { if(el.value !== query) el.value = query; });
            
            if (q.length === 0) {
                const catActiva = localStorage.getItem('improgyp_memoria_cat') || 'Todos';
                let base = catActiva === 'Todos' ? catalogoCompleto : catalogoCompleto.filter(p => p.categoria === catActiva);
                renderizarGrid(base);
                cerrarBurbujaIA();
                return;
            }

            // Filtrado en vivo insensible a tildes
            const filtrados = catalogoCompleto.filter(p => 
                normalize(p.nombre).includes(q) || 
                normalize(p.categoria).includes(q) ||
                (p.marca && normalize(p.marca).includes(q)) ||
                (p.codigo && normalize(p.codigo).includes(q))
            );
            
            renderizarGrid(filtrados);
            if(q.length > 2) {
                // Sensor Ninja: Búsqueda Live (Throttled)
                if(window.liveSearchTimer) clearTimeout(window.liveSearchTimer);
                window.liveSearchTimer = setTimeout(() => {
                    radarNinja('Búsqueda Live', query, 'Live');
                }, 1500);
            }
            if(q.length > 2) cerrarBurbujaIA(); 
        }

        async function buscarConIA(queryDirecto = null, specificInput = null) {
            const inputField = specificInput || document.querySelector('.omni-input-field'); 
            const mensajeUsuario = queryDirecto || inputField.value.trim(); if(!mensajeUsuario) return;
            window.lastSearchSource = 'IA: ' + mensajeUsuario;
            radarNinja('Búsqueda IA', mensajeUsuario, 'AI'); 
            
            // Sincronizar todos los inputs con el mismo valor (opcional pero recomendado)
            document.querySelectorAll('.omni-input-field').forEach(el => el.value = ''); 

            const iconBtns = document.querySelectorAll('.btn-send-icon'); 
            const bubble = document.getElementById('ai-bubble'); 
            const bubbleText = document.getElementById('ai-bubble-text');
            
            inputField.disabled = true; 
            iconBtns.forEach(btn => btn.className = 'fa-solid fa-circle-notch fa-spin'); 
            bubble.classList.add('show');
            bubble.setAttribute('aria-hidden', 'false');
            bubbleText.innerHTML = '<span class="text-slate-400"><i class="fa-solid fa-brain fa-pulse mr-2"></i>Analizando el catálogo...</span>';
            try {
                const catalogoLigero = catalogoCompleto.map(p => ({ nombre: p.nombre, categoria: p.categoria }));
                const response = await fetch('api_tienda.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ mensaje: mensajeUsuario }) });
                const data = await response.json(); 
                // Sanitizar respuesta de la IA antes de renderizar (Seguridad Fase 1)
                const mensajeLimpio = DOMPurify.sanitize(data.mensaje_voz, { ALLOWED_TAGS: ['b', 'i', 'strong', 'br', 'span'], ALLOWED_ATTR: ['class'] });
                bubbleText.innerHTML = mensajeLimpio;
                if(data.skus_recomendados && data.skus_recomendados.length > 0) {
                    improgypGridMode = 'ai';
                    const productosRecomendados = catalogoCompleto.filter(p =>
                        data.skus_recomendados.includes(p.nombre) || (p.codigo && data.skus_recomendados.includes(p.codigo))
                    );
                    const tituloH1 = document.getElementById('titulo-principal'); tituloH1.classList.remove('fade-in'); void tituloH1.offsetWidth; tituloH1.classList.add('fade-in');
                    tituloH1.innerHTML = `Selección <br class="hidden md:block"> <span class="laser-text">Inteligente.</span>`; document.getElementById('subtitulo-principal').innerHTML = `Resultados recomendados específicamente para ti.`;
                    document.querySelectorAll('.cat-pill').forEach(btn => { btn.classList.remove('active', 'border-transparent'); btn.classList.add('border-slate-200'); }); document.querySelectorAll('.bs-cat-btn').forEach(btn => btn.classList.remove('active')); document.getElementById('mobile-cat-label').innerText = "Selección de la IA";
                    if(productosRecomendados.length > 0) {
                        localStorage.setItem('improgyp_ai_cat', productosRecomendados[0].categoria);
                    }
                    renderizarGrid(productosRecomendados); window.scrollTo({ top: 0, behavior: 'smooth' });
                } else if (data.skus_recomendados && data.skus_recomendados.length === 0) { bubbleText.innerHTML += "<br><span class='text-[11px] text-rose-500 font-bold mt-2 block'>No encontré productos exactos para esto, intenta otra búsqueda.</span>"; }
            } catch(error) { bubbleText.innerHTML = "Hubo una desconexión temporal. Intenta de nuevo."; } finally { iconBtns.forEach(btn => btn.className = (btn.classList.contains('text-[10px]') ? 'fa-solid fa-paper-plane text-[10px]' : 'fa-solid fa-paper-plane text-sm')); inputField.disabled = false; inputField.focus(); }
        }
        function cerrarBurbujaIA() {
            const bubble = document.getElementById('ai-bubble');
            if (!bubble) return;
            bubble.classList.remove('show');
            bubble.setAttribute('aria-hidden', 'true');
        }

        function abrirModalProducto(identificador) {
            const prod = findProductByIdent(identificador); if(!prod) return;
            const ident = productIdent(prod);
            improgypModalIdent = ident;
            radarNinja('Ver Producto', prod.nombre, prod.categoria); 
            
            // SOPORTE PARA BOTÓN ATRÁS (NAV)
            if (!history.state || !history.state.modal) {
                history.pushState({modal: true, productName: ident}, "");
            }

            document.getElementById('modal-title').innerText = prod.nombre; 
            document.getElementById('modal-cat').innerText = prod.categoria; 
            document.getElementById('modal-marca-label').innerText = prod.marca || 'IMPROGYP';
            document.getElementById('modal-sku-label').innerText = prod.codigo ? `REF: ${prod.codigo}` : '';
            if(!prod.codigo) document.getElementById('modal-sku-label').classList.add('hidden');
            else document.getElementById('modal-sku-label').classList.remove('hidden');
            
            // --- IMPLEMENTACIÓN DE DOMINIO ABSOLUTO ---
            document.getElementById('modal-img').src = getAbsoluteImgUrl(prod.imagen); 
            // ------------------------------------------

            document.getElementById('modal-desc').innerHTML = prod.desc_larga ? prod.desc_larga : "Sin descripción adicional.";
            let presHTML = ''; let precioInicial = "Consultar";
            if(prod.presentaciones && prod.presentaciones.length > 0) {
                prod.presentaciones.forEach((pres, index) => {
                    let precioLimpio = parsePrecioPresentacion(pres); if(index === 0) precioInicial = precioLimpio;
                    let activeClass = index === 0 ? 'bg-[#1B263B] text-white border-[#1B263B]' : 'bg-white text-slate-500 border-slate-200 hover:border-[#1B263B] hover:text-[#1B263B]';
                    const safePrecio = escapeJsString(precioLimpio);
                    presHTML += `<button type="button" class="px-3 py-1.5 border rounded-lg text-[11px] font-bold transition-colors ${activeClass}" onclick="cambiarPrecioModal('${safePrecio}', this)">${escapeHtml(pres.opcion)}</button>`;
                });
            } else { presHTML = `<span class="text-[11px] text-slate-400 italic">Presentación única</span>`; }
            document.getElementById('modal-presentations').innerHTML = presHTML; 
            document.getElementById('modal-price').innerText = (precioInicial !== "Consultar" && !precioInicial.toString().includes('$')) ? `$${precioInicial}` : precioInicial;
            const safeIdent = escapeJsString(ident);
            const btnWishlist = document.getElementById('modal-btn-wishlist');
            const enWishlist = wishlist.some((w) => wishlistMatchesProduct(w, prod));
            if(enWishlist) { btnWishlist.className = 'w-10 h-10 rounded-lg flex items-center justify-center text-lg transition-colors border border-slate-200 shadow-sm text-rose-500 bg-rose-50'; btnWishlist.innerHTML = '<i class="fa-solid fa-heart"></i>'; } 
            else { btnWishlist.className = 'w-10 h-10 rounded-lg flex items-center justify-center text-lg transition-colors border border-slate-200 shadow-sm text-slate-400 bg-white hover:text-rose-400 hover:border-rose-200'; btnWishlist.innerHTML = '<i class="fa-regular fa-heart"></i>'; }
            btnWishlist.setAttribute('onclick', `toggleWishlist('${safeIdent}', null, true)`); 

            const btnShare = document.getElementById('modal-btn-share');
            const safeCategory = escapeJsString(prod.categoria || '');
            const safeNombre = escapeJsString(prod.nombre);
            btnShare.setAttribute('onclick', `compartirProducto('${safeIdent}', '${safeCategory}', '${safeNombre}')`);

            // --- UPSELLING: PRODUCTOS RELACIONADOS ---
            const relacionados = catalogoCompleto
                .filter(p => p.categoria === prod.categoria && productUniqueKey(p) !== productUniqueKey(prod))
                .sort(() => 0.5 - Math.random()) // Mezclar
                .slice(0, 3); // Top 3

            let relatedHTML = '';
            if (relacionados.length > 0) {
                relatedHTML = `
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <button id="btn-toggle-related" onclick="toggleRelacionados()" class="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-slate-50 to-white border border-slate-200 text-[#1B263B] font-black text-[12px] flex items-center justify-between hover:border-[#1B263B]/30 hover:shadow-md transition-all group uppercase tracking-widest">
                        <span class="flex items-center gap-3"><span class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-600 flex items-center justify-center text-sm"><i class="fa-solid fa-wand-magic-sparkles"></i></span> Complementa tu kit</span>
                        <i id="icon-toggle-related" class="fa-solid fa-chevron-down text-slate-400 group-hover:text-[#1B263B] transition-transform"></i>
                    </button>
                    
                    <div id="modal-related-content" class="hidden mt-5 fade-in">
                        <div class="grid grid-cols-3 gap-3">
                            ${relacionados.map(r => {
                                const rIdentificador = (r.codigo || r.nombre).replace(/'/g, "\\'").replace(/"/g, "&quot;");
                                const rImg = getAbsoluteImgUrl(r.imagen);
                                return `
                                <div class="group cursor-pointer" onclick="abrirModalProducto('${rIdentificador}')">
                                    <div class="aspect-square bg-slate-50 rounded-xl p-2 mb-2 border border-slate-100 group-hover:border-[#1B263B]/30 transition-all">
                                        <img src="${rImg}" class="w-full h-full object-contain mix-blend-multiply group-hover:scale-110 transition-transform" onerror="this.onerror=null; this.src='favicon-app.png?v=5';">
                                    </div>
                                    <p class="text-[9px] font-bold text-slate-800 line-clamp-2 leading-tight">${r.nombre}</p>
                                </div>`;
                            }).join('')}
                        </div>
                    </div>
                </div>`;
            }
            document.getElementById('modal-related-container').innerHTML = relatedHTML;

            actualizarBotonesGrid(); 
            const modal = document.getElementById('product-modal'); modal.classList.remove('hidden'); void modal.offsetWidth; modal.classList.add('show'); document.body.style.overflow = 'hidden'; 
        }

        window.addEventListener('popstate', (e) => {
            if (!e.state || !e.state.modal) {
                cerrarModalProducto(null, true);
            } else if (e.state.productName) {
                abrirModalProducto(e.state.productName);
            }
        });

        function cerrarModalProducto(e, fromPopState = false) { 
            if (!fromPopState && history.state && history.state.modal) {
                history.back();
                return;
            }
            const modal = document.getElementById('product-modal'); modal.classList.remove('show'); setTimeout(() => { modal.classList.add('hidden'); document.body.style.overflow = 'auto'; improgypModalIdent = null; }, 300); 
        }

        function toggleRelacionados() {
            const content = document.getElementById('modal-related-content');
            const icon = document.getElementById('icon-toggle-related');
            const isHidden = content.classList.contains('hidden');
            
            if (isHidden) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }
        function cambiarPrecioModal(precioNuevo, btnSeleccionado) { 
            document.getElementById('modal-price').innerText = (precioNuevo !== "Consultar" && !precioNuevo.toString().includes('$')) ? `$${precioNuevo}` : precioNuevo;
            const contenedor = btnSeleccionado.parentElement; 
            contenedor.querySelectorAll('button').forEach(b => { 
                b.className = 'px-3 py-1.5 border rounded-lg text-[11px] font-bold transition-colors bg-white text-slate-500 border-slate-200 hover:border-[#1B263B] hover:text-[#1B263B]'; 
            }); 
            btnSeleccionado.className = 'px-3 py-1.5 border rounded-lg text-[11px] font-bold transition-colors bg-[#1B263B] text-white border-[#1B263B]'; 
        }

        function toggleWishlist(identificador, btnElement, desdeModal = false) {
            const index = wishlist.findIndex((w) => wishlistMatchesIdent(w, identificador)); 
            if (index > -1) { wishlist.splice(index, 1); } 
            else { 
                const prod = findProductByIdent(identificador); 
                if (prod) { wishlist.push(prod); radarNinja('Añadir a Wishlist', prod.nombre, prod.categoria); } 
            }
            localStorage.setItem('improgyp_wishlist', JSON.stringify(wishlist)); actualizarUIWishlist(); if(desdeModal) { abrirModalProducto(identificador); }
            if (improgypGridMode === 'ai') return;
            let prodFilt = catalogoCompleto;
            if (improgypGridMode === 'wishlist') {
                prodFilt = wishlist;
            } else {
                const catActiva = document.querySelector('.cat-pill.active') ? document.querySelector('.cat-pill.active').getAttribute('data-cat') : 'Todos';
                if (catActiva !== 'Todos') prodFilt = catalogoCompleto.filter(p => p.categoria === catActiva);
            }
            renderizarGrid(prodFilt);
        }
        function actualizarUIWishlist() {
            const badge = document.getElementById('wishlist-badge'); const container = document.getElementById('wishlist-items-container');
            if (wishlist.length > 0) {
                badge.innerText = wishlist.length; badge.classList.remove('hidden'); let html = '';
                [...wishlist].reverse().forEach(prod => { let precioBase = "Consultar"; if(prod.presentaciones && prod.presentaciones.length > 0) { const p = prod.presentaciones[0].precio.split('|')[0].trim(); if(p) precioBase = p; }
                    
                    // --- IMPLEMENTACIÓN DE DOMINIO ABSOLUTO ---
                    const imgUrl = getAbsoluteImgUrl(prod.imagen); 
                    // ------------------------------------------

                    const safeIdentificador = (prod.codigo || prod.nombre).replace(/'/g, "\\'").replace(/"/g, "&quot;");
                    html += `
                    <div class="wishlist-item group">
                        <img src="${imgUrl}" alt="${prod.nombre}" class="cursor-pointer" onclick="abrirModalProducto('${safeIdentificador}')" onerror="this.onerror=null; this.src='favicon-app.png?v=5';">
                        <div class="wishlist-item-info cursor-pointer" onclick="abrirModalProducto('${safeIdentificador}')">
                            <div class="wishlist-item-title">${prod.nombre}</div>
                            <div class="text-[9px] text-slate-400 font-bold">${prod.codigo ? 'REF: ' + prod.codigo : ''}</div>
                            <div class="wishlist-item-price">${(precioBase !== "Consultar" && !precioBase.toString().includes('$')) ? '$' + precioBase : precioBase}</div>
                        </div>
                        <div class="flex gap-1">
                            <button class="bg-slate-100 text-slate-400 hover:bg-rose-500 hover:text-white p-2 rounded-lg transition-colors" onclick="toggleWishlist('${safeIdentificador}');" title="Eliminar"><i class="fa-solid fa-trash-can text-[10px]"></i></button>
                            <button class="bg-[#1B263B]/10 text-[#1B263B] hover:bg-[#1B263B] hover:text-white p-2 rounded-lg transition-colors" onclick="agregarAlCarrito('${safeIdentificador}');" title="Añadir a bolsa"><i class="fa-solid fa-cart-plus text-[10px]"></i></button>
                        </div>
                    </div>`;
                }); container.innerHTML = html;
            } else { badge.classList.add('hidden'); container.innerHTML = `<div class="wishlist-empty"><i class="fa-regular fa-heart text-3xl text-slate-200 mb-3 block"></i>Aún no tienes herramientas favoritos.</div>`; }
            if (badge) badge.setAttribute('aria-hidden', wishlist.length ? 'false' : 'true');
        }
        function toggleWishlistModal(e) {
            if (e) e.stopPropagation();
            const modal = document.getElementById('wishlist-modal');
            const btn = document.getElementById('wishlist-trigger-btn');
            const backdrop = document.getElementById('wishlist-backdrop');
            if (!modal) return;
            if (typeof hideMegaMenu === 'function') hideMegaMenu();
            const willOpen = !modal.classList.contains('show');
            if (willOpen) {
                actualizarUIWishlist();
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                if (btn) btn.setAttribute('aria-expanded', 'true');
                if (backdrop && window.matchMedia('(max-width: 767px)').matches) {
                    backdrop.classList.add('open');
                    document.body.style.overflow = 'hidden';
                }
            } else if (typeof window.improgypCloseWishlistModal === 'function') {
                window.improgypCloseWishlistModal();
            } else {
                modal.classList.remove('show');
            }
        }

        function actualizarTextos(categoria) {
            const data = textosMarketing[categoria] || textosMarketing['Todos'] || {};
            const tituloEl = document.getElementById('titulo-principal');
            const subtituloEl = document.getElementById('subtitulo-principal');
            if (data.tit && tituloEl) tituloEl.innerHTML = data.tit;
            if (data.sub && subtituloEl) subtituloEl.textContent = data.sub;
            
            const mobileLabel = document.getElementById('mobile-cat-label');
            if(mobileLabel) mobileLabel.textContent = categoria === 'Todos' ? 'Todos los productos' : categoria;
        }
        function mostrarWishlistCompleta() {
            improgypGridMode = 'wishlist';
            document.getElementById('wishlist-modal').classList.remove('show'); document.getElementById('titulo-principal').innerHTML = `Tus herramientas <br class="hidden md:block"> <span class="laser-text">Deseados.</span>`; document.getElementById('subtitulo-principal').innerHTML = 'Tu selección personal lista para comprar.';
            document.querySelectorAll('.cat-pill').forEach(btn => { btn.classList.remove('active', 'border-transparent'); btn.classList.add('border-slate-200'); }); document.querySelectorAll('.bs-cat-btn').forEach(btn => btn.classList.remove('active')); document.getElementById('mobile-cat-label').innerText = "Deseados";
            renderizarGrid(wishlist); window.scrollTo({ top: 0, behavior: 'smooth' }); cerrarBurbujaIA();
        }

        function filtrarCategoria(categoriaSeleccionada) {
            improgypGridMode = 'catalog';
            cerrarBurbujaIA(); localStorage.setItem('improgyp_memoria_cat', categoriaSeleccionada); 
            // Limpiar buscadores al cambiar de categoría
            document.querySelectorAll('.omni-input-field').forEach(el => el.value = ''); 
            document.getElementById('mobile-cat-label').innerText = categoriaSeleccionada === 'Todos' ? 'Todos los productos' : categoriaSeleccionada;
            document.querySelectorAll('.cat-pill').forEach(btn => {
                if(btn.getAttribute('data-cat') === categoriaSeleccionada) { btn.classList.add('active', 'border-transparent'); btn.classList.remove('border-slate-200'); btn.scrollIntoView({ behavior: "smooth", block: "nearest", inline: "center" }); } 
                else { btn.classList.remove('active', 'border-transparent'); btn.classList.add('border-slate-200'); }
            });
            document.querySelectorAll('.bs-cat-btn').forEach(btn => { if(btn.getAttribute('data-cat') === categoriaSeleccionada) { btn.classList.add('active'); } else { btn.classList.remove('active'); } });
            
            // RESET MARCAS (Filtro Único)
            marcaActiva = 'Todas';
            document.querySelectorAll('.brand-pill').forEach(btn => {
                if(btn.getAttribute('data-brand') === 'Todas') btn.classList.add('active');
                else btn.classList.remove('active');
            });

            const sheet = document.getElementById('category-bottom-sheet'); if(sheet && sheet.classList.contains('show')) { toggleBottomSheet(); }
            const tituloH1 = document.getElementById('titulo-principal'); const subtitulo = document.getElementById('subtitulo-principal'); tituloH1.classList.remove('fade-in'); void tituloH1.offsetWidth; tituloH1.classList.add('fade-in');
            const textData = textosMarketing[categoriaSeleccionada]; const fallbackData = textosMarketing['Todos'];
            if(textData && textData.tit) { tituloH1.innerHTML = textData.tit; subtitulo.innerHTML = textData.sub; } else if(fallbackData && fallbackData.tit) { tituloH1.innerHTML = fallbackData.tit; subtitulo.innerHTML = fallbackData.sub; } 
            else { tituloH1.innerHTML = `Lo mejor en ${categoriaSeleccionada} <br class="hidden md:block"> para <span class="laser-text">resultados reales.</span>`; subtitulo.innerHTML = `Explora nuestra selección especializada de ${categoriaSeleccionada}.`; }
            if(categoriaSeleccionada !== 'Todos') {
                localStorage.removeItem('improgyp_ai_cat'); // Al navegar manual, bajamos prioridad de IA
            }
            let prodFilt = categoriaSeleccionada !== 'Todos' ? catalogoCompleto.filter(p => p.categoria === categoriaSeleccionada) : catalogoCompleto;
            renderizarGrid(prodFilt); window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function filtrarMarca(marca) {
            improgypGridMode = 'catalog';
            cerrarBurbujaIA();
            marcaActiva = marca;
            
            // UI Update Marcas
            document.querySelectorAll('.brand-pill').forEach(btn => {
                if(btn.getAttribute('data-brand') === marca) btn.classList.add('active');
                else btn.classList.remove('active');
            });

            // UI Update Categorías (Reset to Todos - Filtro Único)
            if(marca !== 'Todas') {
                document.querySelectorAll('.cat-pill').forEach(btn => {
                    if(btn.getAttribute('data-cat') === 'Todos') { btn.classList.add('active', 'border-transparent'); btn.classList.remove('border-slate-200'); }
                    else { btn.classList.remove('active', 'border-transparent'); btn.classList.add('border-slate-200'); }
                });
                document.querySelectorAll('.bs-cat-btn').forEach(btn => {
                    if(btn.getAttribute('data-cat') === 'Todos') btn.classList.add('active');
                    else btn.classList.remove('active');
                });
                document.getElementById('mobile-cat-label').innerText = 'Todos';
                localStorage.setItem('improgyp_memoria_cat', 'Todos');
            }

            let prodFilt = marca !== 'Todas' ? catalogoCompleto.filter(p => p.marca === marca) : catalogoCompleto;
            renderizarGrid(prodFilt);
            window.scrollTo({ top: 0, behavior: 'smooth' });

            radarNinja('Filtrar Marca', marca);
        }

        function clickAdCard(linkAd) {
            if(!linkAd) return;
            if(linkAd.startsWith('http')) { window.open(linkAd, '_blank'); }
            else { abrirModalProducto(linkAd); }
        }

        // ==========================================
        // RENDERIZADO DEL CATÁLOGO Y PAUTAS
        // ==========================================
        // RENDERIZADO DEL CATÁLOGO Y PAUTAS
        function renderizarGrid(arrayProductos) {
            ultimosProductosRenderizados = arrayProductos;
            const productosUnicos = arrayProductos.filter((prod, index, self) => {
                const key = (prod.codigo && String(prod.codigo).trim()) || prod.nombre;
                return index === self.findIndex((p) => {
                    const k2 = (p.codigo && String(p.codigo).trim()) || p.nombre;
                    return k2 === key;
                });
            });
            
            // --- SMART SORTING ---
            productosUnicos.sort((a, b) => {
                const aBoost = datosRanking.impulsados.includes(a.nombre) ? 1 : 0;
                const bBoost = datosRanking.impulsados.includes(b.nombre) ? 1 : 0;
                if (aBoost !== bBoost) return bBoost - aBoost;
                const aTrend = (datosRanking.tendencias || []).find(t => t.nombre === a.nombre)?.clics || 0;
                const bTrend = (datosRanking.tendencias || []).find(t => t.nombre === b.nombre)?.clics || 0;
                if (aTrend !== bTrend) return bTrend - aTrend;
                return 0;
            });

            const grid = document.getElementById('grid-productos');
            if (productosUnicos.length === 0) { grid.innerHTML = `<div class="col-span-full text-center py-16 text-slate-500 font-medium">No hay productos para mostrar.</div>`; return; }

            // POOL DE ANUNCIOS ACTIVOS (Videos + Banners)
            let activeVideos = (adData.videos || []).filter(v => v.activo && v.pos);
            let b2bInyectado = false;
            const posB2b = (adData && adData.b2b_pos) ? parseInt(adData.b2b_pos) : 0;
            const ROMPETRAFICO_COOLDOWN_FILAS = 2;

            let htmlBuffer = '';
            let totalItemsEnGrid = 0; // Productos + Videos + B2B
            let productosEnGrid = 0;
            let ultimaFilaRompet = 0;

            const getCols = () => {
                const w = window.innerWidth;
                if (w >= 1024) return 3;
                if (w >= 768) return 2;
                return 2; 
            };
            const currentCols = getCols();

            const initRompetraficoBanners = (list, cols) => {
                const estilosValidos = ['respiracion', 'split', 'marquee', 'glass'];
                return (list || []).filter(b => b.activo).map((b, idx) => {
                    let cadaNFilas = parseInt(b.cada_n_filas, 10);
                    if (!cadaNFilas || cadaNFilas < 1) {
                        const legacyCadaN = parseInt(b.cada_n, 10);
                        const legacyPos = parseInt(b.pos, 10);
                        if (legacyCadaN >= 1) cadaNFilas = Math.max(1, Math.ceil(legacyCadaN / cols));
                        else if (legacyPos >= 1) cadaNFilas = legacyPos;
                        else cadaNFilas = 4;
                    }
                    cadaNFilas = Math.min(20, Math.max(1, cadaNFilas));
                    const stagger = idx * 2;
                    return {
                        ...b,
                        estilo: estilosValidos.includes(b.estilo) ? b.estilo : 'respiracion',
                        cada_n_filas: cadaNFilas,
                        _nextAtFilas: cadaNFilas + stagger,
                        _pendingEnd: true,
                        _priority: idx
                    };
                });
            };
            let activeBanners = initRompetraficoBanners(adData.banners || [], currentCols);

            const renderVideoCard = (data) => {
                const textoDestino = data.link ? "Ver Promoción" : "Saber Más";
                const safeLink = data.link ? data.link.replace(/'/g, "\\'") : '';
                const videoUrl = getAbsoluteImgUrl(data.url);
                return `
                <article class="flex flex-col h-full relative overflow-hidden cursor-pointer group shadow-lg rounded-[20px] transition-all duration-300 hover:-translate-y-[5px] hover:shadow-2xl border border-slate-700 hover:border-[#1B263B]/50 bg-black min-h-[320px]" onclick="clickAdCard('${safeLink}')">
                    <video src="${videoUrl}" autoplay loop muted playsinline preload="lazy" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 pointer-events-none z-0"></video>
                    <div class="absolute top-4 left-4 bg-[#1B263B] text-white text-[10px] font-black px-3 py-1 rounded-md shadow-lg z-20 uppercase tracking-widest flex items-center gap-1">
                        <i class="fa-solid fa-star"></i> DESTACADO
                    </div>
                    <div class="absolute inset-x-0 bottom-0 p-4 z-20 flex flex-col justify-end">
                        <button class="w-full bg-slate-900/90 backdrop-blur-sm text-white hover:bg-[#1B263B] hover:text-slate-900 font-bold text-[13px] py-2.5 rounded-xl transition-all flex items-center justify-center gap-1.5 h-10 md:h-9">
                            <i class="fa-solid fa-bolt text-current text-sm"></i> <span>${textoDestino}</span>
                        </button>
                    </div>
                </article>`;
            };

            const renderRompetrafico = (data) => {
                const etiqueta = escapeHtml(data.etiqueta || 'PROMO');
                const titulo = escapeHtml(data.titulo || '');
                const desc = escapeHtml(data.desc || '');
                const link = data.link || '';
                const onclickAttr = link ? `onclick="window.open('${escapeJsString(link)}', '_blank')"` : '';
                const estilo = data.estilo || 'respiracion';
                const imgUrl = data.img_url ? getAbsoluteImgUrl(data.img_url) : '';
                const ctaLabel = escapeHtml((data.extra && String(data.extra).trim()) ? String(data.extra).trim() : 'Ver más');
                const ctaHtml = link ? `<span class="rt-cta">${ctaLabel} <i class="fa-solid fa-arrow-right text-[10px]"></i></span>` : '';
                const pillIconForRespiracion = () => {
                    const hay = `${data.etiqueta || ''} ${data.titulo || ''} ${data.desc || ''}`.toLowerCase();
                    if (/env[ií]o|entrega|cobertura|nacional|log[ií]stica/.test(hay)) return 'fa-truck-fast';
                    if (/calidad|premium|certif/.test(hay)) return 'fa-award';
                    return 'fa-bolt';
                };

                const marqueePhrase = (parts) => {
                    const items = parts.filter(Boolean);
                    if (!items.length) return '';
                    const chunk = items.map(t => `<span class="rt-marquee-item">${t}</span><span class="rt-marquee-sep">/</span>`).join('');
                    return `<div class="rt-marquee-track">${chunk}${chunk}</div>`;
                };

                let inner = '';
                if (estilo === 'split') {
                    const bgStyle = imgUrl ? `style="background-image:url('${imgUrl.replace(/'/g, "%27")}')"` : 'style="background:linear-gradient(135deg,#1B263B,#334155)"';
                    inner = `<div class="rt-wrap rt-split cursor-pointer" ${onclickAttr}>
                        <div class="rt-split-text">
                            <span class="rt-pill"><i class="fa-solid fa-bolt"></i> ${etiqueta}</span>
                            <h3 class="rt-title">${titulo}</h3>
                            ${desc ? `<p class="rt-desc">${desc}</p>` : ''}
                            ${ctaHtml}
                        </div>
                        <div class="rt-split-img" ${bgStyle} role="presentation"></div>
                    </div>`;
                } else if (estilo === 'marquee') {
                    const line1 = marqueePhrase([etiqueta, titulo]);
                    const line2 = marqueePhrase([desc, titulo, etiqueta]);
                    inner = `<div class="rt-wrap rt-marquee-wrap cursor-pointer" ${onclickAttr}>
                        <div class="rt-marquee-band rt-marquee-band--red">${line1}</div>
                        <div class="rt-marquee-band rt-marquee-band--dark">${line2}</div>
                    </div>`;
                } else if (estilo === 'glass') {
                    const thumb = imgUrl ? `<div class="rt-glass-thumb" style="background-image:url('${imgUrl.replace(/'/g, "%27")}')"></div>` : '';
                    inner = `<div class="rt-wrap rt-glass-ad cursor-pointer" ${onclickAttr}>
                        ${thumb}
                        <div class="rt-glass-body">
                            <span class="rt-pill"><i class="fa-solid fa-star"></i> ${etiqueta}</span>
                            <h3 class="rt-title">${titulo}</h3>
                            ${desc ? `<p class="rt-desc">${desc}</p>` : ''}
                            ${ctaHtml}
                        </div>
                    </div>`;
                } else {
                    inner = `<div class="rt-wrap rt-respiracion cursor-pointer" ${onclickAttr}>
                        <span class="rt-pill"><i class="fa-solid ${pillIconForRespiracion()}"></i> ${etiqueta}</span>
                        <h3 class="rt-title">${titulo}</h3>
                        ${desc ? `<p class="rt-desc">${desc}</p>` : ''}
                        ${ctaHtml}
                    </div>`;
                }
                return `<div class="col-span-full mt-6 mb-6">${inner}</div>`;
            };

            const inyectarRompetraficosEnFila = (filaActual) => {
                const candidatos = activeBanners.filter(b => filaActual >= b._nextAtFilas);
                if (!candidatos.length) return;
                if (ultimaFilaRompet > 0 && (filaActual - ultimaFilaRompet) < ROMPETRAFICO_COOLDOWN_FILAS) {
                    candidatos.forEach(b => { b._nextAtFilas = filaActual + ROMPETRAFICO_COOLDOWN_FILAS; });
                    return;
                }
                candidatos.sort((a, b) => a._priority - b._priority);
                const winner = candidatos[0];
                htmlBuffer += renderRompetrafico(winner);
                winner._nextAtFilas = filaActual + winner.cada_n_filas;
                winner._pendingEnd = false;
                ultimaFilaRompet = filaActual;
                candidatos.slice(1).forEach(b => {
                    b._nextAtFilas = filaActual + ROMPETRAFICO_COOLDOWN_FILAS;
                });
            };


            const renderB2BCard = () => {
                const etiqueta = adData.b2b_etiqueta || 'MAYORISTAS';
                const btnText = adData.b2b_btn || 'Acceder al Portal';
                const bgImg = adData.b2b_img_url ? getAbsoluteImgUrl(adData.b2b_img_url) : 'favicon-app.png?v=5';
                return `
                <article class="flex flex-col h-full relative overflow-hidden cursor-pointer group shadow-lg rounded-[20px] transition-all duration-300 hover:-translate-y-[5px] hover:shadow-2xl border border-slate-200 bg-slate-900 min-h-[320px]" onclick="window.location.href='b2b/'">
                    <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105 z-0" style="background-image: url('${bgImg}');"></div>
                    <div class="absolute top-4 left-4 bg-amber-500 text-slate-900 text-[10px] font-black px-3 py-1 rounded-md shadow-lg z-20 uppercase tracking-widest flex items-center gap-1">
                        <i class="fa-solid fa-crown"></i> ${etiqueta}
                    </div>
                    <div class="absolute inset-x-0 bottom-0 p-4 z-20 flex flex-col justify-end">
                        <button class="w-full bg-slate-900/90 backdrop-blur-sm text-white hover:bg-[#1B263B] hover:text-slate-900 font-bold text-[13px] py-2.5 rounded-xl transition-all flex items-center justify-center gap-1.5 h-10 md:h-9 shadow-lg">
                            <i class="fa-solid fa-right-to-bracket text-sm"></i> <span>${btnText}</span>
                        </button>
                    </div>
                </article>`;
            };

            // PROCESAR PRODUCTOS
            productosUnicos.forEach((prod, index) => {
                // 1. Verificar Inyección de VIDEOS y B2B por posición absoluta de CARD
                let adInyectadoEnEsteSlot = false;
                do {
                    adInyectadoEnEsteSlot = false;
                    const cardActual = totalItemsEnGrid + 1;
                    const vAd = activeVideos.find(v => parseInt(v.pos) === cardActual);
                    if (vAd) {
                        htmlBuffer += renderVideoCard(vAd);
                        totalItemsEnGrid++;
                        adInyectadoEnEsteSlot = true;
                        activeVideos = activeVideos.filter(v => v !== vAd);
                    }
                    if (adData && adData.b2b_activo && window.IMPROGYP_B2B_PUBLICO !== false && !b2bInyectado && posB2b === cardActual) {
                        htmlBuffer += renderB2BCard();
                        totalItemsEnGrid++;
                        b2bInyectado = true;
                        adInyectadoEnEsteSlot = true;
                    }
                } while (adInyectadoEnEsteSlot);

                // 2. Inyectar el PRODUCTO actual
                let precioBase = "Consultar"; 
                if(prod.presentaciones && prod.presentaciones.length > 0 && prod.presentaciones[0].precio) { 
                    const p = prod.presentaciones[0].precio.split('|')[0].trim(); 
                    if(p) precioBase = p; 
                }
                const imgUrl = getAbsoluteImgUrl(prod.imagen); 
                let descCorta = "Herramienta nutricional."; if(prod.desc_larga) { descCorta = prod.desc_larga.substring(0, 65) + "..."; }
                const safeIdentificador = escapeJsString(productIdent(prod));
                const enWishlist = wishlist.some((w) => wishlistMatchesProduct(w, prod));
                const btnClass = enWishlist ? 'active' : ''; const iconClass = enWishlist ? 'fa-solid' : 'fa-regular';
                const idGridBtn = getSafeId(productIdent(prod)); 
                let fomoBadgeHTML = ''; 
                const impulsado = (datosRanking.impulsados || []).includes(prod.nombre);
                const tendenciaData = (datosRanking.tendencias || []).find(t => t.nombre === prod.nombre);

                if (impulsado) {
                    fomoBadgeHTML = `<div class="absolute top-[10px] left-[10px] bg-[#1B263B]/90 backdrop-blur-md text-white text-[10px] font-black px-2 py-1 rounded-md shadow-lg z-10 flex items-center gap-1 border border-[#1B263B]"><i class="fa-solid fa-bolt-lightning text-white"></i> TOP</div>`;
                } else if (tendenciaData && tendenciaData.clics > 1) {
                    fomoBadgeHTML = `<div class="absolute top-[10px] left-[10px] bg-rose-500/90 backdrop-blur-md text-white text-[10px] font-black px-2 py-1 rounded-md shadow-lg z-10 flex items-center gap-1 border border-rose-400"><i class="fa-solid fa-fire text-yellow-300"></i> TENDENCIA</div>`;
                }

                const attrIdent = escapeHtml(productIdent(prod));
                htmlBuffer += `
                    <article class="glass-card" data-product-ident="${attrIdent}">
                        <div class="product-img-wrapper" onclick="abrirModalProducto('${safeIdentificador}')">
                            ${fomoBadgeHTML ? fomoBadgeHTML : `<span class="badge bg-white/90 text-slate-500 hidden sm:block">${prod.categoria}</span>`}
                            <button class="btn-wishlist ${btnClass}" onclick="event.stopPropagation(); toggleWishlist('${safeIdentificador}', this)" title="Añadir a deseos"><i class="${iconClass} fa-heart"></i></button>
                            <img src="${imgUrl}" alt="${prod.nombre}" class="product-img cursor-pointer" onerror="this.onerror=null; this.src='favicon-app.png?v=5';">
                        </div>
                        <div class="flex flex-col flex-grow cursor-pointer" onclick="abrirModalProducto('${safeIdentificador}')">
                            <span class="brand-label">${prod.marca || 'IMPROGYP'}</span>
                            <h4 class="text-slate-900 font-bold mb-1 text-[14px] md:text-base leading-tight line-clamp-2 min-h-[36px] flex items-center md:h-auto">${prod.nombre}</h4>
                            ${prod.codigo ? `<span class="sku-label self-start mb-2">${prod.codigo}</span>` : ''}
                            <p class="hidden md:block text-[14px] text-slate-500 mb-3 flex-grow leading-relaxed">${descCorta}</p>
                            <div class="flex justify-between items-end mt-auto mb-3">
                                <div><p class="text-[9px] md:text-[10px] text-slate-400 font-bold uppercase mb-0.5">Precio</p><span class="text-[15px] md:text-[17px] font-black text-slate-800">${(precioBase !== "Consultar" && !precioBase.toString().includes('$')) ? '$' + precioBase : precioBase}</span></div>
                            </div>
                        </div>
                        <div id="${idGridBtn}" class="w-full mt-auto"></div>
                    </article>`;
                
                totalItemsEnGrid++;
                productosEnGrid++;
                if (productosEnGrid % currentCols === 0) {
                    inyectarRompetraficosEnFila(productosEnGrid / currentCols);
                }
            });

            // COMPLETO LA ÚLTIMA FILA CON ESPACIOS VACÍOS SI ES NECESARIO
            const resto = totalItemsEnGrid % currentCols;
            if (resto > 0) {
                const faltantes = currentCols - resto;
                for (let i = 0; i < faltantes; i++) {
                    htmlBuffer += `<div class="hidden md:block"></div>`;
                }
            }


            // 4. Fallback B2B / videos / rompetráfico pendiente
            if (adData && adData.b2b_activo && window.IMPROGYP_B2B_PUBLICO !== false && !b2bInyectado) {
                htmlBuffer += renderB2BCard();
                totalItemsEnGrid++;
            }
            if (activeVideos.length > 0) {
                activeVideos.sort((a, b) => (parseInt(a.pos, 10) || 999) - (parseInt(b.pos, 10) || 999));
                htmlBuffer += renderVideoCard(activeVideos[0]);
                totalItemsEnGrid++;
            }
            const pendientesRt = activeBanners.filter(b => b._pendingEnd).sort((a, b) => a._priority - b._priority);
            if (pendientesRt.length > 0 && productosEnGrid > 0) {
                htmlBuffer += renderRompetrafico(pendientesRt[0]);
            }

            // 5. BANNER IA AL FINAL (Estado Original)
            if (adData && adData.ia_activo) {
                const etiqueta = adData.ia_etiqueta || 'Asesoría Gratuita';
                const titulo = adData.ia_titulo || '¿No encuentras lo que buscas?';
                const desc = adData.ia_desc || 'Deja que nuestro Asesor IA analice tus objetivos y te recomiende el combo perfecto en segundos.';
                const btnText = adData.ia_btn || 'Consultar a la IA';
                htmlBuffer += `
                <div class="col-span-full mt-8 mb-6 cursor-pointer" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); setTimeout(()=>{ const el = document.querySelector('.omni-input-field'); if (el) el.focus(); }, 500);">
                    <div class="glass-panel p-8 rounded-3xl text-center border border-[#1B263B]/30 shadow-[0_15px_40px_rgba(122,193,66,0.1)] relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[#1B263B]/5 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                        <span class="inline-block bg-[#1B263B]/10 text-[#1B263B] text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest mb-4 border border-[#1B263B]/20"><i class="fa-solid fa-robot"></i> ${etiqueta}</span>
                        <h3 class="text-2xl md:text-3xl font-black text-slate-900 mb-3"><span class="laser-text">${titulo}</span></h3>
                        <p class="text-slate-500 text-sm max-w-lg mx-auto mb-6">${desc}</p>
                        <button class="bg-slate-900 text-white hover:bg-[#1B263B] hover:text-slate-900 font-bold py-3.5 px-8 rounded-xl transition-all inline-flex items-center gap-2 shadow-lg">
                            <i class="fa-solid fa-magnifying-glass-chart"></i> ${btnText}
                        </button>
                    </div>
                </div>`;
            }

            grid.innerHTML = htmlBuffer; actualizarBotonesGrid(); 
        }
    </script>