<?php require __DIR__ . '/_helpers.php'; doc_section_open('inventario', 'Inventario Web', 'Catálogo B2C: productos, categorías, marcas, imágenes e importación masiva CSV.'); ?>

<div class="doc-card">
    <h3>Operaciones habituales</h3>
    <ul class="doc-list">
        <li><strong>Alta / edición:</strong> título, precio, stock, categoría, marca e imágenes.</li>
        <li><strong>Categorías y marcas:</strong> creación desde el mismo módulo; eliminar solo si no hay productos dependientes.</li>
        <li><strong>CSV:</strong> importación masiva con resumen de filas nuevas, actualizadas y con error.</li>
        <li><strong>Eliminación masiva:</strong> requiere confirmación; irreversible.</li>
    </ul>
</div>

<div class="doc-infographic">
    <svg viewBox="0 0 480 120" xmlns="http://www.w3.org/2000/svg">
        <rect x="20" y="40" width="90" height="40" rx="8" fill="#ecfdf5" stroke="#6ee7b7"/><text x="65" y="65" font-size="10" text-anchor="middle" fill="#047857">CSV</text>
        <text x="130" y="65" font-size="20" fill="#94a3b8">→</text>
        <rect x="150" y="40" width="90" height="40" rx="8" fill="#eff6ff" stroke="#93c5fd"/><text x="195" y="65" font-size="10" text-anchor="middle" fill="#1d4ed8">Validar</text>
        <text x="260" y="65" font-size="20" fill="#94a3b8">→</text>
        <rect x="280" y="40" width="90" height="40" rx="8" fill="#f5f3ff" stroke="#c4b5fd"/><text x="325" y="65" font-size="10" text-anchor="middle" fill="#5b21b6">Catálogo</text>
        <text x="390" y="65" font-size="20" fill="#94a3b8">→</text>
        <rect x="410" y="40" width="50" height="40" rx="8" fill="#1B263B"/><text x="435" y="65" font-size="9" text-anchor="middle" fill="#fff">Web</text>
    </svg>
</div>

<div class="doc-callout doc-callout-tip">
    <i class="fa-solid fa-lightbulb"></i>
    <div>Tras importar CSV, revisa en la tienda un producto al azar (precio, imagen y categoría) antes de anunciar promociones.</div>
</div>

<?= doc_link('catalogo', 'Gestionar inventario') ?>
<?php doc_section_close(); ?>
