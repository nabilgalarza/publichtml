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

async function loadCheckoutLocales() {
    try {
        const res = await fetch('locales.json?v=' + Date.now());
        checkoutLocales = await res.json();
        checkoutLocalesLoaded = true;
    } catch (e) {
        checkoutLocales = [];
        checkoutLocalesLoaded = false;
    }
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

function renderCheckoutStoreList() {
    const container = document.getElementById('checkout-stores-list');
    if (!container) return;
    if (!checkoutLocales.length) {
        container.innerHTML = '<p class="text-xs text-slate-400 p-2">No se cargaron sucursales.</p>';
        return;
    }
    container.innerHTML = checkoutLocales.map((loc) => {
        const sel = currentStore === loc.id ? 'selected ring-2 ring-[#1B263B]/20 border-[#1B263B]/30' : '';
        return `<button type="button" onclick="selectStore('${loc.id}')" data-store-id="${loc.id}"
            class="store-card w-full p-3 bg-white border border-slate-100 rounded-2xl flex items-center gap-3 text-left transition-all hover:border-[#3A86FF]/30 ${sel}">
            <div class="w-9 h-9 bg-[#1B263B] text-white rounded-xl flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-store text-sm"></i></div>
            <div class="min-w-0 flex-grow">
                <p class="text-[12px] font-black text-slate-800 truncate">${loc.nombre}</p>
                <p class="text-[10px] text-slate-500 truncate">${loc.ciudad || ''} · ${loc.direccion || ''}</p>
            </div>
        </button>`;
    }).join('');
}

function selectStore(id) {
    currentStore = id;
    document.querySelectorAll('.store-card').forEach((btn) => {
        const on = btn.dataset.storeId === id;
        btn.classList.toggle('selected', on);
        btn.classList.toggle('ring-2', on);
        btn.classList.toggle('ring-[#1B263B]/20', on);
    });
}

function setDeliveryMethod(method) {
    currentDeliveryMethod = method;
    const retiro = document.getElementById('form-retiro');
    const envio = document.getElementById('form-envio');
    const btnRetiro = document.getElementById('btn-method-retiro');
    const btnEnvio = document.getElementById('btn-method-envio');
    if (retiro) retiro.classList.toggle('hidden', method !== 'retiro');
    if (envio) envio.classList.toggle('hidden', method !== 'envio');
    if (btnRetiro) {
        btnRetiro.classList.toggle('bg-[#1B263B]', method === 'retiro');
        btnRetiro.classList.toggle('text-white', method === 'retiro');
        btnRetiro.classList.toggle('text-slate-600', method !== 'retiro');
    }
    if (btnEnvio) {
        btnEnvio.classList.toggle('bg-[#1B263B]', method === 'envio');
        btnEnvio.classList.toggle('text-white', method === 'envio');
        btnEnvio.classList.toggle('text-slate-600', method !== 'envio');
    }
}

function setPaymentMethod(method) {
    currentPaymentMethod = method;
    ['transfer', 'card', 'cash', 'deuna'].forEach((m) => {
        const btn = document.getElementById('btn-pago-' + m);
        const panel = document.getElementById('panel-pago-' + m);
        if (btn) btn.classList.toggle('active', m === method);
        if (panel) panel.classList.toggle('hidden', m !== method);
    });
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

function renderCheckoutList() {
    const list = document.getElementById('check-list');
    const subtotalEl = document.getElementById('checkout-subtotal');
    if (!list) return;

    loadCartItems();
    if (!cartItems.length) {
        list.innerHTML = '<p class="text-sm text-slate-400 text-center py-6">Tu bolsa está vacía.</p>';
        if (subtotalEl) subtotalEl.textContent = '$0.00';
        return;
    }

    let subtotal = 0;
    list.innerHTML = cartItems.map((item) => {
        const line = item.cantidad * item.precioNum;
        subtotal += line;
        return `<div class="flex justify-between gap-2 py-2 border-b border-slate-100/80 last:border-0">
            <span class="text-[12px] font-bold text-slate-700 truncate">${item.cantidad}× ${item.nombre}</span>
            <span class="text-[12px] font-black text-[#1B263B] flex-shrink-0">$${line.toFixed(2)}</span>
        </div>`;
    }).join('');
    if (subtotalEl) subtotalEl.textContent = '$' + subtotal.toFixed(2);
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

function buildCheckoutContactWhatsApp(contact) {
    const nombreCompleto = `${contact.nombre} ${contact.apellido}`.trim();
    let block = `*DATOS DE CONTACTO:*\n*Nombre:* ${nombreCompleto}\n*Teléfono:* ${contact.telefono}`;
    if (contact.empresa) block += `\n*Empresa:* ${contact.empresa}`;
    return block;
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

function validateCheckoutContact() {
    const contact = getCheckoutContactData();
    const missing = [];
    if (!contact.nombre) missing.push('contact-nombre');
    if (!contact.apellido) missing.push('contact-apellido');
    if (!contact.telefono) missing.push('contact-telefono');
    if (missing.length) {
        marcarCamposCheckoutInvalidos(missing);
        showToastNotification('Completa nombre, apellido y teléfono de contacto.', 'error');
        return false;
    }
    return true;
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

    loadCartItems();
    renderCheckoutList();
    setDeliveryMethod(currentDeliveryMethod);
    setPaymentMethod(currentPaymentMethod);

    loadCheckoutLocales().then(() => {
        renderCheckoutStoreList();
        if (!currentStore && checkoutLocales.length) {
            currentStore = checkoutLocales[0].id;
            selectStore(currentStore);
        } else {
            selectStore(currentStore);
        }
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

function closeCheckoutModal() {
    const modal = document.getElementById('modal-checkout-header');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

function submitCheckout() {
    loadCartItems();
    if (cartItems.length === 0) {
        showToastNotification('Tu carrito está vacío. Agrega productos antes de enviar el pedido.', 'error');
        return;
    }
    if (!validateCheckoutContact()) return;

    const contact = getCheckoutContactData();
    let deliveryDetails = '';
    let waResolve = resolveWhatsAppForCheckout({
        deliveryMethod: currentDeliveryMethod,
        storeId: currentStore,
        cityText: ''
    });

    if (currentDeliveryMethod === 'envio') {
        const address = document.getElementById('delivery-address')?.value.trim();
        const city = document.getElementById('delivery-city')?.value.trim();
        if (!address || !city) {
            marcarCamposEntregaInvalidos();
            showToastNotification('Completa la dirección y ciudad de entrega.', 'error');
            return;
        }
        waResolve = resolveWhatsAppForCheckout({
            deliveryMethod: 'envio',
            storeId: currentStore,
            cityText: city
        });
        let atencion = `*Atención:* ${waResolve.localNombre}`;
        if (waResolve.motivo === 'domicilio_ciudad') {
            atencion += ' (domicilio por cobertura)';
        } else if (waResolve.motivo === 'domicilio_matriz') {
            atencion += ` (asignado a matriz — ciudad: ${city})`;
        }
        deliveryDetails = `*Entrega a domicilio*\n*Dirección:* ${address}\n*Ciudad:* ${city}\n${atencion}`;
    } else {
        const local = getLocalById(currentStore);
        const storeName = getLocalDisplayName(local);
        waResolve = resolveWhatsAppForCheckout({
            deliveryMethod: 'retiro',
            storeId: currentStore,
            cityText: ''
        });
        deliveryDetails = `*Retiro en local*\n*Sucursal:* ${storeName}\n*Atención:* ${waResolve.localNombre} (retiro)`;
    }

    let subtotal = 0;
    let productosBlock = '*PRODUCTOS:*\n';
    cartItems.forEach((item) => {
        subtotal += item.cantidad * item.precioNum;
        productosBlock += `🔹 ${item.cantidad}x ${item.nombre} ($${item.precioNum.toFixed(2)} c/u)\n`;
    });

    const pagoLabel = PAYMENT_LABELS[currentPaymentMethod] || currentPaymentMethod;
    let pagoExtra = '';
    if (currentPaymentMethod === 'card') {
        const cardName = document.getElementById('card-name-input')?.value.trim();
        if (cardName) pagoExtra = `\n*Referencia tarjeta:* ${cardName}`;
        showToastNotification('Te abriremos WhatsApp para coordinar el pago con tarjeta.', 'info');
    }

    let texto = 'Hola IMPROGYP, deseo finalizar mi cotización:\n\n';
    texto += buildCheckoutContactWhatsApp(contact) + '\n\n';
    texto += deliveryDetails + '\n\n';
    texto += `*FORMA DE PAGO:* ${pagoLabel}${pagoExtra}\n\n`;
    texto += productosBlock;
    texto += `\n*TOTAL ESTIMADO:* $${subtotal.toFixed(2)}\n`;
    texto += '_Gracias por confiar en IMPROGYP._';

    if (currentPaymentMethod !== 'card') {
        registrarPedidoPublicoSilent(cartItems, subtotal);
    }

    const url = 'https://wa.me/' + waResolve.wa + '?text=' + encodeURIComponent(texto);
    window.open(url, '_blank');
    closeCheckoutModal();
    clearCartAfterCheckout();
    showToastNotification('Pedido enviado. Completa el chat en WhatsApp.', 'success');
}

document.addEventListener('DOMContentLoaded', () => {
    loadCheckoutLocales();
    loadCartItems();
});
