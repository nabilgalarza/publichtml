<?php
/**
 * Modal de sucursales (compartido home + tienda).
 */
?>
<div class="modal-overlay" id="locations-modal" onclick="cerrarModalLocales(event)">
    <div class="product-modal-content flex flex-col px-3 py-5 md:p-6 relative bg-white w-full max-w-4xl mx-2 md:mx-4 rounded-2xl md:rounded-3xl shadow-2xl overflow-hidden" onclick="event.stopPropagation()" style="max-height: 90vh;">
        <button type="button" class="absolute top-3 right-3 md:top-4 md:right-4 text-slate-400 hover:text-rose-500 text-lg md:text-xl z-20 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-slate-50 rounded-full transition-colors" onclick="cerrarModalLocales()">&times;</button>

        <div class="mb-6 md:mb-8">
            <span class="inline-block bg-[#1B263B]/10 text-[#1B263B] text-[9px] md:text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest mb-2 border border-[#1B263B]/20">Red de Suministros</span>
            <h2 class="text-xl md:text-3xl font-black text-slate-900 tracking-tight">Nuestras Sucursales</h2>
            <p class="text-slate-500 text-[13px] md:text-sm mt-1">Encuentra el punto IMPROGYP más cercano a tu obra.</p>
        </div>

        <div class="flex-grow overflow-y-auto custom-scrollbar pr-2">
            <div id="modal-nearest-location" class="mb-6 md:mb-8 hidden"></div>
            <div class="mb-4 md:mb-5">
                <p class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Listado completo</p>
            </div>
            <div id="locations-grid" class="modal-location-grid pb-6"></div>
        </div>
    </div>
</div>
