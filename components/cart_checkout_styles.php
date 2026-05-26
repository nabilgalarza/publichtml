<style>
    .cart-drawer-overlay {
        position: fixed; inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 2000;
        opacity: 0; pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .cart-drawer-overlay.show { opacity: 1; pointer-events: auto; }
    .cart-drawer {
        position: absolute; top: 0; right: 0;
        width: 100%; max-width: 380px; height: 100%;
        background: #fff;
        transform: translateX(100%);
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: flex; flex-direction: column;
        box-shadow: -10px 0 40px rgba(0,0,0,0.1);
    }
    .cart-drawer-overlay.show .cart-drawer { transform: translateX(0); }
    .cart-item {
        display: flex; align-items: center; gap: 10px; padding: 14px;
        border-bottom: 1px solid #f1f5f9;
    }
    .cart-item-img {
        width: 45px; height: 45px; object-fit: contain;
        border-radius: 8px; background: #fff; padding: 4px;
        border: 1px solid #e2e8f0; flex-shrink: 0; cursor: pointer;
    }
    .cart-qty-btn {
        width: 26px; height: 26px; border-radius: 6px;
        background: #f1f5f9; color: #475569; border: none;
        font-weight: bold; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
    }
    .checkout-modal-panel { max-height: 85vh; }
    .checkout-col-scroll { max-height: calc(85vh - 4rem); overflow-y: auto; }
    .payment-tab.active {
        border-color: #1B263B !important;
        background: #1B263B !important;
        color: #fff !important;
    }
    .store-card.selected {
        ring: 2px;
        ring-color: rgba(27, 38, 59, 0.25);
        border-color: rgba(27, 38, 59, 0.35);
    }
    #toast-container {
        position: fixed; top: 1rem; right: 1rem; z-index: 9999;
        display: flex; flex-direction: column; gap: 0.5rem;
        pointer-events: none; max-width: min(360px, calc(100vw - 2rem));
    }
    .improgyp-toast {
        pointer-events: auto;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        animation: improgypToastIn 0.35s ease;
    }
    .improgyp-toast.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .improgyp-toast.success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .improgyp-toast.info { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    @keyframes improgypToastIn {
        from { opacity: 0; transform: translateX(12px); }
        to { opacity: 1; transform: translateX(0); }
    }
</style>
