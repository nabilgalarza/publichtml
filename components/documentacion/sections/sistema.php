<?php require __DIR__ . '/_helpers.php'; doc_section_open('sistema', 'Estado del Sistema', 'Interruptores de mantenimiento de la tienda B2C, activación del portal B2B público, limpieza de caché y mensajes de estado para visitantes.'); ?>

<div class="doc-grid-2">
    <div class="doc-card">
        <h3>Tienda B2C (mantenimiento)</h3>
        <p>Activa el modo mantenimiento para mostrar un aviso global en la web pública mientras actualizas catálogo o migraciones.</p>
    </div>
    <div class="doc-card doc-card-master">
        <h3>Portal B2B público</h3>
        <p>Switch <strong>b2b_publico_activo</strong>: cuando está OFF, el portal muestra pantalla de mantenimiento a visitantes; el master sigue gestionando mayoristas desde el panel.</p>
    </div>
</div>

<div class="doc-infographic">
    <svg viewBox="0 0 440 100" xmlns="http://www.w3.org/2000/svg">
        <rect x="40" y="30" width="80" height="40" rx="20" fill="#e2e8f0"/>
        <circle cx="100" cy="50" r="16" fill="#94a3b8"/>
        <text x="80" y="90" font-size="9" text-anchor="middle" fill="#64748b">B2C OFF</text>
        <rect x="180" y="30" width="80" height="40" rx="20" fill="#bbf7d0"/>
        <circle cx="200" cy="50" r="16" fill="#22c55e"/>
        <text x="220" y="90" font-size="9" text-anchor="middle" fill="#64748b">B2C ON</text>
        <rect x="320" y="30" width="80" height="40" rx="20" fill="#fed7aa"/>
        <circle cx="340" cy="50" r="16" fill="#f97316"/>
        <text x="360" y="90" font-size="9" text-anchor="middle" fill="#64748b">B2B switch</text>
    </svg>
</div>

<ol class="doc-steps">
    <li>Entra a <strong>Estado del Sistema</strong> (solo perfiles autorizados).</li>
    <li>Antes de deploy, activa mantenimiento B2C si aplica.</li>
    <li>Tras validar mayoristas piloto, enciende el <strong>portal B2B público</strong>.</li>
    <li>Usa <strong>limpiar caché</strong> si los cambios de apariencia no se ven de inmediato.</li>
    <li>Desactiva mantenimiento y verifica home + portal en incógnito.</li>
</ol>

<div class="doc-callout doc-callout-warn">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>Encender el portal B2B sin cuentas VIP creadas genera soporte vacío. Orden recomendado: altas VIP → prueba piloto → switch público ON.</div>
</div>

<?= doc_link('sistema', 'Abrir Estado del Sistema') ?>
<?php doc_section_close(); ?>
