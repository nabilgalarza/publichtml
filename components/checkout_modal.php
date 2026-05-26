<div id="modal-checkout-header" class="hidden fixed inset-0 z-[3000] bg-slate-900/60 backdrop-blur-sm items-center justify-center p-3 md:p-4" onclick="if(event.target===this) closeCheckoutModal()">
    <div class="checkout-modal-panel bg-white rounded-3xl w-full max-w-5xl shadow-2xl flex flex-col overflow-hidden" onclick="event.stopPropagation()">
        <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center flex-shrink-0">
            <div>
                <h3 class="font-black text-lg text-slate-900">
                    <i class="fa-brands fa-whatsapp text-emerald-500 mr-2"></i> Finalizar cotización
                </h3>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">Configura entrega, contacto y pago</p>
            </div>
            <button type="button" onclick="closeCheckoutModal()" class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 hover:text-rose-500 flex items-center justify-center" aria-label="Cerrar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="flex flex-col md:flex-row flex-grow min-h-0">
            <!-- Columna izquierda ~45% -->
            <div class="md:w-[45%] border-b md:border-b-0 md:border-r border-slate-100 p-4 checkout-col-scroll custom-scrollbar space-y-4">
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">1. Método de recepción</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" id="btn-method-envio" onclick="setDeliveryMethod('envio')" class="py-2.5 rounded-xl border border-slate-200 text-[11px] font-black text-slate-600 transition-all">A domicilio</button>
                        <button type="button" id="btn-method-retiro" onclick="setDeliveryMethod('retiro')" class="py-2.5 rounded-xl border border-slate-200 text-[11px] font-black bg-[#1B263B] text-white transition-all">Retiro en local</button>
                    </div>
                </div>

                <div id="form-contacto" class="space-y-2">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">2. Datos de contacto</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" id="contact-nombre" autocomplete="given-name" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-[13px] focus:outline-none focus:ring-2 focus:ring-[#3A86FF]/20" placeholder="Nombre">
                        <input type="text" id="contact-apellido" autocomplete="family-name" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-[13px] focus:outline-none focus:ring-2 focus:ring-[#3A86FF]/20" placeholder="Apellido">
                    </div>
                    <input type="text" id="contact-empresa" autocomplete="organization" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-[13px] focus:outline-none focus:ring-2 focus:ring-[#3A86FF]/20" placeholder="Empresa (opcional)">
                    <input type="tel" id="contact-telefono" autocomplete="tel" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-[13px] focus:outline-none focus:ring-2 focus:ring-[#3A86FF]/20" placeholder="Teléfono móvil">
                </div>

                <div id="form-envio" class="hidden space-y-2">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">3. Datos de entrega</label>
                    <input type="text" id="delivery-address" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-[13px] focus:outline-none focus:ring-2 focus:ring-[#3A86FF]/20" placeholder="Dirección (calle, #, referencia)">
                    <input type="text" id="delivery-city" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-[13px] focus:outline-none focus:ring-2 focus:ring-[#3A86FF]/20" placeholder="Ciudad / Cantón">
                </div>

                <div id="form-retiro" class="space-y-2">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 ml-1">3. Punto de retiro</label>
                    <div id="checkout-stores-list" class="space-y-2 max-h-[180px] overflow-y-auto pr-1 scrollbar-hide">
                        <p class="text-[10px] text-slate-400 font-medium py-2">Cargando sucursales…</p>
                    </div>
                </div>

                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">4. Forma de pago</label>
                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <button type="button" id="btn-pago-transfer" onclick="setPaymentMethod('transfer')" class="payment-tab active py-2 rounded-xl border border-slate-200 text-[10px] font-black uppercase tracking-wide">Transferencia</button>
                        <button type="button" id="btn-pago-card" onclick="setPaymentMethod('card')" class="payment-tab py-2 rounded-xl border border-slate-200 text-[10px] font-black uppercase tracking-wide text-slate-600">Tarjeta</button>
                        <button type="button" id="btn-pago-cash" onclick="setPaymentMethod('cash')" class="payment-tab py-2 rounded-xl border border-slate-200 text-[10px] font-black uppercase tracking-wide text-slate-600">Efectivo</button>
                        <button type="button" id="btn-pago-deuna" onclick="setPaymentMethod('deuna')" class="payment-tab py-2 rounded-xl border border-slate-200 text-[10px] font-black uppercase tracking-wide text-slate-600">De Una</button>
                    </div>

                    <div id="panel-pago-transfer" class="bg-slate-50 border border-slate-100 rounded-2xl p-3 space-y-2 text-[11px] text-slate-600">
                        <p class="font-black text-slate-700 text-[12px]">Cuentas para transferencia</p>
                        <p><span class="font-bold">Pichincha:</span> CTA 2100123456 — IMPROGYP S.A.</p>
                        <p><span class="font-bold">Guayaquil:</span> CTA 0045678901 — IMPROGYP S.A.</p>
                        <p><span class="font-bold">Pacífico:</span> CTA 7890123456 — IMPROGYP S.A.</p>
                        <p class="text-[10px] text-slate-400">Envía el comprobante por WhatsApp al confirmar el pedido.</p>
                    </div>
                    <div id="panel-pago-card" class="hidden bg-slate-50 border border-slate-100 rounded-2xl p-3 space-y-2">
                        <p class="text-[11px] text-slate-500 font-medium">Coordinaremos el cobro con tarjeta vía enlace seguro o datáfono en sucursal.</p>
                        <input type="text" id="card-name-input" class="w-full bg-white border border-slate-100 rounded-xl px-3 py-2 text-[13px]" placeholder="Nombre en la tarjeta (referencia)">
                    </div>
                    <div id="panel-pago-cash" class="hidden bg-slate-50 border border-slate-100 rounded-2xl p-3">
                        <p class="text-[11px] text-slate-600 font-medium">Pago en efectivo al retirar en sucursal o contra entrega según disponibilidad en tu ciudad.</p>
                    </div>
                    <div id="panel-pago-deuna" class="hidden bg-slate-50 border border-slate-100 rounded-2xl p-3">
                        <p class="text-[11px] text-slate-600 font-medium">Solicita el número De Una de la sucursal asignada al confirmar por WhatsApp.</p>
                    </div>
                </div>
            </div>

            <!-- Columna derecha ~55% -->
            <div class="md:w-[55%] flex flex-col min-h-0 p-4 checkout-col-scroll">
                <div class="flex justify-between items-start mb-3 flex-shrink-0">
                    <h4 class="font-black text-slate-800 text-[15px]">Resumen de pedido</h4>
                    <a href="https://wa.me/593991754887?text=<?= rawurlencode('Hola IMPROGYP, necesito asesoría con mi cotización.') ?>" target="_blank" rel="noopener" class="text-[10px] font-bold text-[#3A86FF] hover:underline">Consultar asesor</a>
                </div>
                <div id="check-list" class="flex-grow overflow-y-auto custom-scrollbar space-y-1 min-h-[120px] bg-slate-50/80 rounded-2xl p-3 border border-slate-100 mb-3"></div>
                <div class="flex justify-between items-center py-2 border-t border-slate-100 flex-shrink-0">
                    <span class="text-sm font-bold text-slate-500">Subtotal estimado</span>
                    <span class="text-xl font-black text-[#1B263B]" id="checkout-subtotal">$0.00</span>
                </div>
                <button type="button" onclick="submitCheckout()" class="mt-3 w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-4 rounded-xl flex items-center justify-center gap-2 shadow-lg flex-shrink-0">
                    <i class="fa-brands fa-whatsapp text-lg"></i> Enviar pedido por WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>
