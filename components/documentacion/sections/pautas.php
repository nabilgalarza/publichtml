<?php require __DIR__ . '/_helpers.php'; doc_section_open('pautas', 'Gestor de Pautas', 'Banners e “impulsos” publicitarios en la tienda: imágenes, enlaces, vigencia y orden de aparición.'); ?>

<div class="doc-card">
    <h3>Impulsos y campañas</h3>
    <p>Cada pauta puede llevar imagen, URL de destino y fechas de vigencia. Los impulsos destacados rotan en zonas visibles del home o listados según la configuración del tema.</p>
</div>

<ol class="doc-steps">
    <li>Ve a <strong>Gestor de Pautas</strong> (<code>view=ads</code> en el panel).</li>
    <li>Crea o edita un impulso con imagen optimizada (WebP o JPG ligero).</li>
    <li>Define enlace y periodo activo.</li>
    <li>Guarda y comprueba en la tienda en modo incógnito.</li>
    <li>Al terminar la campaña, elimina o desactiva para no mostrar ofertas vencidas.</li>
</ol>

<div class="doc-callout doc-callout-warn">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>Imágenes muy pesadas ralentizan el home móvil. Recomendado: ancho máximo 1200 px y menos de 200 KB cuando sea posible.</div>
</div>

<?= doc_link('ads', 'Abrir gestor de pautas') ?>
<?php doc_section_close(); ?>
