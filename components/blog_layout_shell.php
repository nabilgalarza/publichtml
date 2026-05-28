<?php
/**
 * Layout visual del blog (compartido home + blog.php).
 * Requiere variables $bl_* de blog_layout_prepare().
 */
$bl_stage_id = $bl_stage_id ?? 'bl-stage';
$bl_pagination_id = $bl_pagination_id ?? 'bl-pagination';
$bl_open_in_modal = !empty($bl_open_in_modal);
?>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&family=Plus+Jakarta+Sans:wght@300;400;600;700;800&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
<style>
.bl-section { padding: 80px 0; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); }
.bl-section.bl-section--page { padding-top: 24px; }
.bl-section * { box-sizing: border-box; }
.bl-inner { max-width: 1260px; margin: 0 auto; padding: 0 24px; font-family: <?= $bl_font_css ?>; }
.bl-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 44px; flex-wrap: wrap; gap: 16px; }
.bl-header h2 { font-family: <?= $bl_font_css ?>; font-size: clamp(1.7rem, 3vw, 2.6rem); font-weight: 900; color: #0f172a; line-height: 1.15; letter-spacing: -.025em; margin: 0; }
.bl-header h2 span { color: <?= $bl_accent ?>; }
.bl-view-all { display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; border: 2px solid <?= $bl_accent ?>; color: <?= $bl_accent ?>; border-radius: 50px; font-size: .82rem; font-weight: 800; text-decoration: none; transition: all .25s; white-space: nowrap; }
.bl-view-all:hover { background: <?= $bl_accent ?>; color: white; transform: translateY(-2px); }
.bl-stage { min-height: 120px; }
.bl-stage.is-fading { opacity: .4; transition: opacity .2s; }
.bl-tag { display: inline-block; padding: 3px 12px; background: rgba(<?= $bl_accent_rgb ?>,.1); color: <?= $bl_accent ?>; border-radius: 20px; font-size: .7rem; font-weight: 900; text-transform: uppercase; letter-spacing: .06em; }
.bl-meta { display: flex; align-items: center; gap: 10px; font-size: .73rem; color: #94a3b8; font-weight: 600; flex-wrap: wrap; margin-top: 10px; }
.bl-card { border-radius: 20px; overflow: hidden; background: white; box-shadow: 0 4px 24px rgba(0,0,0,.06); transition: transform .3s, box-shadow .3s; display: flex; flex-direction: column; text-decoration: none; border: 1.5px solid #f1f5f9; height: 100%; }
.bl-card:hover { transform: translateY(-6px); box-shadow: 0 16px 48px rgba(0,0,0,.12); border-color: rgba(<?= $bl_accent_rgb ?>,.2); }
.bl-card-img { width: 100%; object-fit: cover; background: #e2e8f0; display: block; }
.bl-card-body { padding: 18px; flex: 1; display: flex; flex-direction: column; }
.bl-card-title { font-family: <?= $bl_font_css ?>; font-size: .95rem; font-weight: 800; color: #1e293b; margin: 8px 0 6px; line-height: 1.4; }
.bl-card-excerpt { font-size: .82rem; color: #64748b; line-height: 1.6; flex: 1; }
.bl-card-cta { display: inline-flex; align-items: center; gap: 6px; font-size: .78rem; font-weight: 800; color: <?= $bl_accent ?>; margin-top: 12px; }
.bl-hero { border-radius: 22px; overflow: hidden; background: white; box-shadow: 0 8px 48px rgba(0,0,0,.1); transition: transform .3s; text-decoration: none; display: block; height: 100%; }
.bl-hero:hover { transform: translateY(-8px); box-shadow: 0 24px 64px rgba(0,0,0,.15); }
.bl-hero-img { width: 100%; height: 320px; object-fit: cover; background: #e2e8f0; display: block; }
.bl-hero-body { padding: 26px; }
.bl-hero-title { font-family: <?= $bl_font_css ?>; font-size: 1.35rem; font-weight: 900; color: #0f172a; margin: 12px 0 8px; line-height: 1.3; }
.bl-hero-excerpt { font-size: .85rem; color: #64748b; line-height: 1.6; }
.bl-editorial-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; align-items: stretch; }
.bl-side-col { display: flex; flex-direction: column; gap: 16px; }
.bl-duo50-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; align-items: stretch; }
.bl-duo50-right { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: stretch; }
.bl-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
.bl-carousel-viewport { overflow: hidden; }
.bl-carousel-track { display: flex; gap: 20px; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
.bl-carousel-track::-webkit-scrollbar { display: none; }
.bl-carousel-track .bl-carousel-item { flex: 0 0 calc((100% - 40px) / 3); scroll-snap-align: start; min-width: 0; }
.bl-cyber-section { background: #050514; }
.bl-grid-cyber { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
.bl-cyber-card { border-radius: 16px; overflow: hidden; background: #0d0d2b; border: 1px solid rgba(<?= $bl_accent_rgb ?>,.3); transition: all .3s; display: block; text-decoration: none; height: 100%; }
.bl-cyber-card:hover { border-color: <?= $bl_accent ?>; box-shadow: 0 0 32px rgba(<?= $bl_accent_rgb ?>,.25); transform: translateY(-6px); }
.bl-cyber-img { width: 100%; height: 150px; object-fit: cover; display: block; background: #1e293b; }
.bl-cyber-body { padding: 16px; }
.bl-cyber-cat { font-size: .7rem; font-weight: 900; color: <?= $bl_accent ?>; text-transform: uppercase; }
.bl-cyber-title { font-size: .92rem; font-weight: 900; color: white; margin: 8px 0 6px; line-height: 1.4; }
.bl-cyber-meta { font-size: .72rem; color: rgba(<?= $bl_accent_rgb ?>,.8); font-weight: 700; }
.bl-cyber-cta { display: inline-flex; gap: 6px; margin-top: 10px; font-size: .78rem; font-weight: 800; color: <?= $bl_accent ?>; }
.bl-pagination { display: flex; justify-content: center; gap: 6px; margin-top: 20px; flex-wrap: wrap; }
.bl-pagination[hidden] { display: none !important; }
.bl-dot { height: 8px; width: 8px; border-radius: 4px; border: none; padding: 0; cursor: pointer; background: <?= $bl_accent ?>; opacity: .25; transition: width .25s, opacity .25s; }
.bl-dot:hover { opacity: .5; }
.bl-dot.is-active { width: 24px; opacity: 1; }
@media (max-width: 960px) {
    .bl-editorial-wrap, .bl-duo50-wrap { grid-template-columns: 1fr; }
    .bl-duo50-right { grid-template-columns: 1fr 1fr; }
    .bl-grid-3 { grid-template-columns: 1fr 1fr; }
    .bl-grid-cyber { grid-template-columns: 1fr 1fr; }
    .bl-carousel-track .bl-carousel-item { flex: 0 0 calc((100% - 20px) / 2); }
}
@media (max-width: 600px) {
    .bl-section { padding: 52px 0; }
    .bl-hero-img { height: 220px; }
    .bl-grid-3, .bl-duo50-right, .bl-grid-cyber { grid-template-columns: 1fr; }
    .bl-carousel-track .bl-carousel-item { flex: 0 0 85%; }
    .bl-header { flex-direction: column; align-items: flex-start; }
}
/* Home: fondo transparente, hover sin recorte (ajustes del chat) */
.bl-section.bl-section--home {
    padding: 80px 0;
    background: transparent !important;
    overflow: visible;
}
.bl-section.bl-section--home .bl-inner,
.bl-section.bl-section--home .bl-stage,
.bl-section.bl-section--home .bl-slider-wrap,
.bl-section.bl-section--home .bl-pages-viewport,
.bl-section.bl-section--home .bl-pages-track,
.bl-section.bl-section--home .bl-page-slide,
.bl-section.bl-section--home .bl-carousel-viewport,
.bl-section.bl-section--home .bl-carousel-track {
    overflow: visible;
    background: transparent;
}
.bl-section.bl-section--home .bl-stage { margin-top: 16px; padding-top: 12px; }
.bl-section.bl-section--home .bl-pages-viewport,
.bl-section.bl-section--home .bl-carousel-viewport { padding-top: 14px; padding-bottom: 10px; }
.bl-section.bl-section--home .bl-page-slide,
.bl-section.bl-section--home .bl-carousel-track { padding-top: 16px; }
.bl-section.bl-section--home .bl-card,
.bl-section.bl-section--home .bl-hero {
    box-shadow: none;
    border: 1.5px solid #e2e8f0;
}
.bl-section.bl-section--home .bl-card:hover,
.bl-section.bl-section--home .bl-hero:hover {
    transform: translateY(-4px);
    box-shadow: none;
    border-color: rgba(<?= $bl_accent_rgb ?>, .35);
}
.bl-section.bl-section--home .bl-slider-wrap[data-bl-nav] { padding: 0 28px; }
@media (hover: hover) and (pointer: fine) {
    .bl-section.bl-section--home .bl-nav-prev { left: -22px; }
    .bl-section.bl-section--home .bl-nav-next { right: -22px; }
}
.bl-section.bl-section--home .bl-grid-3,
.bl-section.bl-section--archive .bl-grid-3 {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}
@media (min-width: 768px) {
    .bl-section.bl-section--home .bl-grid-3 {
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
    .bl-section.bl-section--archive .bl-grid-3 {
        grid-template-columns: repeat(3, 1fr);
        gap: 22px;
    }
}
@media (min-width: 1024px) {
    .bl-section.bl-section--archive .bl-grid-3 {
        grid-template-columns: repeat(4, 1fr);
    }
}
.bl-card[data-bl-open-slug], .bl-hero[data-bl-open-slug], .bl-cyber-card[data-bl-open-slug] { cursor: pointer; }
.bl-article-modal { position: fixed; inset: 0; z-index: 2500; display: none; align-items: center; justify-content: center; padding: 16px; opacity: 0; pointer-events: none; transition: opacity .2s ease; }
.bl-article-modal.is-open { display: flex; opacity: 1; pointer-events: auto; }
.bl-article-modal-backdrop { position: absolute; inset: 0; background: rgba(15, 23, 42, .6); }
.bl-article-modal-panel { position: relative; z-index: 1; width: 100%; max-width: 800px; max-height: 92vh; background: #fff; border-radius: 1.25rem; box-shadow: 0 24px 64px rgba(0,0,0,.25); display: flex; flex-direction: column; overflow: hidden; transform: scale(.98) translateY(8px); transition: transform .2s ease; }
.bl-article-modal.is-open .bl-article-modal-panel { transform: scale(1) translateY(0); }
.bl-article-modal-close { position: absolute; top: 12px; right: 12px; z-index: 5; width: 36px; height: 36px; border: none; border-radius: 999px; background: rgba(255,255,255,.95); color: #64748b; font-size: 1.35rem; line-height: 1; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
.bl-article-modal-close:hover { color: #e11d48; }
.bl-article-modal-scroll { overflow-y: auto; flex: 1; min-height: 0; }
.bl-article-modal-cover { width: 100%; max-height: 240px; object-fit: cover; background: #e2e8f0; display: block; }
.bl-article-modal-body { padding: 24px 24px 8px; }
.bl-article-modal-title { font-family: <?= $bl_font_css ?>; font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 10px 0 8px; line-height: 1.25; }
.bl-article-modal-resumen { font-size: .95rem; color: #475569; font-weight: 500; margin-bottom: 16px; border-left: 3px solid <?= $bl_accent ?>; padding-left: 12px; }
.bl-article-modal-prose { font-size: .9rem; color: #334155; line-height: 1.7; }
.bl-article-modal-prose p { margin-bottom: .75rem; }
.bl-article-modal-prose img { max-width: 100%; height: auto; border-radius: 8px; }
.bl-article-modal-footer { padding: 12px 16px 16px; border-top: 1px solid #f1f5f9; flex-shrink: 0; display: flex; flex-wrap: wrap; gap: 10px; justify-content: stretch; }
.bl-article-modal-btn { flex: 1; min-width: 140px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: .78rem; font-weight: 800; border-radius: 12px; padding: 10px 14px; border: none; cursor: pointer; text-decoration: none; transition: background .2s, color .2s; }
.bl-article-modal-btn--ghost { background: #f1f5f9; color: #475569; }
.bl-article-modal-btn--ghost:hover { background: #e2e8f0; color: #0f172a; }
.bl-article-modal-btn--primary { background: <?= $bl_accent ?>; color: #fff; }
.bl-article-modal-btn--primary:hover { filter: brightness(1.08); color: #fff; }
.bl-article-loading { text-align: center; color: #94a3b8; font-size: .9rem; padding: 2rem 1rem; }
.bl-article-modal-progress { position: sticky; top: 0; left: 0; height: 3px; width: 0; background: <?= $bl_accent ?>; z-index: 4; transition: width .1s linear; }
@media (max-width: 640px) {
    .bl-article-modal { align-items: flex-end; padding: 0; }
    .bl-article-modal-panel { max-width: 100%; max-height: 94vh; border-radius: 1.25rem 1.25rem 0 0; }
}
</style>

<section class="bl-section <?= $bl_is_cyber ? 'bl-cyber-section' : '' ?> <?= htmlspecialchars($bl_section_class_extra) ?>" id="<?= htmlspecialchars($bl_section_id) ?>">
    <div class="bl-inner">
        <div class="bl-header">
            <h2 <?= $bl_is_cyber ? 'style="color:white"' : '' ?>><?= $bl_heading_html ?></h2>
            <?php if ($bl_show_view_all): ?>
            <a href="blog.php" class="bl-view-all" <?= $bl_is_cyber ? 'style="border-color:' . htmlspecialchars($bl_accent) . ';color:' . htmlspecialchars($bl_accent) . '"' : '' ?>>
                Ver todos los artículos <span style="font-size:.7rem">→</span>
            </a>
            <?php endif; ?>
        </div>
        <div class="bl-stage" id="<?= htmlspecialchars($bl_stage_id) ?>" aria-live="polite"></div>
        <div class="bl-pagination" id="<?= htmlspecialchars($bl_pagination_id) ?>" hidden></div>
    </div>
</section>
<?php if (!empty($bl_open_in_modal)): ?>
    <?php include __DIR__ . '/blog_article_modal.php'; ?>
<?php endif; ?>
<script>
(function () {
    const CFG = <?= json_encode($bl_js_cfg, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) ?>;
    const stage = document.getElementById(<?= json_encode($bl_stage_id) ?>);
    const paginationEl = document.getElementById(<?= json_encode($bl_pagination_id) ?>);
    if (!stage || !CFG.articles?.length) return;

    let currentPage = 0;

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s ?? '';
        return d.innerHTML;
    }

    function imgSrc(art) {
        return art.portada ? esc(art.portada) : CFG.imgFallback;
    }

    function metaHtml(art) {
        const parts = [];
        if (CFG.showDate) {
            try {
                const dt = new Date(art.fecha);
                parts.push('<span>📅 ' + dt.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }) + '</span>');
            } catch (e) { parts.push('<span>📅 ' + esc(art.fecha) + '</span>'); }
        }
        if (CFG.showRT) parts.push('<span>⏱ ' + esc(art.tiempo_lectura) + '</span>');
        if (CFG.showViews) parts.push('<span>👁 ' + Number(art.visitas).toLocaleString('es') + '</span>');
        return parts.length ? '<div class="bl-meta">' + parts.join('') + '</div>' : '';
    }

    function articleLinkOpen(art, className, inner) {
        if (CFG.openInModal) {
            return `<div class="${className}" data-bl-open-slug="${esc(art.slug)}" role="button" tabindex="0">${inner}</div>`;
        }
        return `<a href="blog.php?slug=${encodeURIComponent(art.slug)}" class="${className}">${inner}</a>`;
    }

    function cardHtml(art, imgH) {
        const h = imgH || 180;
        const excerpt = (art.resumen || '').substring(0, 90);
        const inner = `<img src="${imgSrc(art)}" alt="${esc(art.titulo)}" class="bl-card-img" style="height:${h}px" loading="lazy"
                 onerror="this.onerror=null;this.src='${CFG.imgFallback}'">
            <div class="bl-card-body">
                <span class="bl-tag">${esc(art.categoria)}</span>
                <h3 class="bl-card-title">${esc(art.titulo)}</h3>
                ${metaHtml(art)}
                <p class="bl-card-excerpt">${esc(excerpt)}${excerpt.length >= 90 ? '…' : ''}</p>
                <span class="bl-card-cta">Leer más →</span>
            </div>`;
        return articleLinkOpen(art, 'bl-card', inner);
    }

    function heroHtml(art) {
        const excerpt = (art.resumen || '').substring(0, 140);
        const inner = `<img src="${imgSrc(art)}" alt="${esc(art.titulo)}" class="bl-hero-img" loading="lazy"
                 onerror="this.onerror=null;this.src='${CFG.imgFallback}'">
            <div class="bl-hero-body">
                <span class="bl-tag">${esc(art.categoria)}</span>
                <h2 class="bl-hero-title">${esc(art.titulo)}</h2>
                ${metaHtml(art)}
                <p class="bl-hero-excerpt">${esc(excerpt)}${excerpt.length >= 140 ? '…' : ''}</p>
                <span class="bl-card-cta" style="margin-top:14px;display:inline-flex">Leer artículo completo →</span>
            </div>`;
        return articleLinkOpen(art, 'bl-hero', inner);
    }

    function cyberCardHtml(art) {
        let fecha = art.fecha;
        try {
            fecha = new Date(art.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
        } catch (e) {}
        const inner = `<img src="${imgSrc(art)}" alt="${esc(art.titulo)}" class="bl-cyber-img" loading="lazy"
                 onerror="this.onerror=null;this.src='${CFG.imgFallback}'">
            <div class="bl-cyber-body">
                <div class="bl-cyber-cat">${esc(art.categoria)}</div>
                <h3 class="bl-cyber-title">${esc(art.titulo)}</h3>
                <div class="bl-cyber-meta">${esc(fecha)} · ${esc(art.tiempo_lectura)}</div>
                <div class="bl-cyber-cta">Leer más <span style="font-size:.7rem">→</span></div>
            </div>`;
        return articleLinkOpen(art, 'bl-cyber-card', inner);
    }

    const articlesBySlug = {};
    CFG.articles.forEach((a) => { if (a.slug) articlesBySlug[a.slug] = a; });

    function bindBlogArticleOpens(root) {
        if (!CFG.openInModal || !root) return;
        root.querySelectorAll('[data-bl-open-slug]').forEach((el) => {
            if (el.dataset.blBound) return;
            el.dataset.blBound = '1';
            const open = () => openBlogArticleModal(el.getAttribute('data-bl-open-slug'));
            el.addEventListener('click', (e) => { e.preventDefault(); open(); });
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); }
            });
        });
    }

    let currentModalSlug = null;

    function closeBlogArticleModal() {
        const modal = document.getElementById('bl-article-modal');
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        currentModalSlug = null;
        setTimeout(() => modal.classList.add('hidden'), 200);
    }

    function showBlogArticleModalUi() {
        const modal = document.getElementById('bl-article-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        });
    }

    function fillBlogModalMeta(art) {
        const imgEl = document.getElementById('bl-article-modal-img');
        const titleEl = document.getElementById('bl-article-modal-title');
        const catEl = document.getElementById('bl-article-modal-cat');
        const metaEl = document.getElementById('bl-article-modal-meta');
        const resumenEl = document.getElementById('bl-article-modal-resumen');
        const fullEl = document.getElementById('bl-article-modal-full');
        if (imgEl) {
            imgEl.src = art.portada || CFG.imgFallback;
            imgEl.alt = art.titulo || '';
            imgEl.onerror = function () { this.onerror = null; this.src = CFG.imgFallback; };
        }
        if (titleEl) titleEl.textContent = art.titulo || '';
        if (catEl) catEl.textContent = art.categoria || '';
        if (metaEl) metaEl.innerHTML = metaHtml(art);
        if (resumenEl) {
            resumenEl.textContent = art.resumen || '';
            resumenEl.style.display = art.resumen ? '' : 'none';
        }
        if (fullEl) fullEl.href = 'blog.php?slug=' + encodeURIComponent(art.slug || '');
    }

    function bindModalReadingProgress() {
        const scrollEl = document.getElementById('bl-article-modal-scroll');
        const bar = document.getElementById('bl-article-modal-progress');
        if (!scrollEl || !bar) return;
        const onScroll = () => {
            const max = scrollEl.scrollHeight - scrollEl.clientHeight;
            const pct = max > 0 ? Math.min(100, (scrollEl.scrollTop / max) * 100) : 0;
            bar.style.width = pct + '%';
        };
        scrollEl.removeEventListener('scroll', scrollEl._blProgressFn || (() => {}));
        scrollEl._blProgressFn = onScroll;
        scrollEl.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    async function openBlogArticleModal(slug) {
        if (!slug) return;
        const artMeta = articlesBySlug[slug];
        const modal = document.getElementById('bl-article-modal');
        const contentEl = document.getElementById('bl-article-modal-content');
        if (!modal || !contentEl) {
            window.location.href = 'blog.php?slug=' + encodeURIComponent(slug);
            return;
        }
        if (!artMeta) {
            window.location.href = 'blog.php?slug=' + encodeURIComponent(slug);
            return;
        }

        currentModalSlug = slug;
        fillBlogModalMeta(artMeta);
        contentEl.innerHTML = '<p class="bl-article-loading"><i class="fa-solid fa-circle-notch fa-spin"></i> Cargando artículo…</p>';
        showBlogArticleModalUi();
        bindModalReadingProgress();

        const cacheKey = 'improgyp_bl_' + slug;
        let full = null;
        try {
            const cached = sessionStorage.getItem(cacheKey);
            if (cached) full = JSON.parse(cached);
        } catch (e) { /* ignore */ }

        if (!full || !full.contenido) {
            try {
                const apiUrl = 'api_blog_articulo.php?slug=' + encodeURIComponent(slug);
                const res = await fetch(apiUrl);
                const data = await res.json();
                if (data.success && data.article) {
                    full = data.article;
                    try { sessionStorage.setItem(cacheKey, JSON.stringify(full)); } catch (e) { /* ignore */ }
                }
            } catch (e) {
                contentEl.innerHTML = '<p class="bl-article-loading">No se pudo cargar. <a href="blog.php?slug=' + encodeURIComponent(slug) + '">Abrir en el blog</a></p>';
                return;
            }
        }

        if (!full) {
            contentEl.innerHTML = '<p class="bl-article-loading">Artículo no encontrado.</p>';
            return;
        }

        fillBlogModalMeta(full);
        contentEl.innerHTML = full.contenido || '<p>Sin contenido.</p>';
        bindModalReadingProgress();
    }

    if (CFG.openInModal) {
        const blogModal = document.getElementById('bl-article-modal');
        if (blogModal) {
            blogModal.querySelectorAll('[data-bl-close-modal]').forEach((btn) => {
                btn.addEventListener('click', closeBlogArticleModal);
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && blogModal.classList.contains('is-open')) closeBlogArticleModal();
            });
            const shareBtn = document.getElementById('bl-article-modal-share');
            if (shareBtn) {
                shareBtn.addEventListener('click', () => {
                    if (!currentModalSlug) return;
                    const url = new URL('blog.php', window.location.href);
                    url.searchParams.set('slug', currentModalSlug);
                    const art = articlesBySlug[currentModalSlug];
                    const shareData = {
                        title: (art && art.titulo) || 'IMPROGYP Blog',
                        text: (art && art.resumen) || '',
                        url: url.toString(),
                    };
                    if (navigator.share) {
                        navigator.share(shareData).catch(() => {});
                    } else if (navigator.clipboard) {
                        navigator.clipboard.writeText(url.toString()).then(() => {
                            shareBtn.innerHTML = '<i class="fa-solid fa-check"></i> Copiado';
                            setTimeout(() => {
                                shareBtn.innerHTML = '<i class="fa-solid fa-share-nodes"></i> Compartir';
                            }, 2000);
                        });
                    }
                });
            }
        }
        window.openBlogArticleModal = openBlogArticleModal;
        window.closeBlogArticleModal = closeBlogArticleModal;
    }

    function pageSlice(page) {
        const start = page * CFG.perPage;
        return CFG.articles.slice(start, start + CFG.perPage);
    }

    function pageCount() {
        return Math.max(1, Math.ceil(CFG.articles.length / CFG.perPage));
    }

    function renderEditorial(slice) {
        let html = '<div class="bl-editorial-wrap">';
        if (slice[0]) html += heroHtml(slice[0]);
        if (slice.length > 1) {
            html += '<div class="bl-side-col">';
            for (let i = 1; i < slice.length; i++) html += cardHtml(slice[i], 110);
            html += '</div>';
        }
        html += '</div>';
        return html;
    }

    function renderDuo50(slice) {
        let html = '<div class="bl-duo50-wrap">';
        if (slice[0]) html += '<div class="bl-duo50-left">' + cardHtml(slice[0], 200) + '</div>';
        if (slice.length > 1) {
            html += '<div class="bl-duo50-right">';
            for (let i = 1; i < slice.length; i++) html += cardHtml(slice[i], 160);
            html += '</div>';
        }
        html += '</div>';
        return html;
    }

    function renderGrid3(slice) {
        return '<div class="bl-grid-3">' + slice.map(a => cardHtml(a, 180)).join('') + '</div>';
    }

    function renderCyber(slice) {
        return '<div class="bl-grid-cyber">' + slice.map(cyberCardHtml).join('') + '</div>';
    }

    function renderPaginatedPage(page) {
        const slice = pageSlice(page);
        if (!slice.length) return '';
        switch (CFG.layout) {
            case 'editorial': return renderEditorial(slice);
            case 'duo50': return renderDuo50(slice);
            case 'grid3': return renderGrid3(slice);
            case 'cyberneon': return renderCyber(slice);
            default: return renderEditorial(slice);
        }
    }

    function buildPagination(active, total, onSelect) {
        if (!paginationEl) return;
        if (total <= 1) {
            paginationEl.hidden = true;
            paginationEl.innerHTML = '';
            return;
        }
        paginationEl.hidden = false;
        paginationEl.innerHTML = '';
        for (let i = 0; i < total; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'bl-dot' + (i === active ? ' is-active' : '');
            btn.setAttribute('aria-label', 'Ir a página ' + (i + 1) + ' de ' + total);
            if (i === active) btn.setAttribute('aria-current', 'true');
            btn.addEventListener('click', () => onSelect(i));
            paginationEl.appendChild(btn);
        }
    }

    function goToPage(page) {
        const total = pageCount();
        currentPage = Math.max(0, Math.min(page, total - 1));
        stage.classList.add('is-fading');
        requestAnimationFrame(() => {
            stage.innerHTML = renderPaginatedPage(currentPage);
            stage.classList.remove('is-fading');
            buildPagination(currentPage, total, goToPage);
            bindBlogArticleOpens(stage);
        });
    }

    function renderCarousel() {
        const visible = CFG.perPage;
        const total = CFG.articles.length;
        const pages = Math.max(1, Math.ceil(total / visible));
        const trackId = <?= json_encode($bl_stage_id) ?> + '-track';

        stage.innerHTML = '<div class="bl-carousel-viewport"><div class="bl-carousel-track" id="' + trackId + '">' +
            CFG.articles.map(a => '<div class="bl-carousel-item">' + cardHtml(a, 180) + '</div>').join('') +
            '</div></div>';

        const track = document.getElementById(trackId);
        bindBlogArticleOpens(stage);
        if (!track) return;

        let activeWindow = 0;
        let scrollTimer = null;

        function scrollToWindow(idx) {
            const items = track.querySelectorAll('.bl-carousel-item');
            if (!items.length) return;
            const target = items[Math.min(idx * visible, items.length - 1)];
            if (target) track.scrollTo({ left: target.offsetLeft - track.offsetLeft, behavior: 'smooth' });
            activeWindow = idx;
            buildPagination(activeWindow, pages, scrollToWindow);
        }

        function onScroll() {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(() => {
                const items = track.querySelectorAll('.bl-carousel-item');
                if (!items.length) return;
                let best = 0;
                let minDist = Infinity;
                const scrollLeft = track.scrollLeft;
                items.forEach((el, i) => {
                    const dist = Math.abs(el.offsetLeft - track.offsetLeft - scrollLeft);
                    if (dist < minDist) { minDist = dist; best = i; }
                });
                activeWindow = Math.min(pages - 1, Math.floor(best / visible));
                buildPagination(activeWindow, pages, scrollToWindow);
            }, 80);
        }

        track.addEventListener('scroll', onScroll, { passive: true });
        buildPagination(0, pages, scrollToWindow);
    }

    if (CFG.homePreview) {
        stage.innerHTML = renderGrid3(CFG.articles);
        bindBlogArticleOpens(stage);
        if (paginationEl) {
            paginationEl.hidden = true;
            paginationEl.innerHTML = '';
        }
        return;
    }

    if (CFG.layout === 'carousel') {
        if (paginationEl) paginationEl.hidden = false;
        renderCarousel();
    } else {
        goToPage(0);
    }
})();
</script>
