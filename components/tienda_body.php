    <header class="pt-32 pb-10 px-6 max-w-[1000px] mx-auto text-center">
        <h1 id="titulo-principal" class="text-3xl md:text-4xl lg:text-5xl font-black tracking-tight text-slate-900 mb-4 leading-tight fade-in">
            Herramientas profesionales <br class="hidden md:block"> para <span class="laser-text">tu máximo nivel.</span>
        </h1>
        <p id="subtitulo-principal" class="text-slate-500 font-medium text-sm md:text-base max-w-2xl mx-auto fade-in">Explora nuestro catálogo técnico especializado o consulta a nuestro Asesor IA.</p>
    </header>

    <main class="max-w-[1200px] mx-auto px-6 relative z-10 pb-32 md:pb-0">
        <div id="sticky-cat-bar" class="mb-4 sticky top-[var(--mega-nav-h,68px)] z-30 bg-[#f8fafc]/95 backdrop-blur-md pt-2 pb-2 nav-transition space-y-3">
            <div id="category-pills" class="hidden md:flex gap-2 overflow-x-auto w-full scrollbar-hide draggable-container"></div>
            
            <div class="md:hidden w-full px-2">
                <button onclick="toggleBottomSheet()" class="w-full bg-white border border-slate-200 shadow-sm rounded-xl py-3 px-5 flex justify-between items-center text-[13px] font-bold text-slate-700 transition-all active:scale-95">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-layer-group text-[#1B263B] text-base"></i> <span id="mobile-cat-label">Todos</span></span><i class="fa-solid fa-chevron-down text-slate-400"></i>
                </button>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-8 items-start">
            <!-- SIDEBAR (ESCRITORIO) -->
            <aside class="hidden md:block w-[240px] flex-shrink-0 sticky top-[140px] h-fit space-y-8">
                <!-- MARCAS -->
                <div id="sidebar-brand-container" class="fade-in">
                    <span class="text-[11px] font-black text-[#1B263B] uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1B263B]"></span> Marcas
                    </span>
                    <div id="sidebar-brand-pills" class="flex flex-wrap gap-2"></div>
                </div>

                <!-- SUCURSALES (DINÁMICO) -->
                <div id="sidebar-location-container" class="fade-in hidden">
                    <span class="text-[11px] font-black text-[#1B263B] uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1B263B]"></span> Sucursal Cercana
                    </span>
                    <div id="nearest-location-widget"></div>
                    <button onclick="abrirModalLocales()" class="w-full mt-4 text-[11px] font-bold text-slate-400 hover:text-[#0E75AE] transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-map-location-dot"></i> Ver todos los locales
                    </button>
                </div>

                <!-- ASESORÍA TÉCNICA -->
                <div class="bg-blue-50/50 border border-blue-100 rounded-[2rem] p-6 shadow-sm shadow-blue-900/5 fade-in">
                    <h3 class="text-[11px] font-black text-[#1B263B] uppercase tracking-widest mb-4">Asesoría Técnica</h3>
                    <p class="text-[13px] text-slate-500 leading-relaxed mb-6 font-medium">¿Necesitas ayuda con las cantidades para tu obra?</p>
                    <a href="https://wa.me/593991754887" target="_blank" class="text-[11px] font-black text-[#1B263B] hover:text-[#0E75AE] uppercase tracking-widest flex items-center gap-2 group transition-colors">
                        Contactar Soporte <i class="fa-solid fa-arrow-right-long mt-0.5 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </aside>

            <!-- GRID DE PRODUCTOS -->
            <div id="grid-productos" class="flex-grow grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <article class="glass-card min-h-[320px]"><div class="w-full aspect-square rounded-xl skeleton-box mb-4"></div><div class="h-3 skeleton-box rounded w-3/4 mb-3"></div><div class="h-2 skeleton-box rounded w-full mb-1"></div><div class="h-2 skeleton-box rounded w-5/6 mb-4 flex-grow"></div><div class="h-9 skeleton-box rounded-lg w-full mt-auto"></div></article>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/product_modal.php'; ?>

    <div class="bottom-sheet-overlay" id="category-bottom-sheet" onclick="toggleBottomSheet()">
        <div class="bottom-sheet" id="bs-panel" onclick="event.stopPropagation()">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-6 cursor-grab" id="bs-handle"></div>
            
            <div class="flex-grow overflow-y-auto custom-scrollbar pr-1">
                <h3 class="text-base font-black text-slate-800 mb-4 px-1 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-slate-400 text-sm"></i> Categorías
                </h3>
                <div id="mobile-category-list" class="flex flex-col gap-2 pb-4 px-1"></div>

                <div id="mobile-brands-section" class="mt-8 border-t border-slate-100 pt-6 px-1">
                    <h3 class="text-base font-black text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-tags text-slate-400 text-sm"></i> Filtrar por Marca
                    </h3>
                    <div id="mobile-brands-list" class="flex flex-wrap gap-2 pb-10"></div>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (!defined('IMPROGYP_LOCALES_MODAL')) {
        define('IMPROGYP_LOCALES_MODAL', true);
        include __DIR__ . '/locales_modal.php';
    }
    ?>

