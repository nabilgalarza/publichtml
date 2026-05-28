<?php
require_once __DIR__ . '/../lib/checkout_helpers.php';
$checkout_cfg = improgyp_checkout_config();
$checkout_banks = improgyp_checkout_banks();
$checkout_bank_base = $checkout_cfg['logos_base'] ?? 'logos_bancos/';
$checkout_deuna_logo = $checkout_cfg['deuna_logo'] ?? 'Deuna!_icono.svg.png';
$checkout_advisor_wa = preg_replace('/\D/', '', (string) ($checkout_cfg['advisor_whatsapp'] ?? '593991754887'));
$checkout_transfer_hint = (string) ($checkout_cfg['transfer_hint'] ?? '');
$checkout_advisor_text = rawurlencode('Hola IMPROGYP, necesito asesoría con mi cotización.');
?>
<div id="modal-checkout-header" class="checkout-modal-overlay hidden fixed inset-0 z-[3000] items-center justify-center p-2 sm:p-4" onclick="if(event.target===this) closeCheckoutModal()">
    <div class="checkout-modal-panel" onclick="event.stopPropagation()">
        <header class="checkout-modal-header">
            <div class="checkout-modal-header-text">
                <span class="checkout-modal-badge"><i class="fa-brands fa-whatsapp"></i> Cotización</span>
                <h3 class="checkout-modal-title">Finalizar pedido</h3>
                <p class="checkout-modal-subtitle">Entrega, contacto, pago y envío por WhatsApp</p>
            </div>
            <button type="button" onclick="closeCheckoutModal()" class="checkout-modal-close" aria-label="Cerrar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </header>

        <div class="checkout-modal-body">
            <div id="checkout-form-root" class="checkout-form-col checkout-col-scroll custom-scrollbar">
                <section class="checkout-section">
                    <h4 class="checkout-section-label"><span class="checkout-step-num">1</span> Método de recepción</h4>
                    <div class="checkout-segment-group">
                        <button type="button" id="btn-method-envio" onclick="setDeliveryMethod('envio')" class="checkout-segment">A domicilio</button>
                        <button type="button" id="btn-method-retiro" onclick="setDeliveryMethod('retiro')" class="checkout-segment checkout-segment-active">Retiro en local</button>
                    </div>
                </section>

                <section id="form-envio" class="checkout-section hidden">
                    <h4 class="checkout-section-label"><span class="checkout-step-num">3</span> Datos de entrega</h4>
                    <div class="checkout-fields">
                        <input type="text" id="delivery-address" class="checkout-input" placeholder="Dirección (calle, #, referencia)">
                        <input type="text" id="delivery-city" class="checkout-input" placeholder="Ciudad / Cantón">
                    </div>
                </section>

                <section id="form-retiro" class="checkout-section">
                    <h4 class="checkout-section-label"><span class="checkout-step-num">3</span> Punto de retiro</h4>
                    <div id="checkout-store-select-wrap" class="checkout-store-select-wrap">
                        <div class="checkout-store-dropdown" id="checkout-store-dropdown">
                            <button type="button" id="checkout-store-trigger" class="checkout-store-dropdown-trigger" aria-haspopup="listbox" aria-expanded="false" aria-controls="checkout-store-menu">
                                <span id="checkout-store-trigger-label">Selecciona sucursal</span>
                                <i class="fa-solid fa-chevron-down checkout-store-trigger-chevron" aria-hidden="true"></i>
                            </button>
                            <div id="checkout-store-menu" class="checkout-store-dropdown-menu" role="listbox" aria-label="Sucursal de retiro" hidden></div>
                        </div>
                        <p id="checkout-store-select-detail" class="checkout-store-select-detail"></p>
                    </div>
                </section>

                <section class="checkout-section">
                    <h4 class="checkout-section-label"><span class="checkout-step-num">2</span> Datos de contacto</h4>
                    <div id="form-contacto" class="checkout-fields">
                        <div class="checkout-field-row">
                            <input type="text" id="contact-nombre" autocomplete="given-name" class="checkout-input" placeholder="Nombre">
                            <input type="text" id="contact-apellido" autocomplete="family-name" class="checkout-input" placeholder="Apellido">
                        </div>
                        <input type="text" id="contact-empresa" autocomplete="organization" class="checkout-input" placeholder="Empresa (opcional)">
                        <input type="tel" id="contact-telefono" autocomplete="tel" class="checkout-input" placeholder="Teléfono móvil">
                    </div>
                </section>

                <section class="checkout-section checkout-section-last">
                    <h4 class="checkout-section-label"><span class="checkout-step-num">4</span> Forma de pago</h4>
                    <div class="checkout-payment-tabs" role="tablist" aria-label="Forma de pago">
                        <button type="button" id="btn-pago-transfer" data-payment="transfer" class="payment-tab checkout-pay-tab checkout-pay-tab-icon active" title="Transferencia" role="tab" aria-selected="true">
                            <i class="fa-solid fa-building-columns"></i><span class="checkout-pay-tab-txt">Transfer</span>
                        </button>
                        <button type="button" id="btn-pago-card" data-payment="card" class="payment-tab checkout-pay-tab checkout-pay-tab-icon" title="Tarjeta" role="tab" aria-selected="false">
                            <i class="fa-regular fa-credit-card"></i><span class="checkout-pay-tab-txt">Tarjeta</span>
                        </button>
                        <button type="button" id="btn-pago-cash" data-payment="cash" class="payment-tab checkout-pay-tab checkout-pay-tab-icon" title="Efectivo" role="tab" aria-selected="false">
                            <i class="fa-solid fa-money-bill-wave"></i><span class="checkout-pay-tab-txt">Efectivo</span>
                        </button>
                        <button type="button" id="btn-pago-deuna" data-payment="deuna" class="payment-tab checkout-pay-tab checkout-pay-tab-icon" title="De Una" role="tab" aria-selected="false">
                            <img src="<?= htmlspecialchars($checkout_bank_base . $checkout_deuna_logo, ENT_QUOTES, 'UTF-8') ?>" alt="" class="checkout-pay-tab-deuna-icon" width="18" height="18">
                            <span class="checkout-pay-tab-txt">De Una</span>
                        </button>
                    </div>

                    <div id="panel-pago-transfer" class="checkout-pay-panel" role="tabpanel">
                        <p class="checkout-pay-panel-title">Selecciona tu banco</p>
                        <div class="checkout-bank-logos" id="checkout-bank-logos">
                            <?php foreach ($checkout_banks as $b): ?>
                            <button type="button" class="checkout-bank-logo-cell" data-bank-name="<?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?>" aria-pressed="false">
                                <img src="<?= htmlspecialchars($checkout_bank_base . $b['file'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            </button>
                            <?php endforeach; ?>
                        </div>
                        <p class="checkout-pay-hint"><?= htmlspecialchars($checkout_transfer_hint, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div id="panel-pago-card" class="checkout-pay-panel hidden" role="tabpanel">
                        <p class="checkout-pay-panel-title">Tipo de tarjeta</p>
                        <div class="checkout-card-brands" id="checkout-card-brands">
                            <button type="button" class="checkout-card-brand-btn" data-card-brand="visa" title="Visa" aria-pressed="false"><i class="fa-brands fa-cc-visa"></i></button>
                            <button type="button" class="checkout-card-brand-btn" data-card-brand="mastercard" title="Mastercard" aria-pressed="false"><i class="fa-brands fa-cc-mastercard"></i></button>
                            <button type="button" class="checkout-card-brand-btn" data-card-brand="amex" title="American Express" aria-pressed="false"><i class="fa-brands fa-cc-amex"></i></button>
                            <button type="button" class="checkout-card-brand-btn" data-card-brand="discover" title="Discover" aria-pressed="false"><i class="fa-brands fa-cc-discover"></i></button>
                        </div>
                        <p class="checkout-pay-hint">Coordinaremos el cobro con tarjeta vía enlace seguro o datáfono en sucursal.</p>
                        <input type="text" id="card-name-input" class="checkout-input checkout-input-on-panel" placeholder="Nombre en la tarjeta (referencia)" required aria-required="true">
                    </div>
                    <div id="panel-pago-cash" class="checkout-pay-panel hidden">
                        <div class="checkout-cash-visual">
                            <div class="checkout-cash-icon-wrap">
                                <i class="fa-solid fa-hand-holding-dollar"></i>
                            </div>
                            <div>
                                <p class="checkout-cash-title">Pago en efectivo</p>
                                <p class="checkout-cash-desc">Al retirar en sucursal o contra entrega según disponibilidad en tu ciudad.</p>
                            </div>
                        </div>
                    </div>
                    <div id="panel-pago-deuna" class="checkout-pay-panel hidden">
                        <div class="checkout-deuna-visual">
                            <img src="<?= htmlspecialchars($checkout_bank_base . $checkout_deuna_logo, ENT_QUOTES, 'UTF-8') ?>" alt="De Una" class="checkout-deuna-logo">
                            <p class="checkout-pay-hint">Solicita el número De Una de la sucursal asignada al confirmar por WhatsApp.</p>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="checkout-summary-col">
                <div class="checkout-summary-inner">
                    <div class="checkout-summary-head">
                        <div>
                            <h4 class="checkout-summary-title">Resumen de pedido</h4>
                            <p id="checkout-summary-meta" class="checkout-summary-meta">0 productos</p>
                        </div>
                        <a href="https://wa.me/<?= htmlspecialchars($checkout_advisor_wa, ENT_QUOTES, 'UTF-8') ?>?text=<?= $checkout_advisor_text ?>" target="_blank" rel="noopener" class="checkout-advisor-link">
                            <i class="fa-solid fa-headset"></i> Asesor
                        </a>
                    </div>
                    <div id="check-list" class="checkout-items-scroll custom-scrollbar"></div>
                    <div class="checkout-totals-block">
                        <div class="checkout-total-row">
                            <span>Subtotal (sin IVA)</span>
                            <span class="tabular-nums" id="checkout-base">$0.00</span>
                        </div>
                        <div class="checkout-total-row">
                            <span>IVA (15%)</span>
                            <span class="tabular-nums" id="checkout-iva">$0.00</span>
                        </div>
                        <div class="checkout-total-row checkout-total-row-grand">
                            <span>Total estimado</span>
                            <span class="tabular-nums checkout-grand-amount" id="checkout-total">$0.00</span>
                        </div>
                        <p class="checkout-totals-disclaimer">Cotización referencial. Precios de catálogo sin IVA; el total incluye IVA 15%.</p>
                    </div>
                    <button type="button" onclick="submitCheckout()" class="checkout-wa-cta">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>Enviar pedido por WhatsApp</span>
                    </button>
                </div>
            </aside>
        </div>
    </div>
</div>
