<?php require __DIR__ . '/_helpers.php'; doc_section_open('blog', 'Gestor del Blog', 'Artículos, extractos, imágenes destacadas y publicación en la web pública. La apariencia de la portada se configura en Apariencia → Blog.'); ?>

<div class="doc-card">
    <h3>Ciclo de un artículo</h3>
    <ul class="doc-list">
        <li><strong>Borrador:</strong> título obligatorio; puedes guardar sin publicar.</li>
        <li><strong>Publicado:</strong> visible en <code>blog.php</code> con URL amigable y SEO del artículo.</li>
        <li><strong>Eliminado:</strong> desaparece del listado público; confirma antes de borrar.</li>
    </ul>
</div>

<ol class="doc-steps">
    <li>Configura la portada en <strong>Apariencia → Apariencia Blog</strong>.</li>
    <li>Crea el post en <strong>Gestor Blog</strong> con imagen y extracto.</li>
    <li>Revisa vista previa y metadatos (título único, slug coherente).</li>
    <li>Publica y comparte el enlace; verifica Open Graph si cambiaste SEO global.</li>
</ol>

<div class="doc-callout doc-callout-tip">
    <i class="fa-solid fa-lightbulb"></i>
    <div>Artículos ligados a productos del catálogo mejoran conversión: enlaza SKUs o categorías desde el cuerpo del post.</div>
</div>

<?= doc_link('blog', 'Abrir Gestor Blog') ?>
<?= doc_link('apariencia', 'Apariencia portada blog', '&sub=blog') ?>
<?php doc_section_close(); ?>
