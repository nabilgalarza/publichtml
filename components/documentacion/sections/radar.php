<?php require __DIR__ . '/_helpers.php'; doc_section_open('radar', 'Radar de Ventas', 'Vista ejecutiva del rendimiento B2C: pedidos, conversión y señales rápidas para decidir dónde actuar.'); ?>

<div class="doc-card">
    <h3>Qué ves aquí</h3>
    <ul class="doc-list">
        <li>Resumen de pedidos y métricas clave de la tienda pública.</li>
        <li>Indicadores para priorizar stock, campañas o atención comercial.</li>
        <li>Punto de partida diario antes de entrar a Inventario o Pedidos Públicos.</li>
    </ul>
</div>

<div class="doc-infographic">
    <svg viewBox="0 0 520 140" xmlns="http://www.w3.org/2000/svg">
        <circle cx="260" cy="70" r="55" fill="none" stroke="#e2e8f0" stroke-width="12"/>
        <path d="M260 15 A55 55 0 0 1 305 95" fill="none" stroke="#3A86FF" stroke-width="12" stroke-linecap="round"/>
        <text x="260" y="75" text-anchor="middle" font-size="18" font-weight="800" fill="#1B263B">KPI</text>
        <text x="80" y="125" font-size="10" fill="#64748b">Pedidos</text>
        <text x="260" y="125" font-size="10" fill="#64748b" text-anchor="middle">Conversión</text>
        <text x="440" y="125" font-size="10" fill="#64748b" text-anchor="end">Alertas</text>
    </svg>
</div>

<ol class="doc-steps">
    <li>Abre <strong>Radar de Ventas</strong> desde el menú Público (B2C).</li>
    <li>Compara el periodo actual con la semana anterior.</li>
    <li>Si hay caída, cruza con <strong>Pedidos Públicos</strong> y productos sin stock en Inventario.</li>
</ol>

<?= doc_link('radar', 'Abrir Radar') ?>
<?php doc_section_close(); ?>
