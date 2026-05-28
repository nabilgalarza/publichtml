<?php
$seo_titulo = 'Demo checkout móvil | IMPROGYP';
$seo_desc = 'Prototipo interactivo del checkout en móvil.';
$base_url = '';
require_once __DIR__ . '/core_init.php';
require_once __DIR__ . '/lib/checkout_helpers.php';
$checkout_cfg = improgyp_checkout_config();
$checkout_banks = improgyp_checkout_banks();
$checkout_bank_base = $checkout_cfg['logos_base'] ?? 'logos_bancos/';
$checkout_transfer_hint = (string) ($checkout_cfg['transfer_hint'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($seo_titulo, ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="favicon-app.png?v=5">
    <?php include __DIR__ . '/components/cart_checkout_styles.php'; ?>
    <link rel="stylesheet" href="css/checkout_mobile_demo_visual.css?v=<?= time() ?>">
</head>
<body class="demo-page">
    <header class="demo-page-header">
        <h1>Demo checkout móvil</h1>
        <p>Maqueta interactiva · <a href="productos.php">Volver a tienda</a></p>
    </header>

    <div class="demo-shell">
        <div class="demo-controls" role="toolbar">
            <p class="demo-controls-title"><i class="fa-solid fa-sliders"></i> Variante de resumen</p>
            <div class="demo-control-seg">
                <button type="button" class="demo-control-btn" data-variant="sheet">
                    Sheet al tocar
                    <small>Barra abajo → desliza el resumen con lista completa</small>
                </button>
                <button type="button" class="demo-control-btn active" data-variant="sheet-open">
                    Sheet abierto
                    <small>Lista visible de entrada (recomendada)</small>
                </button>
                <button type="button" class="demo-control-btn" data-variant="peek">
                    Media hoja
                    <small>Asoma el resumen; sube al tocar la barra</small>
                </button>
                <button type="button" class="demo-control-btn" data-variant="dock">
                    Dock fijo
                    <small>Lista + totales siempre visibles sobre Enviar</small>
                </button>
            </div>
            <p class="demo-variant-desc" id="demo-variant-desc">Sheet abierto: formulario arriba, bolsa desplazable abajo.</p>
        </div>

        <div class="demo-phone">
            <div id="checkout-demo-panel" class="checkout-modal-panel checkout-mob-variant-sheet-open checkout-mob-sheet-open">
                <header class="checkout-modal-header">
                    <div class="checkout-modal-header-text">
                        <span class="checkout-modal-badge"><i class="fa-brands fa-whatsapp"></i> Cotización</span>
                        <h3 class="checkout-modal-title">Finalizar pedido</h3>
                    </div>
                    <button type="button" class="checkout-modal-close" aria-label="Cerrar" disabled><i class="fa-solid fa-xmark"></i></button>
                </header>

                <div class="checkout-modal-body">
                    <div class="checkout-form-col checkout-col-scroll">
                        <?php
                        $demo_delivery_block = function () {
                            ?>
                            <div class="checkout-segment-group demo-delivery-segments">
                                <button type="button" class="checkout-segment demo-delivery-btn" data-delivery="envio">A domicilio</button>
                                <button type="button" class="checkout-segment checkout-segment-active demo-delivery-btn" data-delivery="retiro">Retiro en local</button>
                            </div>
                            <div class="demo-block-retiro">
                                <div class="checkout-store-select-wrap">
                                    <div class="checkout-store-dropdown demo-store-wrap">
                                        <button type="button" class="checkout-store-dropdown-trigger demo-store-trigger" aria-expanded="false">
                                            <span class="demo-store-label">Matriz Guayaquil — Guayaquil</span>
                                            <i class="fa-solid fa-chevron-down checkout-store-trigger-chevron"></i>
                                        </button>
                                        <div class="checkout-store-dropdown-menu demo-store-menu" role="listbox" hidden>
                                            <button type="button" class="checkout-store-dropdown-option demo-store-option" data-store-id="gye" data-store-label="Matriz Guayaquil — Guayaquil" data-store-detail="Av. Francisco de Orellana · 04 2000000">Matriz Guayaquil — Guayaquil</button>
                                            <button type="button" class="checkout-store-dropdown-option demo-store-option" data-store-id="dur" data-store-label="Sucursal Durán" data-store-detail="Km 4.5 vía Durán · 04 2111111">Sucursal Durán</button>
                                            <button type="button" class="checkout-store-dropdown-option demo-store-option" data-store-id="uio" data-store-label="Quito — Norte" data-store-detail="Av. Amazonas · 02 2222222">Quito — Norte</button>
                                        </div>
                                    </div>
                                    <p class="checkout-store-select-detail demo-store-detail">Av. Francisco de Orellana · 04 2000000</p>
                                </div>
                            </div>
                            <div class="demo-block-envio hidden">
                                <div class="checkout-fields">
                                    <input type="text" class="checkout-input demo-delivery-address" placeholder="Dirección (calle, #, referencia)">
                                    <input type="text" class="checkout-input demo-delivery-city" placeholder="Ciudad / Cantón">
                                </div>
                            </div>
                            <?php
                        };
                        $demo_contact_block = function () {
                            ?>
                            <div class="checkout-fields">
                                <div class="checkout-field-row">
                                    <input type="text" class="checkout-input demo-contact-nombre" placeholder="Nombre" value="Nabil">
                                    <input type="text" class="checkout-input demo-contact-apellido" placeholder="Apellido" value="Galarza">
                                </div>
                                <input type="text" class="checkout-input demo-contact-empresa" placeholder="Empresa (opcional)" value="Provind">
                                <input type="tel" class="checkout-input demo-contact-telefono" placeholder="Teléfono móvil" value="0991754887">
                            </div>
                            <?php
                        };
                        ?>

                        <div class="checkout-mob-accordion-wrap">
                            <div class="checkout-mob-acc-item is-open" data-acc="recepcion">
                                <button type="button" class="checkout-mob-acc-trigger" aria-expanded="true">
                                    <span class="checkout-mob-acc-title">Recepción</span>
                                    <span class="checkout-mob-acc-preview" data-mob-preview="recepcion"></span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                                <div class="checkout-mob-acc-panel">
                                    <section class="checkout-section"><?php $demo_delivery_block(); ?></section>
                                </div>
                            </div>
                            <div class="checkout-mob-acc-item" data-acc="contacto">
                                <button type="button" class="checkout-mob-acc-trigger" aria-expanded="false">
                                    <span class="checkout-mob-acc-title">Contacto</span>
                                    <span class="checkout-mob-acc-preview" data-mob-preview="contacto"></span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                                <div class="checkout-mob-acc-panel">
                                    <section class="checkout-section"><?php $demo_contact_block(); ?></section>
                                </div>
                            </div>
                            <div class="checkout-mob-acc-item" data-acc="pago">
                                <button type="button" class="checkout-mob-acc-trigger" aria-expanded="false">
                                    <span class="checkout-mob-acc-title">Pago</span>
                                    <span class="checkout-mob-acc-preview" data-mob-preview="pago"></span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                                <div class="checkout-mob-acc-panel">
                                    <section class="checkout-section checkout-section-last">
                                        <?php include __DIR__ . '/components/checkout_mobile_demo_pay_block.php'; ?>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checkout-mob-sheet-backdrop" id="demo-sheet-backdrop"></div>

                <aside class="checkout-summary-col" id="demo-summary-sheet">
                    <button type="button" class="demo-sheet-handle" id="demo-sheet-handle" aria-label="Arrastrar resumen">
                        <span></span>
                    </button>
                    <div class="checkout-summary-inner">
                        <div class="checkout-summary-head">
                            <div>
                                <h4 class="checkout-summary-title">Resumen de pedido</h4>
                                <p class="checkout-summary-meta" id="demo-summary-meta">0 productos</p>
                            </div>
                            <span class="checkout-advisor-link"><i class="fa-solid fa-headset"></i> Asesor</span>
                        </div>
                        <div class="demo-sheet-chips" id="demo-sheet-chips" aria-hidden="true"></div>
                        <div id="demo-cart-list" class="checkout-items-scroll custom-scrollbar"></div>
                        <div class="checkout-totals-block">
                            <div class="checkout-total-row"><span>Subtotal (sin IVA)</span><span class="tabular-nums" id="demo-checkout-base">$0.00</span></div>
                            <div class="checkout-total-row"><span>IVA (15%)</span><span class="tabular-nums" id="demo-checkout-iva">$0.00</span></div>
                            <div class="checkout-total-row checkout-total-row-grand"><span>Total estimado</span><span class="tabular-nums checkout-grand-amount" id="demo-checkout-total">$0.00</span></div>
                            <p class="checkout-totals-disclaimer">Cotización referencial. Precios sin IVA; total con IVA 15%.</p>
                        </div>
                        <button type="button" class="checkout-wa-cta" id="demo-submit-sheet">
                            <i class="fa-brands fa-whatsapp"></i>
                            <span>Enviar pedido por WhatsApp</span>
                        </button>
                    </div>
                </aside>

                <div class="checkout-mob-bottom-bar">
                    <button type="button" class="checkout-mob-bar-open" id="demo-bar-open" aria-expanded="true">
                        <span class="checkout-mob-bar-meta" id="demo-bar-meta">0 productos</span>
                        <strong class="checkout-mob-bar-total" id="demo-bar-total">$0.00</strong>
                        <i class="fa-solid fa-chevron-up"></i>
                    </button>
                    <button type="button" class="checkout-mob-bar-cta" id="demo-submit-bar">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>Enviar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/checkout_mobile_demo_ui.js?v=<?= time() ?>"></script>
</body>
</html>
