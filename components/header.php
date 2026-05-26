<?php
require_once __DIR__ . '/megamenu_config.php';

$header_config_path = __DIR__ . '/../config_header.json';
$header_data = [];
if (file_exists($header_config_path)) {
    $header_data = json_decode(file_get_contents($header_config_path), true) ?? [];
}

$megamenu_divisions = improgyp_normalize_megamenu($header_data['megamenu'] ?? null);
$megamenu_js_map = improgyp_megamenu_js_map($megamenu_divisions);
$megamenu_first_id = $megamenu_divisions[0]['id'] ?? 'drywall';

$nivel3_menu = $header_data['nivel3_menu'] ?? [];
if (!is_array($nivel3_menu) || empty($nivel3_menu)) {
    $nivel3_menu = improgyp_header_default_nivel3_menu();
}
$header_site_nav = improgyp_header_site_nav_items($nivel3_menu);
$header_current_page = basename($_SERVER['PHP_SELF'] ?? '');

$page = $improgyp_page ?? '';
$is_shop = ($page === 'tienda');
$is_catalog = ($is_shop || basename($_SERVER['PHP_SELF'] ?? '') === 'productos.php');
$logo_href = $is_shop ? 'productos.php' : 'index.php';
?>
<style>
    #main-nav {
        --mega-nav-h: 56px;
        transition: top 0.35s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
    }
    @media (min-width: 768px) {
        #main-nav { --mega-nav-h: 72px; }
    }
    .improgyp-mega-shell {
        position: fixed;
        left: 0;
        right: 0;
        z-index: 2050;
        pointer-events: none;
        isolation: isolate;
    }
    .improgyp-mega-shell.is-open {
        pointer-events: none;
    }
    @media (min-width: 768px) {
        .improgyp-mega-shell {
            top: var(--mega-nav-h, 72px);
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
    }
    @media (max-width: 767px) {
        .improgyp-mega-shell.is-open {
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            max-width: none;
            padding: 0;
        }
    }
    #mega-menu-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1;
        background: rgba(15, 23, 42, 0.5);
        pointer-events: auto;
    }
    #mega-menu-backdrop.open {
        display: block;
    }
    @media (min-width: 768px) {
        #mega-menu-backdrop { display: none !important; }
    }
    .mega-menu-panel {
        display: none;
        flex-direction: column;
        position: relative;
        z-index: 2;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(24px);
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.12);
        pointer-events: auto;
        overflow: hidden;
        -webkit-overflow-scrolling: touch;
        touch-action: manipulation;
    }
    .mega-menu-panel.improgyp-mega-open {
        display: flex !important;
    }
    @media (min-width: 768px) {
        .mega-menu-panel {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            border-radius: 28px;
        }
    }
    @media (max-width: 767px) {
        .mega-menu-panel {
            position: fixed !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            top: auto !important;
            max-height: 92vh;
            border-radius: 28px 28px 0 0;
            border-bottom: none;
            transform: translateY(100%);
            transition: transform 0.38s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .mega-menu-panel.improgyp-mega-open {
            transform: translateY(0);
        }
    }
    .mega-menu-scroll {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
    }
    .mega-menu-body {
        display: grid;
        grid-template-columns: 1fr;
        min-height: 0;
    }
    @media (min-width: 768px) {
        .mega-menu-scroll { flex: none; overflow: visible; }
        .mega-menu-body {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            min-height: 440px;
        }
    }
    .sidebar-tab.active {
        background: #fff;
        color: #1B263B;
        border-color: #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }
    .submenu-item { display: block; padding: 4px 0; text-decoration: none; }
    .mega-site-footer {
        flex-shrink: 0;
        border-top: 1px solid rgba(226, 232, 240, 0.9);
        background: rgba(248, 250, 252, 0.95);
    }
    @media (max-width: 767px) {
        .mega-site-footer {
            position: sticky;
            bottom: 0;
            z-index: 12;
            padding: 1rem 1rem max(1rem, env(safe-area-inset-bottom));
            box-shadow: 0 -10px 28px rgba(15, 23, 42, 0.08);
        }
        .mega-site-nav-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem 1rem;
            width: 100%;
        }
        .mega-sidebar-desktop { display: none !important; }
        #megamenu-content-container { display: none !important; }
        .mega-accordion-mobile { display: block; padding: 0.75rem 1rem 0.5rem; }
        .mega-accordion-item { border-bottom: 1px solid rgba(226, 232, 240, 0.9); }
        .mega-accordion-item:last-child { border-bottom: none; }
        .mega-accordion-trigger {
            width: 100%;
            text-align: left;
            padding: 0.875rem 0.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            background: transparent;
            border: none;
            cursor: pointer;
        }
        .mega-accordion-trigger .mega-acc-chevron {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            color: #94a3b8;
            font-size: 10px;
        }
        .mega-accordion-item.is-open .mega-accordion-trigger { color: #3A86FF; }
        .mega-accordion-item.is-open .mega-acc-chevron {
            transform: rotate(90deg);
            color: #3A86FF;
        }
        .mega-accordion-panel {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
        }
        .mega-accordion-item.is-open .mega-accordion-panel { opacity: 1; }
        .mega-accordion-panel-inner {
            padding: 0 0.25rem 1rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .mega-col-aside .mega-aside-desktop { display: none !important; }
        .mega-aside-mobile {
            display: block;
            border-top: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(248, 250, 252, 0.6);
        }
        .mega-col-aside { border-left: none !important; }
    }
    @media (min-width: 768px) {
        .mega-accordion-mobile { display: none !important; }
        .mega-aside-mobile { display: none !important; }
    }
</style>
<nav id="main-nav" class="fixed top-0 w-full z-40 bg-white/80 backdrop-blur-xl border-b border-slate-200/60 nav-transition" data-header-profile="<?= $is_catalog ? 'catalog' : 'default' ?>">
    <div class="relative max-w-[1240px] mx-auto px-4 md:px-6">
        <div class="py-3 md:py-4 flex items-center gap-2 md:gap-4">
            <a href="<?= htmlspecialchars($logo_href) ?>" class="shrink-0" aria-label="IMPROGYP inicio">
                <img src="logo-oscuro.png?v=5" alt="IMPROGYP" class="h-7 md:h-8 object-contain">
            </a>

            <button id="mega-menu-trigger" type="button" onclick="window.toggleMegaMenu(event)" aria-expanded="false" aria-controls="improgyp-mega-menu"
                class="flex-shrink-0 flex items-center gap-1.5 md:gap-2 px-2.5 md:px-4 py-2 bg-white border border-slate-200 text-[#1B263B] font-bold rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-all text-[10px] md:text-[11px] uppercase tracking-wider shadow-sm select-none active:scale-95 max-w-[42vw] md:max-w-none">
                <span class="relative flex h-2 w-2 shrink-0" aria-hidden="true">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#3A86FF] opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-[#3A86FF]"></span>
                </span>
                <span class="truncate"><span class="md:hidden">Explorar</span><span class="hidden md:inline">Explorar Divisiones</span></span>
                <i id="trigger-arrow" class="fa-solid fa-chevron-down text-[8px] text-slate-400 transition-transform duration-300 shrink-0" aria-hidden="true"></i>
            </button>

            <div class="hidden md:flex flex-1 max-w-lg mx-2 min-w-0">
                <?php $omnibar_variant = 'header'; include __DIR__ . '/omnibar_input.php'; ?>
            </div>

            <!-- Compartir, deseos, carrito. B2B, sucursales y sitio → megamenú Explorar -->
            <div class="flex items-center gap-2 md:gap-3 shrink-0 ml-auto">
                <button type="button" onclick="typeof compartirTienda==='function'&&compartirTienda()" class="relative w-9 h-9 md:w-10 md:h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#1B263B] hover:text-[#1B263B] transition-all shadow-sm" title="Compartir tienda" aria-label="Compartir tienda">
                    <i class="fa-solid fa-share-nodes text-[13px] md:text-sm" aria-hidden="true"></i>
                </button>

                <div class="relative">
                    <button type="button" onclick="typeof toggleWishlistModal==='function'&&toggleWishlistModal(event)" class="relative w-9 h-9 md:w-10 md:h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-700 hover:border-rose-400 hover:text-rose-500 transition-all shadow-sm" title="Lista de deseos" aria-label="Mis deseos">
                        <i class="fa-solid fa-heart text-[13px] md:text-sm" aria-hidden="true"></i>
                        <span id="wishlist-badge" class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center shadow-md transition-transform duration-200 hidden" aria-hidden="true">0</span>
                    </button>
                    <div id="wishlist-modal" class="wishlist-dropdown" role="dialog" aria-hidden="true">
                        <div class="wishlist-header">
                            <span>Mis Deseos</span>
                            <button type="button" onclick="typeof toggleWishlistModal==='function'&&toggleWishlistModal(event)" class="text-slate-400 hover:text-rose-500 transition-colors" aria-label="Cerrar lista de deseos">
                                <i class="fa-solid fa-xmark text-base"></i>
                            </button>
                        </div>
                        <div id="wishlist-items-container" class="wishlist-items custom-scrollbar"></div>
                        <div class="wishlist-footer">
                            <a href="productos.php?wishlist=1">Ver lista completa <i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="typeof improgypOpenCart==='function'?improgypOpenCart(event):location.href='productos.php'" class="relative w-9 h-9 md:w-10 md:h-10 rounded-full bg-[#1B263B] border border-[#1B263B] flex items-center justify-center text-white hover:bg-[#3A86FF] transition-all shadow-md shadow-[#1B263B]/30" title="Bolsa de compras" aria-label="Abrir cotización">
                    <i class="fa-solid fa-bag-shopping text-[13px] md:text-sm" aria-hidden="true"></i>
                    <span id="cart-badge" class="absolute -top-1 -right-1 bg-slate-900 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center shadow-md transition-transform duration-200 hidden" aria-hidden="true">0</span>
                </button>
            </div>
        </div>
    </div>
</nav>

<div class="improgyp-mega-shell" id="improgyp-mega-shell" aria-hidden="true">
    <div id="mega-menu-backdrop" aria-hidden="true"></div>

    <div id="improgyp-mega-menu" class="mega-menu-panel improgyp-mega-closed bg-white/95 backdrop-blur-3xl" role="dialog" aria-modal="true" aria-labelledby="improgyp-mega-menu-title" aria-hidden="true">
            <div class="md:hidden flex shrink-0 items-center justify-between px-4 py-3 border-b border-slate-100 bg-white/90 z-10">
                <span id="improgyp-mega-menu-title" class="text-[11px] font-black text-[#1B263B] uppercase tracking-widest">Explorar Divisiones</span>
                <button type="button" onclick="window.hideMegaMenu()" class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-500" aria-label="Cerrar menú">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="mega-menu-scroll custom-scrollbar">
                <div class="mega-menu-body">
                    <!-- Móvil: acordeón -->
                    <div class="mega-accordion-mobile md:hidden" role="region" aria-label="Divisiones">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Divisiones Improgyp</p>
                        <?php foreach ($megamenu_divisions as $mi => $mdiv):
                            $mid = htmlspecialchars($mdiv['id'], ENT_QUOTES, 'UTF-8');
                            $mtitle = htmlspecialchars($mdiv['title'], ENT_QUOTES, 'UTF-8');
                            $miconRaw = $mdiv['icon'] ?? 'fa-tag';
                            $micon = preg_match('/^fa-[a-z0-9\-]+$/i', $miconRaw) ? $miconRaw : 'fa-tag';
                            $micon = htmlspecialchars($micon, ENT_QUOTES, 'UTF-8');
                            $mcolor = htmlspecialchars($mdiv['iconColor'] ?? 'text-slate-400', ENT_QUOTES, 'UTF-8');
                            $accOpen = '';
                            $accExpanded = 'false';
                        ?>
                        <div class="mega-accordion-item<?= $accOpen ?>" data-division-id="<?= $mid ?>">
                            <button type="button" class="mega-accordion-trigger text-xs font-black text-slate-600 uppercase tracking-wide" id="mega-acc-trigger-<?= $mid ?>" aria-expanded="<?= $accExpanded ?>" aria-controls="mega-panel-<?= $mid ?>" onclick="window.toggleMegaAccordion('<?= $mid ?>', event)">
                                <span class="flex items-center gap-2.5 min-w-0">
                                    <i class="fa-solid <?= $micon ?> text-[13px] <?= $mcolor ?> shrink-0"></i>
                                    <span class="truncate"><?= $mtitle ?></span>
                                </span>
                                <i class="fa-solid fa-chevron-right mega-acc-chevron shrink-0" aria-hidden="true"></i>
                            </button>
                            <div class="mega-accordion-panel" id="mega-panel-<?= $mid ?>" role="region" aria-labelledby="mega-acc-trigger-<?= $mid ?>">
                                <div class="mega-accordion-panel-inner" data-panel-content="<?= $mid ?>"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Desktop: sidebar -->
                    <div class="mega-sidebar-desktop bg-slate-50/70 p-4 md:p-6 border-r border-slate-100 flex flex-col gap-1.5">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Divisiones Improgyp</p>
                        <?php foreach ($megamenu_divisions as $mi => $mdiv):
                            $mid = htmlspecialchars($mdiv['id'], ENT_QUOTES, 'UTF-8');
                            $mtitle = htmlspecialchars($mdiv['title'], ENT_QUOTES, 'UTF-8');
                            $miconRaw = $mdiv['icon'] ?? 'fa-tag';
                            $micon = preg_match('/^fa-[a-z0-9\-]+$/i', $miconRaw) ? $miconRaw : 'fa-tag';
                            $micon = htmlspecialchars($micon, ENT_QUOTES, 'UTF-8');
                            $mcolor = htmlspecialchars($mdiv['iconColor'] ?? 'text-slate-400', ENT_QUOTES, 'UTF-8');
                            $activeClass = ($mi === 0) ? ' active' : '';
                        ?>
                        <button type="button" onmouseover="window.showCategoryContent('<?= $mid ?>')" onclick="window.showCategoryContent('<?= $mid ?>')" class="sidebar-tab w-full text-left px-4 py-3 md:py-3.5 rounded-xl text-xs font-black text-slate-500 flex items-center justify-between sidebar-tab-btn hybrid-tab<?= $activeClass ?>" id="tab-<?= $mid ?>">
                            <span class="flex items-center gap-2.5"><i class="fa-solid <?= $micon ?> text-[13px] <?= $mcolor ?>"></i> <?= $mtitle ?></span>
                            <i class="fa-solid fa-chevron-right text-[8px] opacity-60"></i>
                        </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="hidden md:grid md:col-span-2 p-4 md:p-8 grid-cols-1 md:grid-cols-2 gap-5 md:gap-8" id="megamenu-content-container"></div>

                    <!-- Col 4 asistencia -->
                    <div class="mega-col-aside bg-slate-50/40 p-0 md:p-6 border-l border-slate-100 flex flex-col md:justify-between md:gap-5 max-md:border-l-0">
                        <div class="mega-aside-desktop hidden md:flex md:flex-col md:justify-between md:gap-5 md:flex-1 md:w-full">
                            <div class="space-y-4">
                                <div class="p-4 bg-[#3A86FF]/5 border border-[#3A86FF]/10 rounded-2xl">
                                    <p class="text-[9px] font-black text-[#3A86FF] uppercase tracking-widest leading-none mb-1">
                                        <i class="fa-solid fa-compass-drafting mr-1"></i> Asesoría en proyectos
                                    </p>
                                    <h5 class="text-[11px] font-bold text-[#1B263B] leading-tight">¿Necesitas ayuda técnica o una cotización a medida para tu obra?</h5>
                                    <p class="text-[9px] text-slate-500 font-medium mt-1.5 leading-relaxed">Nuestros asesores te ayudan a calcular materiales y elegir el mejor sistema constructivo.</p>
                                </div>
                                <div class="space-y-2.5">
                                    <a href="https://wa.me/593991754887?text=Hola%20IMPROGYP%2C%20necesito%20asesor%C3%ADa%20t%C3%A9cnica" target="_blank" rel="noopener" class="flex items-center justify-between w-full p-3 rounded-xl border border-slate-200 bg-white hover:border-emerald-500 hover:bg-emerald-500/5 transition-all text-left group">
                                        <span class="text-[10px] font-black text-slate-600 uppercase tracking-wider group-hover:text-emerald-600">
                                            <i class="fa-brands fa-whatsapp text-emerald-500 text-xs mr-2"></i> Asesor en línea
                                        </span>
                                        <i class="fa-solid fa-chevron-right text-[9px] text-slate-400 group-hover:text-emerald-500"></i>
                                    </a>
                                    <button type="button" onclick="typeof abrirModalLocales==='function'&&abrirModalLocales(); window.hideMegaMenu();" class="flex items-center justify-between w-full p-3 rounded-xl border border-slate-200 bg-white hover:border-[#3A86FF] hover:bg-blue-600/5 transition-all text-left group">
                                        <span class="text-[10px] font-black text-slate-600 uppercase tracking-wider group-hover:text-[#3A86FF]">
                                            <i class="fa-solid fa-map-location-dot text-[#3A86FF] text-xs mr-2"></i> Nuestras sucursales
                                        </span>
                                        <i class="fa-solid fa-chevron-right text-[9px] text-slate-400 group-hover:text-[#3A86FF]"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mega-b2b-block p-4 bg-slate-100/70 border border-slate-200/50 rounded-2xl text-center">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider leading-tight">¿Compra mayorista?</p>
                                <p class="text-[9px] text-slate-500 font-bold mt-1 mb-2.5">Precios por volumen y stock en tiempo real.</p>
                                <a href="b2b/" class="inline-flex items-center justify-center w-full py-2 bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black rounded-lg uppercase tracking-widest text-[8px] transition-all shadow-sm">
                                    Portal B2B Exclusivo <i class="fa-solid fa-arrow-right-to-bracket ml-1.5"></i>
                                </a>
                            </div>
                        </div>

                        <div class="mega-aside-mobile px-4 py-3">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2.5">Asistencia y tiendas</p>
                            <div class="grid grid-cols-2 gap-2">
                                <a href="https://wa.me/593991754887?text=Hola%20IMPROGYP%2C%20necesito%20asesor%C3%ADa%20t%C3%A9cnica" target="_blank" rel="noopener" class="flex flex-col items-center justify-center gap-1.5 p-2.5 rounded-xl border border-slate-200 bg-white text-center min-h-[4.5rem]">
                                    <i class="fa-brands fa-whatsapp text-emerald-500 text-lg"></i>
                                    <span class="text-[8px] font-black text-slate-600 uppercase tracking-wide leading-tight">WhatsApp</span>
                                </a>
                                <button type="button" onclick="typeof abrirModalLocales==='function'&&abrirModalLocales(); window.hideMegaMenu();" class="flex flex-col items-center justify-center gap-1.5 p-2.5 rounded-xl border border-slate-200 bg-white text-center min-h-[4.5rem]">
                                    <i class="fa-solid fa-map-location-dot text-[#3A86FF] text-base"></i>
                                    <span class="text-[8px] font-black text-slate-600 uppercase tracking-wide leading-tight">Sucursales</span>
                                </button>
                            </div>
                            <a href="b2b/" class="mt-2.5 flex items-center justify-center gap-2 w-full py-2.5 bg-[#1B263B] text-white font-black rounded-xl uppercase tracking-widest text-[9px] hover:bg-[#3A86FF] transition-colors">
                                <i class="fa-solid fa-arrow-right-to-bracket text-[10px]"></i> Portal B2B
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mega-site-footer px-4 md:px-6 py-4 md:py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-4 md:gap-3">
                <a href="productos.php" class="inline-flex items-center gap-2 text-[10px] font-black text-[#1B263B] uppercase tracking-wider hover:text-[#3A86FF] transition-all shrink-0 pb-1 md:pb-0 border-b border-slate-200/80 md:border-b-0">
                    Ver catálogo total <i class="fa-solid fa-arrow-right-long"></i>
                </a>
                <?php if (!empty($header_site_nav)): ?>
                <nav class="mega-site-nav-grid md:!flex md:flex-wrap md:items-center md:gap-x-4 md:gap-y-2" aria-label="Menú principal">
                    <?php foreach ($header_site_nav as $item):
                        $text_lower = mb_strtolower($item['text']);
                        $link_page = basename($item['link'] ?? '');
                        $nav_active = ($header_current_page === $link_page);
                        if ($header_current_page === 'index.php' && strpos($text_lower, 'inicio') === false) {
                            $nav_active = false;
                        }
                        $nav_icon = improgyp_header_site_nav_icon($item['text']);
                    ?>
                    <a href="<?= htmlspecialchars($item['link']) ?>" class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider transition-colors max-md:py-1 <?= $nav_active ? 'text-[#3A86FF]' : 'text-slate-500 hover:text-[#1B263B]' ?>">
                        <i class="fa-solid <?= $nav_icon ?> text-[9px]"></i>
                        <?= htmlspecialchars($item['text']) ?>
                    </a>
                    <?php endforeach; ?>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const categoryDivisionMap = <?= json_encode($megamenu_js_map, JSON_UNESCAPED_UNICODE) ?>;
const megamenuFirstId = <?= json_encode($megamenu_first_id) ?>;
const isLanding = <?= $is_shop ? 'false' : 'true' ?>;

function isMegaMenuMobile() {
    return window.matchMedia('(max-width: 767px)').matches;
}

function buildCategoryContentHtml(catId) {
    const mapping = categoryDivisionMap[catId];
    if (!mapping) return '';
    const renderLinks = (links) => {
        const list = Array.isArray(links) ? links : [];
        return list.map(item => {
            if (typeof item === 'string') {
                item = { name: item, linkType: 'category', linkValue: item };
            }
            const name = item.name || item.linkValue || '';
            const lt = item.linkType === 'search' ? 'search' : 'category';
            const lv = item.linkValue || name;
            return `<li><a href="#" class="submenu-item text-[11px] font-bold text-slate-500 hover:text-[#3A86FF] transition-all" data-link-type="${lt}" data-link-value="${String(lv).replace(/"/g, '&quot;')}" onclick="window.filterMegaMenuLinkFromEl(this, event)">${name}</a></li>`;
        }).join('');
    };
    const linksLeft = mapping.linksLeft || mapping.catsLeft || [];
    const linksRight = mapping.linksRight || mapping.catsRight || [];
    return `<div class="space-y-3"><h5 class="text-[10px] font-black text-[#1B263B] uppercase tracking-widest pb-1 border-b border-slate-100">${mapping.titleLeft}</h5><ul class="space-y-2">${renderLinks(linksLeft)}</ul></div>`
        + `<div class="space-y-3"><h5 class="text-[10px] font-black text-[#1B263B] uppercase tracking-widest pb-1 border-b border-slate-100">${mapping.titleRight}</h5><ul class="space-y-2">${renderLinks(linksRight)}</ul></div>`;
}

function closeMegaAccordionItem(item) {
    if (!item) return;
    const panel = item.querySelector('.mega-accordion-panel');
    const trigger = item.querySelector('.mega-accordion-trigger');
    item.classList.remove('is-open');
    if (trigger) trigger.setAttribute('aria-expanded', 'false');
    if (panel) {
        panel.style.maxHeight = '0';
        panel.style.opacity = '0';
    }
}

function openMegaAccordionItem(item, catId) {
    if (!item) return;
    const inner = item.querySelector('[data-panel-content]');
    const panel = item.querySelector('.mega-accordion-panel');
    const trigger = item.querySelector('.mega-accordion-trigger');
    if (inner) inner.innerHTML = buildCategoryContentHtml(catId);
    item.classList.add('is-open');
    if (trigger) trigger.setAttribute('aria-expanded', 'true');
    if (!panel) return;
    panel.style.opacity = '1';
    panel.style.maxHeight = 'none';
    const targetHeight = panel.scrollHeight;
    panel.style.maxHeight = '0';
    requestAnimationFrame(() => {
        panel.style.maxHeight = targetHeight + 'px';
    });
}

function toggleMegaAccordion(catId, event) {
    if (event) event.stopPropagation();
    if (!isMegaMenuMobile()) {
        showCategoryContent(catId);
        return;
    }
    const item = document.getElementById('mega-panel-' + catId)?.closest('.mega-accordion-item');
    if (!item) return;
    const wasOpen = item.classList.contains('is-open');
    document.querySelectorAll('.mega-accordion-item').forEach(el => closeMegaAccordionItem(el));
    if (!wasOpen) openMegaAccordionItem(item, catId);
}

function showCategoryContent(catId) {
    const mapping = categoryDivisionMap[catId];
    if (!mapping) return;
    if (isMegaMenuMobile()) {
        document.querySelectorAll('.mega-accordion-item').forEach(el => closeMegaAccordionItem(el));
        const item = document.getElementById('mega-panel-' + catId)?.closest('.mega-accordion-item');
        if (item) openMegaAccordionItem(item, catId);
        return;
    }
    const container = document.getElementById('megamenu-content-container');
    if (!container) return;
    container.innerHTML = buildCategoryContentHtml(catId);
    document.querySelectorAll('.sidebar-tab-btn').forEach(tab => tab.classList.remove('active'));
    const activeTab = document.getElementById('tab-' + catId);
    if (activeTab) activeTab.classList.add('active');
}

function filterMegaMenuLinkFromEl(el, event) {
    if (!el) return;
    filterMegaMenuLink(el.getAttribute('data-link-type') || 'category', el.getAttribute('data-link-value') || '', event);
}

function filterMegaMenuLink(linkType, linkValue, event) {
    if (event) event.preventDefault();
    hideMegaMenu();
    const type = linkType === 'search' ? 'search' : 'category';
    const value = (linkValue || '').trim();
    if (!value) return;
    if (type === 'category') {
        if (isLanding) {
            window.location.href = 'productos.php?cat=' + encodeURIComponent(value);
            return;
        }
        if (typeof window.filtrarCategoria === 'function') {
            window.filtrarCategoria(value);
            return;
        }
        window.location.href = 'productos.php?cat=' + encodeURIComponent(value);
        return;
    }
    if (isLanding) {
        window.location.href = 'productos.php?q=' + encodeURIComponent(value);
        return;
    }
    const input = document.querySelector('.omni-input-field');
    if (input) {
        input.value = value;
        if (typeof window.filtrarPorTexto === 'function') window.filtrarPorTexto(value);
    } else {
        window.location.href = 'productos.php?q=' + encodeURIComponent(value);
    }
}

function openMegaMenuMobile() {
    const first = document.querySelector('.mega-accordion-item');
    if (!first) return;
    document.querySelectorAll('.mega-accordion-item').forEach(el => closeMegaAccordionItem(el));
    const id = first.getAttribute('data-division-id') || megamenuFirstId;
    openMegaAccordionItem(first, id);
}

function toggleMegaMenu(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('improgyp-mega-menu');
    const arrow = document.getElementById('trigger-arrow');
    const backdrop = document.getElementById('mega-menu-backdrop');
    const shell = document.getElementById('improgyp-mega-shell');
    if (!menu) return;

    const willOpen = !menu.classList.contains('improgyp-mega-open');
    if (willOpen) {
        const wishlist = document.getElementById('wishlist-modal');
        if (wishlist && wishlist.classList.contains('show')) wishlist.classList.remove('show');

        menu.classList.remove('improgyp-mega-closed');
        menu.classList.add('improgyp-mega-open');
        menu.setAttribute('aria-hidden', 'false');
        if (shell) {
            shell.setAttribute('aria-hidden', 'false');
            shell.classList.add('is-open');
        }
        if (arrow) arrow.style.transform = 'rotate(180deg)';
        if (backdrop && isMegaMenuMobile()) {
            backdrop.classList.add('open');
            backdrop.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }
        const trigger = document.getElementById('mega-menu-trigger');
        if (trigger) trigger.setAttribute('aria-expanded', 'true');

        if (isMegaMenuMobile()) {
            openMegaMenuMobile();
        } else {
            showCategoryContent(megamenuFirstId);
        }
    } else {
        hideMegaMenu();
    }
}

function hideMegaMenu() {
    const menu = document.getElementById('improgyp-mega-menu');
    const arrow = document.getElementById('trigger-arrow');
    const backdrop = document.getElementById('mega-menu-backdrop');
    const shell = document.getElementById('improgyp-mega-shell');
    if (menu) {
        menu.classList.remove('improgyp-mega-open');
        menu.classList.add('improgyp-mega-closed');
        menu.setAttribute('aria-hidden', 'true');
    }
    if (shell) {
        shell.setAttribute('aria-hidden', 'true');
        shell.classList.remove('is-open');
    }
    if (arrow) arrow.style.transform = 'rotate(0deg)';
    if (backdrop) {
        backdrop.classList.remove('open');
        backdrop.setAttribute('aria-hidden', 'true');
    }
    const trigger = document.getElementById('mega-menu-trigger');
    if (trigger) trigger.setAttribute('aria-expanded', 'false');
    if (!document.getElementById('wishlist-modal')?.classList.contains('show')) {
        document.body.style.overflow = '';
    }
}

window.toggleMegaMenu = toggleMegaMenu;
window.hideMegaMenu = hideMegaMenu;
window.showCategoryContent = showCategoryContent;
window.toggleMegaAccordion = toggleMegaAccordion;
window.filterMegaMenuLinkFromEl = filterMegaMenuLinkFromEl;

document.addEventListener('click', (e) => {
    const menu = document.getElementById('improgyp-mega-menu');
    const trigger = document.getElementById('mega-menu-trigger');
    const backdrop = document.getElementById('mega-menu-backdrop');
    if (!menu || !menu.classList.contains('improgyp-mega-open')) return;
    if (menu.contains(e.target)) return;
    if (backdrop && (e.target === backdrop || backdrop.contains(e.target))) {
        hideMegaMenu();
        return;
    }
    if (!trigger || !trigger.contains(e.target)) {
        hideMegaMenu();
    }
}, true);

const improgypMegaMenuEl = document.getElementById('improgyp-mega-menu');
if (improgypMegaMenuEl) {
    improgypMegaMenuEl.addEventListener('click', (e) => e.stopPropagation());
}

if (document.getElementById('mega-menu-backdrop')) {
    document.getElementById('mega-menu-backdrop').addEventListener('click', hideMegaMenu);
}
</script>

<?php include __DIR__ . '/cart_checkout_styles.php'; ?>
<?php include __DIR__ . '/cart_drawer.php'; ?>
<?php include __DIR__ . '/checkout_modal.php'; ?>
<div id="toast-container" aria-live="polite" aria-atomic="true"></div>
