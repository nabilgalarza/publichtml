<?php
/** Bloque de pago reutilizable en la demo móvil (step + acordeón). */
?>
<div class="checkout-payment-tabs demo-payment-tabs" role="tablist">
    <button type="button" data-payment="transfer" class="payment-tab checkout-pay-tab checkout-pay-tab-icon active" role="tab"><i class="fa-solid fa-building-columns"></i><span class="checkout-pay-tab-txt">Transfer</span></button>
    <button type="button" data-payment="card" class="payment-tab checkout-pay-tab checkout-pay-tab-icon" role="tab"><i class="fa-regular fa-credit-card"></i><span class="checkout-pay-tab-txt">Tarjeta</span></button>
    <button type="button" data-payment="cash" class="payment-tab checkout-pay-tab checkout-pay-tab-icon" role="tab"><i class="fa-solid fa-money-bill-wave"></i><span class="checkout-pay-tab-txt">Efectivo</span></button>
    <button type="button" data-payment="deuna" class="payment-tab checkout-pay-tab checkout-pay-tab-icon" role="tab"><i class="fa-solid fa-qrcode"></i><span class="checkout-pay-tab-txt">De Una</span></button>
</div>
<div class="checkout-pay-panel demo-pay-panel" data-pay-panel="transfer">
    <p class="checkout-pay-panel-title">Selecciona tu banco</p>
    <div class="checkout-bank-logos demo-bank-logos">
        <?php foreach ($checkout_banks as $b): ?>
        <button type="button" class="checkout-bank-logo-cell" data-bank-name="<?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?>">
            <img src="<?= htmlspecialchars($checkout_bank_base . $b['file'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" onerror="this.style.display='none'">
        </button>
        <?php endforeach; ?>
    </div>
    <p class="checkout-pay-hint"><?= htmlspecialchars($checkout_transfer_hint, ENT_QUOTES, 'UTF-8') ?></p>
</div>
<div class="checkout-pay-panel demo-pay-panel hidden" data-pay-panel="card">
    <p class="checkout-pay-panel-title">Tipo de tarjeta</p>
    <div class="checkout-card-brands demo-card-brands">
        <button type="button" class="checkout-card-brand-btn" data-card-brand="visa"><i class="fa-brands fa-cc-visa"></i></button>
        <button type="button" class="checkout-card-brand-btn" data-card-brand="mastercard"><i class="fa-brands fa-cc-mastercard"></i></button>
        <button type="button" class="checkout-card-brand-btn" data-card-brand="amex"><i class="fa-brands fa-cc-amex"></i></button>
        <button type="button" class="checkout-card-brand-btn" data-card-brand="discover"><i class="fa-brands fa-cc-discover"></i></button>
    </div>
    <p class="checkout-pay-hint">Coordinaremos el cobro con tarjeta vía enlace seguro o datáfono.</p>
    <input type="text" class="checkout-input checkout-input-on-panel demo-card-name" placeholder="Nombre en la tarjeta (referencia)">
</div>
<div class="checkout-pay-panel demo-pay-panel hidden" data-pay-panel="cash">
    <div class="checkout-cash-visual">
        <div class="checkout-cash-icon-wrap"><i class="fa-solid fa-hand-holding-dollar"></i></div>
        <div>
            <p class="checkout-cash-title">Pago en efectivo</p>
            <p class="checkout-cash-desc">Al retirar en sucursal o contra entrega según ciudad.</p>
        </div>
    </div>
</div>
<div class="checkout-pay-panel demo-pay-panel hidden" data-pay-panel="deuna">
    <div class="checkout-deuna-visual">
        <img src="<?= htmlspecialchars($checkout_bank_base . $checkout_deuna_logo, ENT_QUOTES, 'UTF-8') ?>" alt="De Una" class="checkout-deuna-logo" onerror="this.style.display='none'">
        <p class="checkout-pay-hint">Solicita el número De Una al confirmar por WhatsApp.</p>
    </div>
</div>
