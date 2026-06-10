<?php
/**
 * Modal de sucursales (compartido home + tienda + footer).
 */
?>
<div class="modal-overlay" id="locations-modal" onclick="cerrarModalLocales(event)">
    <div class="product-modal-content flex flex-col px-3 py-5 md:p-6 relative bg-white w-full max-w-4xl mx-2 md:mx-4 rounded-2xl md:rounded-3xl shadow-2xl overflow-hidden" onclick="event.stopPropagation()" style="max-height: 90vh;">
        <button type="button" class="absolute top-3 right-3 md:top-4 md:right-4 text-slate-400 hover:text-rose-500 text-lg md:text-xl z-20 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-slate-50 rounded-full transition-colors" onclick="cerrarModalLocales()">&times;</button>

        <div class="mb-5 md:mb-6 pr-8">
            <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">Nuestras sucursales</h2>
            <p class="text-slate-500 text-[13px] md:text-sm mt-1">Puntos de venta y asesoría técnica en todo Ecuador.</p>
        </div>

        <div class="flex-grow overflow-y-auto custom-scrollbar pr-2">
            <div id="locations-grid" class="modal-location-grid locales-showroom-modal-grid pb-6"></div>
        </div>
    </div>
</div>
