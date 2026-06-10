<?php require __DIR__ . '/_helpers.php'; doc_section_open('pedidos-publicos', 'Pedidos Públicos (B2C)', 'Bandeja de pedidos que llegan desde la tienda web y el flujo de contacto por WhatsApp.'); ?>

<div class="doc-grid-2">
    <div class="doc-card">
        <h3>Estados y seguimiento</h3>
        <p>Cada pedido muestra datos del cliente, productos y estado operativo. Actualiza el estado cuando confirmes pago o despacho para no perder trazabilidad.</p>
    </div>
    <div class="doc-card">
        <h3>WhatsApp</h3>
        <p>El botón de contacto abre una conversación con el mensaje precargado. <strong>No reinicies contadores de clics</strong> al reenviar: el sistema conserva la métrica de interés.</p>
    </div>
</div>

<div class="doc-callout doc-callout-warn">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>Pedidos antiguos sin respuesta generan abandono. Define un SLA interno (por ejemplo, responder en menos de 2 horas en horario comercial).</div>
</div>

<ol class="doc-steps">
    <li>Entra a <strong>Pedidos Públicos</strong>.</li>
    <li>Ordena por los más recientes o pendientes.</li>
    <li>Cambia el estado tras confirmar pago o entrega.</li>
    <li>Usa WhatsApp solo para cerrar la venta; detalles sensibles, por canal acordado con el cliente.</li>
</ol>

<?= doc_link('pedidos_publicos', 'Ver pedidos públicos') ?>
<?php doc_section_close(); ?>
