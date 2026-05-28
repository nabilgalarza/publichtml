/**
 * Checkout WhatsApp — modal 2 columnas, contacto, 4 pagos, cobertura locales.json.
 */
const WA_FALLBACK = '593991754887';
const MATRIZ_STORE_ID = 'gye-matriz';

let checkoutLocales = [];
let checkoutLocalesLoaded = false;
let cartItems = [];
let currentDeliveryMethod = 'retiro';
let currentPaymentMethod = 'transfer';
let currentStore = MATRIZ_STORE_ID;
let selectedTransferBank = null;
let selectedCardBrand = null;

const CARD_BRAND_LABELS = {
    visa: 'Visa',
    mastercard: 'Mastercard',
    amex: 'American Express',
    discover: 'Discover'
};

const CITY_ALIASES = {
    gye: 'guayaquil', guayaquil: 'guayaquil',
    uio: 'quito', quito: 'quito',
    duran: 'durán', durán: 'durán',
    manta: 'manta', ambato: 'ambato', loja: 'loja',
    portoviejo: 'portoviejo', riobamba: 'riobamba'
};

const PAYMENT_LABELS = {
    transfer: 'Transferencia bancaria',
    card: 'Tarjeta (coordinar con asesor)',
    cash: 'Efectivo',
    deuna: 'De Una'
};

function getIvaPercentLabel() {
    return Math.round(getCheckoutIvaRate() * 100);
}

/** Etiqueta de pago para el mensaje WhatsApp (según método y recepción). */
function getPaymentWhatsAppLabel(method, deliveryMethod, cardName, transferBank, cardBrand) {
    switch (method) {
        case 'transfer':
            return transferBank
                ? `Transferencia bancaria (${transferBank})`
                : 'Transferencia bancaria';
        case 'card': {
            const brandLabel = cardBrand ? (CARD_BRAND_LABELS[cardBrand] || cardBrand) : '';
            const parts = [];
            if (brandLabel) parts.push(brandLabel);
            if (cardName) parts.push(`ref. ${cardName}`);
            if (parts.length) return `Tarjeta (${parts.join(' · ')})`;
            return 'Tarjeta (coordinar con asesor)';
        }
        case 'cash':
            return deliveryMethod === 'envio'
                ? 'Efectivo (Pago contra entrega)'
                : 'Efectivo (Pago al retirar en sucursal)';
        case 'deuna':
            return 'De Una';
        default:
            return PAYMENT_LABELS[method] || method;
    }
}

function ensureValidCurrentStore() {
    if (!checkoutLocales.length) {
        currentStore = '';
        return null;
    }
    const existing = currentStore ? getLocalById(currentStore) : null;
    if (existing) return existing;
    const fallback = getMatrizLocal() || checkoutLocales[0];
    if (fallback) {
        currentStore = fallback.id;
        selectStore(currentStore);
    }
    return fallback;
}

/** Precios de catálogo sin IVA; total estimado = base + IVA (config: config_checkout.json) */
function getCheckoutIvaRate() {
    const cfg = typeof window.IMPROGYP_CHECKOUT === 'object' && window.IMPROGYP_CHECKOUT
        ? window.IMPROGYP_CHECKOUT
        : null;
    const rate = cfg && typeof cfg.iva_rate === 'number' ? cfg.iva_rate : 0.15;
    return rate >= 0 && rate <= 1 ? rate : 0.15;
}

function escapeCheckoutHtml(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}

function getCheckoutImgUrl(ruta) {
    if (!ruta) return 'favicon-app.png?v=5';
    if (String(ruta).startsWith('http')) return ruta;
    const base = typeof IMPROGYP_BASE_URL === 'string' ? IMPROGYP_BASE_URL : '';
    return base + String(ruta).replace(/^\.\//, '').replace(/^\//, '');
}

/**
 * @param {Array<{cantidad:number,precioNum:number}>} items
 */
function calcCheckoutTotals(items) {
    const base = (items || []).reduce((s, i) => s + (i.cantidad || 0) * (i.precioNum || 0), 0);
    const iva = base * getCheckoutIvaRate();
    const total = base + iva;
    const units = (items || []).reduce((s, i) => s + (i.cantidad || 0), 0);
    const lines = (items || []).length;
    return { base, iva, total, units, lines };
}

function formatMoney(n) {
    return '$' + (Number(n) || 0).toFixed(2);
}

function renderCheckoutTotals(totals) {
    const meta = document.getElementById('checkout-summary-meta');
    const baseEl = document.getElementById('checkout-base');
    const ivaEl = document.getElementById('checkout-iva');
    const totalEl = document.getElementById('checkout-total');
    if (meta) {
        const p = totals.lines === 1 ? 'producto' : 'productos';
        const u = totals.units === 1 ? 'unidad' : 'unidades';
        meta.textContent = `${totals.lines} ${p} · ${totals.units} ${u}`;
    }
    if (baseEl) baseEl.textContent = formatMoney(totals.base);
    if (ivaEl) ivaEl.textContent = formatMoney(totals.iva);
    if (totalEl) totalEl.textContent = formatMoney(totals.total);
}

function normalizeCityKey(str) {
    if (!str) return '';
    let s = str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
    if (CITY_ALIASES[s]) s = CITY_ALIASES[s];
    return s;
}

function sanitizeWa(num) {
    let digits = String(num || '').replace(/\D/g, '');
    if (/^5939\d{8}$/.test(digits)) return digits;
    if (/^09\d{8}$/.test(digits)) return '593' + digits.slice(1);
    if (/^9\d{8}$/.test(digits)) return '593' + digits;
    if (digits.startsWith('593') && digits.length >= 12) {
        const t = digits.slice(0, 12);
        if (/^5939\d{8}$/.test(t)) return t;
    }
    return WA_FALLBACK;
}

function getLocalesJsonUrl() {
    const base = typeof IMPROGYP_BASE_URL === 'string' ? IMPROGYP_BASE_URL : '';
    return base + 'locales.json?v=' + Date.now();
}

async function loadCheckoutLocales() {
    try {
        const res = await fetch(getLocalesJsonUrl());
        if (!res.ok) throw new Error('locales http ' + res.status);
        const data = await res.json();
        checkoutLocales = Array.isArray(data) ? data : [];
        checkoutLocalesLoaded = true;
    } catch (e) {
        checkoutLocales = [];
        checkoutLocalesLoaded = false;
    }
    return checkoutLocales;
}

function findLocalByCity(cityText) {
    const key = normalizeCityKey(cityText);
    if (!key || !checkoutLocales.length) return null;
    for (const loc of checkoutLocales) {
        const cov = loc.cobertura || [];
        for (const c of cov) {
            if (normalizeCityKey(c) === key) return loc;
        }
    }
    for (const loc of checkoutLocales) {
        if (normalizeCityKey(loc.ciudad) === key) return loc;
    }
    return null;
}

function getLocalById(id) {
    return checkoutLocales.find((l) => l.id === id) || null;
}

function getMatrizLocal() {
    return getLocalById(MATRIZ_STORE_ID) || checkoutLocales[0] || null;
}

function getLocalDisplayName(local) {
    return local?.nombre || 'Sucursal IMPROGYP';
}

function resolveWhatsAppForCheckout(opts) {
    const method = opts.deliveryMethod || 'retiro';
    let local = null;
    let motivo = 'retiro';
    let localNombre = 'Matriz';

    if (method === 'retiro') {
        local = getLocalById(opts.storeId) || getMatrizLocal();
        motivo = 'retiro';
        localNombre = getLocalDisplayName(local);
    } else {
        const city = (opts.cityText || '').trim();
        if (city) {
            local = findLocalByCity(city);
            if (local) {
                motivo = 'domicilio_ciudad';
                localNombre = getLocalDisplayName(local);
            } else {
                local = getMatrizLocal();
                motivo = 'domicilio_matriz';
                localNombre = getLocalDisplayName(local);
            }
        } else {
            local = getMatrizLocal();
            motivo = 'domicilio_sin_ciudad';
            localNombre = getLocalDisplayName(local);
        }
    }

    return {
        wa: sanitizeWa(local?.whatsapp),
        local,
        localNombre,
        motivo
    };
}

function getStoreOptionLabel(loc) {
    if (!loc) return '';
    return `${loc.nombre || ''} — ${loc.ciudad || ''}`;
}

function positionCheckoutStoreMenu() {
    const menu = document.getElementById('checkout-store-menu');
    const trigger = document.getElementById('checkout-store-trigger');
    if (!menu || !trigger || menu.hidden) return;
    const r = trigger.getBoundingClientRect();
    menu.style.position = 'fixed';
    menu.style.top = `${r.bottom + 6}px`;
    menu.style.left = `${r.left}px`;
    menu.style.width = `${r.width}px`;
    menu.style.right = 'auto';
    menu.style.zIndex = '3100';
}

function resetCheckoutStoreMenuPosition() {
    const menu = document.getElementById('checkout-store-menu');
    if (!menu) return;
    menu.style.position = '';
    menu.style.top = '';
    menu.style.left = '';
    menu.style.width = '';
    menu.style.right = '';
    menu.style.zIndex = '';
}

function closeCheckoutStoreDropdown() {
    const menu = document.getElementById('checkout-store-menu');
    const trigger = document.getElementById('checkout-store-trigger');
    if (menu) menu.hidden = true;
    if (trigger) trigger.setAttribute('aria-expanded', 'false');
    resetCheckoutStoreMenuPosition();
}

function toggleCheckoutStoreDropdown() {
    const menu = document.getElementById('checkout-store-menu');
    const trigger = document.getElementById('checkout-store-trigger');
    if (!menu || !trigger) return;
    const open = menu.hidden;
    if (open) {
        menu.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        positionCheckoutStoreMenu();
    } else {
        closeCheckoutStoreDropdown();
    }
}

function renderCheckoutStoreDropdown() {
    const menu = document.getElementById('checkout-store-menu');
    const triggerLabel = document.getElementById('checkout-store-trigger-label');
    if (!menu) return;

    if (!checkoutLocales.length) {
        menu.innerHTML = '<p class="checkout-store-dropdown-empty">No se cargaron sucursales.</p>';
        if (triggerLabel) triggerLabel.textContent = 'Sin sucursales';
        return;
    }

    menu.innerHTML = checkoutLocales.map((loc) => {
        const selected = currentStore === loc.id;
        const label = escapeCheckoutHtml(getStoreOptionLabel(loc));
        const check = selected
            ? '<i class="fa-solid fa-check checkout-store-option-check" aria-hidden="true"></i>'
            : '<span class="checkout-store-option-check-spacer" aria-hidden="true"></span>';
        return `<button type="button" role="option" aria-selected="${selected}"
            class="checkout-store-dropdown-option${selected ? ' is-selected' : ''}"
            data-store-id="${escapeCheckoutHtml(loc.id)}">
            ${check}<span>${label}</span>
        </button>`;
    }).join('');

    const loc = getLocalById(currentStore) || checkoutLocales[0];
    if (triggerLabel && loc) triggerLabel.textContent = getStoreOptionLabel(loc);
}

function renderCheckoutStoreList() {
    renderCheckoutStoreDropdown();
    updateStoreSelectDetail();
}

function updateStoreSelectDetail() {
    const detail = document.getElementById('checkout-store-select-detail');
    const triggerLabel = document.getElementById('checkout-store-trigger-label');
    const loc = getLocalById(currentStore);
    const detailText = loc
        ? [loc.direccion, loc.telefono].filter(Boolean).join(' · ')
        : '';
    if (detail) detail.textContent = detailText;
    if (triggerLabel && loc) triggerLabel.textContent = getStoreOptionLabel(loc);
}

function selectStore(id) {
    currentStore = id;
    closeCheckoutStoreDropdown();
    renderCheckoutStoreDropdown();
    updateStoreSelectDetail();
}

function scrollCheckoutSectionTo(sectionId) {
    const el = document.getElementById(sectionId);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function initCheckoutStoreDropdown() {
    const trigger = document.getElementById('checkout-store-trigger');
    const menu = document.getElementById('checkout-store-menu');
    if (!trigger || !menu) return;

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleCheckoutStoreDropdown();
    });

    menu.addEventListener('click', (e) => {
        const opt = e.target.closest('.checkout-store-dropdown-option');
        if (!opt?.dataset.storeId) return;
        selectStore(opt.dataset.storeId);
    });

    if (!window._checkoutStoreDropdownDocBound) {
        window._checkoutStoreDropdownDocBound = true;
        document.addEventListener('click', (e) => {
            const wrap = document.getElementById('checkout-store-dropdown');
            const menu = document.getElementById('checkout-store-menu');
            if (menu && !menu.hidden && menu.contains(e.target)) return;
            if (wrap && !wrap.contains(e.target)) closeCheckoutStoreDropdown();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeCheckoutStoreDropdown();
        });
        window.addEventListener('resize', () => {
            const menu = document.getElementById('checkout-store-menu');
            if (menu && !menu.hidden) positionCheckoutStoreMenu();
        });
        document.querySelector('.checkout-form-col')?.addEventListener('scroll', () => {
            const menu = document.getElementById('checkout-store-menu');
            if (menu && !menu.hidden) positionCheckoutStoreMenu();
        }, { passive: true });
    }
}

function setDeliveryMethod(method) {
    currentDeliveryMethod = method;
    const retiro = document.getElementById('form-retiro');
    const envio = document.getElementById('form-envio');
    const btnRetiro = document.getElementById('btn-method-retiro');
    const btnEnvio = document.getElementById('btn-method-envio');
    if (retiro) retiro.classList.toggle('hidden', method !== 'retiro');
    if (envio) envio.classList.toggle('hidden', method !== 'envio');
    if (btnRetiro) btnRetiro.classList.toggle('checkout-segment-active', method === 'retiro');
    if (btnEnvio) btnEnvio.classList.toggle('checkout-segment-active', method === 'envio');
}

function setPaymentMethod(method) {
    if (!['transfer', 'card', 'cash', 'deuna'].includes(method)) return;
    currentPaymentMethod = method;
    ['transfer', 'card', 'cash', 'deuna'].forEach((m) => {
        const btn = document.getElementById('btn-pago-' + m);
        const panel = document.getElementById('panel-pago-' + m);
        const active = m === method;
        if (btn) {
            btn.classList.toggle('active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
        }
        if (panel) panel.classList.toggle('hidden', !active);
    });
}

function selectTransferBank(bankName) {
    selectedTransferBank = bankName || null;
    document.querySelectorAll('.checkout-bank-logo-cell').forEach((el) => {
        const on = el.getAttribute('data-bank-name') === bankName;
        el.classList.toggle('checkout-bank-selected', on);
        el.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
}

function selectCardBrand(brand) {
    selectedCardBrand = brand || null;
    document.querySelectorAll('.checkout-card-brand-btn').forEach((el) => {
        const on = el.getAttribute('data-card-brand') === brand;
        el.classList.toggle('checkout-card-selected', on);
        el.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
}

function initCheckoutPaymentUI() {
    const tabs = document.querySelector('.checkout-payment-tabs');
    if (tabs && !tabs.dataset.checkoutPayBound) {
        tabs.dataset.checkoutPayBound = '1';
        tabs.addEventListener('click', (e) => {
            const tab = e.target.closest('[data-payment]');
            if (!tab) return;
            e.preventDefault();
            setPaymentMethod(tab.getAttribute('data-payment'));
        });
    }

    const banks = document.getElementById('checkout-bank-logos');
    if (banks && !banks.dataset.checkoutBankBound) {
        banks.dataset.checkoutBankBound = '1';
        banks.addEventListener('click', (e) => {
            const cell = e.target.closest('[data-bank-name]');
            if (!cell) return;
            e.preventDefault();
            selectTransferBank(cell.getAttribute('data-bank-name'));
        });
    }

    const cards = document.getElementById('checkout-card-brands');
    if (cards && !cards.dataset.checkoutCardBound) {
        cards.dataset.checkoutCardBound = '1';
        cards.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-card-brand]');
            if (!btn) return;
            e.preventDefault();
            selectCardBrand(btn.getAttribute('data-card-brand'));
        });
    }
}

function loadCartItems() {
    try {
        cartItems = JSON.parse(localStorage.getItem('improgyp_carrito')) || [];
    } catch (e) {
        cartItems = [];
    }
}

window.syncCheckoutCartItems = function (carrito) {
    cartItems = Array.isArray(carrito) ? carrito : [];
};

function getCheckoutItemKey(item) {
    return item?.codigo || item?.nombre || '';
}

function removeCheckoutItem(identificador) {
    if (!identificador) return;
    if (typeof eliminarDelCarrito === 'function') {
        eliminarDelCarrito(identificador);
    } else if (typeof window.improgypEliminarDelCarritoLite === 'function') {
        window.improgypEliminarDelCarritoLite(identificador);
    } else {
        loadCartItems();
        cartItems = cartItems.filter((c) => getCheckoutItemKey(c) !== identificador);
        localStorage.setItem('improgyp_carrito', JSON.stringify(cartItems));
        if (typeof window.syncCheckoutCartItems === 'function') {
            window.syncCheckoutCartItems(cartItems);
        }
    }
    loadCartItems();
    if (!cartItems.length) {
        renderCheckoutList();
        showToastNotification('Producto eliminado. Tu bolsa está vacía.', 'info');
        return;
    }
    renderCheckoutList();
    if (typeof actualizarUICarrito === 'function') actualizarUICarrito();
}
window.removeCheckoutItem = removeCheckoutItem;

function changeCheckoutQty(identificador, delta) {
    if (!identificador || !delta) return;
    if (typeof modificarCantidad === 'function') {
        modificarCantidad(identificador, delta);
    } else if (typeof window.improgypModificarCantidadLite === 'function') {
        window.improgypModificarCantidadLite(identificador, delta);
    } else {
        loadCartItems();
        const idx = cartItems.findIndex((c) => getCheckoutItemKey(c) === identificador);
        if (idx === -1) return;
        cartItems[idx].cantidad = (cartItems[idx].cantidad || 1) + delta;
        if (cartItems[idx].cantidad <= 0) cartItems.splice(idx, 1);
        localStorage.setItem('improgyp_carrito', JSON.stringify(cartItems));
        if (typeof window.syncCheckoutCartItems === 'function') {
            window.syncCheckoutCartItems(cartItems);
        }
    }
    loadCartItems();
    if (!cartItems.length) {
        renderCheckoutList();
        showToastNotification('Producto eliminado. Tu bolsa está vacía.', 'info');
        return;
    }
    renderCheckoutList();
    if (typeof actualizarUICarrito === 'function') actualizarUICarrito();
}
window.changeCheckoutQty = changeCheckoutQty;

function renderCheckoutList() {
    const list = document.getElementById('check-list');
    if (!list) return;

    loadCartItems();
    const emptyTotals = { base: 0, iva: 0, total: 0, units: 0, lines: 0 };

    if (!cartItems.length) {
        list.innerHTML = '<p class="checkout-empty">Tu bolsa está vacía.</p>';
        renderCheckoutTotals(emptyTotals);
        return;
    }

    const totals = calcCheckoutTotals(cartItems);
    list.innerHTML = cartItems.map((item) => {
        const qty = item.cantidad || 1;
        const unit = item.precioNum || 0;
        const line = qty * unit;
        const img = escapeCheckoutHtml(getCheckoutImgUrl(item.imagen));
        const name = escapeCheckoutHtml(item.nombre);
        const ref = item.codigo ? escapeCheckoutHtml(item.codigo) : '';
        const meta = ref ? `REF ${ref}` : '';
        const itemKey = escapeCheckoutHtml(getCheckoutItemKey(item));
        const safeId = String(getCheckoutItemKey(item)).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        return `<div class="checkout-item-row" data-item-key="${itemKey}">
            <img src="${img}" alt="" class="checkout-item-thumb" loading="lazy" onerror="this.onerror=null;this.src='favicon-app.png?v=5'">
            <div class="checkout-item-body">
                <div class="checkout-item-name">${name}</div>
                ${meta ? `<div class="checkout-item-meta">${meta}</div>` : ''}
                <div class="checkout-item-qty-stepper" onclick="event.stopPropagation()">
                    <button type="button" class="checkout-qty-btn" onclick="changeCheckoutQty('${safeId}', -1)" aria-label="Reducir cantidad">−</button>
                    <span class="checkout-qty-num" aria-live="polite">${qty}</span>
                    <button type="button" class="checkout-qty-btn" onclick="changeCheckoutQty('${safeId}', 1)" aria-label="Aumentar cantidad">+</button>
                </div>
            </div>
            <div class="checkout-item-price-col">
                <div class="checkout-item-unit">${formatMoney(unit)} c/u</div>
                <div class="checkout-item-line">${formatMoney(line)}</div>
            </div>
            <button type="button" class="checkout-item-remove" onclick="removeCheckoutItem('${safeId}')" title="Eliminar producto" aria-label="Eliminar producto">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </div>`;
    }).join('');
    renderCheckoutTotals(totals);
}

function showToastNotification(mensaje, tipo) {
    const container = document.getElementById('toast-container');
    if (!container) {
        if (tipo === 'error') alert(mensaje);
        return;
    }
    const t = tipo === 'error' || tipo === 'success' || tipo === 'info' ? tipo : 'info';
    const el = document.createElement('div');
    el.className = 'improgyp-toast ' + t;
    el.textContent = mensaje;
    container.appendChild(el);
    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transition = 'opacity 0.3s';
        setTimeout(() => el.remove(), 300);
    }, 4200);
}

function getCheckoutContactData() {
    return {
        nombre: document.getElementById('contact-nombre')?.value.trim() || '',
        apellido: document.getElementById('contact-apellido')?.value.trim() || '',
        empresa: document.getElementById('contact-empresa')?.value.trim() || '',
        telefono: document.getElementById('contact-telefono')?.value.trim() || ''
    };
}

/** Negrita WhatsApp: *texto* */
function waBold(text) {
    return '*' + String(text).replace(/\*/g, '') + '*';
}

function waLabelValue(label, value) {
    return `${waBold(label + ':')} ${value}\n`;
}

/**
 * Arma el mensaje WhatsApp según plantilla de pedido IMPROGYP (negritas con *).
 */
function buildCheckoutWhatsAppMessage(contact, delivery, payment, items, totals) {
    const nombreCompleto = `${contact.nombre} ${contact.apellido}`.trim();
    let texto = '¡Hola Improgyp! Deseo realizar un pedido con los siguientes detalles:\n\n';
    texto += waBold('DATOS DE CONTACTO:') + '\n';
    texto += waLabelValue('Nombre', nombreCompleto);
    texto += waLabelValue('Teléfono', contact.telefono);
    if (contact.empresa) texto += waLabelValue('Empresa', contact.empresa);

    texto += '\n' + waBold('RECEPCIÓN:') + '\n';
    if (delivery.method === 'retiro') {
        texto += waBold('Retiro en local') + '\n';
        texto += waLabelValue('Sucursal', delivery.storeName);
        texto += waLabelValue('Atención', `${delivery.attentionName} (retiro)`);
    } else {
        texto += waBold('Envío a domicilio') + '\n';
        texto += waLabelValue('Dirección', delivery.address);
        texto += waLabelValue('Ciudad', delivery.city);
        let atencion = delivery.attentionName;
        if (delivery.attentionNote) atencion += ` (${delivery.attentionNote})`;
        texto += waLabelValue('Atención', atencion);
    }

    texto += '\n' + waBold('INFORMACIÓN DE PAGO:') + '\n';
    texto += waLabelValue('Forma de Pago', payment.label);

    texto += '\n' + waBold('PRODUCTOS:') + '\n';
    (items || []).forEach((item) => {
        const qty = item.cantidad || 1;
        const unit = (Number(item.precioNum) || 0).toFixed(2);
        texto += `• ${qty}x ${item.nombre} ($${unit} c/u)\n`;
    });

    const ivaPct = getIvaPercentLabel();
    texto += '\n' + waLabelValue('Subtotal (Neto)', `$${totals.base.toFixed(2)}`);
    texto += waLabelValue(`IVA (${ivaPct}%)`, `$${totals.iva.toFixed(2)}`);
    texto += waLabelValue('TOTAL ESTIMADO', `$${totals.total.toFixed(2)}`);
    texto += '\nQuedo atento para confirmar y coordinar los detalles.';
    return texto;
}

function isValidCheckoutPhone(telefono) {
    const digits = String(telefono || '').replace(/\D/g, '');
    return digits.length >= 9;
}

/**
 * @returns {{ ok: boolean, focus?: string, message?: string }}
 */
function validateCheckoutForm() {
    const contact = getCheckoutContactData();
    const missing = [];
    if (!contact.nombre) missing.push('contact-nombre');
    if (!contact.apellido) missing.push('contact-apellido');
    if (!contact.telefono) missing.push('contact-telefono');

    if (missing.length) {
        marcarCamposCheckoutInvalidos(missing);
        return {
            ok: false,
            focus: 'contact-nombre',
            message: 'Completa nombre, apellido y teléfono de contacto.'
        };
    }
    if (!isValidCheckoutPhone(contact.telefono)) {
        marcarCamposCheckoutInvalidos(['contact-telefono']);
        return {
            ok: false,
            focus: 'contact-telefono',
            message: 'Ingresa un teléfono válido (mínimo 9 dígitos).'
        };
    }

    if (currentDeliveryMethod === 'envio') {
        const address = document.getElementById('delivery-address')?.value.trim();
        const city = document.getElementById('delivery-city')?.value.trim();
        const missEntrega = [];
        if (!address) missEntrega.push('delivery-address');
        if (!city) missEntrega.push('delivery-city');
        if (missEntrega.length) {
            marcarCamposEntregaInvalidos();
            return {
                ok: false,
                focus: 'form-envio',
                message: 'Completa la dirección y ciudad de entrega.'
            };
        }
    } else {
        if (!checkoutLocales.length) {
            return {
                ok: false,
                focus: 'form-retiro',
                message: 'No hay sucursales disponibles. Intenta de nuevo en unos segundos.'
            };
        }
        const local = ensureValidCurrentStore();
        if (!local || !currentStore) {
            return {
                ok: false,
                focus: 'form-retiro',
                message: 'Selecciona una sucursal de retiro.'
            };
        }
    }

    if (currentPaymentMethod === 'transfer') {
        if (!selectedTransferBank) {
            return {
                ok: false,
                focus: 'panel-pago-transfer',
                message: 'Selecciona el banco donde harás la transferencia.'
            };
        }
    }

    if (currentPaymentMethod === 'card') {
        if (!selectedCardBrand) {
            return {
                ok: false,
                focus: 'panel-pago-card',
                message: 'Selecciona el tipo de tarjeta (Visa, Mastercard, etc.).'
            };
        }
        const cardName = document.getElementById('card-name-input')?.value.trim();
        if (!cardName) {
            marcarCamposCheckoutInvalidos(['card-name-input']);
            return {
                ok: false,
                focus: 'panel-pago-card',
                message: 'Indica el nombre en la tarjeta (referencia).'
            };
        }
    }

    return { ok: true };
}

/** @deprecated use validateCheckoutForm */
function validateCheckoutContact() {
    return validateCheckoutForm().ok;
}

function marcarCamposCheckoutInvalidos(fieldIds) {
    fieldIds.forEach((id) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('ring-2', 'ring-rose-400', 'border-rose-400', 'bg-rose-50/50');
        const clear = function () {
            el.classList.remove('ring-2', 'ring-rose-400', 'border-rose-400', 'bg-rose-50/50');
            el.removeEventListener('input', clear);
            el.removeEventListener('change', clear);
        };
        el.addEventListener('input', clear);
        el.addEventListener('change', clear);
    });
}

function marcarCamposEntregaInvalidos() {
    marcarCamposCheckoutInvalidos(['delivery-address', 'delivery-city']);
}

function registrarPedidoPublicoSilent(items, totalEstimado) {
    try {
        const payload = {
            total: totalEstimado,
            items: items.map((item) => ({
                nombre: item.nombre,
                cantidad: item.cantidad,
                precio: item.precioNum
            })),
            source: (typeof window.lastSearchSource === 'string' && window.lastSearchSource)
                ? window.lastSearchSource
                : 'checkout_modal'
        };
        const body = JSON.stringify(payload);
        if (navigator.sendBeacon) {
            const blob = new Blob([body], { type: 'application/json' });
            navigator.sendBeacon('api_pedido_publico.php', blob);
        } else {
            fetch('api_pedido_publico.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body,
                keepalive: true
            }).catch(() => {});
        }
    } catch (e) { /* no bloquear WhatsApp */ }
}

function clearCartAfterCheckout() {
    localStorage.setItem('improgyp_carrito', '[]');
    cartItems = [];
    if (typeof carrito !== 'undefined') {
        try { carrito.length = 0; } catch (e) {}
    }
    renderCheckoutList();
    if (typeof actualizarUICarrito === 'function') actualizarUICarrito();
    else if (typeof window.improgypOnCartUpdated === 'function') window.improgypOnCartUpdated();
    const drawer = document.getElementById('cart-drawer-overlay');
    if (drawer?.classList.contains('show')) toggleCartDrawer();
}

function openCheckoutModal() {
    const modal = document.getElementById('modal-checkout-header');
    if (!modal) {
        window.location.href = 'productos.php';
        return;
    }
    const drawer = document.getElementById('cart-drawer-overlay');
    if (drawer?.classList.contains('show') && typeof toggleCartDrawer === 'function') {
        toggleCartDrawer();
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    closeCheckoutStoreDropdown();

    loadCartItems();
    renderCheckoutList();
    setDeliveryMethod(currentDeliveryMethod);
    setPaymentMethod(currentPaymentMethod);

    loadCheckoutLocales()
        .then(() => {
            ensureValidCurrentStore();
            renderCheckoutStoreList();
        })
        .catch(() => {
            renderCheckoutStoreList();
        });
}

function onCheckoutVisibilityRefresh() {
    if (document.visibilityState !== 'visible') return;
    const modal = document.getElementById('modal-checkout-header');
    if (!modal || modal.classList.contains('hidden')) return;
    loadCheckoutLocales().then(() => {
        ensureValidCurrentStore();
        renderCheckoutStoreList();
    });
}

function toggleCheckoutModal() {
    const modal = document.getElementById('modal-checkout-header');
    if (!modal) {
        window.location.href = 'productos.php';
        return;
    }
    if (modal.classList.contains('hidden')) {
        loadCartItems();
        if (!cartItems.length) {
            showToastNotification('Tu bolsa está vacía. Agrega productos para cotizar.', 'info');
            return;
        }
        openCheckoutModal();
    } else {
        closeCheckoutModal();
    }
}

/** Clic en icono bolsa del header: abre checkout directo (sin drawer lateral). */
function improgypOpenCart(evt) {
    if (evt) evt.preventDefault();
    const modal = document.getElementById('modal-checkout-header');
    if (!modal) {
        window.location.href = 'productos.php';
        return;
    }
    if (!modal.classList.contains('hidden')) {
        closeCheckoutModal();
        return;
    }
    loadCartItems();
    if (!cartItems.length) {
        showToastNotification('Tu bolsa está vacía. Te llevamos a la tienda.', 'info');
        if (!/productos\.php/i.test(window.location.pathname)) {
            setTimeout(() => { window.location.href = 'productos.php'; }, 900);
        }
        return;
    }
    openCheckoutModal();
}

window.improgypOpenCart = improgypOpenCart;
window.setPaymentMethod = setPaymentMethod;
window.setDeliveryMethod = setDeliveryMethod;
window.selectTransferBank = selectTransferBank;
window.selectCardBrand = selectCardBrand;

function closeCheckoutModal() {
    const modal = document.getElementById('modal-checkout-header');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

function buildCheckoutDeliveryPayload(waResolve) {
    if (currentDeliveryMethod === 'retiro') {
        const local = getLocalById(currentStore) || waResolve.local;
        const storeName = getLocalDisplayName(local);
        return {
            method: 'retiro',
            storeName,
            attentionName: waResolve.localNombre,
            attentionNote: ''
        };
    }
    const address = document.getElementById('delivery-address')?.value.trim() || '';
    const city = document.getElementById('delivery-city')?.value.trim() || '';
    let attentionNote = 'domicilio';
    if (waResolve.motivo === 'domicilio_ciudad') attentionNote = 'domicilio por cobertura';
    else if (waResolve.motivo === 'domicilio_matriz') attentionNote = `asignado a matriz — ciudad: ${city}`;
    else if (waResolve.motivo === 'domicilio_sin_ciudad') attentionNote = 'sin ciudad — matriz';
    return {
        method: 'envio',
        address,
        city,
        storeName: waResolve.localNombre,
        attentionName: waResolve.localNombre,
        attentionNote
    };
}

async function submitCheckout() {
    loadCartItems();
    if (cartItems.length === 0) {
        showToastNotification('Tu carrito está vacío. Agrega productos antes de enviar el pedido.', 'error');
        return;
    }

    await loadCheckoutLocales();
    ensureValidCurrentStore();

    const validation = validateCheckoutForm();
    if (!validation.ok) {
        if (validation.focus) scrollCheckoutSectionTo(validation.focus);
        showToastNotification(validation.message || 'Completa los campos obligatorios.', 'error');
        return;
    }

    const contact = getCheckoutContactData();
    const cityText = currentDeliveryMethod === 'envio'
        ? (document.getElementById('delivery-city')?.value.trim() || '')
        : '';

    const waResolve = resolveWhatsAppForCheckout({
        deliveryMethod: currentDeliveryMethod,
        storeId: currentStore,
        cityText
    });

    const delivery = buildCheckoutDeliveryPayload(waResolve);
    const cardName = document.getElementById('card-name-input')?.value.trim() || '';
    const payment = {
        label: getPaymentWhatsAppLabel(
            currentPaymentMethod,
            currentDeliveryMethod,
            cardName,
            selectedTransferBank,
            selectedCardBrand
        )
    };
    const totals = calcCheckoutTotals(cartItems);
    const texto = buildCheckoutWhatsAppMessage(contact, delivery, payment, cartItems, totals);

    if (currentPaymentMethod === 'card') {
        showToastNotification('Te abriremos WhatsApp para coordinar el pago con tarjeta.', 'info');
    }

    registrarPedidoPublicoSilent(cartItems, totals.total);

    const url = 'https://wa.me/' + waResolve.wa + '?text=' + encodeURIComponent(texto);
    window.open(url, '_blank');
    closeCheckoutModal();
    clearCartAfterCheckout();
    showToastNotification('Pedido enviado. Completa el chat en WhatsApp.', 'success');
}

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-checkout-header');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    initCheckoutStoreDropdown();
    initCheckoutPaymentUI();
    loadCheckoutLocales().then(() => {
        ensureValidCurrentStore();
        renderCheckoutStoreList();
    });
    loadCartItems();
    document.addEventListener('visibilitychange', onCheckoutVisibilityRefresh);
});

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    const modal = document.getElementById('modal-checkout-header');
    if (modal && !modal.classList.contains('hidden')) {
        closeCheckoutModal();
    }
});
