<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
    :root { --theme-green: #1B263B; --theme-accent: #3A86FF; }
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
        background: linear-gradient(90deg, #3A86FF 0%, #1B263B 50%, #3A86FF 100%);
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
        background: radial-gradient(circle, rgba(58,134,255,0.12) 0%, transparent 70%);
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
    }
    .modal-overlay.show .product-modal-content { transform: scale(1) translateY(0); }
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
        background: #0f172a;
        color: white;
        border-radius: 32px;
        min-height: 240px;
        display: flex;
        align-items: center;
        overflow: hidden;
        position: relative;
        box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);
        border: 1px solid rgba(255,255,255,0.05);
        transition: all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    .rompetrafico:hover { transform: translateY(-5px) scale(1.005); }
    .rt-glass-pill {
        background: rgba(255,255,255,0.18);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.25);
        font-size: 9px;
        font-weight: 900;
        padding: 6px 14px;
        border-radius: 100px;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        margin-bottom: 1.5rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .rt-content { position: relative; z-index: 10; padding: 50px; width: 70%; }
    .rt-title { font-size: 2.2rem; font-weight: 900; line-height: 1.1; margin-bottom: 1rem; }
    .rt-desc { color: rgba(255,255,255,0.85); font-size: 1rem; max-width: 550px; line-height: 1.6; }
    .rt-chevron { position: absolute; right: 8%; top: 50%; transform: translateY(-50%); font-size: 3rem; color: rgba(255,255,255,0.2); }
    @media (max-width: 768px) {
        .rt-content { width: 100%; padding: 40px 28px; text-align: center; display: flex; flex-direction: column; align-items: center; }
        .rt-title { font-size: 1.6rem; }
        .rt-chevron { display: none; }
    }
</style>
