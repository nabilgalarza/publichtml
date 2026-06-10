<?php require __DIR__ . '/init.php'; ?>
<div id="doc-view" class="doc-view relative z-10" data-seccion="<?= htmlspecialchars($docSeccionActiva, ENT_QUOTES, 'UTF-8') ?>" data-modo-completo="<?= $docModoCompleto ? '1' : '0' ?>">
    <div class="doc-view-shell glass-card flex overflow-hidden min-h-[calc(100vh-12rem)] border border-slate-100<?= $docModoCompleto ? ' doc-view-all' : '' ?>">
        <aside class="doc-sidebar custom-scrollbar shrink-0" aria-label="Índice de la guía">
            <h2 class="doc-view-title">Centro de <span>Ayuda</span></h2>
            <p class="doc-version">Guía Maestra · IMPROGYP OS V1</p>
            <input type="search" id="doc-search" class="doc-search" placeholder="Buscar en el índice…" autocomplete="off">

            <nav>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'inicio' ? ' active' : '' ?>" data-doc="inicio"><i class="fa-solid fa-house-chimney"></i> Bienvenida</button>

                <p class="doc-nav-group">Público (B2C)</p>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'radar' ? ' active' : '' ?>" data-doc="radar"><i class="fa-solid fa-chart-pie"></i> Radar de Ventas</button>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'pedidos-publicos' ? ' active' : '' ?>" data-doc="pedidos-publicos"><i class="fa-solid fa-cart-shopping"></i> Pedidos Públicos</button>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'inventario' ? ' active' : '' ?>" data-doc="inventario"><i class="fa-solid fa-boxes-stacked"></i> Inventario Web</button>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'pautas' ? ' active' : '' ?>" data-doc="pautas"><i class="fa-solid fa-bullhorn"></i> Gestor de Pautas</button>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'marketing' ? ' active' : '' ?>" data-doc="marketing"><i class="fa-solid fa-wand-magic-sparkles"></i> Marketing IA</button>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'seo' ? ' active' : '' ?>" data-doc="seo"><i class="fa-solid fa-magnifying-glass-chart"></i> SEO Dinámico</button>

                <p class="doc-nav-group">Apariencia web</p>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'apariencia' ? ' active' : '' ?>" data-doc="apariencia"><i class="fa-solid fa-palette"></i> Apariencia & Home</button>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'blog' ? ' active' : '' ?>" data-doc="blog"><i class="fa-solid fa-pen-nib"></i> Blog</button>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'locales' ? ' active' : '' ?>" data-doc="locales"><i class="fa-solid fa-map-location-dot"></i> Sucursales</button>

                <?php if ($docVerB2b): ?>
                <p class="doc-nav-group">Mayoristas (B2B)</p>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'vip' ? ' active' : '' ?>" data-doc="vip"><i class="fa-solid fa-users-gear"></i> Clientes VIP</button>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'pedidos-b2b' ? ' active' : '' ?>" data-doc="pedidos-b2b"><i class="fa-solid fa-file-invoice-dollar"></i> Pedidos B2B</button>
                <?php endif; ?>

                <p class="doc-nav-group">Sala de máquinas</p>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'sistema' ? ' active' : '' ?>" data-doc="sistema"><i class="fa-solid fa-toggle-on"></i> Estado del Sistema</button>
                <?php if ($docEsMaster): ?>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'usuarios' ? ' active' : '' ?>" data-doc="usuarios"><i class="fa-solid fa-user-shield"></i> Usuarios Admin <span class="doc-nav-badge">Master</span></button>
                <?php endif; ?>
                <button type="button" class="doc-nav-btn<?= $docSeccionActiva === 'faq' ? ' active' : '' ?>" data-doc="faq"><i class="fa-solid fa-circle-question"></i> Preguntas frecuentes</button>
            </nav>

            <div class="doc-sidebar-footer">
                <div class="pill">
                    <strong>IMPROGYP OS V1</strong>
                    <?= $docEsMaster ? 'Perfil master: guía completa.' : 'Perfil gestor: módulos de tu menú.' ?>
                </div>
            </div>
        </aside>

        <div class="doc-main flex-1 flex flex-col min-w-0 min-h-0">
            <header class="doc-view-toolbar">
                <button type="button" id="doc-toggle-view" class="doc-toolbar-btn" aria-pressed="<?= $docModoCompleto ? 'true' : 'false' ?>">
                    <?= $docModoCompleto ? 'Una sección' : 'Ver toda la guía' ?>
                </button>
            </header>
            <div class="doc-body custom-scrollbar flex-1" id="doc-body-scroll">
                <?php
                foreach ($docSectionIds as $id) {
                    $file = $sectionsDir . '/' . $id . '.php';
                    if (is_file($file)) {
                        $docSectionId = $id;
                        include $file;
                    }
                }
                ?>
            </div>
            <footer class="doc-footer-note shrink-0">
                Guía Maestra · IMPROGYP OS V1
            </footer>
        </div>
    </div>
</div>
<script src="components/documentacion/assets/doc-view.js" defer></script>
