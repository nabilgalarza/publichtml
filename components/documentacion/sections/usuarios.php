<?php require __DIR__ . '/_helpers.php'; doc_section_open('usuarios', 'Usuarios Admin', 'Cuentas del panel: roles Master y Gestor. Límite de cuentas y permisos diferenciados.'); ?>

<div class="doc-card doc-card-master">
    <h3><i class="fa-solid fa-user-shield"></i> Solo Master</h3>
    <p>Esta sección no aparece para gestores. Puedes crear hasta el límite configurado de administradores, editar credenciales y eliminar cuentas obsoletas.</p>
</div>

<div class="doc-grid-2">
    <div class="doc-card">
        <h3>Master</h3>
        <p>Control total: B2B, sistema, usuarios y métricas sensibles.</p>
    </div>
    <div class="doc-card">
        <h3>Gestor</h3>
        <p>Operación comercial y de contenido sin tocar usuarios ni switches globales B2B.</p>
    </div>
</div>

<ol class="doc-steps">
    <li>Master → <strong>Usuarios Admin</strong>.</li>
    <li>Crea gestor con correo único y contraseña robusta.</li>
    <li>Entrega credenciales por canal privado.</li>
    <li>Audita periódicamente y elimina cuentas de personal que ya no trabaja en la empresa.</li>
</ol>

<div class="doc-callout doc-callout-warn">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>No compartas la cuenta master. Si se alcanza el límite de usuarios, elimina una cuenta inactiva antes de crear otra.</div>
</div>

<?= doc_link('usuarios', 'Administrar usuarios') ?>
<?php doc_section_close(); ?>
