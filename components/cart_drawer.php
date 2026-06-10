<div class="cart-drawer-overlay" id="cart-drawer-overlay" onclick="toggleCartDrawer()">
    <div class="cart-drawer" onclick="event.stopPropagation()">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white shadow-sm z-10">
            <h3 class="font-black text-base text-slate-800">
                <i class="fa-solid fa-box-open text-[#1B263B] mr-2"></i> Tu Bolsa de Compras
            </h3>
            <button type="button" onclick="toggleCartDrawer()" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:text-rose-500 flex items-center justify-center transition-colors" aria-label="Cerrar bolsa">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
        <div id="cart-items-container" class="flex-grow overflow-y-auto custom-scrollbar bg-slate-50/50 p-2"></div>
        <div class="p-4 bg-white border-t border-slate-200 shadow-[0_-10px_20px_rgba(0,0,0,0.02)]">
            <div class="flex justify-between items-center mb-3">
                <span class="text-[13px] font-bold text-slate-500">Total Estimado</span>
                <span class="text-xl font-black text-slate-800" id="cart-subtotal">$0.00</span>
            </div>
            <button type="button" onclick="toggleCheckoutModal()" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 hover:bg-[#1B263B] transition-colors shadow-lg text-[14px]">
                <i class="fa-brands fa-whatsapp text-lg"></i> Ir al checkout
            </button>
            <button type="button" onclick="toggleCartDrawer()" class="w-full mt-2 text-slate-500 text-[12px] font-bold py-2 hover:text-slate-700">Seguir comprando</button>
        </div>
    </div>
</div>
