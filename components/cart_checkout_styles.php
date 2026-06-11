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
    /* —— Checkout modal premium —— */
    .checkout-modal-overlay {
        background: rgba(15, 23, 42, 0.72);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .checkout-modal-overlay.hidden { display: none !important; }
    .checkout-modal-overlay.flex {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .checkout-modal-panel {
        width: 100%;
        max-width: min(920px, calc(100vw - 1rem));
        max-height: min(92vh, 720px);
        background: #fff;
        border-radius: 20px;
        box-shadow:
            0 0 0 1px rgba(255,255,255,0.08) inset,
            0 25px 50px -12px rgba(15, 23, 42, 0.35),
            0 12px 24px -8px rgba(27, 38, 59, 0.15);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .checkout-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        background: linear-gradient(135deg, #1B263B 0%, #243447 100%);
        color: #fff;
        flex-shrink: 0;
    }
    .checkout-modal-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #86efac;
        background: rgba(255,255,255,0.08);
        padding: 3px 10px;
        border-radius: 999px;
        margin-bottom: 6px;
    }
    .checkout-modal-title {
        font-size: 17px;
        font-weight: 900;
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin: 0;
    }
    .checkout-modal-subtitle {
        font-size: 11px;
        font-weight: 500;
        color: rgba(255,255,255,0.65);
        margin: 4px 0 0;
    }
    .checkout-modal-close {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: none;
        background: rgba(255,255,255,0.12);
        color: rgba(255,255,255,0.9);
        cursor: pointer;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s, color 0.2s;
    }
    .checkout-modal-close:hover {
        background: rgba(248, 113, 113, 0.35);
        color: #fff;
    }
    .checkout-modal-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
    }
    @media (min-width: 768px) {
        .checkout-modal-body { flex-direction: row; }
    }
    .checkout-form-col {
        flex: 1 1 56%;
        padding: 12px 14px 14px;
        min-width: 0;
    }
    .checkout-col-scroll {
        max-height: min(calc(92vh - 72px), 648px);
        overflow-y: auto;
    }
    .checkout-section {
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f1f5f9;
    }
    .checkout-section-last { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .checkout-section-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        margin: 0 0 6px;
    }
    .checkout-step-num {
        width: 18px;
        height: 18px;
        border-radius: 6px;
        background: #1B263B;
        color: #fff;
        font-size: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .checkout-segment-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        padding: 4px;
        background: #f1f5f9;
        border-radius: 12px;
    }
    .checkout-segment {
        padding: 9px 12px;
        border: none;
        border-radius: 9px;
        font-size: 11px;
        font-weight: 800;
        color: #64748b;
        background: transparent;
        cursor: pointer;
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    }
    .checkout-segment-active {
        background: #fff;
        color: #1B263B;
        box-shadow: 0 1px 3px rgba(15,23,42,0.08);
    }
    .checkout-fields { display: flex; flex-direction: column; gap: 6px; }
    .checkout-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .checkout-input {
        width: 100%;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 600;
        color: #1e293b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    }
    .checkout-input:focus {
        outline: none;
        border-color: #0E75AE;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(14, 117, 174, 0.12);
    }
    .checkout-input-on-panel { margin-top: 8px; background: #fff; }
    .checkout-stores-scroll {
        display: flex;
        flex-direction: column;
        gap: 6px;
        max-height: 140px;
        overflow-y: auto;
        padding-right: 2px;
    }
    .checkout-muted {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 600;
        padding: 8px 0;
        margin: 0;
    }
    .checkout-payment-tabs {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 6px;
        margin-bottom: 10px;
    }
    .checkout-pay-tab {
        padding: 8px 4px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }
    .checkout-pay-tab.active,
    .payment-tab.checkout-pay-tab.active {
        border-color: #1B263B;
        background: #1B263B;
        color: #fff;
        box-shadow: 0 2px 8px rgba(27, 38, 59, 0.2);
    }
    .checkout-pay-panel.hidden { display: none !important; }
    .checkout-pay-panel {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
    }
    .checkout-pay-panel-title {
        font-size: 12px;
        font-weight: 900;
        color: #1B263B;
        margin: 0 0 8px;
    }
    .checkout-bank-list {
        list-style: none;
        margin: 0;
        padding: 0;
        font-size: 11px;
        color: #475569;
        line-height: 1.5;
    }
    .checkout-bank-list li { padding: 3px 0; }
    .checkout-bank-list strong { color: #1e293b; font-weight: 800; }
    .checkout-pay-hint {
        font-size: 10px;
        color: #64748b;
        font-weight: 600;
        margin: 0;
        line-height: 1.45;
    }
    .checkout-pay-panel .checkout-pay-hint + .checkout-input { margin-top: 10px; }
    .checkout-bank-logos {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 10px;
    }
    @media (min-width: 480px) {
        .checkout-bank-logos { grid-template-columns: repeat(5, 1fr); }
    }
    .checkout-bank-logo-cell {
        aspect-ratio: 2 / 1;
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 6px 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
        font: inherit;
        width: 100%;
    }
    .checkout-bank-logo-cell:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .checkout-bank-logo-cell.checkout-bank-selected {
        border-color: #1B263B;
        background: #eff6ff;
        box-shadow: 0 0 0 2px rgba(27, 38, 59, 0.12);
    }
    .checkout-bank-logo-cell img {
        max-width: 100%;
        max-height: 28px;
        object-fit: contain;
        pointer-events: none;
    }
    .checkout-card-brands {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        padding: 10px 0 12px;
    }
    .checkout-card-brand-btn {
        width: 52px;
        height: 40px;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        background: #fff;
        color: #334155;
        font-size: 28px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
        padding: 0;
    }
    .checkout-card-brand-btn:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .checkout-card-brand-btn.checkout-card-selected {
        border-color: #1B263B;
        background: #eff6ff;
        color: #1B263B;
        box-shadow: 0 0 0 2px rgba(27, 38, 59, 0.12);
    }
    .checkout-card-brand-btn i { pointer-events: none; }
    .checkout-cash-visual {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px;
        border-radius: 12px;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 50%, #a7f3d0 100%);
        border: 1px solid #6ee7b7;
    }
    .checkout-cash-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #16a34a, #22c55e);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35);
    }
    .checkout-cash-title {
        font-size: 13px;
        font-weight: 900;
        color: #14532d;
        margin: 0 0 4px;
    }
    .checkout-cash-desc {
        font-size: 10px;
        font-weight: 600;
        color: #166534;
        margin: 0;
        line-height: 1.4;
    }
    .checkout-deuna-visual {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 12px 10px 6px;
        width: 100%;
    }
    .checkout-deuna-logo {
        display: block;
        max-width: 120px;
        max-height: 56px;
        width: auto;
        height: auto;
        object-fit: contain;
        margin: 0 auto 12px;
    }
    .checkout-deuna-visual .checkout-pay-hint {
        max-width: 280px;
        margin-left: auto;
        margin-right: auto;
    }
    .checkout-pay-tab-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 8px 4px !important;
        min-height: 52px;
    }
    .checkout-pay-tab-icon i { font-size: 16px; }
    .checkout-pay-tab-txt { font-size: 8px; letter-spacing: 0.02em; }
    .checkout-pay-tab-deuna-icon { object-fit: contain; }
    .checkout-pay-tab.active .checkout-pay-tab-deuna-icon { filter: brightness(0) invert(1); }
    .checkout-store-select-wrap { margin-bottom: 4px; }
    .checkout-store-dropdown {
        position: relative;
        width: 100%;
    }
    .checkout-store-dropdown-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 11px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        background: #fff;
        border: 1px solid #93c5fd;
        border-radius: 12px;
        cursor: pointer;
        line-height: 1.35;
        text-align: left;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .checkout-store-dropdown-trigger:hover {
        border-color: #60a5fa;
    }
    .checkout-store-dropdown-trigger[aria-expanded="true"] {
        border-color: #0E75AE;
        box-shadow: 0 0 0 3px rgba(14, 117, 174, 0.18);
    }
    .checkout-store-dropdown-trigger[aria-expanded="true"] .checkout-store-trigger-chevron {
        transform: rotate(180deg);
    }
    .checkout-store-trigger-chevron {
        font-size: 11px;
        color: #475569;
        flex-shrink: 0;
        transition: transform 0.2s;
    }
    .checkout-store-dropdown-menu {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 100;
        margin: 0;
        padding: 6px 0;
        list-style: none;
        background: rgba(45, 55, 72, 0.96);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 10px;
        box-shadow:
            0 0 0 1px rgba(0, 0, 0, 0.2),
            0 18px 40px rgba(15, 23, 42, 0.45);
        max-height: min(280px, 40vh);
        overflow-y: auto;
        overscroll-behavior: contain;
    }
    .checkout-store-dropdown-menu:not([hidden]) {
        display: block;
        animation: checkoutStoreMenuIn 0.18s ease;
    }
    @keyframes checkoutStoreMenuIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .checkout-store-dropdown-option {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px 7px 10px;
        border: none;
        background: transparent;
        color: rgba(255, 255, 255, 0.95);
        font-size: 13px;
        font-weight: 500;
        font-family: inherit;
        cursor: pointer;
        text-align: left;
        line-height: 1.35;
        transition: background 0.12s ease;
    }
    .checkout-store-dropdown-option:hover,
    .checkout-store-dropdown-option:focus {
        background: #0E75AE;
        outline: none;
        color: #fff;
    }
    .checkout-store-option-check,
    .checkout-store-option-check-spacer {
        width: 16px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        opacity: 0.95;
    }
    .checkout-store-dropdown-empty {
        padding: 10px 14px;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.6);
    }
    .checkout-store-select-detail {
        font-size: 11px;
        color: #64748b;
        font-weight: 600;
        margin: 8px 0 0;
        padding: 0 2px;
        line-height: 1.45;
    }

    .checkout-summary-col {
        flex: 1 1 44%;
        min-width: 0;
        min-height: 0;
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        border-top: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
    }
    @media (min-width: 768px) {
        .checkout-summary-col {
            border-top: none;
            border-left: 1px solid #e2e8f0;
            max-height: min(calc(92vh - 72px), 648px);
        }
    }
    .checkout-summary-inner {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        padding: 14px 16px 16px;
        gap: 10px;
    }
    .checkout-summary-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        flex-shrink: 0;
    }
    .checkout-summary-title {
        font-size: 14px;
        font-weight: 900;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.02em;
    }
    .checkout-summary-meta {
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        margin: 3px 0 0;
    }
    .checkout-advisor-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 11px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        color: #1d4ed8;
        background: #fff;
        border: 1px solid #bfdbfe;
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(59, 130, 246, 0.08);
        transition: background 0.2s, color 0.2s, transform 0.15s;
        flex-shrink: 0;
    }
    .checkout-advisor-link:hover {
        background: #0E75AE;
        color: #fff;
        border-color: #0E75AE;
        transform: translateY(-1px);
    }
    .checkout-empty {
        text-align: center;
        padding: 2rem 1rem;
        font-size: 12px;
        font-weight: 600;
        color: #94a3b8;
        margin: 0;
    }
    .checkout-items-scroll {
        flex: 1 1 0;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(15,23,42,0.04);
    }
    .checkout-item-row {
        display: grid;
        grid-template-columns: 44px 1fr auto 28px;
        align-items: center;
        gap: 8px;
        padding: 9px 10px 9px 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .checkout-item-row:last-child { border-bottom: none; }
    .checkout-item-thumb {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
        object-fit: contain;
        padding: 4px;
    }
    .checkout-item-body { min-width: 0; }
    .checkout-item-name {
        font-size: 11px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .checkout-item-meta {
        font-size: 9px;
        font-weight: 700;
        color: #94a3b8;
        margin-top: 2px;
    }
    .checkout-item-qty-stepper {
        display: inline-flex;
        align-items: center;
        margin-top: 4px;
        height: 22px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0 2px;
        gap: 0;
    }
    .checkout-qty-btn {
        width: 22px;
        height: 20px;
        padding: 0;
        border: none;
        border-radius: 6px;
        background: #fff;
        color: #64748b;
        font-size: 13px;
        font-weight: 900;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: color 0.12s, background 0.12s;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }
    .checkout-qty-btn:hover {
        color: #1B263B;
        background: #f8fafc;
    }
    .checkout-qty-btn:active { transform: scale(0.94); }
    .checkout-qty-num {
        min-width: 20px;
        padding: 0 4px;
        text-align: center;
        font-size: 10px;
        font-weight: 900;
        color: #1e293b;
        font-variant-numeric: tabular-nums;
        user-select: none;
    }
    .checkout-item-price-col { text-align: right; white-space: nowrap; }
    .checkout-item-unit { font-size: 9px; font-weight: 700; color: #94a3b8; }
    .checkout-item-line { font-size: 12px; font-weight: 900; color: #1B263B; margin-top: 1px; }
    .checkout-item-remove {
        width: 28px;
        height: 28px;
        padding: 0;
        border: none;
        border-radius: 8px;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: color 0.15s, background 0.15s;
    }
    .checkout-item-remove:hover {
        color: #e11d48;
        background: #fff1f2;
    }
    .checkout-item-remove i { font-size: 11px; pointer-events: none; }
    .checkout-totals-block {
        flex-shrink: 0;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 11px 14px;
        box-shadow: 0 2px 8px rgba(15,23,42,0.04);
    }
    .checkout-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
    }
    .checkout-total-row span:last-child {
        font-weight: 900;
        color: #334155;
    }
    .checkout-total-row-grand {
        margin-top: 6px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
        font-size: 12px;
        font-weight: 900;
        color: #0f172a;
    }
    .checkout-grand-amount {
        font-size: 20px !important;
        font-weight: 900 !important;
        color: #1B263B !important;
        letter-spacing: -0.02em;
    }
    .checkout-totals-disclaimer {
        font-size: 9px;
        color: #94a3b8;
        font-weight: 600;
        margin: 8px 0 0;
        line-height: 1.4;
    }
    .checkout-wa-cta {
        flex-shrink: 0;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 13px 16px;
        border: none;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 900;
        color: #fff;
        cursor: pointer;
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        box-shadow: 0 4px 14px rgba(34, 197, 94, 0.35);
        transition: transform 0.15s, box-shadow 0.2s, filter 0.2s;
    }
    .checkout-wa-cta i { font-size: 20px; }
    .checkout-wa-cta:hover {
        filter: brightness(1.05);
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
        transform: translateY(-1px);
    }
    .checkout-wa-cta:active { transform: translateY(0); }
    .store-card {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        background: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
        text-align: left;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .store-card:hover { border-color: #93c5fd; }
    .store-card.selected {
        border-color: #1B263B;
        box-shadow: 0 0 0 2px rgba(27, 38, 59, 0.12);
        background: #f8fafc;
    }
    .store-card-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #1B263B, #334155);
        color: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 13px;
    }
    .store-card-name {
        font-size: 11px;
        font-weight: 900;
        color: #1e293b;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .store-card-addr {
        font-size: 9px;
        font-weight: 600;
        color: #64748b;
        margin: 2px 0 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .tabular-nums { font-variant-numeric: tabular-nums; }
    @media (min-width: 768px) {
        .checkout-mob-bottom-bar,
        .checkout-mob-sheet-backdrop,
        .checkout-mob-sheet-handle { display: none !important; }
    }
    @media (max-width: 767px) {
        .checkout-modal-overlay.flex {
            padding: 14px 12px;
            padding-top: max(14px, env(safe-area-inset-top, 0px));
            padding-bottom: max(14px, env(safe-area-inset-bottom, 0px));
            align-items: center;
            justify-content: center;
        }
        .checkout-modal-panel {
            --checkout-mob-bar-h: 62px;
            width: 100%;
            max-width: calc(100vw - 24px);
            max-height: calc(100vh - 28px - env(safe-area-inset-top, 0px) - env(safe-area-inset-bottom, 0px));
            border-radius: 20px;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .checkout-modal-body {
            flex: 1;
            min-height: 0;
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .checkout-form-col,
        .checkout-col-scroll {
            flex: 1;
            min-height: 0;
            max-height: none;
            overflow-y: auto;
        }
        /* Sheet dentro de .checkout-modal-body; la barra inferior está fuera del body → bottom: 0 */
        .checkout-summary-col {
            position: absolute;
            left: 0;
            right: 0;
            top: auto;
            bottom: 0;
            height: 75%;
            max-height: 75%;
            z-index: 28;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 -16px 48px rgba(15, 23, 42, 0.22);
            transform: translateY(100%);
            transition: transform 0.34s cubic-bezier(0.32, 0.72, 0, 1);
            overflow: hidden;
            visibility: hidden;
            pointer-events: none;
        }
        .checkout-modal-panel.checkout-mob-sheet-open .checkout-summary-col {
            top: 25%;
            bottom: 0;
            height: auto;
            max-height: none;
            min-height: 0;
            transform: translateY(0);
            visibility: visible;
            pointer-events: auto;
            display: flex;
            flex-direction: column;
        }
        .checkout-mob-sheet-handle {
            flex-shrink: 0;
            width: 100%;
            padding: 8px 0 4px;
            border: none;
            background: transparent;
            display: flex;
            justify-content: center;
            cursor: pointer;
        }
        .checkout-mob-sheet-handle span {
            width: 40px;
            height: 4px;
            border-radius: 999px;
            background: #cbd5e1;
        }
        .checkout-modal-panel.checkout-mob-sheet-open .checkout-summary-inner {
            flex: 1;
            min-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 0 12px 12px;
            gap: 8px;
        }
        .checkout-modal-panel.checkout-mob-sheet-open .checkout-summary-head {
            flex-shrink: 0;
        }
        .checkout-modal-panel.checkout-mob-sheet-open #check-list.checkout-items-scroll {
            flex: 1 1 0;
            min-height: 0;
            max-height: none !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .checkout-modal-panel.checkout-mob-sheet-open .checkout-totals-block {
            flex-shrink: 0;
            margin-top: auto;
            padding: 8px 12px;
        }
        .checkout-modal-panel.checkout-mob-sheet-open .checkout-totals-block .checkout-total-row {
            padding: 2px 0;
            font-size: 10px;
        }
        .checkout-modal-panel.checkout-mob-sheet-open .checkout-totals-block .checkout-total-row-grand {
            margin-top: 4px;
            padding-top: 6px;
            font-size: 11px;
        }
        .checkout-modal-panel.checkout-mob-sheet-open .checkout-totals-block .checkout-grand-amount {
            font-size: 17px !important;
        }
        .checkout-modal-panel.checkout-mob-sheet-open .checkout-totals-disclaimer {
            margin: 4px 0 0;
            font-size: 8px;
            line-height: 1.3;
        }
        .checkout-summary-inner .checkout-wa-cta { display: none; }
        .checkout-mob-sheet-backdrop {
            position: absolute;
            inset: 0;
            z-index: 20;
            background: rgba(15, 23, 42, 0.45);
            border: none;
            padding: 0;
            margin: 0;
            cursor: pointer;
        }
        .checkout-modal-panel.checkout-mob-sheet-open .checkout-mob-sheet-backdrop:not([hidden]) {
            display: block;
        }
        .checkout-mob-sheet-backdrop[hidden] { display: none !important; }
        .checkout-mob-bottom-bar {
            flex-shrink: 0;
            display: flex;
            align-items: stretch;
            gap: 8px;
            padding: 10px 12px 12px;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            box-shadow: 0 -4px 20px rgba(15, 23, 42, 0.08);
            position: relative;
            z-index: 30;
            border-radius: 0 0 20px 20px;
        }
        .checkout-mob-bar-open {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            cursor: pointer;
            text-align: left;
            font: inherit;
            color: inherit;
        }
        .checkout-mob-bar-meta {
            display: block;
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
        }
        .checkout-mob-bar-total {
            font-size: 15px;
            font-weight: 900;
            color: #1B263B;
        }
        .checkout-mob-bar-open i {
            margin-left: auto;
            font-size: 11px;
            color: #64748b;
            transition: transform 0.25s;
        }
        .checkout-modal-panel.checkout-mob-sheet-open .checkout-mob-bar-open i {
            transform: rotate(180deg);
        }
        .checkout-mob-bar-cta {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0 18px;
            min-width: 110px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: #fff;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
        }
        .checkout-payment-tabs { grid-template-columns: repeat(2, 1fr); }
    }
    .payment-tab.active {
        border-color: #1B263B !important;
        background: #1B263B !important;
        color: #fff !important;
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
    .improgyp-toast-cart {
        position: relative;
        pointer-events: auto;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1rem;
        padding-right: 1.75rem;
        min-width: 260px;
        max-width: min(360px, calc(100vw - 2rem));
        background: #1B263B;
        border-radius: 16px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.35);
        animation: improgypCartToastIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .improgyp-toast-cart--out {
        opacity: 0;
        transform: translateX(16px);
        transition: opacity 0.35s ease, transform 0.35s ease;
    }
    .improgyp-toast-cart__icon {
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        background: #10b981;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #fff;
    }
    .improgyp-toast-cart__body { min-width: 0; flex: 1 1 auto; }
    .improgyp-toast-cart__btn {
        flex-shrink: 0;
        margin-left: 0.25rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        border: none;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    .improgyp-toast-cart__btn:hover { background: rgba(255, 255, 255, 0.2); }
    .improgyp-toast-cart__close {
        position: absolute;
        top: 0.375rem;
        right: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.375rem;
        height: 1.375rem;
        padding: 0;
        border: none;
        border-radius: 9999px;
        background: transparent;
        color: rgba(255, 255, 255, 0.55);
        font-size: 11px;
        line-height: 1;
        cursor: pointer;
        transition: color 0.2s ease, background 0.2s ease;
    }
    .improgyp-toast-cart__close:hover { color: #fff; background: rgba(255, 255, 255, 0.12); }
    .improgyp-toast-cart__title {
        font-size: 10px;
        font-weight: 900;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        line-height: 1.2;
    }
    .improgyp-toast-cart__name {
        font-size: 9px;
        font-weight: 700;
        color: #cbd5e1;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
    }
    @keyframes improgypCartToastIn {
        from { opacity: 0; transform: translateX(100%); }
        to { opacity: 1; transform: translateX(0); }
    }
    /* Móvil: toast centrado si el modal de producto está abierto (no tapa la X) */
    @media (max-width: 767px) {
        #toast-container.toast-container--product-modal {
            top: 50%;
            left: 50%;
            right: auto;
            transform: translate(-50%, -50%);
            width: calc(100vw - 24px);
            max-width: min(360px, calc(100vw - 24px));
            align-items: stretch;
        }
        #toast-container.toast-container--product-modal .improgyp-toast-cart {
            width: 100%;
            max-width: 100%;
        }
    }
</style>
