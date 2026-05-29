<?php require __DIR__ . '/_helpers.php'; doc_section_open('marketing', 'Marketing IA', 'Textos dinámicos de la tienda (productos.php) generados o refinados con asistencia de IA. Los títulos del home se editan en Apariencia → Editor del Home.'); ?>

<div class="doc-grid-2">
    <div class="doc-card">
        <h3>Qué controla este módulo</h3>
        <p>Copys de listados y fichas en <strong>productos.php</strong>, persistidos en <code>textos_tienda.json</code>. Ideal para temporadas, lanzamientos o tono de marca uniforme.</p>
    </div>
    <div class="doc-card">
        <h3>Qué NO controla</h3>
        <p>Hero, bloques y encabezados del <strong>index</strong> viven en <strong>Apariencia → Editor del Home</strong>. No mezcles responsabilidades entre vistas.</p>
    </div>
</div>

<ol class="doc-steps">
    <li>Abre <strong>Marketing IA</strong>.</li>
    <li>Selecciona el bloque de texto a mejorar.</li>
    <li>Usa la IA como borrador; revisa precios, stock y claims legales antes de publicar.</li>
    <li>Guarda y recarga la tienda.</li>
</ol>

<div class="doc-callout doc-callout-tip">
    <i class="fa-solid fa-lightbulb"></i>
    <div>Mantén un glosario interno (nombres de líneas, garantías, política de envíos) y pégalo en tus prompts para coherencia entre productos.</div>
</div>

<?= doc_link('marketing', 'Editor Marketing IA') ?>
<?= doc_link('apariencia', 'Ir a Apariencia', '&sub=home') ?>
<?php doc_section_close(); ?>
