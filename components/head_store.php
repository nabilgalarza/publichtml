<!DOCTYPE html>
<html lang="es" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    
    <title><?= htmlspecialchars($seo_titulo) ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <meta name="description" content="<?= htmlspecialchars($seo_desc) ?>">
    
    <?php include __DIR__ . '/seo_meta_og.php'; ?>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#1B263B">
    <link rel="icon" type="image/png" href="favicon-app.png?v=5"> 
    <link rel="apple-touch-icon" href="favicon-app.png?v=5">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>tailwind.config = { corePlugins: { preflight: true } };</script>
    <script>var IMPROGYP_BASE_URL = <?= json_encode($base_url ?? '', JSON_UNESCAPED_SLASHES) ?>;</script>
    <script>window.IMPROGYP_B2B_PUBLICO = <?= json_encode(function_exists('improgyp_b2b_mostrar_en_tienda') && improgyp_b2b_mostrar_en_tienda()) ?>;</script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        :root { --bg-color: #f8fafc; --text-dark: #0f172a; --theme-green: #1B263B; --theme-green-hover: #0E75AE; --text-muted: #64748b; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        body { background-color: var(--bg-color); background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 30px 30px; color: var(--text-dark); overflow-x: hidden; padding-bottom: 0; scroll-behavior: smooth; }
        .site-footer { margin-bottom: 0; }
        @media (max-width: 767px) {
            body { padding-bottom: calc(100px + env(safe-area-inset-bottom, 0px)); }
        }
        
        /* Animación Menú Dinámico y Categorías */
        #main-nav {
            --mega-nav-h: 68px;
            padding-top: env(safe-area-inset-top, 0px);
        }
        @media (min-width: 768px) { #main-nav { --mega-nav-h: 72px; } }
        .nav-transition { transition: top 0.35s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease; }
        #main-nav.nav-hidden {
            top: calc(-1 * var(--mega-nav-h, 72px) - 4px);
            transform: none;
        }
        .cat-bar-up { top: 0 !important; }

        /* Rompetráfico catálogo (Sprint 5) + CTA home (.rompetrafico legacy) */
        .rt-wrap { border-radius: 28px; overflow: hidden; transition: transform 0.35s ease, box-shadow 0.35s ease; }
        .rt-wrap:hover { transform: translateY(-4px); box-shadow: 0 24px 60px -20px rgba(15,23,42,0.25); }
        .rt-pill { display: inline-flex; align-items: center; gap: 6px; width: max-content; font-size: 9px; font-weight: 900; padding: 6px 14px; border-radius: 100px; text-transform: uppercase; letter-spacing: 0.18em; margin-bottom: 1rem; }
        .rt-cta { display: inline-flex; align-items: center; gap: 8px; margin-top: 1.25rem; background: #0f172a; color: #fff; font-size: 11px; font-weight: 800; padding: 12px 22px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.08em; }
        .rt-respiracion { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; background: #f8fafc; background-image: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%); border: 1px solid #e2e8f0; padding: 2.75rem 2rem 3rem; text-align: center; box-shadow: 0 1px 0 rgba(255,255,255,0.9) inset; }
        .rt-respiracion .rt-pill { background: rgba(27,38,59,0.08); color: #1B263B; border: 1px solid rgba(27,38,59,0.12); }
        .rt-respiracion .rt-title { width: 100%; max-width: 32rem; margin-left: auto; margin-right: auto; margin-bottom: 0.75rem; font-size: clamp(1.5rem, 4vw, 2.25rem); font-weight: 900; color: #0f172a; line-height: 1.15; text-align: center; letter-spacing: -0.02em; text-wrap: balance; }
        .rt-respiracion .rt-desc { width: 100%; max-width: 520px; margin: 0 auto; color: #475569; font-size: 0.95rem; line-height: 1.6; text-align: center; }
        .rt-split { display: grid; grid-template-columns: 1fr 1fr; min-height: 220px; background: #fff; border: 1px solid #e2e8f0; }
        .rt-split-text { padding: 2rem; display: flex; flex-direction: column; justify-content: center; align-items: flex-start; }
        .rt-split-text .rt-pill { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .rt-split-text .rt-title { font-size: clamp(1.25rem, 3vw, 1.85rem); font-weight: 900; color: #0f172a; line-height: 1.2; margin-bottom: 0.5rem; }
        .rt-split-text .rt-desc { color: #64748b; font-size: 0.875rem; line-height: 1.55; }
        .rt-split-img { background: #f1f5f9 center/cover no-repeat; min-height: 180px; }
        .rt-glass-ad { background: rgba(255,255,255,0.92); backdrop-filter: blur(20px); border: 1px solid rgba(27,38,59,0.12); padding: 2rem; display: flex; flex-wrap: wrap; align-items: center; gap: 1.5rem; }
        .rt-glass-ad .rt-pill { background: rgba(27,38,59,0.08); color: #1B263B; margin-bottom: 0; }
        .rt-glass-ad .rt-glass-body { flex: 1; min-width: 200px; }
        .rt-glass-ad .rt-title { font-size: clamp(1.35rem, 3vw, 2rem); font-weight: 900; color: #0f172a; line-height: 1.15; }
        .rt-glass-ad .rt-desc { color: #64748b; font-size: 0.9rem; margin-top: 0.5rem; }
        .rt-glass-ad .rt-glass-thumb { width: 140px; height: 140px; border-radius: 16px; background: #f8fafc center/cover no-repeat; border: 1px solid #e2e8f0; flex-shrink: 0; }
        .rt-marquee-wrap { overflow: hidden; padding: 0; background: #0f172a; }
        .rt-marquee-band { overflow: hidden; white-space: nowrap; padding: 14px 0; position: relative; }
        .rt-marquee-band--red { background: #dc2626; transform: rotate(-2.5deg); margin: 12px -2% 0; z-index: 2; }
        .rt-marquee-band--dark { background: #0f172a; transform: rotate(2.5deg); margin: -6px -2% 12px; z-index: 1; }
        .rt-marquee-track { display: inline-flex; gap: 2rem; animation: rtMarqueeL 28s linear infinite; }
        .rt-marquee-band--dark .rt-marquee-track { animation-name: rtMarqueeR; }
        .rt-marquee-item { font-size: clamp(0.75rem, 2.5vw, 1rem); font-weight: 900; text-transform: uppercase; letter-spacing: 0.12em; color: #fff; }
        .rt-marquee-sep { opacity: 0.5; padding: 0 0.25rem; }
        @keyframes rtMarqueeL { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        @keyframes rtMarqueeR { 0% { transform: translateX(-50%); } 100% { transform: translateX(0); } }
        /* Home CTA (landing_section_cta) */
        .rompetrafico { background: #17151d; color: #fff; border-radius: 24px; min-height: 320px; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; border: 1px solid rgba(255,255,255,0.09); box-shadow: 0 26px 60px -32px rgba(9,10,18,0.9); transition: transform .35s ease, box-shadow .35s ease, border-color .3s ease; }
        .rompetrafico:hover { transform: translateY(-3px); border-color: rgba(255,255,255,0.16); box-shadow: 0 34px 70px -32px rgba(9,10,18,0.95); }
        .rompetrafico .rt-bg-image { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .14; filter: saturate(.8) contrast(.95); z-index: 0; }
        .rompetrafico .rt-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(20,18,28,0.94) 0%, rgba(20,18,28,0.88) 100%); z-index: 1; }
        .rt-home-glow { position: absolute; width: 180px; height: 180px; border-radius: 50%; left: -65px; bottom: -65px; background: radial-gradient(circle, rgba(255,84,112,.22) 0%, rgba(255,84,112,0) 72%); z-index: 2; pointer-events: none; }
        .rt-content { position: relative; z-index: 3; width: min(760px, 92%); margin: 0 auto; padding: 46px 24px; display: flex; flex-direction: column; align-items: center; text-align: center; }
        .rt-glass-pill { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; font-size: 9px; font-weight: 900; padding: 7px 14px; border-radius: 999px; text-transform: uppercase; letter-spacing: .2em; margin-bottom: 1.2rem; display: inline-flex; align-items: center; gap: 8px; width: max-content; }
        .rompetrafico .rt-title { font-size: clamp(2rem, 3.2vw, 3rem); font-weight: 900; line-height: 1.08; letter-spacing: -0.04em; margin-bottom: .85rem; color: #fff; max-width: 18ch; text-wrap: balance; }
        .rompetrafico .rt-desc { color: rgba(255,255,255,.78); font-size: clamp(.95rem, 1.5vw, 1.05rem); line-height: 1.62; max-width: 56ch; margin: 0 auto; }
        .rt-home-cta-btn { display: inline-flex; align-items: center; gap: 10px; margin-top: 1.45rem; background: linear-gradient(180deg, #ff5f79 0%, #f43f5e 100%); color: #fff; font-size: 12px; font-weight: 900; letter-spacing: .08em; text-transform: none; padding: 13px 24px; border-radius: 999px; border: 1px solid rgba(255,255,255,.2); box-shadow: 0 16px 30px -18px rgba(244,63,94,.75); }
        .rt-home-cta-btn i { font-size: 12px; }
        .rt-chevron { display: none; }
        @media (max-width: 768px) {
            .rompetrafico { min-height: auto; border-radius: 22px; }
            .rt-home-glow { width: 145px; height: 145px; left: -58px; bottom: -58px; }
            .rt-content { padding: 40px 22px; }
            .rompetrafico .rt-title { font-size: 2rem; max-width: 13ch; }
            .rompetrafico .rt-desc { font-size: 1rem; max-width: 26ch; }
            .rt-home-cta-btn { margin-top: 1.3rem; padding: 13px 22px; }
        }

        .laser-text { background: linear-gradient(90deg, #0E75AE 0%, #FFFFFF 50%, #0E75AE 100%); background-size: 200% auto; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: laserSweep 3s linear infinite; }
        @keyframes laserSweep { 0% { background-position: -100% center; } 100% { background-position: 200% center; } }
        
        /* Estilos Tarjetas Productos */
        .glass-panel { background: rgba(255,255,255,0.85); backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.9); box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
        .glass-card { background: rgba(255,255,255,0.9); border: 1px solid rgba(255,255,255,0.9); border-radius: 20px; padding: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); display: flex; flex-direction: column; transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, border-color 0.2s ease; height: 100%; position: relative;}
        @media (max-width: 640px) { .glass-card { padding: 10px; border-radius: 16px; } }
        .glass-card:hover { transform: translateY(-5px); background: #ffffff; box-shadow: 0 15px 35px rgba(27, 38, 59, 0.1); border-color: rgba(27, 38, 59, 0.3); }
        
        .product-img-wrapper { width: 100%; aspect-ratio: 1/1; border-radius: 12px; overflow: hidden; margin-bottom: 12px; background: #ffffff; position: relative; padding: 1rem; border: 1px solid #f1f5f9; cursor: pointer; }
        .product-img { width: 100%; height: 100%; object-fit: contain; transition: transform 0.5s ease; mix-blend-mode: multiply; }
        .glass-card:hover .product-img { transform: scale(1.08); }
        .badge { position: absolute; top: 10px; left: 10px; background: rgba(255,255,255,0.95); backdrop-filter: blur(4px); color: var(--theme-green); font-size: 9px; font-weight: 800; padding: 4px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 1px; z-index: 2; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid rgba(122,193,66,0.2);}
        .btn-wishlist { position: absolute; top: 10px; right: 10px; background: rgba(255,255,255,0.95); backdrop-filter: blur(4px); color: #64748b; border: none; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 13px; cursor: pointer; z-index: 10; transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.03);}
        .btn-wishlist:hover { transform: scale(1.15); color: #f43f5e; }
        .btn-wishlist.active { color: #f43f5e; }
        .btn-wishlist.active i { font-weight: 900; }
        .btn-IMPROGYP { font-size: 13px; font-weight: 700; color: white; background: var(--theme-green); border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: none; cursor: pointer; transition: all 0.2s ease; }
        .btn-IMPROGYP:hover { transform: scale(1.02); background: var(--theme-green-hover); box-shadow: 0 4px 15px rgba(27, 38, 59, 0.4); }
        .omni-bar-container { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); width: 95%; max-width: 600px; z-index: 50; }
        .omni-input-wrapper { display: flex; align-items: center; gap: 12px; padding: 6px 6px 6px 16px; border-radius: 100px; transition: all 0.3s ease; }
        .omni-input-wrapper:focus-within { box-shadow: 0 15px 50px rgba(27, 38, 59, 0.2); border-color: rgba(27, 38, 59, 0.4); }
        .omni-input { flex-grow: 1; border: none; outline: none; background: transparent; font-size: 14px; color: var(--text-dark); font-family: 'Inter', sans-serif; }
        .omni-input::placeholder { color: #94a3b8; }
        .btn-send { width: 40px; height: 40px; border-radius: 50%; background: var(--theme-green); color: white; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 12px rgba(27, 38, 59, 0.3); flex-shrink: 0;}
        .btn-send:hover { transform: scale(1.05); background: var(--theme-green-hover); }
        
        /* RESTAURADO A TU CSS ORIGINAL DONDE EL ACTIVO ERA VERDE SÓLIDO */
        .cat-pill.active, .cat-pill.active:hover { background: var(--theme-green) !important; color: white !important; border-color: var(--theme-green) !important; box-shadow: 0 4px 12px rgba(122,193,66,0.4) !important; }
        
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        
        .draggable-container { cursor: grab; user-select: none; -webkit-user-select: none; scroll-behavior: auto !important; }
        .draggable-container:active { cursor: grabbing; }

        .fade-in { animation: fadeIn 0.4s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
        .wishlist-dropdown { display: none; position: absolute; top: 55px; right: 0; width: 300px; background: rgba(255,255,255,0.95); backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.9); border-radius: 16px; box-shadow: 0 15px 50px rgba(0,0,0,0.1); z-index: 100; flex-direction: column; overflow: hidden; transform-origin: top right; }
        .wishlist-dropdown.show { display: flex; animation: dropDown 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        @keyframes dropDown { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .wishlist-header { padding: 14px; border-bottom: 1px solid #f1f5f9; font-weight: 800; font-size: 14px; color: var(--text-dark); display: flex; justify-content: space-between; align-items: center; }
        .wishlist-items { max-height: 280px; overflow-y: auto; }
        .wishlist-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; transition: background 0.2s;}
        .wishlist-item:hover { background: #f8fafc; }
        .wishlist-item img { width: 40px; height: 40px; object-fit: contain; border-radius: 8px; background: #fff; border: 1px solid #f1f5f9; padding: 2px;}
        .wishlist-item-info { flex-grow: 1; min-width: 0; cursor: pointer;}
        .wishlist-item-title { font-size: 11px; font-weight: 700; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2; margin-bottom: 3px;}
        .wishlist-item-price { font-size: 11px; color: var(--theme-green); font-weight: 800; }
        .wishlist-footer { padding: 10px 14px; background: #f8fafc; text-align: center; border-top: 1px solid #f1f5f9;}
        .wishlist-footer a { font-size: 11px; font-weight: 800; color: var(--theme-green); text-decoration: none; transition: color 0.2s; display: inline-flex; align-items: center; gap: 4px;}
        .wishlist-empty { padding: 30px 14px; text-align: center; color: var(--text-muted); font-size: 12px; font-weight: 500;}
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(8px); z-index: 2000; display: flex; justify-content: center; align-items: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; padding: 20px;}
        .modal-overlay.show { opacity: 1; pointer-events: auto; }
        .product-modal-content { transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); transform: scale(0.95) translateY(20px); max-height: 92vh; overflow-y: auto; overflow-x: hidden; }
        .modal-overlay.show .product-modal-content { transform: scale(1) translateY(0); }
        @media (max-width: 767px) {
            .modal-overlay { padding: 12px 8px; }
            .improgyp-product-modal { width: 100%; max-width: none; margin-left: 0; margin-right: 0; }
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .ai-bubble { position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%) translateY(20px); width: 95%; max-width: 450px; background: rgba(255,255,255,0.95); backdrop-filter: blur(24px); border: 1px solid rgba(122,193,66,0.3); border-radius: 16px; padding: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); z-index: 100; opacity: 0; pointer-events: none; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .ai-bubble.show { opacity: 1; pointer-events: auto; transform: translateX(-50%) translateY(0); }
        .ai-bubble::after { content: ''; position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 10px solid transparent; border-right: 10px solid transparent; border-top: 10px solid rgba(255,255,255,0.95); }
        @media (min-width: 768px) {
            .ai-bubble { top: 85px; bottom: auto; transform: translateX(-50%) translateY(-20px); }
            .ai-bubble.show { transform: translateX(-50%) translateY(0); }
            .ai-bubble::after { top: -8px; bottom: auto; border-top: none; border-bottom: 10px solid rgba(255,255,255,0.95); }
        }
        .ai-bubble-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
        .ai-bubble-title { font-size: 12px; font-weight: 800; color: var(--theme-green); display: flex; align-items: center; gap: 6px;}
        .ai-bubble-close { color: #94a3b8; cursor: pointer; transition: color 0.2s; }
        .ai-bubble-text { font-size: 13px; color: var(--text-dark); line-height: 1.5; font-weight: 500;}
        .bottom-sheet-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 2000; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        .bottom-sheet-overlay.show { opacity: 1; pointer-events: auto; }
        .bottom-sheet { position: absolute; bottom: 0; left: 0; width: 100%; background: #ffffff; border-radius: 28px 28px 0 0; padding: 24px; padding-bottom: max(24px, env(safe-area-inset-bottom)); transform: translateY(100%); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 -10px 40px rgba(0,0,0,0.1); }
        .bottom-sheet-overlay.show .bottom-sheet { transform: translateY(0); }
        .bs-cat-btn { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-radius: 14px; background: #f8fafc; border: 1px solid #f1f5f9; font-weight: 700; color: #334155; font-size: 13px; transition: all 0.2s; cursor: pointer; }
        .bs-cat-btn.active { background: var(--theme-green); color: white; border-color: var(--theme-green); box-shadow: 0 4px 15px rgba(27, 38, 59, 0.3); }
        .bs-cat-btn.active .bs-icon { color: white; opacity: 1; }
        .bs-icon { color: #94a3b8; font-size: 14px; opacity: 0; transition: opacity 0.2s; }
        .cart-drawer-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 2000; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        .cart-drawer-overlay.show { opacity: 1; pointer-events: auto; }
        .cart-drawer { position: absolute; top: 0; right: 0; width: 100%; max-width: 380px; height: 100%; background: #ffffff; transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; flex-direction: column; box-shadow: -10px 0 40px rgba(0,0,0,0.1); }
        .cart-drawer-overlay.show .cart-drawer { transform: translateX(0); }
        .cart-item { display: flex; align-items: center; gap: 10px; padding: 14px; border-bottom: 1px solid #f1f5f9; transition: background 0.2s;}
        .cart-item-img { width: 45px; height: 45px; object-fit: contain; border-radius: 8px; background: #fff; padding: 4px; border: 1px solid #e2e8f0; flex-shrink: 0;}
        .cart-qty-btn { width: 26px; height: 26px; border-radius: 6px; background: #f1f5f9; color: #475569; border: none; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;}
        /* Brand Pills Styles */
        .brand-pill { background: white; border: 1px solid #e2e8f0; color: #64748b; font-size: 11px; font-weight: 800; padding: 6px 14px; border-radius: 100px; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); cursor: pointer; white-space: nowrap; shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .brand-pill:hover { border-color: var(--theme-green); color: var(--theme-green); transform: translateY(-1px); }
        .brand-pill.active { background: var(--theme-green); color: white; border-color: var(--theme-green); box-shadow: 0 4px 12px rgba(27, 38, 59, 0.3); }
        
        .brand-label { font-size: 10px; font-weight: 800; color: var(--theme-green); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; display: block; opacity: 0.8; }
        .sku-label { font-size: 10px; font-weight: 600; color: #94a3b8; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 4px; }

        /* Estilos Sucursales */
        .location-card { background: white; border: 1px solid #f1f5f9; border-radius: 20px; padding: 18px; transition: all 0.3s ease; cursor: pointer; }
        .location-card:hover { border-color: var(--theme-green); box-shadow: 0 10px 30px rgba(27, 38, 59, 0.05); transform: translateY(-2px); }
        .location-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981; margin-right: 6px; box-shadow: 0 0 8px rgba(16,185,129,0.5); }
        .btn-location-action { background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 800; padding: 10px; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s; border: 1px solid #f1f5f9; flex: 1; }
        .btn-location-action:hover { background: #f1f5f9; color: var(--theme-green); border-color: var(--theme-green)/20; }
        
        .modal-location-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; width: 100%; padding: 15px 4px 30px 4px; }
        @media (max-width: 640px) { .modal-location-grid { grid-template-columns: 1fr; } }
    </style>
    <link rel="stylesheet" href="css/locales_showroom.css?v=1">
</head>
