<?php require __DIR__ . '/_helpers.php'; doc_section_open('locales', 'Sucursales y Locales', 'Puntos de venta físicos: dirección, mapa, horarios y contacto mostrados en la web.'); ?>

<div class="doc-card">
    <h3>Datos por sucursal</h3>
    <p>Nombre comercial, dirección, teléfono, horario, coordenadas para el mapa y <strong>foto propia</strong> (sección «Nuestras sucursales» en la home y modal de tienda). Sin foto subida, la web usa una imagen genérica según la ciudad sede.</p>
</div>

<ol class="doc-steps">
    <li>Abre <strong>Sucursales</strong> en el panel.</li>
    <li>Alta o edición: completa datos y, si quieres, sube una foto horizontal (JPG/PNG/WebP, ~16:9).</li>
    <li>Guarda y revisa la miniatura en la tabla y la tarjeta en la página pública.</li>
    <li>Para volver al fondo genérico, marca <strong>Quitar foto</strong> al editar.</li>
    <li>Al cerrar temporalmente, edita horario o cobertura de domicilio.</li>
</ol>

<?= doc_link('locales', 'Gestionar locales') ?>
<?php doc_section_close(); ?>
