<?php require __DIR__ . '/_helpers.php'; doc_section_open('pedidos-b2b', 'Pedidos B2B', 'Pedidos ingresados por mayoristas autenticados: montos, estados, KPI de conversión y enlace al detalle operativo.'); ?>

<div class="doc-card">
    <h3>Métricas y bandeja</h3>
    <ul class="doc-list">
        <li>Listado de pedidos B2B con filtros por estado y fecha.</li>
        <li>KPI de conversión del canal (visitas vs pedidos cerrados).</li>
        <li>Acceso rápido al detalle para facturación o despacho.</li>
    </ul>
</div>

<div class="doc-callout doc-card-master doc-callout">
    <i class="fa-solid fa-crown"></i>
    <div>Con el portal B2B <strong>apagado</strong>, los clientes finales no entran al portal, pero los pedidos históricos y la administración siguen visibles para perfiles master en el dashboard.</div>
</div>

<ol class="doc-steps">
    <li>Revisa pedidos nuevos al inicio del día.</li>
    <li>Actualiza estado (confirmado, en preparación, entregado).</li>
    <li>Cruza stock en Inventario Web si compartes SKU entre B2C y B2B.</li>
    <li>Usa el Radar B2B (si está en la vista distribuidores) para detectar caídas de conversión.</li>
</ol>

<?= doc_link('pedidos', 'Ver pedidos B2B') ?>
<?= doc_link('distribuidores', 'Panel mayoristas') ?>
<?php doc_section_close(); ?>
