<?php require __DIR__ . '/_helpers.php'; doc_section_open('vip', 'Clientes VIP (Mayoristas B2B)', 'Alta y gestión de cuentas mayoristas: RUC, PIN de acceso, estado activo/suspendido e historial de chat del portal.'); ?>

<div class="doc-grid-2">
    <div class="doc-card">
        <h3>Alta de mayorista</h3>
        <p>Registra RUC, razón social, contacto y <strong>PIN</strong> (se almacena de forma segura). El cliente ingresa al portal B2B con esas credenciales.</p>
    </div>
    <div class="doc-card doc-card-master">
        <h3>Solo Master (operación sensible)</h3>
        <p>Crear, suspender, revocar y vaciar historial de chat. Si el portal B2B está <strong>apagado</strong> para el público, el menú puede mostrar “· OFF” pero el master sigue administrando cuentas.</p>
    </div>
</div>

<div class="doc-infographic">
    <svg viewBox="0 0 500 130" xmlns="http://www.w3.org/2000/svg">
        <rect x="40" y="40" width="100" height="50" rx="8" fill="#1B263B"/><text x="90" y="70" fill="#fff" font-size="10" text-anchor="middle">Master</text>
        <rect x="200" y="40" width="100" height="50" rx="8" fill="#f59e0b"/><text x="250" y="70" fill="#fff" font-size="10" text-anchor="middle">Alta VIP</text>
        <rect x="360" y="40" width="100" height="50" rx="8" fill="#10b981"/><text x="410" y="70" fill="#fff" font-size="10" text-anchor="middle">Portal B2B</text>
        <path d="M140 65 H200 M300 65 H360" stroke="#94a3b8" stroke-width="2"/>
    </svg>
</div>

<ol class="doc-steps">
    <li>Confirma en <strong>Estado del Sistema</strong> si el portal B2B está activo para el público.</li>
    <li>Crea la cuenta en <strong>Clientes VIP</strong> con PIN entregado por canal seguro.</li>
    <li>El mayorista accede en <code>/b2b/</code> (o la URL configurada).</li>
    <li>Ante abuso o impago, <strong>suspende</strong> sin borrar historial de pedidos.</li>
</ol>

<div class="doc-callout doc-callout-warn">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>Nunca compartas PINs por grupos abiertos de WhatsApp. Rotación: revoca y emite nuevo PIN desde el panel.</div>
</div>

<?= doc_link('distribuidores', 'Gestionar clientes VIP') ?>
<?php doc_section_close(); ?>
