<?php require __DIR__ . '/_helpers.php'; doc_section_open('inicio', 'Bienvenida a IMPROGYP OS V1', 'Tu panel unifica la tienda pública (B2C), el blog, la apariencia web y el canal mayorista (B2B). Esta guía sigue el mismo orden que el menú lateral del dashboard.'); ?>

<div class="doc-grid-2">
    <div class="doc-card">
        <h3><i class="fa-solid fa-user-tie text-indigo-500"></i> Rol: Gestor</h3>
        <p>Opera el día a día: catálogo, pedidos públicos, pautas, marketing, SEO, apariencia, blog y locales. <strong>No</strong> ve usuarios admin ni controles master del portal B2B si el módulo está oculto.</p>
    </div>
    <div class="doc-card doc-card-master">
        <h3><i class="fa-solid fa-crown text-amber-500"></i> Rol: Master</h3>
        <p>Acceso total: mayoristas VIP, pedidos B2B, estado del sistema (mantenimiento B2C + switch portal B2B), usuarios admin y métricas B2B aunque el portal esté apagado para el público.</p>
    </div>
</div>

<div class="doc-infographic">
    <svg viewBox="0 0 640 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <rect x="20" y="70" width="120" height="60" rx="12" fill="#1B263B"/><text x="80" y="105" fill="#fff" font-size="11" font-weight="700" text-anchor="middle">Dashboard</text>
        <rect x="180" y="30" width="100" height="50" rx="10" fill="#3A86FF"/><text x="230" y="60" fill="#fff" font-size="10" text-anchor="middle">B2C Web</text>
        <rect x="180" y="100" width="100" height="50" rx="10" fill="#6366f1"/><text x="230" y="130" fill="#fff" font-size="10" text-anchor="middle">Blog</text>
        <rect x="320" y="30" width="100" height="50" rx="10" fill="#10b981"/><text x="370" y="60" fill="#fff" font-size="10" text-anchor="middle">SEO / Ads</text>
        <rect x="320" y="100" width="100" height="50" rx="10" fill="#f59e0b"/><text x="370" y="130" fill="#fff" font-size="10" text-anchor="middle">B2B Portal</text>
        <rect x="460" y="70" width="120" height="60" rx="12" fill="#e2e8f0"/><text x="520" y="98" fill="#334155" font-size="10" text-anchor="middle">Cliente final</text>
        <text x="520" y="115" fill="#64748b" font-size="9" text-anchor="middle">Mayorista</text>
        <path d="M140 100 H180 M280 55 H320 M280 125 H320 M420 55 H460 M420 125 H460" stroke="#94a3b8" stroke-width="2" marker-end="url(#arrow)"/>
        <defs><marker id="arrow" markerWidth="8" markerHeight="8" refX="6" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8" fill="#94a3b8"/></marker></defs>
    </svg>
    <p class="doc-caption">Flujo conceptual: un solo panel alimenta la web pública, el blog y el canal mayorista.</p>
</div>

<ol class="doc-steps">
    <li><strong>Revisa el Radar</strong> cada mañana para ver ventas y alertas.</li>
    <li><strong>Atiende pedidos públicos</strong> antes que caduquen en WhatsApp.</li>
    <li><strong>Mantén inventario y SEO</strong> alineados con campañas activas.</li>
    <li><strong>Master:</strong> valida el switch B2B en Estado del Sistema antes de anunciar el portal.</li>
</ol>

<div class="doc-callout doc-callout-tip">
    <i class="fa-solid fa-lightbulb"></i>
    <div>Usa la <strong>barra de búsqueda</strong> del índice izquierdo para saltar a un módulo. La URL guarda <code>?seccion=radar</code> para compartir un capítulo con tu equipo.</div>
</div>

<?= doc_link('radar', 'Ir al Radar en el panel') ?>
<?php doc_section_close(); ?>
