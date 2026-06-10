<?php
/**
 * Modal de producto compartido (tienda + home).
 * Home: mismo layout que tienda + botón "Ver en la tienda".
 */
$modal_home = ($improgyp_page ?? '') === 'home';
?>
<div class="modal-overlay<?= $modal_home ? ' hidden' : '' ?>" id="product-modal" onclick="typeof cerrarModalProducto==='function'?cerrarModalProducto(event):(typeof cerrarModalProductoLanding==='function'&&cerrarModalProductoLanding(event))" role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-hidden="true">
    <div class="product-modal-content improgyp-product-modal flex flex-col md:flex-row gap-0 md:gap-6 px-4 py-5 sm:p-5 relative bg-white w-full max-w-3xl mx-2 sm:mx-4 rounded-2xl shadow-2xl custom-scrollbar" onclick="event.stopPropagation()">
        <button type="button" class="absolute top-3 right-3 md:top-4 md:right-4 text-slate-400 hover:text-rose-500 text-xl z-20 w-8 h-8 flex items-center justify-center bg-slate-100 md:bg-transparent rounded-full transition-colors" onclick="typeof cerrarModalProducto==='function'?cerrarModalProducto():(typeof cerrarModalProductoLanding==='function'&&cerrarModalProductoLanding())" aria-label="Cerrar">&times;</button>
        <div class="w-full md:w-5/12 bg-[#f8fafc] rounded-xl p-4 flex justify-center items-center relative mb-4 md:mb-0 border border-slate-100 flex-shrink-0">
            <span id="modal-cat" class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-[#1B263B] text-[9px] font-bold px-2.5 py-1 rounded uppercase tracking-wider shadow-sm border border-[#1B263B]/20 z-10"></span>
            <img id="modal-img" src="" alt="" class="w-full max-h-48 sm:max-h-56 md:max-h-64 object-contain mix-blend-multiply" onerror="this.onerror=null; this.src='favicon-app.png?v=5';">
        </div>
        <div class="w-full md:w-7/12 flex flex-col pt-1 min-h-0">
            <h2 id="modal-title" class="text-xl md:text-2xl font-black text-slate-800 mb-1 leading-tight"></h2>
            <div id="modal-brand-sku" class="flex items-center gap-2 mb-4 flex-wrap">
                <span id="modal-marca-label" class="brand-label !mb-0"></span>
                <span id="modal-sku-label" class="sku-label !mt-0"></span>
            </div>
            <div class="custom-scrollbar overflow-y-auto pr-3 mb-4" style="max-height: 18vh;">
                <p id="modal-desc" class="text-[13px] text-slate-500 leading-relaxed whitespace-pre-line"></p>
            </div>
            <div class="mb-4 flex-shrink-0">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2.5">Presentaciones disponibles</p>
                <div id="modal-presentations" class="flex flex-wrap gap-2"></div>
            </div>
            <div class="improgyp-modal-footer mt-auto pt-4 border-t border-slate-100 flex-shrink-0 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="min-w-0 flex-shrink-0">
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-1">Precio</p>
                    <span id="modal-price" class="text-xl sm:text-2xl font-black text-slate-800 tabular-nums leading-tight break-words"></span>
                </div>
                <div class="flex flex-col gap-2 w-full md:w-auto md:flex-shrink-0 md:flex-row md:items-center md:gap-2">
                    <div class="flex gap-2 w-full md:w-auto">
                        <button type="button" id="modal-btn-share" class="w-10 h-10 shrink-0 rounded-lg flex items-center justify-center text-lg transition-colors border border-slate-200 shadow-sm text-slate-400 hover:text-[#1B263B] hover:border-[#1B263B]/30" title="Compartir producto"><i class="fa-solid fa-share-nodes"></i></button>
                        <button type="button" id="modal-btn-wishlist" class="w-10 h-10 shrink-0 rounded-lg flex items-center justify-center text-lg transition-colors border border-slate-200 shadow-sm" title="Lista de deseos"></button>
                        <div id="modal-btn-add-wrapper" class="flex-1 min-w-0 md:flex-none">
                            <button type="button" id="modal-btn-add" class="btn-IMPROGYP w-full md:w-28 h-10 text-[13px] px-4"><i class="fa-solid fa-cart-plus"></i> <span class="ml-1">Añadir</span></button>
                        </div>
                    </div>
                    <?php if ($modal_home): ?>
                    <a id="modal-btn-shop" href="productos.php" class="w-full md:w-auto h-10 inline-flex items-center justify-center gap-2 px-3 sm:px-4 rounded-lg border-2 border-[#1B263B] text-[#1B263B] font-black text-[10px] sm:text-[11px] uppercase tracking-wide hover:bg-[#1B263B] hover:text-white transition-colors shrink-0 whitespace-nowrap" title="Ver en la tienda">
                        Ver en la tienda <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div id="modal-related-container"></div>
        </div>
    </div>
</div>
