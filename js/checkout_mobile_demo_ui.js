/**
 * Demo checkout móvil — variantes sheet (estado local, sin backend).
 */
(function () {
    const panel = document.getElementById('checkout-demo-panel');
    if (!panel) return;

    const IVA = 0.15;
    const CARD_LABELS = { visa: 'Visa', mastercard: 'Mastercard', amex: 'Amex', discover: 'Discover' };
    const PAY_LABELS = { transfer: 'Transferencia', card: 'Tarjeta', cash: 'Efectivo', deuna: 'De Una' };

    const VARIANTS = ['sheet', 'sheet-open', 'peek', 'dock'];
    const VARIANT_DESC = {
        sheet: 'Toca la barra inferior para desplegar la bolsa con todos los productos.',
        'sheet-open': 'Sheet abierto: formulario arriba, bolsa desplazable abajo.',
        peek: 'El resumen asoma a media altura; sube la hoja para ver la lista completa.',
        dock: 'Resumen fijo: lista y totales siempre visibles sobre el botón Enviar.'
    };

    const state = {
        variant: 'sheet-open',
        delivery: 'retiro',
        store: { id: 'gye', label: 'Matriz Guayaquil — Guayaquil', detail: 'Av. Francisco de Orellana · 04 2000000' },
        contact: { nombre: 'Nabil', apellido: 'Galarza', empresa: 'Provind', telefono: '0991754887' },
        address: '',
        city: '',
        payment: 'transfer',
        bank: 'Pichincha',
        cardBrand: null,
        cardName: '',
        cart: [
            { codigo: '20MDMS800', nombre: 'Lijadora orbital aleatoria', imagen: 'img_catalogo/20MDMS800.webp', precio: 164.22, cantidad: 2 },
            { codigo: '20MOS600R', nombre: 'Lijadora orbital aleatoria', imagen: 'img_catalogo/20MOS600R.webp', precio: 150.66, cantidad: 1 },
            { codigo: '20MDMS500', nombre: 'Lijadora orbital aleatoria', imagen: 'img_catalogo/20MDMS500.webp', precio: 132.97, cantidad: 1 }
        ]
    };

    const barOpen = document.getElementById('demo-bar-open');
    const sheetHandle = document.getElementById('demo-sheet-handle');
    const backdrop = document.getElementById('demo-sheet-backdrop');
    const variantDesc = document.getElementById('demo-variant-desc');

    function money(n) {
        return '$' + (Number(n) || 0).toFixed(2);
    }

    function calcTotals() {
        let base = 0;
        let units = 0;
        state.cart.forEach((item) => {
            base += item.precio * item.cantidad;
            units += item.cantidad;
        });
        const iva = base * IVA;
        return { base, iva, total: base + iva, units, lines: state.cart.length };
    }

    function escapeHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
    }

    function renderChips() {
        const wrap = document.getElementById('demo-sheet-chips');
        if (!wrap) return;
        if (!state.cart.length) {
            wrap.innerHTML = '';
            wrap.setAttribute('aria-hidden', 'true');
            return;
        }
        wrap.setAttribute('aria-hidden', 'false');
        wrap.innerHTML = state.cart.map((item) => {
            const cod = escapeHtml(item.codigo);
            return `<span class="demo-sheet-chip" title="${escapeHtml(item.nombre)}">
                <img src="${escapeHtml(item.imagen)}" alt="" onerror="this.src='favicon-app.png?v=5'">
                <span>${item.cantidad}× ${cod}</span>
            </span>`;
        }).join('');
    }

    function renderCart() {
        const list = document.getElementById('demo-cart-list');
        if (!list) return;

        if (!state.cart.length) {
            list.innerHTML = '<p class="checkout-empty">Tu bolsa está vacía.</p>';
            renderChips();
            renderTotals();
            return;
        }

        list.innerHTML = state.cart.map((item) => {
            const line = item.precio * item.cantidad;
            const cod = escapeHtml(item.codigo);
            return `<div class="checkout-item-row" data-cart-codigo="${cod}">
                <img src="${escapeHtml(item.imagen)}" alt="" class="checkout-item-thumb" onerror="this.src='favicon-app.png?v=5'">
                <div class="checkout-item-body">
                    <div class="checkout-item-name">${escapeHtml(item.nombre)}</div>
                    <div class="checkout-item-meta">REF ${cod}</div>
                    <div class="checkout-item-qty-stepper">
                        <button type="button" class="checkout-qty-btn" data-qty-delta="-1" data-codigo="${cod}">−</button>
                        <span class="checkout-qty-num">${item.cantidad}</span>
                        <button type="button" class="checkout-qty-btn" data-qty-delta="1" data-codigo="${cod}">+</button>
                    </div>
                </div>
                <div class="checkout-item-price-col">
                    <div class="checkout-item-unit">${money(item.precio)} c/u</div>
                    <div class="checkout-item-line">${money(line)}</div>
                </div>
                <button type="button" class="checkout-item-remove" data-remove-codigo="${cod}" title="Eliminar"><i class="fa-solid fa-trash-can"></i></button>
            </div>`;
        }).join('');
        renderChips();
        renderTotals();
    }

    function renderTotals() {
        const t = calcTotals();
        const p = t.lines === 1 ? 'producto' : 'productos';
        const u = t.units === 1 ? 'unidad' : 'unidades';
        const meta = `${t.lines} ${p} · ${t.units} ${u}`;

        ['demo-summary-meta', 'demo-bar-meta'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.textContent = meta;
        });
        ['demo-bar-total', 'demo-checkout-total'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.textContent = money(t.total);
        });
        const baseEl = document.getElementById('demo-checkout-base');
        const ivaEl = document.getElementById('demo-checkout-iva');
        if (baseEl) baseEl.textContent = money(t.base);
        if (ivaEl) ivaEl.textContent = money(t.iva);

        updateAccordionPreviews();
    }

    function syncDeliveryUI() {
        panel.querySelectorAll('.demo-delivery-btn').forEach((btn) => {
            const on = btn.dataset.delivery === state.delivery;
            btn.classList.toggle('checkout-segment-active', on);
        });
        panel.querySelectorAll('.demo-block-retiro').forEach((el) => {
            el.classList.toggle('hidden', state.delivery !== 'retiro');
        });
        panel.querySelectorAll('.demo-block-envio').forEach((el) => {
            el.classList.toggle('hidden', state.delivery !== 'envio');
        });
        panel.querySelectorAll('.demo-store-label').forEach((el) => {
            el.textContent = state.store.label;
        });
        panel.querySelectorAll('.demo-store-detail').forEach((el) => {
            el.textContent = state.store.detail;
        });
    }

    function syncContactUI() {
        panel.querySelectorAll('.demo-contact-nombre').forEach((el) => { el.value = state.contact.nombre; });
        panel.querySelectorAll('.demo-contact-apellido').forEach((el) => { el.value = state.contact.apellido; });
        panel.querySelectorAll('.demo-contact-empresa').forEach((el) => { el.value = state.contact.empresa; });
        panel.querySelectorAll('.demo-contact-telefono').forEach((el) => { el.value = state.contact.telefono; });
        panel.querySelectorAll('.demo-delivery-address').forEach((el) => { el.value = state.address; });
        panel.querySelectorAll('.demo-delivery-city').forEach((el) => { el.value = state.city; });
    }

    function readContactFromInput(target) {
        if (target.classList.contains('demo-contact-nombre')) state.contact.nombre = target.value;
        if (target.classList.contains('demo-contact-apellido')) state.contact.apellido = target.value;
        if (target.classList.contains('demo-contact-empresa')) state.contact.empresa = target.value;
        if (target.classList.contains('demo-contact-telefono')) state.contact.telefono = target.value;
        if (target.classList.contains('demo-delivery-address')) state.address = target.value;
        if (target.classList.contains('demo-delivery-city')) state.city = target.value;
        syncContactUI();
        updateAccordionPreviews();
    }

    function syncPaymentUI() {
        panel.querySelectorAll('.demo-payment-tabs [data-payment]').forEach((btn) => {
            const on = btn.dataset.payment === state.payment;
            btn.classList.toggle('active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panel.querySelectorAll('.demo-pay-panel').forEach((p) => {
            p.classList.toggle('hidden', p.dataset.payPanel !== state.payment);
        });
        panel.querySelectorAll('.demo-bank-logos [data-bank-name]').forEach((btn) => {
            const on = btn.dataset.bankName === state.bank;
            btn.classList.toggle('checkout-bank-selected', on);
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        panel.querySelectorAll('.demo-card-brands [data-card-brand]').forEach((btn) => {
            const on = btn.dataset.cardBrand === state.cardBrand;
            btn.classList.toggle('checkout-card-selected', on);
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        panel.querySelectorAll('.demo-card-name').forEach((el) => {
            if (el !== document.activeElement) el.value = state.cardName;
        });
    }

    function updateAccordionPreviews() {
        const recep = panel.querySelector('[data-mob-preview="recepcion"]');
        if (recep) {
            recep.textContent = state.delivery === 'retiro'
                ? `Retiro · ${state.store.label}`
                : `Envío · ${state.city || state.address || 'sin dirección'}`;
        }
        const cont = panel.querySelector('[data-mob-preview="contacto"]');
        if (cont) {
            const name = `${state.contact.nombre} ${state.contact.apellido}`.trim();
            cont.textContent = name && state.contact.telefono
                ? `${name} · ${state.contact.telefono}`
                : (name || state.contact.telefono || 'Completar datos');
        }
        const pago = panel.querySelector('[data-mob-preview="pago"]');
        if (pago) {
            let txt = PAY_LABELS[state.payment] || state.payment;
            if (state.payment === 'transfer' && state.bank) txt += ` · ${state.bank}`;
            if (state.payment === 'card' && state.cardBrand) txt += ` · ${CARD_LABELS[state.cardBrand] || state.cardBrand}`;
            pago.textContent = txt;
        }
    }

    function setDelivery(method) {
        state.delivery = method;
        syncDeliveryUI();
        updateAccordionPreviews();
    }

    function setStore(id, label, detail) {
        state.store = { id, label, detail };
        closeAllStoreMenus();
        syncDeliveryUI();
        updateAccordionPreviews();
    }

    function setPayment(method) {
        state.payment = method;
        syncPaymentUI();
        updateAccordionPreviews();
    }

    function changeQty(codigo, delta) {
        const item = state.cart.find((c) => c.codigo === codigo);
        if (!item) return;
        item.cantidad += delta;
        if (item.cantidad <= 0) {
            state.cart = state.cart.filter((c) => c.codigo !== codigo);
        }
        renderCart();
    }

    function removeItem(codigo) {
        state.cart = state.cart.filter((c) => c.codigo !== codigo);
        renderCart();
    }

    function syncStoreMenuOverflow() {
        const anyOpen = !!panel.querySelector('.demo-store-menu:not([hidden])');
        panel.classList.toggle('checkout-form-store-open', anyOpen);
    }

    function closeAllStoreMenus() {
        panel.querySelectorAll('.demo-store-menu').forEach((m) => { m.hidden = true; });
        panel.querySelectorAll('.demo-store-trigger').forEach((t) => { t.setAttribute('aria-expanded', 'false'); });
        syncStoreMenuOverflow();
    }

    function buildWaPreview() {
        const t = calcTotals();
        const name = `${state.contact.nombre} ${state.contact.apellido}`.trim();
        let msg = '¡Hola Improgyp! (DEMO)\n\n';
        msg += `*DATOS DE CONTACTO:*\n*Nombre:* ${name}\n*Teléfono:* ${state.contact.telefono}\n`;
        if (state.contact.empresa) msg += `*Empresa:* ${state.contact.empresa}\n`;
        msg += '\n*RECEPCIÓN:*\n';
        if (state.delivery === 'retiro') {
            msg += `*Retiro en local*\n*Sucursal:* ${state.store.label}\n`;
        } else {
            msg += `*Envío a domicilio*\n*Dirección:* ${state.address}\n*Ciudad:* ${state.city}\n`;
        }
        msg += '\n*INFORMACIÓN DE PAGO:*\n';
        let pay = PAY_LABELS[state.payment];
        if (state.payment === 'transfer') pay += ` (${state.bank})`;
        if (state.payment === 'card' && state.cardBrand) pay += ` (${CARD_LABELS[state.cardBrand]})`;
        msg += `*Forma de Pago:* ${pay}\n`;
        msg += '\n*PRODUCTOS:*\n';
        state.cart.forEach((item) => {
            msg += `• ${item.cantidad}x ${item.nombre} (${money(item.precio)} c/u)\n`;
        });
        msg += `\n*Subtotal (Neto):* ${money(t.base)}\n*IVA (15%):* ${money(t.iva)}\n*TOTAL ESTIMADO:* ${money(t.total)}`;
        return msg;
    }

    function submitDemo() {
        if (!state.cart.length) {
            alert('Agrega al menos un producto a la bolsa (demo).');
            return;
        }
        if (state.payment === 'transfer' && !state.bank) {
            alert('Selecciona un banco.');
            return;
        }
        if (state.payment === 'card') {
            if (!state.cardBrand) { alert('Selecciona tipo de tarjeta.'); return; }
            if (!state.cardName.trim()) { alert('Indica nombre en la tarjeta.'); return; }
        }
        if (state.delivery === 'envio' && (!state.address.trim() || !state.city.trim())) {
            alert('Completa dirección y ciudad.');
            return;
        }
        alert(buildWaPreview());
    }

    function syncSheetUi() {
        const open = panel.classList.contains('checkout-mob-sheet-open');
        if (barOpen) {
            barOpen.setAttribute('aria-expanded', open ? 'true' : 'false');
            barOpen.hidden = state.variant === 'dock';
        }
        if (backdrop) {
            const showBackdrop = open && (state.variant === 'sheet' || state.variant === 'peek');
            backdrop.hidden = !showBackdrop;
            backdrop.style.display = showBackdrop ? '' : 'none';
        }
    }

    function openSheet() {
        if (state.variant === 'dock') return;
        closeAllStoreMenus();
        panel.classList.add('checkout-mob-sheet-open');
        syncSheetUi();
    }

    function closeSheet() {
        panel.classList.remove('checkout-mob-sheet-open');
        syncSheetUi();
    }

    function toggleSheet() {
        if (state.variant === 'dock') return;
        if (panel.classList.contains('checkout-mob-sheet-open')) closeSheet();
        else openSheet();
    }

    function setVariant(variant) {
        if (!VARIANTS.includes(variant)) variant = 'sheet-open';
        state.variant = variant;
        closeAllStoreMenus();

        VARIANTS.forEach((v) => panel.classList.remove('checkout-mob-variant-' + v));
        panel.classList.add('checkout-mob-variant-' + variant);
        panel.classList.remove('checkout-mob-sheet-open');

        if (variant === 'sheet-open') openSheet();
        else if (variant === 'dock') syncSheetUi();
        else syncSheetUi();

        document.querySelectorAll('[data-variant]').forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.variant === variant);
        });
        if (variantDesc) variantDesc.textContent = VARIANT_DESC[variant] || '';
    }

    function toggleAccordionItem(item) {
        const willOpen = !item.classList.contains('is-open');
        panel.querySelectorAll('.checkout-mob-acc-item').forEach((other) => {
            other.classList.remove('is-open');
            other.querySelector('.checkout-mob-acc-trigger')?.setAttribute('aria-expanded', 'false');
        });
        item.classList.toggle('is-open', willOpen);
        item.querySelector('.checkout-mob-acc-trigger')?.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    }

    document.querySelectorAll('[data-variant]').forEach((btn) => {
        btn.addEventListener('click', () => setVariant(btn.dataset.variant || 'sheet-open'));
    });

    panel.querySelectorAll('.checkout-mob-acc-trigger').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const item = trigger.closest('.checkout-mob-acc-item');
            if (item) toggleAccordionItem(item);
        });
    });

    if (barOpen) barOpen.addEventListener('click', toggleSheet);
    if (sheetHandle) sheetHandle.addEventListener('click', toggleSheet);
    if (backdrop) backdrop.addEventListener('click', closeSheet);

    document.getElementById('demo-submit-sheet')?.addEventListener('click', submitDemo);
    document.getElementById('demo-submit-bar')?.addEventListener('click', submitDemo);

    panel.addEventListener('click', (e) => {
        const deliveryBtn = e.target.closest('.demo-delivery-btn');
        if (deliveryBtn) {
            setDelivery(deliveryBtn.dataset.delivery);
            return;
        }

        const storeOpt = e.target.closest('.demo-store-option');
        if (storeOpt) {
            setStore(storeOpt.dataset.storeId, storeOpt.dataset.storeLabel, storeOpt.dataset.storeDetail);
            return;
        }

        const storeTrigger = e.target.closest('.demo-store-trigger');
        if (storeTrigger) {
            const wrap = storeTrigger.closest('.demo-store-wrap');
            const menu = wrap?.querySelector('.demo-store-menu');
            if (menu) {
                const open = menu.hidden;
                closeAllStoreMenus();
                menu.hidden = !open;
                storeTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                syncStoreMenuOverflow();
                if (open) {
                    const formCol = panel.querySelector('.checkout-form-col');
                    const item = storeTrigger.closest('.checkout-mob-acc-item');
                    if (formCol && item) {
                        requestAnimationFrame(() => {
                            const menuBottom = menu.getBoundingClientRect().bottom;
                            const formBottom = formCol.getBoundingClientRect().bottom;
                            if (menuBottom > formBottom - 8) {
                                formCol.scrollTop += menuBottom - formBottom + 16;
                            }
                        });
                    }
                }
            }
            return;
        }

        const payBtn = e.target.closest('.demo-payment-tabs [data-payment]');
        if (payBtn) {
            setPayment(payBtn.dataset.payment);
            return;
        }

        const bankBtn = e.target.closest('.demo-bank-logos [data-bank-name]');
        if (bankBtn) {
            state.bank = bankBtn.dataset.bankName;
            syncPaymentUI();
            updateAccordionPreviews();
            return;
        }

        const cardBtn = e.target.closest('.demo-card-brands [data-card-brand]');
        if (cardBtn) {
            state.cardBrand = cardBtn.dataset.cardBrand;
            syncPaymentUI();
            updateAccordionPreviews();
            return;
        }

        const qtyBtn = e.target.closest('[data-qty-delta]');
        if (qtyBtn) {
            changeQty(qtyBtn.dataset.codigo, parseInt(qtyBtn.dataset.qtyDelta, 10));
            return;
        }

        const removeBtn = e.target.closest('[data-remove-codigo]');
        if (removeBtn) {
            removeItem(removeBtn.dataset.removeCodigo);
        }
    });

    panel.addEventListener('input', (e) => {
        const t = e.target;
        if (t.classList.contains('demo-card-name')) {
            state.cardName = t.value;
            panel.querySelectorAll('.demo-card-name').forEach((el) => {
                if (el !== t) el.value = state.cardName;
            });
            updateAccordionPreviews();
            return;
        }
        if (t.matches('.demo-contact-nombre, .demo-contact-apellido, .demo-contact-empresa, .demo-contact-telefono, .demo-delivery-address, .demo-delivery-city')) {
            readContactFromInput(t);
        }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.demo-store-wrap')) closeAllStoreMenus();
    });

    syncDeliveryUI();
    syncContactUI();
    syncPaymentUI();
    renderCart();
    setVariant('sheet-open');
})();
