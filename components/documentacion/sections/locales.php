<?php require __DIR__ . '/_helpers.php'; doc_section_open('locales', 'Sucursales y Locales', 'Puntos de venta físicos: dirección, mapa, horarios y contacto mostrados en la web.'); ?>

<div class="doc-card">
    <h3>Datos por sucursal</h3>
    <p>Nombre comercial, dirección, referencia, teléfono, horario y coordenadas para el mapa. El visitante elige la tienda más cercana desde la sección de locales en la web.</p>
</div>

<ol class="doc-steps">
    <li>Abre <strong>Sucursales</strong> en el panel.</li>
    <li>Alta de local con datos completos (evita mapas en 0,0).</li>
    <li>Guarda y comprueba el pin en la página pública.</li>
    <li>Al cerrar temporalmente, edita horario o añade nota en descripción.</li>
</ol>

<?= doc_link('locales', 'Gestionar locales') ?>
<?php doc_section_close(); ?>
