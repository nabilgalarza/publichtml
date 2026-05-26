<?php
/**
 * Omnibar compartido: burbuja IA + barra móvil fija.
 * @var bool $omnibar_show_mobile Mostrar barra inferior en móvil (default true en páginas públicas)
 */
$omnibar_show_mobile = $omnibar_show_mobile ?? true;

if (empty($GLOBALS['improgyp_omnibar_styles'])) {
    $GLOBALS['improgyp_omnibar_styles'] = true;
    include __DIR__ . '/omnibar_styles.php';
}

if (empty($GLOBALS['improgyp_ai_bubble'])) {
    $GLOBALS['improgyp_ai_bubble'] = true;
?>
<div id="ai-bubble" class="ai-bubble" role="dialog" aria-labelledby="ai-bubble-title" aria-hidden="true">
    <div class="ai-bubble-header">
        <div id="ai-bubble-title" class="ai-bubble-title">
            <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Asistente IMPROGYP
        </div>
        <button type="button" onclick="typeof cerrarBurbujaIA==='function'&&cerrarBurbujaIA()" class="ai-bubble-close" aria-label="Cerrar asistente">
            <i class="fa-solid fa-xmark text-base"></i>
        </button>
    </div>
    <div id="ai-bubble-text" class="ai-bubble-text">Analizando el catálogo…</div>
</div>
<?php } ?>

<?php if ($omnibar_show_mobile): ?>
<div class="omni-bar-container md:hidden" id="omni-bar-mobile">
    <?php $omnibar_variant = 'mobile'; include __DIR__ . '/omnibar_input.php'; ?>
</div>
<?php endif; ?>
