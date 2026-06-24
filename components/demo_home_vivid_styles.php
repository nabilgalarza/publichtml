<?php
/**
 * Capa visual demo: más color e imágenes sin cambiar layout del home.
 */
?>
<style>
/* Banner demo */
.demo-home-vivid-banner {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 3000;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px 14px;
    padding: 6px 12px;
    background: linear-gradient(90deg, #1B263B, #0E75AE 55%, #1B263B);
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.03em;
    text-align: center;
}
.demo-home-vivid-banner a {
    color: #fff;
    text-decoration: underline;
    text-underline-offset: 2px;
}
.demo-home-vivid #main-nav {
    top: 32px;
}
@media (min-width: 768px) {
    .demo-home-vivid-main { padding-top: 9.5rem !important; }
}

/* Fondo general más vivo */
body.demo-home-vivid.landing-page {
    background-color: #f1f5f9;
    background-image:
        radial-gradient(ellipse 80% 50% at 10% -10%, rgba(14, 117, 174, 0.14), transparent 55%),
        radial-gradient(ellipse 60% 40% at 95% 5%, rgba(27, 38, 59, 0.08), transparent 50%),
        radial-gradient(#cbd5e1 1px, transparent 1px);
    background-size: 100% 100%, 100% 100%, 28px 28px;
}

/* Slider: imágenes más visibles, borde acento */
.demo-home-vivid #landing-slider {
    border-color: rgba(14, 117, 174, 0.35);
    box-shadow: 0 24px 56px -12px rgba(14, 117, 174, 0.25), 0 8px 24px rgba(15, 23, 42, 0.12);
}
.demo-home-vivid #landing-slider .landing-slide > .bg-gradient-to-r {
    background: linear-gradient(90deg, rgba(15, 23, 42, 0.88) 0%, rgba(27, 38, 59, 0.55) 45%, rgba(14, 117, 174, 0.15) 100%) !important;
}
.demo-home-vivid #landing-slider .landing-slide img {
    filter: saturate(1.12) contrast(1.04);
}
.demo-home-vivid #landing-slider .inline-block.text-\[\#0E75AE\] {
    background: rgba(14, 117, 174, 0.2);
    color: #7dd3fc;
    padding: 4px 12px;
    border-radius: 999px;
    border: 1px solid rgba(125, 211, 252, 0.35);
}

/* Categorías: tiles con color e imagen de fondo suave */
.demo-home-vivid section:has(.cat-tile) {
    position: relative;
    padding-top: 2rem;
    padding-bottom: 2rem;
}
.demo-home-vivid section:has(.cat-tile)::before {
    content: '';
    position: absolute;
    inset: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100vw;
    background: linear-gradient(180deg, #fff 0%, rgba(224, 242, 254, 0.45) 50%, #fff 100%);
    z-index: -1;
    pointer-events: none;
}
.demo-home-vivid .cat-tile {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(14, 117, 174, 0.12) !important;
    min-height: 140px;
}
.demo-home-vivid .cat-tile::after {
    content: '';
    position: absolute;
    right: -20%;
    bottom: -30%;
    width: 70%;
    height: 70%;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    opacity: 0.14;
    pointer-events: none;
    filter: saturate(1.2);
}
.demo-home-vivid .cat-tile:nth-child(8n+1) { background: linear-gradient(145deg, #fff 0%, #e0f2fe 100%); }
.demo-home-vivid .cat-tile:nth-child(8n+2) { background: linear-gradient(145deg, #fff 0%, #f0fdf4 100%); }
.demo-home-vivid .cat-tile:nth-child(8n+3) { background: linear-gradient(145deg, #fff 0%, #fef3c7 100%); }
.demo-home-vivid .cat-tile:nth-child(8n+4) { background: linear-gradient(145deg, #fff 0%, #ede9fe 100%); }
.demo-home-vivid .cat-tile:nth-child(8n+5) { background: linear-gradient(145deg, #fff 0%, #fce7f3 100%); }
.demo-home-vivid .cat-tile:nth-child(8n+6) { background: linear-gradient(145deg, #fff 0%, #ecfdf5 100%); }
.demo-home-vivid .cat-tile:nth-child(8n+7) { background: linear-gradient(145deg, #fff 0%, #e2e8f0 100%); }
.demo-home-vivid .cat-tile:nth-child(8n)   { background: linear-gradient(145deg, #fff 0%, #dbeafe 100%); }
.demo-home-vivid .cat-tile:nth-child(8n+1)::after { background-image: url('img_catalogo/20MOS20VCII.webp'); }
.demo-home-vivid .cat-tile:nth-child(8n+2)::after { background-image: url('img_catalogo/20MPM1200.webp'); }
.demo-home-vivid .cat-tile:nth-child(8n+3)::after { background-image: url('img_catalogo/20MVC3800.webp'); }
.demo-home-vivid .cat-tile:nth-child(8n+4)::after { background-image: url('img_catalogo/20MDMS500.webp'); }
.demo-home-vivid .cat-tile:nth-child(8n+5)::after { background-image: url('img_catalogo/20MB700.webp'); }
.demo-home-vivid .cat-tile:nth-child(8n+6)::after { background-image: url('img_catalogo/20MOS20VCII.webp'); }
.demo-home-vivid .cat-tile:nth-child(8n+7)::after { background-image: url('img_catalogo/20MPM1200.webp'); }
.demo-home-vivid .cat-tile:nth-child(8n)::after   { background-image: url('img_catalogo/20MVC3800.webp'); }
.demo-home-vivid .cat-tile i {
    width: 2.75rem;
    height: 2.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: rgba(27, 38, 59, 0.08);
    color: #0E75AE !important;
    margin-bottom: 0.65rem;
    position: relative;
    z-index: 1;
}
.demo-home-vivid .cat-tile span { position: relative; z-index: 1; }

/* Tendencias: franja con imagen decorativa */
.demo-home-vivid section:has([data-carousel="tendencias"]) {
    position: relative;
}
.demo-home-vivid section:has([data-carousel="tendencias"])::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: min(320px, 40vw);
    height: 200px;
    background: url('ads_media/banner_1775837301_1.webp') center/cover no-repeat;
    opacity: 0.08;
    border-radius: 0 0 0 2rem;
    pointer-events: none;
}

/* Más vendidos: panel más colorido */
.demo-home-vivid section:has([data-carousel="mas-vendidos"]) .bg-slate-50\/80 {
    background: linear-gradient(135deg, rgba(224, 242, 254, 0.9) 0%, rgba(255, 255, 255, 0.95) 45%, rgba(241, 245, 249, 0.9) 100%) !important;
    border: 1px solid rgba(14, 117, 174, 0.15);
    box-shadow: 0 16px 40px -16px rgba(14, 117, 174, 0.2);
}

/* Cards producto: borde acento al hover */
.demo-home-vivid .landing-page .glass-card:hover,
.demo-home-vivid .product-card-landing:hover {
    border-color: rgba(14, 117, 174, 0.35) !important;
}

/* CTA rompetráfico: imagen más presente */
.demo-home-vivid .rompetrafico {
    border-color: rgba(14, 117, 174, 0.35);
    box-shadow: 0 28px 56px -20px rgba(14, 117, 174, 0.35);
}
.demo-home-vivid .rompetrafico .rt-media-panel img {
    filter: saturate(1.15) contrast(1.05);
}

/* Nosotros: más contraste en imagen */
.demo-home-vivid .landing-nosotros-section {
    background: linear-gradient(180deg, transparent 0%, rgba(224, 242, 254, 0.35) 50%, transparent 100%);
}
.demo-home-vivid .landing-nosotros-media img {
    filter: saturate(1.1);
    box-shadow: 0 20px 48px rgba(27, 38, 59, 0.15);
}

/* Blog home: sección con tinte suave */
.demo-home-vivid .bl-section.bl-section--home {
    background: linear-gradient(180deg, rgba(255,255,255,0.6) 0%, rgba(224, 242, 254, 0.25) 100%) !important;
}

/* Marcas: fondo suave */
.demo-home-vivid .marcas-marquee-section {
    background: linear-gradient(90deg, #fff 0%, #f8fafc 50%, #fff 100%);
    border-top-color: rgba(14, 117, 174, 0.12);
}

/* Locales: panel asesoría más azul */
.demo-home-vivid .asesoria-premium-panel {
    background: linear-gradient(160deg, #1B263B 0%, #0E75AE 100%) !important;
}
.demo-home-vivid .locales-premium-shell {
    border-color: rgba(14, 117, 174, 0.2);
    box-shadow: 0 20px 50px -20px rgba(14, 117, 174, 0.18);
}

/* Headings: subtítulo con más color */
.demo-home-vivid .text-slate-500 {
    color: #64748b;
}
.demo-home-vivid .laser-text {
    background: linear-gradient(90deg, #0E75AE 0%, #38bdf8 35%, #1B263B 70%, #0E75AE 100%);
    background-size: 200% auto;
}
</style>
