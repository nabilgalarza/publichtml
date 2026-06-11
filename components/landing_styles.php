<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
    :root { --theme-green: #1B263B; --theme-accent: #0E75AE; }
    body.landing-page {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
        background-size: 30px 30px;
        color: #0f172a;
        overflow-x: hidden;
    }
    body.landing-page .site-footer { margin-bottom: 0; }
    .laser-text {
        background: linear-gradient(90deg, #0E75AE 0%, #1B263B 50%, #0E75AE 100%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: laserSweep 3s linear infinite;
    }
    @keyframes laserSweep {
        0% { background-position: -100% center; }
        100% { background-position: 200% center; }
    }
    .landing-hero-glow {
        position: absolute;
        top: -20%;
        left: 50%;
        transform: translateX(-50%);
        width: min(600px, 90vw);
        height: 400px;
        background: radial-gradient(circle, rgba(14, 117, 174, 0.12) 0%, transparent 70%);
        pointer-events: none;
    }
    .glass-card-landing {
        background: rgba(255,255,255,0.92);
        border: 1px solid rgba(255,255,255,0.95);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.2s;
    }
    .glass-card-landing:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(27,38,59,0.08);
        border-color: rgba(27,38,59,0.15);
    }
    /* Cards producto home = mismo patrón que productos.php */
    .landing-page .glass-card {
        background: rgba(255,255,255,0.9);
        border: 1px solid rgba(255,255,255,0.9);
        border-radius: 20px;
        padding: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        display: flex;
        flex-direction: column;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, border-color 0.2s ease;
        height: 100%;
        position: relative;
    }
    @media (max-width: 640px) {
        .landing-page .glass-card { padding: 10px; border-radius: 16px; }
    }
    .landing-page .glass-card:hover {
        transform: translateY(-5px);
        background: #ffffff;
        box-shadow: 0 15px 35px rgba(27, 38, 59, 0.1);
        border-color: rgba(27, 38, 59, 0.3);
    }
    .landing-page .product-img-wrapper {
        width: 100%;
        aspect-ratio: 1/1;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 12px;
        background: #ffffff;
        position: relative;
        padding: 1rem;
        border: 1px solid #f1f5f9;
        cursor: pointer;
    }
    .landing-page .product-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        mix-blend-mode: multiply;
        transition: transform 0.3s ease;
    }
    .landing-page .glass-card:hover .product-img { transform: scale(1.08); }
    .landing-page .btn-wishlist {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(4px);
        color: #64748b;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        cursor: pointer;
        z-index: 10;
        transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.03);
    }
    .landing-page .btn-wishlist:hover { transform: scale(1.15); color: #f43f5e; }
    .landing-page .btn-wishlist.active { color: #f43f5e; }
    .landing-page .badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(4px);
        color: var(--theme-green);
        font-size: 9px;
        font-weight: 800;
        padding: 4px 8px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 1px;
        z-index: 2;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: 1px solid rgba(27,38,59,0.12);
    }
    .landing-page .fade-in { animation: fadeIn 0.4s ease-in-out; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .landing-page .improgyp-home-card-body { cursor: pointer; }
    .cat-tile {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1.25rem 1rem;
        min-height: 130px;
        justify-content: center;
    }
    .cat-tile i { font-size: 1.5rem; margin-bottom: 0.75rem; color: var(--theme-green); }
    .product-card-landing .product-img-wrap {
        aspect-ratio: 1;
        background: #fff;
        border-radius: 12px;
        padding: 0.75rem;
        border: 1px solid #f1f5f9;
        margin-bottom: 0.75rem;
        overflow: hidden;
    }
    .product-card-landing img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        mix-blend-mode: multiply;
    }
    .brand-label {
        font-size: 10px;
        font-weight: 800;
        color: var(--theme-green);
        text-transform: uppercase;
        letter-spacing: 1px;
        display: block;
        opacity: 0.85;
    }
    .sku-label {
        font-size: 10px;
        font-weight: 600;
        color: #94a3b8;
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-block;
    }
    .btn-IMPROGYP {
        font-size: 13px;
        font-weight: 700;
        color: white;
        background: var(--theme-green);
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-IMPROGYP:hover {
        transform: scale(1.02);
        background: var(--theme-accent);
        box-shadow: 0 4px 15px rgba(27, 38, 59, 0.25);
    }
    #product-modal.hidden {
        display: none;
    }
    #product-modal:not(.hidden) {
        display: flex;
    }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .nav-transition { transition: transform 0.3s ease; }
    #main-nav.nav-hidden {
        top: calc(-1 * var(--mega-nav-h, 72px) - 4px);
        transform: none;
    }
    .wishlist-dropdown { display: none; position: absolute; top: 55px; right: 0; width: 280px; background: #fff; border-radius: 16px; box-shadow: 0 15px 50px rgba(0,0,0,0.1); z-index: 250; flex-direction: column; overflow: hidden; }
    .wishlist-dropdown.show { display: flex; }
    .wishlist-header { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-weight: 800; font-size: 14px; display: flex; justify-content: space-between; align-items: center; }
    .wishlist-items { max-height: 280px; overflow-y: auto; }
    .wishlist-item { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid #f1f5f9; }
    .wishlist-item-info { flex-grow: 1; min-width: 0; cursor: pointer; }
    .wishlist-item-title { font-size: 12px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wishlist-item-price { font-size: 11px; font-weight: 800; color: #1B263B; margin-top: 2px; }
    .wishlist-empty { padding: 2rem 1rem; text-align: center; color: #94a3b8; font-size: 12px; font-weight: 600; }
    .wishlist-footer { padding: 12px 16px; background: #f8fafc; border-top: 1px solid #f1f5f9; text-align: center; }
    .wishlist-footer a { font-size: 11px; font-weight: 800; color: #1B263B; text-decoration: none; }
    .modal-overlay {
        position: fixed; inset: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(8px);
        z-index: 2000;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
        padding: 20px;
    }
    .modal-overlay.show { opacity: 1; pointer-events: auto; }
    .product-modal-content {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        transform: scale(0.95) translateY(20px);
        max-height: 92vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .modal-overlay.show .product-modal-content { transform: scale(1) translateY(0); }
    @media (max-width: 767px) {
        .modal-overlay { padding: 12px 8px; }
        .improgyp-product-modal { width: 100%; max-width: none; margin-left: 0; margin-right: 0; }
    }
    /* Home: pie del modal en 2 filas (acciones + precio/ver tienda) */
    .improgyp-modal-footer--home {
        flex-direction: column;
    }
    .improgyp-modal-footer--home .improgyp-modal-footer__actions {
        justify-content: flex-end;
    }
    .improgyp-modal-footer--home .improgyp-modal-footer__bottom {
        align-items: flex-end;
    }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .location-card {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        padding: 18px;
        transition: all 0.3s ease;
        cursor: default;
    }
    .location-card--clickable { cursor: pointer; }
    .location-card--clickable:hover {
        border-color: var(--theme-green);
        box-shadow: 0 10px 30px rgba(27, 38, 59, 0.05);
        transform: translateY(-2px);
    }
    .location-dot {
        display: inline-block;
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 8px rgba(16, 185, 129, 0.5);
    }
    .btn-location-action {
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        padding: 10px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid #f1f5f9;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-location-action:hover { background: #f1f5f9; color: var(--theme-green); }

    /* Sucursales + asesoría — layout shell (Showroom: css/locales_showroom.css) */
    .locales-premium-section {
        padding: 3.5rem 0 4.5rem;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 45%, #f1f5f9 100%);
        border-top: 1px solid rgba(226, 232, 240, 0.8);
        border-bottom: 1px solid rgba(226, 232, 240, 0.6);
    }
    @media (min-width: 768px) {
        .locales-premium-section { padding: 5rem 0 6rem; }
    }
    .locales-premium-shell {
        border-radius: 28px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 24px 80px -24px rgba(15, 23, 42, 0.12);
        overflow: hidden;
    }
    .locales-premium-grid {
        display: grid;
        grid-template-columns: 1fr;
    }
    @media (min-width: 1024px) {
        .locales-premium-grid { grid-template-columns: 1fr 1fr; min-height: 520px; }
    }
    .locales-premium-locations {
        padding: 2rem 1.75rem 2.25rem;
    }
    @media (min-width: 768px) {
        .locales-premium-locations { padding: 2.5rem 2.5rem 2.75rem; }
    }
    @media (min-width: 1024px) {
        .locales-premium-locations {
            padding: 2.75rem 2.75rem 2.75rem 3rem;
            border-right: 1px solid rgba(226, 232, 240, 0.9);
        }
    }
    .locales-premium-eyebrow {
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: #0E75AE;
        margin-bottom: 0.625rem;
    }
    .locales-premium-widget .location-card--featured {
        background: rgba(248, 250, 252, 0.95);
        border: 1px solid #e2e8f0;
        border-left: 4px solid #0E75AE;
        border-radius: 16px;
        padding: 1.25rem 1.35rem;
        box-shadow: none;
    }
    .locales-premium-widget .location-card--featured.location-card--clickable:hover {
        border-color: #e2e8f0;
        border-left-color: #0E75AE;
        box-shadow: 0 12px 32px -12px rgba(27, 38, 59, 0.12);
        transform: translateY(-1px);
    }
    .locales-premium-widget .location-card--featured .btn-location-action {
        background: #fff;
        border-color: #e2e8f0;
        color: #475569;
    }
    .locales-premium-widget .location-card--featured .btn-location-action:hover {
        background: #1B263B;
        border-color: #1B263B;
        color: #fff;
    }
    .locales-premium-ghost-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.875rem 1.25rem;
        font-size: 12px;
        font-weight: 800;
        color: #334155;
        background: transparent;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }
    .locales-premium-ghost-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #1B263B;
    }
    .asesoria-premium-panel {
        position: relative;
        background: #1B263B;
        padding: 2rem 1.75rem 2.25rem;
    }
    @media (min-width: 768px) {
        .asesoria-premium-panel { padding: 2.5rem 2.5rem 2.75rem; }
    }
    @media (min-width: 1024px) {
        .asesoria-premium-panel { padding: 2.75rem 3rem; }
    }
    .asesoria-premium-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px);
        background-size: 24px 24px;
        pointer-events: none;
        opacity: 0.5;
    }
    .asesoria-premium-panel__inner {
        position: relative;
        z-index: 1;
    }
    .asesoria-premium-panel__head {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 0.75rem;
    }
    .asesoria-premium-badge {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: rgba(14, 117, 174, 0.2);
        border: 1px solid rgba(14, 117, 174, 0.35);
        color: #93c5fd;
        font-size: 1.1rem;
    }
    .asesoria-premium-title {
        font-size: 1.25rem;
        font-weight: 900;
        color: #fff;
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin-bottom: 0.35rem;
    }
    @media (min-width: 768px) {
        .asesoria-premium-title { font-size: 1.5rem; }
    }
    .asesoria-premium-lead {
        font-size: 0.875rem;
        color: rgba(203, 213, 225, 0.95);
        line-height: 1.5;
    }
    .asesoria-premium-trust {
        font-size: 11px;
        color: rgba(148, 163, 184, 0.9);
        margin-bottom: 1.5rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .asesoria-premium-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(148, 163, 184, 0.95);
        margin-bottom: 0.4rem;
    }
    .asesoria-premium-input {
        width: 100%;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.08);
        color: #f8fafc;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.75rem 1rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    .asesoria-premium-input::placeholder {
        color: rgba(148, 163, 184, 0.65);
        font-weight: 500;
    }
    .asesoria-premium-input:focus {
        outline: none;
        border-color: rgba(14, 117, 174, 0.65);
        background: rgba(255, 255, 255, 0.1);
        box-shadow: 0 0 0 3px rgba(14, 117, 174, 0.25);
    }
    .asesoria-premium-textarea {
        resize: vertical;
        min-height: 88px;
    }
    .asesoria-premium-msg {
        font-size: 0.875rem;
        font-weight: 700;
    }
    .asesoria-premium-msg.text-success { color: #6ee7b7; }
    .asesoria-premium-msg.text-error { color: #fca5a5; }
    .asesoria-premium-submit {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.625rem;
        margin-top: 0.25rem;
        padding: 1rem 1.25rem;
        border: none;
        border-radius: 14px;
        background: #fff;
        color: #1B263B;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.15s ease, opacity 0.2s ease;
    }
    .asesoria-premium-submit:hover:not(:disabled) {
        background: #0E75AE;
        color: #fff;
    }
    .asesoria-premium-submit:active:not(:disabled) {
        transform: scale(0.99);
    }
    .asesoria-premium-submit:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }
    .asesoria-premium-submit__spinner.hidden,
    .asesoria-premium-submit.is-loading .asesoria-premium-submit__label,
    .asesoria-premium-submit.is-loading .asesoria-premium-submit__icon {
        display: none;
    }
    .asesoria-premium-submit.is-loading .asesoria-premium-submit__spinner {
        display: inline-flex;
    }

    .modal-location-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
        width: 100%;
        padding: 4px 4px 24px;
    }
    @media (max-width: 640px) {
        .modal-location-grid { grid-template-columns: 1fr; }
    }
    .glass-card {
        background: rgba(255,255,255,0.9);
        border: 1px solid rgba(255,255,255,0.9);
        border-radius: 20px;
        padding: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        position: relative;
    }
    .glass-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,0.08); }
    .rompetrafico {
        background: #17151d;
        color: #fff;
        border-radius: 24px;
        min-height: 340px;
        overflow: hidden;
        position: relative;
        border: 1px solid rgba(255,255,255,0.09);
        box-shadow: 0 26px 60px -32px rgba(9,10,18,0.9);
        transition: transform .35s ease, box-shadow .35s ease, border-color .3s ease;
    }
    .rompetrafico:hover {
        transform: translateY(-3px);
        border-color: rgba(255,255,255,0.16);
        box-shadow: 0 34px 70px -32px rgba(9,10,18,0.95);
    }
    .rompetrafico.rompetrafico--home-cta,
    .rompetrafico.rompetrafico--home-cta:hover {
        box-shadow: none;
    }
    .rompetrafico-has-image {
        display: grid;
        grid-template-columns: minmax(280px, 40%) minmax(0, 1fr);
        align-items: stretch;
    }
    .rompetrafico-text-only {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    .rt-copy-panel {
        position: relative;
        background: linear-gradient(160deg, #0f1a3f 0%, #12172d 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100%;
    }
    .rt-copy-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 10% 82%, rgba(255,84,112,.2) 0%, rgba(255,84,112,0) 58%);
        pointer-events: none;
    }
    .rt-media-panel {
        position: relative;
        min-height: 100%;
        background: #0b122d;
    }
    .rt-media-panel::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(11,18,45,0.25) 0%, rgba(11,18,45,0.02) 35%, rgba(11,18,45,0.02) 100%);
        pointer-events: none;
    }
    .rt-media-panel img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transform: scale(1.02);
        transition: transform .45s ease;
    }
    .rompetrafico-has-image:hover .rt-media-panel img { transform: scale(1.06); }
    .rt-content {
        position: relative;
        z-index: 2;
        width: min(480px, 92%);
        margin: 0 auto;
        padding: 44px 26px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
    .rt-glass-pill {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.2);
        color: #fff;
        font-size: 9px;
        font-weight: 900;
        padding: 7px 14px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: .2em;
        margin-bottom: 1.2rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: max-content;
    }
    .rt-title {
        font-size: clamp(2rem, 3.2vw, 3rem);
        font-weight: 900;
        line-height: 1.08;
        letter-spacing: -0.04em;
        margin-bottom: .85rem;
        color: #fff;
        max-width: 18ch;
        text-wrap: balance;
    }
    .rt-desc {
        color: rgba(255,255,255,.78);
        font-size: clamp(.95rem, 1.5vw, 1.05rem);
        line-height: 1.62;
        max-width: 56ch;
        margin: 0;
    }
    .rt-home-cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 1.45rem;
        background: linear-gradient(180deg, #ff5f79 0%, #f43f5e 100%);
        color: #fff;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 13px 24px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.2);
        box-shadow: 0 16px 30px -18px rgba(244,63,94,.75);
        width: max-content;
    }
    .rt-home-cta-btn i { font-size: 12px; }
    .rt-chevron { display: none; }
    .rompetrafico-text-only .rt-content { align-items: center; text-align: center; }
    @media (max-width: 768px) {
        .rompetrafico { min-height: auto; border-radius: 22px; }
        .rompetrafico-has-image {
            grid-template-columns: 1fr;
        }
        .rt-copy-panel {
            order: 1;
        }
        .rt-media-panel {
            order: 2;
            min-height: 180px;
        }
        .rt-content {
            width: 100%;
            padding: 36px 22px 30px;
            align-items: center;
            text-align: center;
        }
        .rt-title { font-size: 2rem; max-width: 13ch; }
        .rt-desc { font-size: 1rem; max-width: 26ch; margin: 0 auto; }
        .rt-home-cta-btn { margin-top: 1.3rem; padding: 13px 22px; }
    }

    /* Carrusel productos home (Tendencias / Más vendidos) */
    .improgyp-product-carousel {
        position: relative;
    }
    .improgyp-carousel-viewport {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-snap-type: x mandatory;
        scroll-padding-inline: 2px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding: 4px 2px 10px;
    }
    .improgyp-carousel-viewport::-webkit-scrollbar {
        display: none;
    }
    .improgyp-carousel-slide {
        flex: 0 0 calc((100% - 1rem) / 2.12);
        min-width: 0;
        scroll-snap-align: start;
    }
    @media (max-width: 767px) {
        .improgyp-carousel-viewport {
            gap: 0.5rem;
            padding: 2px 0 8px;
            scroll-padding-inline: 0;
        }
        .improgyp-carousel-slide {
            flex: 0 0 calc((100% - 0.5rem) / 2);
        }
        .landing-page .glass-card {
            padding: 8px;
        }
        .landing-page .product-img-wrapper {
            padding: 0.5rem;
            margin-bottom: 8px;
        }
    }
    @media (min-width: 768px) {
        .improgyp-product-carousel {
            padding: 0 2.75rem;
        }
        .improgyp-carousel-slide {
            flex: 0 0 calc((100% - 2rem) / 3);
        }
    }
    @media (min-width: 1024px) {
        .improgyp-carousel-slide {
            flex: 0 0 calc((100% - 3rem) / 4);
        }
    }
    .improgyp-carousel-btn {
        display: none;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #1B263B;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.1);
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s, color 0.2s, opacity 0.2s;
    }
    @media (min-width: 768px) {
        .improgyp-carousel-btn {
            display: inline-flex;
        }
    }
    .improgyp-carousel-btn:hover:not(:disabled) {
        background: #1B263B;
        color: #fff;
        border-color: #1B263B;
    }
    .improgyp-carousel-btn:disabled {
        opacity: 0.35;
        cursor: default;
    }
    .improgyp-carousel-prev { left: 0; }
    .improgyp-carousel-next { right: 0; }

    /* Marcas aliadas — marquee de logos (sin caja; logos sobre fondo de página) */
    .marcas-marquee-section .marcas-marquee-wrap {
        position: relative;
        margin-top: 0.5rem;
        overflow: hidden;
    }
    .marcas-marquee-viewport {
        overflow: hidden;
        padding: 0.5rem 0 0.75rem;
    }
    .marcas-marquee-track {
        display: flex;
        width: max-content;
        animation: marcasMarqueeScroll var(--marcas-marquee-duration, 50s) linear infinite;
    }
    .marcas-marquee-wrap:hover .marcas-marquee-track {
        animation-play-state: paused;
    }
    @media (prefers-reduced-motion: reduce) {
        .marcas-marquee-track { animation: none; }
        .marcas-marquee-viewport { overflow-x: auto; }
    }
    @keyframes marcasMarqueeScroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .marcas-marquee-group {
        display: flex;
        align-items: center;
        gap: 2.5rem;
        padding: 0 1.25rem;
        flex-shrink: 0;
    }
    @media (min-width: 768px) {
        .marcas-marquee-group { gap: 3.5rem; padding: 0 1.75rem; }
    }
    .marcas-marquee-item {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 262px;
        height: 113px;
    }
    @media (min-width: 768px) {
        .marcas-marquee-item { width: 305px; height: 122px; }
    }
    .marcas-marquee-logo {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        filter: grayscale(100%);
        opacity: 0.65;
        transition: filter 0.25s ease, opacity 0.25s ease, transform 0.25s ease;
    }
    .marcas-marquee-item:hover .marcas-marquee-logo {
        filter: grayscale(0%);
        opacity: 1;
        transform: scale(1.05);
    }
    .marcas-marquee-fade {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 48px;
        z-index: 2;
        pointer-events: none;
    }
    .marcas-marquee-fade--left {
        left: 0;
        background: linear-gradient(90deg, #f8fafc 0%, transparent 100%);
    }
    .marcas-marquee-fade--right {
        right: 0;
        background: linear-gradient(270deg, #f8fafc 0%, transparent 100%);
    }
    .landing-nosotros-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2.5rem;
        align-items: center;
    }
    @media (min-width: 1024px) {
        .landing-nosotros-grid {
            grid-template-columns: 1fr 1fr;
            gap: 3.5rem;
        }
    }
    .landing-nosotros-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem 2.5rem;
    }
    .landing-nosotros-stat {
        padding-left: 1rem;
        border-left: 3px solid #0E75AE;
        min-width: 120px;
    }
    .landing-nosotros-media-frame {
        border-radius: 1.25rem;
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(27, 38, 59, 0.08);
        box-shadow: 0 20px 50px rgba(27, 38, 59, 0.08);
    }
    .landing-nosotros-img {
        width: 100%;
        height: auto;
        min-height: 240px;
        max-height: 420px;
        object-fit: cover;
        display: block;
    }
    @media (min-width: 1024px) {
        .landing-nosotros-media { order: 2; }
        .landing-nosotros-copy { order: 1; }
    }
</style>
<link rel="stylesheet" href="css/locales_showroom.css?v=1">
