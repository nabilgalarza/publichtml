<?php
/** @var string $omnibar_variant 'header' | 'mobile' */
$variant = $omnibar_variant ?? 'header';
$isHeader = ($variant === 'header');
$iconClass = $isHeader ? 'fa-wand-magic-sparkles text-[#1B263B] text-sm' : 'fa-robot text-[#1B263B] text-lg';
$wrapperClass = $isHeader
    ? 'glass-panel omni-input-wrapper w-full !py-1.5 !px-4 !bg-slate-100/30 border-slate-200/50'
    : 'glass-panel omni-input-wrapper';
$btnClass = $isHeader ? 'btn-send !w-8 !h-8 !shadow-none' : 'btn-send';
$iconBtnClass = $isHeader ? 'fa-solid fa-paper-plane text-[10px]' : 'fa-solid fa-paper-plane text-sm';
$inputId = $isHeader ? ' id="omni-input-field"' : '';
?>
<div class="<?= $wrapperClass ?>">
    <i class="fa-solid <?= $iconClass ?>" aria-hidden="true"></i>
    <input type="search"<?= $inputId ?> class="omni-input omni-input-field" autocomplete="off" enterkeyhint="search"
        placeholder="Busca producto o pregúntale a la IA"
        aria-label="Buscar en catálogo o consultar al asesor IA">
    <button type="button" class="<?= $btnClass ?>" aria-label="Enviar consulta a la IA">
        <i class="btn-send-icon <?= $iconBtnClass ?>"></i>
    </button>
</div>
