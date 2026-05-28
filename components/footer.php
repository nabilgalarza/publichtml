<?php
require_once __DIR__ . '/../lib/footer_helpers.php';
$omnibar_show_mobile = true;
include __DIR__ . '/omnibar.php';

$footer_nav = improgyp_footer_nav_items();
$footer_contact = improgyp_footer_matriz_contact();
$footer_wa = $footer_contact['whatsapp'] ?: '593991754887';
$footer_wa_url = 'https://wa.me/' . $footer_wa . '?text=' . rawurlencode('Hola IMPROGYP, necesito asesoría.');
$footer_year = date('Y');
$footer_page = basename($_SERVER['PHP_SELF'] ?? '');
$footer_page_kind = $improgyp_page ?? '';
?>
<footer class="site-footer mt-16 border-t border-slate-200/90 bg-[#0F172A] text-slate-300">
    <div class="max-w-[1200px] mx-auto px-6 py-12 md:py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8">
            <div class="sm:col-span-2 lg:col-span-4" id="nosotros">
                <a href="index.php" class="inline-block mb-4">
                    <img src="logo-claro.png?v=5" alt="IMPROGYP" class="h-8 object-contain" onerror="this.src='logo-oscuro.png?v=5'; this.classList.add('brightness-0','invert');">
                </a>
                <p class="text-[13px] text-slate-400 leading-relaxed mb-5 max-w-sm">
                    Distribuidor de herramientas profesionales para construcción en seco en Ecuador. Catálogo técnico, asesoría y cotización por WhatsApp.
                </p>
                <div class="flex items-center gap-2">
                    <a href="<?= htmlspecialchars($footer_wa_url) ?>" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-white/10 hover:bg-emerald-500/20 text-emerald-400 flex items-center justify-center transition-colors" aria-label="WhatsApp">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                    </a>
                    <a href="productos.php" class="w-9 h-9 rounded-full bg-white/10 hover:bg-[#3A86FF]/20 text-[#3A86FF] flex items-center justify-center transition-colors" aria-label="Tienda">
                        <i class="fa-solid fa-bag-shopping text-sm"></i>
                    </a>
                    <a href="blog.php" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 flex items-center justify-center transition-colors" aria-label="Blog">
                        <i class="fa-solid fa-newspaper text-sm"></i>
                    </a>
                </div>
            </div>

            <div class="lg:col-span-2">
                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-4">Comprar</h4>
                <ul class="space-y-2.5 text-[13px] font-bold">
                    <li><a href="productos.php" class="hover:text-white transition-colors">Catálogo completo</a></li>
                    <li><a href="productos.php" class="hover:text-white transition-colors">Asesor con IA</a></li>
                    <li><a href="productos.php?wishlist=1" class="hover:text-white transition-colors">Lista de deseos</a></li>
                    <li><a href="b2b/" class="hover:text-white transition-colors">Portal mayoristas</a></li>
                </ul>
            </div>

            <div class="lg:col-span-2">
                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-4">Sitio</h4>
                <ul class="space-y-2.5 text-[13px] font-bold">
                    <?php foreach ($footer_nav as $item):
                        $link = $item['link'] ?? '#';
                        $text = $item['text'] ?? '';
                        $icon = improgyp_header_site_nav_icon($text);
                        $active = improgyp_footer_nav_item_active($link, $text, $footer_page, $footer_page_kind);
                    ?>
                    <li>
                        <a href="<?= htmlspecialchars($link) ?>" class="inline-flex items-center gap-2 <?= improgyp_footer_link_class($active) ?>">
                            <i class="fa-solid <?= $icon ?> text-[10px] opacity-70"></i>
                            <?= htmlspecialchars($text) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sm:col-span-2 lg:col-span-4" id="contacto">
                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-4">Contacto</h4>
                <ul class="space-y-3 text-[13px] font-medium text-slate-400 mb-5">
                    <?php if (!empty($footer_contact['telefono'])): ?>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-phone text-[#3A86FF] mt-0.5 w-4"></i>
                        <span><?= htmlspecialchars($footer_contact['telefono']) ?> · Matriz <?= htmlspecialchars($footer_contact['ciudad']) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($footer_contact['email'])): ?>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-envelope text-[#3A86FF] mt-0.5 w-4"></i>
                        <a href="mailto:<?= htmlspecialchars($footer_contact['email']) ?>" class="hover:text-white transition-colors"><?= htmlspecialchars($footer_contact['email']) ?></a>
                    </li>
                    <?php endif; ?>
                    <li class="flex items-start gap-2">
                        <i class="fa-brands fa-whatsapp text-emerald-400 mt-0.5 w-4"></i>
                        <a href="<?= htmlspecialchars($footer_wa_url) ?>" target="_blank" rel="noopener" class="hover:text-white transition-colors font-bold text-emerald-400">Cotizar por WhatsApp</a>
                    </li>
                </ul>
                <div class="flex flex-col sm:flex-row gap-2">
                    <a href="<?= htmlspecialchars($footer_wa_url) ?>" target="_blank" rel="noopener" class="flex-1 inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-black text-[11px] uppercase tracking-wider py-3 px-4 rounded-xl transition-colors">
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp
                    </a>
                    <button type="button" onclick="typeof abrirModalLocales==='function'&&abrirModalLocales()" class="flex-1 inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/15 text-white font-black text-[11px] uppercase tracking-wider py-3 px-4 rounded-xl border border-white/10 transition-colors">
                        <i class="fa-solid fa-map-location-dot text-[#3A86FF]"></i> Sucursales
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-12 pt-6 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
            <p class="text-[11px] text-slate-500 font-medium">
                © <?= $footer_year ?> IMPROGYP · Herramientas profesionales · Ecuador
            </p>
            <div class="flex flex-wrap justify-center gap-4 text-[11px] font-bold">
                <a href="productos.php" class="<?= improgyp_footer_link_class(improgyp_footer_bottom_link_active('tienda', $footer_page, $footer_page_kind), 'text-slate-500 hover:text-white transition-colors') ?>">Tienda</a>
                <a href="blog.php" class="<?= improgyp_footer_link_class(improgyp_footer_bottom_link_active('blog', $footer_page, $footer_page_kind), 'text-slate-500 hover:text-white transition-colors') ?>">Blog</a>
                <a href="b2b/" class="<?= improgyp_footer_link_class(improgyp_footer_bottom_link_active('b2b', $footer_page, $footer_page_kind), 'text-slate-500 hover:text-white transition-colors') ?>">B2B</a>
                <a href="index.php#contacto" class="<?= improgyp_footer_link_class(improgyp_footer_bottom_link_active('contacto', $footer_page, $footer_page_kind), 'text-slate-500 hover:text-white transition-colors') ?>">Contacto</a>
            </div>
        </div>
    </div>
</footer>

<?php improgyp_include_locales_modal_once(); ?>
<?php include __DIR__ . '/checkout_scripts.php'; ?>
