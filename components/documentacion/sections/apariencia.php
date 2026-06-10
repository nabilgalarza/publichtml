<?php require __DIR__ . '/_helpers.php';
$base = $docDashboardBase;
doc_section_open('apariencia', 'Apariencia & Home', 'Tres sub-módulos en el menú: Editor del Home, Megamenú B2C y Apariencia del Blog (portada).'); ?>

<div class="doc-grid-3">
    <div class="doc-card">
        <h3><i class="fa-solid fa-house"></i> Editor del Home</h3>
        <p>Hero, secciones destacadas, banners y textos del index. Cambios visibles de inmediato en la portada B2C.</p>
        <?= doc_link('apariencia', 'Abrir Home', '&sub=home') ?>
    </div>
    <div class="doc-card">
        <h3><i class="fa-solid fa-bars-staggered"></i> Megamenú B2C</h3>
        <p>Navegación multinivel: categorías, subcategorías y <strong>nivel 3</strong>. Vista previa de huérfanas y columnas vacías.</p>
        <?= doc_link('apariencia', 'Abrir Megamenú', '&sub=megamenu') ?>
    </div>
    <div class="doc-card">
        <h3><i class="fa-solid fa-newspaper"></i> Apariencia Blog</h3>
        <p>Portada del blog: titulares, orden visual y bloques de entrada antes de publicar artículos en el Gestor Blog.</p>
        <?= doc_link('apariencia', 'Apariencia Blog', '&sub=blog') ?>
    </div>
</div>

<div class="doc-infographic">
    <svg viewBox="0 0 560 160" xmlns="http://www.w3.org/2000/svg">
        <rect x="30" y="50" width="140" height="70" rx="10" fill="#f5f3ff" stroke="#a78bfa"/>
        <text x="100" y="85" text-anchor="middle" font-size="11" font-weight="700" fill="#5b21b6">Nivel 1</text>
        <text x="100" y="102" text-anchor="middle" font-size="9" fill="#7c3aed">Categoría</text>
        <rect x="210" y="50" width="140" height="70" rx="10" fill="#eff6ff" stroke="#60a5fa"/>
        <text x="280" y="85" text-anchor="middle" font-size="11" font-weight="700" fill="#1d4ed8">Nivel 2</text>
        <rect x="390" y="50" width="140" height="70" rx="10" fill="#ecfdf5" stroke="#34d399"/>
        <text x="460" y="85" text-anchor="middle" font-size="11" font-weight="700" fill="#047857">Nivel 3</text>
        <path d="M170 85 H210 M350 85 H390" stroke="#cbd5e1" stroke-width="2"/>
    </svg>
    <p class="doc-caption">Megamenú: hasta tres niveles; enlaces huérfanos se señalan en el editor para corregir antes de publicar.</p>
</div>

<ol class="doc-steps">
    <li>Define primero la estructura del <strong>Megamenú</strong> (árbol de categorías).</li>
    <li>Ajusta el <strong>Home</strong> para que los bloques apunten a categorías reales.</li>
    <li>Personaliza la <strong>portada del blog</strong> si el canal de contenido está activo.</li>
    <li>Prueba en móvil: menú hamburguesa y columnas del megamenú.</li>
</ol>

<div class="doc-callout doc-callout-warn">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>Un enlace del megamenú a categoría vacía muestra listado sin productos. Cruza siempre con Inventario Web.</div>
</div>

<p class="doc-note">Tras guardar el megamenú, revisa la tienda en móvil y escritorio antes de anunciar cambios.</p>
<?php doc_section_close(); ?>
