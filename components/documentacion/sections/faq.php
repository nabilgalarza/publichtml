<?php require __DIR__ . '/_helpers.php'; doc_section_open('faq', 'Preguntas frecuentes', 'Respuestas rápidas sin salir del panel.'); ?>

<div class="doc-faq">
    <div class="doc-faq-item open">
        <button type="button" class="doc-faq-q">¿Por qué no veo el menú de Mayoristas (B2B)?</button>
        <div class="doc-faq-a">
            <p>Si eres <strong>gestor</strong> y el módulo B2B está oculto o el portal está en modo restringido, el menú B2B no se muestra. Los perfiles <strong>master</strong> siempre acceden a distribuidores y pedidos B2B.</p>
        </div>
    </div>
    <div class="doc-faq-item">
        <button type="button" class="doc-faq-q">El portal B2B dice “mantenimiento” pero yo soy master.</button>
        <div class="doc-faq-a">
            <p>El switch <strong>Portal B2B público</strong> está en OFF: los visitantes ven mantenimiento, pero tú gestionas cuentas desde el dashboard. Actívalo en Estado del Sistema cuando termines las pruebas piloto.</p>
        </div>
    </div>
    <div class="doc-faq-item">
        <button type="button" class="doc-faq-q">Cambié el home y no se actualiza.</button>
        <div class="doc-faq-a">
            <p>Guarda en Apariencia → Home, luego en Estado del Sistema usa <strong>limpiar caché</strong>. Revisa en ventana de incógnito por caché del navegador.</p>
        </div>
    </div>
    <div class="doc-faq-item">
        <button type="button" class="doc-faq-q">¿Dónde edito textos del listado de productos con IA?</button>
        <div class="doc-faq-a">
            <p>En <strong>Marketing IA</strong> (<code>view=marketing</code>), no en el Editor del Home. Los encabezados del index son en Apariencia → Editor del Home.</p>
        </div>
    </div>
    <div class="doc-faq-item">
        <button type="button" class="doc-faq-q">¿Existe documentación de Marketing Social?</button>
        <div class="doc-faq-a">
            <p>No. Ese módulo fue retirado del producto. Las campañas sociales se gestionan fuera de IMPROGYP OS V1.</p>
        </div>
    </div>
    <div class="doc-faq-item">
        <button type="button" class="doc-faq-q">¿Cómo comparto un capítulo de esta guía?</button>
        <div class="doc-faq-a">
            <p>Usa el menú lateral de esta guía para saltar entre capítulos. El botón <strong>Ver toda la guía</strong> muestra todos los capítulos en un solo scroll.</p>
        </div>
    </div>
</div>

<div class="doc-callout doc-callout-tip">
    <i class="fa-solid fa-circle-check"></i>
    <div>¿Aprobás esta demo? Indica por capítulo si falta copy, capturas o pasos. Tras tu OK integramos la guía en el botón “Centro de ayuda” del dashboard.</div>
</div>

<?php doc_section_close(); ?>
