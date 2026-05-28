<?php
/** Modal lector (solo home) — contenido vía api_blog_articulo.php */
?>
<div class="bl-article-modal hidden" id="bl-article-modal" role="dialog" aria-modal="true" aria-labelledby="bl-article-modal-title" aria-hidden="true">
    <div class="bl-article-modal-backdrop" data-bl-close-modal></div>
    <div class="bl-article-modal-panel" onclick="event.stopPropagation()">
        <button type="button" class="bl-article-modal-close" data-bl-close-modal aria-label="Cerrar">&times;</button>
        <div class="bl-article-modal-scroll custom-scrollbar" id="bl-article-modal-scroll">
            <div class="bl-article-modal-progress" id="bl-article-modal-progress" aria-hidden="true"></div>
            <img id="bl-article-modal-img" src="" alt="" class="bl-article-modal-cover">
            <div class="bl-article-modal-body">
                <span id="bl-article-modal-cat" class="bl-tag"></span>
                <h2 id="bl-article-modal-title" class="bl-article-modal-title"></h2>
                <div id="bl-article-modal-meta" class="bl-meta"></div>
                <p id="bl-article-modal-resumen" class="bl-article-modal-resumen"></p>
                <div id="bl-article-modal-content" class="bl-article-modal-prose">
                    <p class="bl-article-loading"><i class="fa-solid fa-circle-notch fa-spin"></i> Cargando artículo…</p>
                </div>
            </div>
        </div>
        <div class="bl-article-modal-footer">
            <button type="button" id="bl-article-modal-share" class="bl-article-modal-btn bl-article-modal-btn--ghost">
                <i class="fa-solid fa-share-nodes"></i> Compartir
            </button>
            <a id="bl-article-modal-full" href="blog.php" class="bl-article-modal-btn bl-article-modal-btn--primary" target="_blank" rel="noopener">
                Enlace permanente <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
        </div>
    </div>
</div>
