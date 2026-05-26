<?php
require_once __DIR__ . '/security_headers.php';
// 1. BOTÓN DE PÁNICO (Modo Mantenimiento)
$ruta_estado = __DIR__ . '/estado_tienda.json';
$modo_mantenimiento = false;
if (file_exists($ruta_estado)) {
    $estado_data = json_decode(file_get_contents($ruta_estado), true);
    if (isset($estado_data['mantenimiento']) && $estado_data['mantenimiento'] === true) {
        $modo_mantenimiento = true;
    }
}

if ($modo_mantenimiento) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Actualizando Catálogo | IMPROGYP</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="bg-[#0F172A] h-screen flex flex-col items-center justify-center text-center p-6 selection:bg-[#1B263B] selection:text-white">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-96 bg-[#1B263B] rounded-full mix-blend-screen filter blur-[128px] opacity-10 pointer-events-none"></div>
        <img src="logo-claro.png?v=5" alt="IMPROGYP" class="h-10 md:h-12 mb-10 opacity-90 relative z-10" onerror="this.style.display='none'">
        
        <div class="bg-slate-800/40 backdrop-blur-xl p-8 md:p-10 rounded-3xl border border-slate-700 max-w-md w-full shadow-2xl relative z-10">
            <div class="w-20 h-20 bg-rose-500/10 border border-rose-500/30 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fa-solid fa-lock text-3xl text-rose-500"></i>
            </div>
            <h1 class="text-2xl font-black text-white mb-3">Actualizando Catálogo</h1>
            <p class="text-slate-400 text-sm mb-8 leading-relaxed">Nuestra tienda pública se encuentra en mantenimiento temporal para actualizar inventario y precios. Volveremos a estar en línea en unos minutos.</p>
            <a href="https://wa.me/593991754887" class="inline-flex w-full justify-center items-center gap-2 bg-[#25D366] text-black font-black py-4 px-6 rounded-xl hover:bg-[#20b858] transition-colors shadow-lg active:scale-95"><i class="fa-brands fa-whatsapp text-xl"></i> Atención por WhatsApp</a>
        </div>
    </body>
    </html>
    <?php
    exit; 
}

// 2. SEO Dinámico
$seo_titulo = "IMPROGYP | E-commerce Inteligente";
$seo_desc = "La mejor selección de herramientas técnicas y profesionales. Compra fácil, rápido y seguro.";
$seo_img = "favicon-app.png"; 

$ruta_seo = __DIR__ . '/seo.json';
if (file_exists($ruta_seo)) {
    $seo_data = json_decode(file_get_contents($ruta_seo), true);
    if (!empty($seo_data['titulo'])) $seo_titulo = $seo_data['titulo'];
    if (!empty($seo_data['descripcion'])) $seo_desc = $seo_data['descripcion'];
    if (!empty($seo_data['imagen_url'])) $seo_img = $seo_data['imagen_url'];
}

// 3. Deep Linking & OG Dinámico por Producto
$prod_compartido = null;
if (isset($_GET['p'])) {
    $p_name = trim($_GET['p']);
    $ruta_cat = __DIR__ . '/catalogo.json';
    if (file_exists($ruta_cat)) {
        $cat_data = json_decode(file_get_contents($ruta_cat), true);
        if (is_array($cat_data)) {
            foreach ($cat_data as $p) {
                if ($p['nombre'] === $p_name || (isset($p['codigo']) && $p['codigo'] === $p_name)) {
                    $prod_compartido = $p;
                    $seo_titulo = $p['nombre'] . " | IMPROGYP";
                    if (!empty($p['desc_larga'])) {
                        $seo_desc = mb_substr(strip_tags($p['desc_larga']), 0, 150) . "...";
                    }
                    if (!empty($p['imagen'])) {
                        $seo_img = $p['imagen'];
                    }
                    break;
                }
            }
        }
    }
}

$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$url_actual = $protocolo . "://" . $host . $uri;

// Base path for relative images
$script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_path = ($script_path === '/') ? '/' : rtrim($script_path, '/') . '/';
$base_url = $protocolo . "://" . $host . $base_path;

if (!empty($seo_img) && !preg_match("~^(?:f|ht)tps?://~i", $seo_img)) {
    $seo_img = $base_url . ltrim($seo_img, '/');
}
?>
<!DOCTYPE html>
<html lang="es" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title><?= htmlspecialchars($seo_titulo) ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <meta name="description" content="<?= htmlspecialchars($seo_desc) ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($url_actual) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($seo_titulo) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seo_desc) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seo_img) ?>?v=<?= time() ?>">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= htmlspecialchars($seo_titulo) ?>">
    <meta property="og:site_name" content="IMPROGYP">
    <meta property="og:locale" content="es_EC">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= htmlspecialchars($url_actual) ?>">
    <meta property="twitter:title" content="<?= htmlspecialchars($seo_titulo) ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($seo_desc) ?>">
    <meta property="twitter:image" content="<?= htmlspecialchars($seo_img) ?>?v=<?= time() ?>">
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#1B263B">
    <link rel="icon" type="image/png" href="favicon-app.png?v=5"> 
    <link rel="apple-touch-icon" href="favicon-app.png?v=5">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>tailwind.config = { corePlugins: { preflight: true } };</script>
    <script>var IMPROGYP_BASE_URL = '';</script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        :root { --bg-color: #f8fafc; --text-dark: #0f172a; --theme-green: #1B263B; --theme-green-hover: #3A86FF; --text-muted: #64748b; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        body { background-color: var(--bg-color); background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 30px 30px; color: var(--text-dark); overflow-x: hidden; padding-bottom: 120px; scroll-behavior: smooth; }
        
        /* Animación Menú Dinámico y Categorías */
        .nav-transition { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), top 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .nav-hidden { transform: translateY(-100%); }
        .cat-bar-up { top: 0 !important; }

        /* Banner Rompetrafico: Elite Contrast System (Unified Style) */
        .rompetrafico { background: #0f172a; color: white; border-radius: 32px; min-height: 240px; display: flex; align-items: center; overflow: hidden; position: relative; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.05); transition: all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1); }
        .rompetrafico:hover { transform: translateY(-5px) scale(1.005); box-shadow: 0 50px 120px -30px rgba(0,0,0,0.6); border-color: rgba(255,255,255,0.15); }
        
        .rt-glass-pill { background: rgba(255,255,255,0.18); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.25); color: #fff; font-size: 9px; font-weight: 900; padding: 6px 14px; border-radius: 100px; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 1.5rem; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        
        .rt-content { position: relative; z-index: 10; padding: 50px; width: 70%; }
        .rt-title { font-size: 2.8rem; font-weight: 900; line-height: 1.1; letter-spacing: -0.04em; margin-bottom: 1rem; color: #ffffff; }
        .rt-desc { color: rgba(255,255,255,0.85); font-size: 1rem; font-weight: 500; max-width: 550px; line-height: 1.6; opacity: 1; text-shadow: 0 2px 10px rgba(0,0,0,0.5); }
        
        .rt-chevron { position: absolute; right: 8%; top: 50%; transform: translateY(-50%); font-size: 4rem; color: rgba(255,255,255,0.2); transition: all 0.3s ease; }
        .rompetrafico:hover .rt-chevron { transform: translateY(-50%) translateX(10px); color: rgba(255,255,255,0.5); }

        @media (max-width: 768px) {
            .rompetrafico { min-height: auto; }
            .rt-content { width: 100%; padding: 40px 30px; text-align: center; display: flex; flex-direction: column; align-items: center; }
            .rt-title { font-size: 1.8rem; }
            .rt-desc { font-size: 0.9rem; }
            .rt-chevron { display: none; }
        }

        .laser-text { background: linear-gradient(90deg, #3A86FF 0%, #FFFFFF 50%, #3A86FF 100%); background-size: 200% auto; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: laserSweep 3s linear infinite; }
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
</head>
<body class="antialiased">

    <nav id="main-nav" class="fixed top-0 w-full z-40 bg-white/70 backdrop-blur-xl border-b border-slate-200/50 nav-transition">
        <div class="max-w-[1240px] mx-auto px-4 md:px-6 py-4 flex justify-between items-center gap-4">
            <!-- LOGO -->
            <div class="flex items-center flex-shrink-0">
                <a href="#" onclick="filtrarCategoria('Todos'); return false;"><img src="logo-oscuro.png?v=5" alt="IMPROGYP" class="h-7 md:h-8 object-contain"></a>
            </div>
            
            <!-- BUSCADOR ESCRITORIO (Centro) -->
            <div class="hidden md:flex flex-1 max-w-lg mx-4">
                <div class="glass-panel omni-input-wrapper w-full !py-1.5 !px-4 !bg-slate-100/30 border-slate-200/50">
                    <i class="fa-solid fa-wand-magic-sparkles text-[#1B263B] text-sm"></i>
                    <input type="text" class="omni-input omni-input-field" placeholder="Busca producto o pregúntale a la IA" oninput="filtrarPorTexto(this.value)" onkeypress="if(event.key === 'Enter') buscarConIA(null, this)">
                    <button class="btn-send !w-8 !h-8 !shadow-none" onclick="buscarConIA(null, this.previousElementSibling)"><i class="btn-send-icon fa-solid fa-paper-plane text-[10px]"></i></button>
                </div>
            </div>

            <!-- ACCIONES Y ACCESO B2B -->
            <div class="flex items-center gap-2 md:gap-3">
                <!-- Mayoristas -->
                <a href="b2b/" class="hidden md:flex items-center gap-2 text-[13px] font-bold text-slate-500 hover:text-[#1B263B] transition-colors mr-2">
                    <i class="fa-solid fa-briefcase"></i> Mayoristas
                </a>
                <a href="b2b/" class="md:hidden relative w-9 h-9 md:w-10 md:h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#1B263B] hover:text-[#1B263B] transition-all shadow-sm" title="Acceso B2B">
                    <i class="fa-solid fa-briefcase text-[13px] md:text-sm"></i>
                </a>

                <!-- Compartir Tienda -->
                <button onclick="compartirTienda()" class="relative w-9 h-9 md:w-10 md:h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#1B263B] hover:text-[#1B263B] transition-all shadow-sm" title="Compartir Tienda">
                    <i class="fa-solid fa-share-nodes text-[13px] md:text-sm"></i>
                </button>

                <!-- Sucursales (Móvil) -->
                <button onclick="abrirModalLocales()" class="md:hidden relative w-9 h-9 md:w-10 md:h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-700 hover:border-emerald-400 hover:text-emerald-500 transition-all shadow-sm">
                    <i class="fa-solid fa-map-location-dot text-[13px] md:text-sm"></i>
                </button>

                <!-- Deseos / Wishlist -->
                <div class="relative">
                    <button onclick="toggleWishlistModal(event)" class="relative w-9 h-9 md:w-10 md:h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-700 hover:border-rose-400 hover:text-rose-500 transition-all shadow-sm">
                        <i class="fa-solid fa-heart text-[13px] md:text-sm"></i>
                        <span id="wishlist-badge" class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center shadow-md transition-transform duration-200 hidden">0</span>
                    </button>
                    <div id="wishlist-modal" class="wishlist-dropdown">
                        <div class="wishlist-header"><span>Mis Deseos</span><button onclick="toggleWishlistModal(event)" class="text-slate-400 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark text-base"></i></button></div>
                        <div id="wishlist-items-container" class="wishlist-items custom-scrollbar"></div>
                        <div class="wishlist-footer"><a href="#" onclick="mostrarWishlistCompleta(); return false;">Ver lista completa <i class="fa-solid fa-arrow-right-long"></i></a></div>
                    </div>
                </div>

                <!-- Carrito -->
                <button onclick="toggleCartDrawer()" class="relative w-9 h-9 md:w-10 md:h-10 rounded-full bg-[#1B263B] border border-[#1B263B] flex items-center justify-center text-white hover:bg-[#3A86FF] transition-all shadow-md shadow-[#1B263B]/30">
                    <i class="fa-solid fa-bag-shopping text-[13px] md:text-sm"></i>
                    <span id="cart-badge" class="absolute -top-1 -right-1 bg-slate-900 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center shadow-md transition-transform duration-200 hidden">0</span>
                </button>
            </div>
        </div>
    </nav>

    <header class="pt-32 pb-10 px-6 max-w-[1000px] mx-auto text-center">
        <h1 id="titulo-principal" class="text-3xl md:text-4xl lg:text-5xl font-black tracking-tight text-slate-900 mb-4 leading-tight fade-in">
            Herramientas profesionales <br class="hidden md:block"> para <span class="laser-text">tu máximo nivel.</span>
        </h1>
        <p id="subtitulo-principal" class="text-slate-500 font-medium text-sm md:text-base max-w-2xl mx-auto fade-in">Explora nuestro catálogo técnico especializado o consulta a nuestro Asesor IA.</p>
    </header>

    <main class="max-w-[1200px] mx-auto px-6 relative z-10">
        <div id="sticky-cat-bar" class="mb-4 sticky top-[72px] z-30 bg-[#f8fafc]/95 backdrop-blur-md pt-2 pb-2 nav-transition space-y-3">
            <div id="category-pills" class="hidden md:flex gap-2 overflow-x-auto w-full scrollbar-hide draggable-container"></div>
            
            <!-- SECCIÓN DE MARCAS (SOLO MÓVIL AQUÍ) -->

            <div class="md:hidden flex flex-col gap-2 w-full px-2">
                <button onclick="toggleBottomSheet()" class="w-full bg-white border border-slate-200 shadow-sm rounded-xl py-3 px-5 flex justify-between items-center text-[13px] font-bold text-slate-700 transition-all active:scale-95">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-layer-group text-[#1B263B] text-base"></i> <span id="mobile-cat-label">Todos</span></span><i class="fa-solid fa-chevron-down text-slate-400"></i>
                </button>
                <!-- Filtro móvil de marcas -->
                <div id="mobile-brand-pills" class="flex gap-2 overflow-x-auto scrollbar-hide pb-2"></div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-8 items-start">
            <!-- SIDEBAR (ESCRITORIO) -->
            <aside class="hidden md:block w-[240px] flex-shrink-0 sticky top-[140px] h-fit space-y-8">
                <!-- MARCAS -->
                <div id="sidebar-brand-container" class="fade-in">
                    <span class="text-[11px] font-black text-[#1B263B] uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1B263B]"></span> Marcas
                    </span>
                    <div id="sidebar-brand-pills" class="flex flex-wrap gap-2"></div>
                </div>

                <!-- SUCURSALES (DINÁMICO) -->
                <div id="sidebar-location-container" class="fade-in hidden">
                    <span class="text-[11px] font-black text-[#1B263B] uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1B263B]"></span> Sucursal Cercana
                    </span>
                    <div id="nearest-location-widget"></div>
                    <button onclick="abrirModalLocales()" class="w-full mt-4 text-[11px] font-bold text-slate-400 hover:text-[#3A86FF] transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-map-location-dot"></i> Ver todos los locales
                    </button>
                </div>

                <!-- ASESORÍA TÉCNICA -->
                <div class="bg-blue-50/50 border border-blue-100 rounded-[2rem] p-6 shadow-sm shadow-blue-900/5 fade-in">
                    <h3 class="text-[11px] font-black text-[#1B263B] uppercase tracking-widest mb-4">Asesoría Técnica</h3>
                    <p class="text-[13px] text-slate-500 leading-relaxed mb-6 font-medium">¿Necesitas ayuda con las cantidades para tu obra?</p>
                    <a href="https://wa.me/593991754887" target="_blank" class="text-[11px] font-black text-[#1B263B] hover:text-[#3A86FF] uppercase tracking-widest flex items-center gap-2 group transition-colors">
                        Contactar Soporte <i class="fa-solid fa-arrow-right-long mt-0.5 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </aside>

            <!-- GRID DE PRODUCTOS -->
            <div id="grid-productos" class="flex-grow grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <article class="glass-card min-h-[320px]"><div class="w-full aspect-square rounded-xl skeleton-box mb-4"></div><div class="h-3 skeleton-box rounded w-3/4 mb-3"></div><div class="h-2 skeleton-box rounded w-full mb-1"></div><div class="h-2 skeleton-box rounded w-5/6 mb-4 flex-grow"></div><div class="h-9 skeleton-box rounded-lg w-full mt-auto"></div></article>
            </div>
        </div>
    </main>

    <!-- BURBUJA GLOBAL DE IA -->
    <div id="ai-bubble" class="ai-bubble">
        <div class="ai-bubble-header">
            <div class="ai-bubble-title"><i class="fa-solid fa-wand-magic-sparkles"></i> Asistente de IMPROGYP</div>
            <button onclick="cerrarBurbujaIA()" class="ai-bubble-close"><i class="fa-solid fa-xmark text-base"></i></button>
        </div>
        <div id="ai-bubble-text" class="ai-bubble-text">Analizando...</div>
    </div>

    <!-- OMNIBAR VERSION MOVIL -->
    <div class="omni-bar-container md:hidden">
        <div class="glass-panel omni-input-wrapper">
            <i class="fa-solid fa-robot text-[#1B263B] text-lg"></i>
            <input type="text" class="omni-input omni-input-field" placeholder="Busca producto o pregúntale a la IA" oninput="filtrarPorTexto(this.value)" onkeypress="if(event.key === 'Enter') buscarConIA(null, this)">
            <button class="btn-send" onclick="buscarConIA(null, this.previousElementSibling)"><i class="btn-send-icon fa-solid fa-paper-plane text-sm"></i></button>
        </div>
    </div>

    <div class="modal-overlay" id="product-modal" onclick="cerrarModalProducto(event)">
        <div class="product-modal-content flex flex-col md:flex-row gap-0 md:gap-6 p-5 relative bg-white w-full max-w-3xl mx-4 rounded-2xl shadow-2xl custom-scrollbar" onclick="event.stopPropagation()">
            <button class="absolute top-3 right-3 md:top-4 md:right-4 text-slate-400 hover:text-rose-500 text-xl z-20 w-8 h-8 flex items-center justify-center bg-slate-100 md:bg-transparent rounded-full transition-colors" onclick="cerrarModalProducto()">&times;</button>
            <div class="w-full md:w-5/12 bg-[#f8fafc] rounded-xl p-4 flex justify-center items-center relative mb-4 md:mb-0 border border-slate-100 flex-shrink-0">
                <span id="modal-cat" class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-[#1B263B] text-[9px] font-bold px-2.5 py-1 rounded uppercase tracking-wider shadow-sm border border-[#1B263B]/20 z-10"></span>
                <img id="modal-img" src="" class="w-full max-h-48 sm:max-h-56 md:max-h-64 object-contain mix-blend-multiply" onerror="this.onerror=null; this.src='favicon-app.png?v=5';">
            </div>
            <div class="w-full md:w-7/12 flex flex-col pt-1">
                <h2 id="modal-title" class="text-xl md:text-2xl font-black text-slate-800 mb-1 leading-tight"></h2>
                <div id="modal-brand-sku" class="flex items-center gap-2 mb-4">
                    <span id="modal-marca-label" class="brand-label !mb-0"></span>
                    <span id="modal-sku-label" class="sku-label !mt-0"></span>
                </div>
                <div class="custom-scrollbar overflow-y-auto pr-3 mb-4" style="max-height: 18vh;"><p id="modal-desc" class="text-[13px] text-slate-500 leading-relaxed whitespace-pre-line"></p></div>
                <div class="mb-4 flex-shrink-0"><p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2.5">Presentaciones disponibles</p><div id="modal-presentations" class="flex flex-wrap gap-2"></div></div>
                <div class="flex items-end justify-between mt-auto pt-4 border-t border-slate-100 flex-shrink-0">
                    <div><p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-1">Precio</p><span id="modal-price" class="text-2xl font-black text-slate-800"></span></div>
                    <div class="flex gap-2">
                         <button id="modal-btn-share" class="w-10 h-10 rounded-lg flex items-center justify-center text-lg transition-colors border border-slate-200 shadow-sm text-slate-400 hover:text-[#1B263B] hover:border-[#1B263B]/30" title="Compartir producto"><i class="fa-solid fa-share-nodes"></i></button>
                         <button id="modal-btn-wishlist" class="w-10 h-10 rounded-lg flex items-center justify-center text-lg transition-colors border border-slate-200 shadow-sm"></button>
                         <div id="modal-btn-add-wrapper" class="flex-shrink-0"><button id="modal-btn-add" class="btn-IMPROGYP px-4 h-10 text-[13px] w-28"><i class="fa-solid fa-cart-plus"></i> <span class="ml-1">Añadir</span></button></div>
                    </div>
                </div>
                
                <!-- UPSELLING / RELACIONADOS -->
                <div id="modal-related-container"></div>
            </div>
        </div>
    </div>

    <div class="bottom-sheet-overlay" id="category-bottom-sheet" onclick="toggleBottomSheet()">
        <div class="bottom-sheet" id="bs-panel" onclick="event.stopPropagation()">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-6 cursor-grab" id="bs-handle"></div>
            
            <div class="flex-grow overflow-y-auto custom-scrollbar pr-1">
                <h3 class="text-base font-black text-slate-800 mb-4 px-1 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-slate-400 text-sm"></i> Categorías
                </h3>
                <div id="mobile-category-list" class="flex flex-col gap-2 pb-4 px-1"></div>

                <div id="mobile-brands-section" class="mt-8 border-t border-slate-100 pt-6 px-1">
                    <h3 class="text-base font-black text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-tags text-slate-400 text-sm"></i> Filtrar por Marca
                    </h3>
                    <div id="mobile-brands-list" class="flex flex-wrap gap-2 pb-10"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="cart-drawer-overlay" id="cart-drawer-overlay" onclick="toggleCartDrawer()">
        <div class="cart-drawer" onclick="event.stopPropagation()">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white shadow-sm z-10"><h3 class="font-black text-base text-slate-800"><i class="fa-solid fa-box-open text-[#1B263B] mr-2"></i> Tu Bolsa de Compras</h3><button onclick="toggleCartDrawer()" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:text-rose-500 flex items-center justify-center transition-colors"><i class="fa-solid fa-xmark text-xs"></i></button></div>
            <div id="cart-items-container" class="flex-grow overflow-y-auto custom-scrollbar bg-slate-50/50 p-2"></div>
            <div class="p-4 bg-white border-t border-slate-200 shadow-[0_-10px_20px_rgba(0,0,0,0.02)]">
                <div class="flex justify-between items-center mb-3"><span class="text-[13px] font-bold text-slate-500">Total Estimado</span><span class="text-xl font-black text-slate-800" id="cart-subtotal">$0.00</span></div>
                <button onclick="enviarPedidoWhatsApp()" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 hover:bg-[#1B263B] transition-colors shadow-lg text-[14px]"><i class="fa-brands fa-whatsapp text-lg"></i> Comprar por WhatsApp</button>
            </div>
        </div>
    </div>
    
    <!-- MODAL DE LOCALES -->
    <div class="modal-overlay" id="locations-modal" onclick="cerrarModalLocales(event)">
        <div class="product-modal-content flex flex-col px-3 py-5 md:p-6 relative bg-white w-full max-w-4xl mx-2 md:mx-4 rounded-2xl md:rounded-3xl shadow-2xl overflow-hidden" onclick="event.stopPropagation()" style="max-height: 90vh;">
            <button class="absolute top-3 right-3 md:top-4 md:right-4 text-slate-400 hover:text-rose-500 text-lg md:text-xl z-20 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-slate-50 rounded-full transition-colors" onclick="cerrarModalLocales()">&times;</button>
            
            <div class="mb-6 md:mb-8">
                <span class="inline-block bg-[#1B263B]/10 text-[#1B263B] text-[9px] md:text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest mb-2 border border-[#1B263B]/20">Red de Suministros</span>
                <h2 class="text-xl md:text-3xl font-black text-slate-900 tracking-tight">Nuestras Sucursales</h2>
                <p class="text-slate-500 text-[13px] md:text-sm mt-1">Encuentra el punto IMPROGYP más cercano a tu obra.</p>
            </div>

            <div class="flex-grow overflow-y-auto custom-scrollbar pr-2">
                <div id="modal-nearest-location" class="mb-6 md:mb-8 hidden"></div>
                <div class="mb-4 md:mb-5">
                   <p class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Listado Completo</p>
                </div>
                <div id="locations-grid" class="modal-location-grid pb-6"></div>
            </div>
        </div>
    </div>

    <script>
        let catalogoCompleto = [];
        let adData = {};
        let textosMarketing = {};
        let listaLocales = [];
        let carrito = [];
        let wishlist = [];
        let datosRanking = { impulsados: [], tendencias: [] };
        let marcaActiva = 'Todas';
        let ultimosProductosRenderizados = [];

        try { carrito = JSON.parse(localStorage.getItem('improgyp_carrito')) || []; } catch(e) { console.error("Error cargando carrito"); }
        try { wishlist = JSON.parse(localStorage.getItem('improgyp_wishlist')) || []; } catch(e) { console.error("Error cargando wishlist"); }

        function getAbsoluteImgUrl(ruta) {
    // 1. Si no hay ruta, mostrar el favicon por defecto
    if (!ruta) return 'favicon-app.png?v=5';
    
    // 2. Si la ruta ya es absoluta (http...), no tocar nada
    if (ruta.startsWith('http')) return ruta;
    
    // 3. Limpiar la ruta de ./ o / al inicio
    let rutaLimpia = ruta.replace(/^\.\//, '').replace(/^\//, ''); 
    
    // 4. Retornar la unión (si IMPROGYP_BASE_URL es '', solo queda la ruta limpia)
    return IMPROGYP_BASE_URL + rutaLimpia;
}
        // =====================================================================

        let lastScrollY = window.scrollY;
        const navBar = document.getElementById('main-nav');
        const stickyBar = document.getElementById('sticky-cat-bar');
        
        let tickingScroll = false;
        window.addEventListener('scroll', () => {
            if (!tickingScroll) {
                window.requestAnimationFrame(() => {
                    if (window.scrollY > 100) {
                        if (window.scrollY > lastScrollY) {
                            navBar.classList.add('nav-hidden');
                            if(stickyBar) { stickyBar.classList.remove('top-[72px]'); stickyBar.classList.add('cat-bar-up'); }
                        } else {
                            navBar.classList.remove('nav-hidden');
                            if(stickyBar) { stickyBar.classList.remove('cat-bar-up'); stickyBar.classList.add('top-[72px]'); }
                        }
                    } else {
                        navBar.classList.remove('nav-hidden');
                        if(stickyBar) { stickyBar.classList.remove('cat-bar-up'); stickyBar.classList.add('top-[72px]'); }
                    }
                    lastScrollY = window.scrollY;
                    tickingScroll = false;
                });
                tickingScroll = true;
            }
        }, { passive: true });

        function radarNinja(evento, valor, categoria = 'General') {
            if (navigator.sendBeacon) {
                navigator.sendBeacon('api_metricas.php', JSON.stringify({e: evento, v: valor, c: categoria}));
            }
        }

        function getSafeId(str) { return 'item-' + str.replace(/[^a-zA-Z0-9]/g, '-'); }

        document.addEventListener('DOMContentLoaded', async () => {
            try {
                // 1. CARGA PRIORITARIA: Catálogo primero para mostrar productos de inmediato
                const catRes = await fetch('catalogo.json?v=' + Date.now()).then(r => r.ok ? r.json() : []).catch(() => []);
                catalogoCompleto = catRes;
                
                if (Array.isArray(catalogoCompleto) && catalogoCompleto.length > 0) {
                    const catMemoria = localStorage.getItem('improgyp_ai_cat') || localStorage.getItem('improgyp_memoria_cat') || 'Todos';
                    filtrarCategoria(catMemoria); // RENDERIZADO INICIAL RÁPIDO
                }

                // 2. CARGA DE LOCALES
                initLocales();

                // 3. CARGA DE APOYO: Rankings, Textos y Ads en segundo plano (sin bloquear)
                Promise.all([
                    fetch('api_ranking.php?v=' + Date.now()).then(r => r.ok ? r.json() : {}).catch(() => ({})),
                    fetch('ads.json?v=' + Date.now()).then(r => r.ok ? r.json() : {}).catch(() => ({})),
                    fetch('textos_tienda.json?v=' + Date.now()).then(r => r.ok ? r.json() : {}).catch(() => ({}))
                ]).then(([rankRes, adsRes, textosRes]) => {
                    adData = adsRes;
                    textosMarketing = textosRes;
                    if (rankRes && typeof rankRes === 'object') {
                        datosRanking = {
                            impulsados: Array.isArray(rankRes.impulsados) ? rankRes.impulsados : [],
                            tendencias: Array.isArray(rankRes.tendencias) ? rankRes.tendencias : []
                        };
                    }
                    
                    // Actualizar nombres de categorías y marcas si es necesario
                    inicializarCategoriasYMarcas();
                    
                    // Re-renderizar si ya hay rankings o ads para añadir badges/pautas
                    if (datosRanking.impulsados.length > 0 || Object.keys(adData).length > 0) {
                        const currentCat = document.querySelector('.cat-pill.active')?.dataset.cat || 'Todos';
                        filtrarCategoria(currentCat);
                    }
                });

                // Funcionalidades secundarias
                try { actualizarUIWishlist(); } catch(e) {}
                try { actualizarUICarrito(); } catch(e) {}
                
                // Deep Linking
                const urlParams = new URLSearchParams(window.location.search);
                const prodName = urlParams.get('p');
                if (prodName) {
                    setTimeout(() => {
                        const existe = catalogoCompleto.find(p => p.nombre === prodName || p.codigo === prodName);
                        if (existe) abrirModalProducto(prodName);
                    }, 500); 
                }
                // Habilitar Draggable en Escritorio
                makeDraggable('category-pills');
            } catch(e) { 
                console.error("Error global de inicialización:", e);
                const grid = document.getElementById('grid-productos');
                if (grid) grid.innerHTML = `<div class="col-span-full text-center py-10 bg-rose-50 text-rose-600 font-bold">Error de sincronización. Por favor recarga la página.</div>`; 
            }
        });

        function inicializarCategoriasYMarcas() {
            const categoriasUnicas = [...new Set(catalogoCompleto.map(item => item.categoria))].filter(Boolean);
            const pillsContainer = document.getElementById('category-pills'); 
            const sheetContainer = document.getElementById('mobile-category-list');
            const catMemoria = localStorage.getItem('improgyp_ai_cat') || localStorage.getItem('improgyp_memoria_cat') || 'Todos';

            let pillsHTML = `<button data-cat="Todos" class="cat-pill ${catMemoria==='Todos'?'active':''} px-4 py-1.5 rounded-full text-[13px] font-bold cursor-pointer transition-all border border-transparent bg-white text-slate-500 hover:border-[#1B263B] hover:text-[#1B263B] whitespace-nowrap" onclick="filtrarCategoria('Todos')">Todos</button>`;
            let sheetHTML = `<button data-cat="Todos" class="bs-cat-btn ${catMemoria==='Todos'?'active':''}" onclick="filtrarCategoria('Todos')"><span>Todos los productos</span><i class="fa-solid fa-check bs-icon"></i></button>`;

            categoriasUnicas.forEach(cat => {
                const isActive = cat === catMemoria;
                pillsHTML += `<button data-cat="${cat}" class="cat-pill ${isActive?'active':''} px-4 py-1.5 rounded-full text-[13px] font-bold cursor-pointer transition-all border border-slate-200 bg-white text-slate-500 hover:border-[#1B263B] hover:text-[#1B263B] whitespace-nowrap" onclick="filtrarCategoria('${cat}')">${cat}</button>`;
                sheetHTML += `<button data-cat="${cat}" class="bs-cat-btn ${isActive?'active':''}" onclick="filtrarCategoria('${cat}')"><span>${cat}</span><i class="fa-solid fa-check bs-icon"></i></button>`;
            });
            
            if(pillsContainer) pillsContainer.innerHTML = pillsHTML; 
            if(sheetContainer) sheetContainer.innerHTML = sheetHTML;

            const marcasUnicas = [...new Set(catalogoCompleto.map(item => item.marca))].filter(Boolean).sort();
            const sidebarBrandPillsContainer = document.getElementById('sidebar-brand-pills');
            const mobileBrandPillsContainer = document.getElementById('mobile-brands-list');
            
            if (marcasUnicas.length > 0) {
                let brandHTML = `<button onclick="filtrarMarca('Todas')" class="brand-pill active" data-brand="Todas">Todas</button>`;
                marcasUnicas.forEach(m => { brandHTML += `<button onclick="filtrarMarca('${m}')" class="brand-pill" data-brand="${m}">${m}</button>`; });
                if(sidebarBrandPillsContainer) sidebarBrandPillsContainer.innerHTML = brandHTML;
                if(mobileBrandPillsContainer) mobileBrandPillsContainer.innerHTML = brandHTML;
            }
        }

        /* LÓGICA DE LOCALES Y GEOLOCALIZACIÓN */
        async function initLocales() {
            try {
                const res = await fetch('locales.json?v=' + Date.now());
                listaLocales = await res.json();
                
                // 1. Intentar obtener ubicación del navegador (Precisión)
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            renderizarLocalesCercanos(pos.coords.latitude, pos.coords.longitude);
                        },
                        () => {
                            // Fallback: Si el usuario deniega, usar ciudad por IP si es posible
                            renderizarLocalesCercanos(null, null);
                        }
                    );
                } else {
                    renderizarLocalesCercanos(null, null);
                }
            } catch(e) { console.error("Error cargando locales:", e); }
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radio de la Tierra en km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        function renderizarLocalesCercanos(userLat, userLng) {
            if (!listaLocales.length) return;

            let localesProcesados = [...listaLocales];

            if (userLat && userLng) {
                localesProcesados.forEach(l => {
                    l.distancia = calculateDistance(userLat, userLng, l.lat, l.lng);
                });
                localesProcesados.sort((a, b) => a.distancia - b.distancia);
            }

            const masCercano = localesProcesados[0];
            const widget = document.getElementById('nearest-location-widget');
            const container = document.getElementById('sidebar-location-container');
            const modalNearest = document.getElementById('modal-nearest-location');

            if (masCercano) {
                const distText = masCercano.distancia ? `<span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full ml-auto">A ${masCercano.distancia.toFixed(1)} km</span>` : '';
                const cardHTML = `
                    <div class="location-card border-[#1B263B]/20" onclick="abrirModalLocales()">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="text-[14px] font-black text-slate-800 leading-tight">${masCercano.nombre}</h4>
                            ${distText}
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed mb-4">${masCercano.direccion}</p>
                        <div class="flex gap-2">
                            <a href="${masCercano.maps}" target="_blank" class="btn-location-action w-full" onclick="event.stopPropagation()"><i class="fa-solid fa-location-dot"></i> Ver en Google Maps</a>
                        </div>
                    </div>
                `;
                
                if (widget) widget.innerHTML = cardHTML;
                if (container) container.classList.remove('hidden');

                if (modalNearest && masCercano.distancia) {
                    modalNearest.innerHTML = `
                         <div class="bg-emerald-50/50 border border-emerald-100 rounded-3xl p-5 mb-2">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-emerald-500 text-white text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest shadow-sm shadow-emerald-500/20">Cercano</span>
                                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Tu mejor opción ahora mismo</p>
                            </div>
                            ${cardHTML}
                         </div>
                    `;
                    modalNearest.classList.remove('hidden');
                }
            }

            // Renderizar Grid del Modal
            const grid = document.getElementById('locations-grid');
            if (grid) {
                grid.innerHTML = localesProcesados.map(l => {
                    const waLink = `https://wa.me/${l.whatsapp}${l.whatsapp_msj ? '?text=' + encodeURIComponent(l.whatsapp_msj) : ''}`;
                    return `
                    <div class="location-card group">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="location-dot"></span>
                            <h4 class="text-[15px] font-black text-slate-900">${l.nombre}</h4>
                        </div>
                        <p class="text-[12px] text-slate-500 mb-4 h-10 overflow-hidden leading-relaxed">${l.direccion}</p>
                        
                        <div class="space-y-2 mb-5">
                            <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium">
                                <i class="fa-solid fa-phone text-[#1B263B]/40 w-4"></i> ${l.telefono}
                            </div>
                            <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium">
                                <i class="fa-solid fa-envelope text-[#1B263B]/40 w-4"></i> ${l.email}
                            </div>
                            <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium">
                                <i class="fa-solid fa-clock text-[#1B263B]/40 w-4"></i> ${l.horario || '08:30 - 18:00'}
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <a href="${l.maps}" target="_blank" class="btn-location-action flex-grow hover:bg-[#1B263B] hover:text-white transition-all"><i class="fa-solid fa-location-dot"></i> Cómo llegar</a>
                            <a href="${waLink}" target="_blank" class="bg-emerald-500 text-white px-5 rounded-xl flex items-center justify-center gap-2 hover:bg-emerald-600 transition-colors shadow-lg shadow-emerald-500/20 text-[11px] font-bold">
                                <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                `;}).join('');
            }
        }

        function abrirModalLocales() { document.getElementById('locations-modal').classList.add('show'); document.body.style.overflow = 'hidden'; }
        function cerrarModalLocales(e) { if(!e || e.target.classList.contains('modal-overlay') || e.target.innerHTML === '&times;') { document.getElementById('locations-modal').classList.remove('show'); document.body.style.overflow = 'auto'; } }

        const bsPanel = document.getElementById('bs-panel'); const bsOverlay = document.getElementById('category-bottom-sheet');
        let startY = 0, currentY = 0, deltaY = 0, isDragging = false;
        bsPanel.addEventListener('touchstart', (e) => { const scrollArea = e.target.closest('#mobile-category-list'); if(scrollArea && scrollArea.scrollTop > 0) return; startY = e.touches[0].clientY; isDragging = true; bsPanel.style.transition = 'none'; }, {passive: true});
        bsPanel.addEventListener('touchmove', (e) => { if(!isDragging) return; currentY = e.touches[0].clientY; deltaY = currentY - startY; if(deltaY > 0) { bsPanel.style.transform = `translateY(${deltaY}px)`; } }, {passive: true});
        bsPanel.addEventListener('touchend', (e) => { if(!isDragging) return; isDragging = false; bsPanel.style.transition = 'transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)'; if(deltaY > 80) { toggleBottomSheet(); setTimeout(() => bsPanel.style.transform = '', 300); } else { bsPanel.style.transform = ''; } deltaY = 0; });
        function toggleBottomSheet() { bsOverlay.classList.toggle('show'); document.body.style.overflow = bsOverlay.classList.contains('show') ? 'hidden' : 'auto'; }
        function toggleCartDrawer() { const drawer = document.getElementById('cart-drawer-overlay'); drawer.classList.toggle('show'); document.body.style.overflow = drawer.classList.contains('show') ? 'hidden' : 'auto'; if(drawer.classList.contains('show')) actualizarUICarrito(); }
        
        function makeDraggable(containerId) {
            const slider = document.getElementById(containerId);
            if (!slider) return;

            let isDown = false;
            let startX;
            let scrollLeft;

            slider.addEventListener('mousedown', (e) => {
                isDown = true;
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });

            slider.addEventListener('mouseleave', () => { isDown = false; });
            slider.addEventListener('mouseup', () => { isDown = false; });

            slider.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 2; 
                slider.scrollLeft = scrollLeft - walk;
            });
        }

        function actualizarBotonesGrid() {
            // OPTIMIZACIÓN: Solo iterar sobre los productos que están actualmente en el grid
            const targetList = ultimosProductosRenderizados.length > 0 ? ultimosProductosRenderizados : catalogoCompleto;
            targetList.forEach(prod => {
                const actionContainer = document.getElementById(getSafeId(prod.nombre)); if(!actionContainer) return; 
                const itemEnCarrito = carrito.find(c => (c.codigo && c.codigo === prod.codigo) || c.nombre === prod.nombre); 
                const safeIdentificador = (prod.codigo || prod.nombre).replace(/'/g, "\\'").replace(/"/g, "&quot;");
                if(itemEnCarrito) {
                    actionContainer.innerHTML = `<div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl p-1 h-10 md:h-9 mt-auto shadow-inner" onclick="event.stopPropagation()"><button onclick="modificarCantidad('${safeIdentificador}', -1)" class="w-9 md:w-8 h-full rounded-lg bg-white text-slate-500 shadow-sm hover:text-rose-500 font-black text-base active:scale-95">-</button><span class="font-black text-[13px] text-slate-800 w-6 text-center select-none">${itemEnCarrito.cantidad}</span><button onclick="modificarCantidad('${safeIdentificador}', 1)" class="w-9 md:w-8 h-full rounded-lg bg-white text-slate-500 shadow-sm hover:text-[#1B263B] font-black text-base active:scale-95">+</button></div>`;
                } else { actionContainer.innerHTML = `<button class="btn-IMPROGYP w-full text-[13px] md:text-[12px] py-2 h-10 md:h-9 mt-auto" onclick="agregarAlCarrito('${safeIdentificador}'); event.stopPropagation();"><i class="fa-solid fa-cart-plus"></i> <span class="ml-1">Añadir</span></button>`; }
            });
            const modalTitleEl = document.getElementById('modal-title');
            const modalSkuEl = document.getElementById('modal-sku-label');
            const modalSkuText = (modalSkuEl && modalSkuEl.innerText) ? modalSkuEl.innerText.replace('REF: ', '').trim() : null;
            const modalTitle = (modalTitleEl && modalTitleEl.innerText) ? modalTitleEl.innerText : null;
            
            if(modalTitle || modalSkuText) {
                const identificadorModal = modalSkuText || modalTitle;
                const itemModal = carrito.find(c => (c.codigo && c.codigo === identificadorModal) || c.nombre === identificadorModal);
                const btnModalWrapper = document.getElementById('modal-btn-add-wrapper'); 
                const safeIdentificadorModal = identificadorModal.replace(/'/g, "\\'");
                if(btnModalWrapper) {
                    if(itemModal) { btnModalWrapper.innerHTML = `<div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl p-1 h-10 w-28 shadow-inner"><button onclick="modificarCantidad('${safeIdentificadorModal}', -1)" class="w-8 h-full rounded-lg bg-white text-slate-500 shadow-sm hover:text-rose-500 font-black text-base active:scale-95">-</button><span class="font-black text-[14px] text-slate-800 flex-grow text-center select-none">${itemModal.cantidad}</span><button onclick="modificarCantidad('${safeIdentificadorModal}', 1)" class="w-8 h-full rounded-lg bg-white text-slate-500 shadow-sm hover:text-[#1B263B] font-black text-base active:scale-95">+</button></div>`;
                    } else { btnModalWrapper.innerHTML = `<button class="btn-IMPROGYP px-4 h-10 text-[13px] w-28" onclick="agregarAlCarrito('${safeIdentificadorModal}')"><i class="fa-solid fa-cart-plus"></i> <span class="ml-1">Añadir</span></button>`; }
                }
            }
        }

        function agregarAlCarrito(identificador) {
            const prodIndex = carrito.findIndex(c => (c.codigo && c.codigo === identificador) || c.nombre === identificador);
            if (prodIndex > -1) { carrito[prodIndex].cantidad += 1; } 
            else {
                const prodReal = catalogoCompleto.find(p => p.codigo === identificador || p.nombre === identificador);
                if (prodReal) { 
                    let precioBase = "0.00"; 
                    if(prodReal.presentaciones && prodReal.presentaciones.length > 0) { 
                        precioBase = prodReal.presentaciones[0].precio.split('|')[0].trim().replace(/[^0-9.]/g, ''); 
                    }
                    carrito.push({ 
                        nombre: prodReal.nombre, 
                        codigo: prodReal.codigo || '',
                        imagen: prodReal.imagen, 
                        precioNum: parseFloat(precioBase) || 0, 
                        cantidad: 1 
                    }); 
                    radarNinja('Añadir a Carrito', prodReal.nombre, prodReal.categoria); 
                }
            }
            localStorage.setItem('improgyp_carrito', JSON.stringify(carrito)); actualizarUICarrito();
            const badge = document.getElementById('cart-badge'); badge.parentElement.classList.add('scale-110', 'border-slate-900'); setTimeout(() => { badge.parentElement.classList.remove('scale-110', 'border-slate-900'); }, 200);
            if (navigator.vibrate) navigator.vibrate(50);
        }

        function modificarCantidad(identificador, delta) { 
            const prodIndex = carrito.findIndex(c => (c.codigo && c.codigo === identificador) || c.nombre === identificador); 
            if (prodIndex > -1) { 
                carrito[prodIndex].cantidad += delta; 
                if(carrito[prodIndex].cantidad <= 0) carrito.splice(prodIndex, 1); 
                localStorage.setItem('improgyp_carrito', JSON.stringify(carrito)); 
                actualizarUICarrito(); 
            } 
        }
        function eliminarDelCarrito(identificador) { 
            const prodIndex = carrito.findIndex(c => (c.codigo && c.codigo === identificador) || c.nombre === identificador); 
            if (prodIndex > -1) { 
                carrito.splice(prodIndex, 1); 
                localStorage.setItem('improgyp_carrito', JSON.stringify(carrito)); 
                actualizarUICarrito(); 
            } 
        }

        function actualizarUICarrito() {
            const container = document.getElementById('cart-items-container'); const badge = document.getElementById('cart-badge'); const totalElement = document.getElementById('cart-subtotal');
            let html = ''; let totalQty = 0; let subtotal = 0;
            if(carrito.length === 0) { badge.classList.add('hidden'); container.innerHTML = `<div class="h-full flex flex-col items-center justify-center p-6 text-center text-slate-400"><i class="fa-solid fa-box-open text-4xl mb-3 text-slate-200"></i><p class="text-[13px] font-medium">Tu bolsa de compras está vacía.</p></div>`; totalElement.innerText = "$0.00"; } 
            else { badge.classList.remove('hidden');
                [...carrito].reverse().forEach(item => {
                    totalQty += item.cantidad; subtotal += (item.precioNum * item.cantidad); 
                    
                    // --- IMPLEMENTACIÓN DE DOMINIO ABSOLUTO ---
                    const imgUrl = getAbsoluteImgUrl(item.imagen); 
                    const safeIdentificador = (item.codigo || item.nombre).replace(/'/g, "\\'").replace(/"/g, "&quot;");
                    html += `
                    <div class="cart-item bg-white rounded-xl mb-2 mx-2 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                        <img src="${imgUrl}" class="cart-item-img" onerror="this.onerror=null; this.src='favicon-app.png?v=5';" onclick="abrirModalProducto('${safeIdentificador}'); toggleCartDrawer();">
                        <div class="flex-grow min-w-0 pr-2">
                            <h4 class="text-[11px] font-bold text-slate-800 leading-tight mb-0.5 truncate">${item.nombre}</h4>
                            <div class="text-[9px] text-slate-400 font-bold mb-1">${item.codigo ? 'REF: ' + item.codigo : ''}</div>
                            <div class="text-[11px] text-[#1B263B] font-black">$${item.precioNum.toFixed(2)}</div>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <div class="flex items-center gap-1 bg-slate-50 border border-slate-100 rounded-md p-1">
                                <button class="cart-qty-btn h-6 w-6" onclick="modificarCantidad('${safeIdentificador}', -1)"><i class="fa-solid fa-minus text-[9px]"></i></button>
                                <span class="text-[11px] font-black text-slate-700 w-3 text-center">${item.cantidad}</span>
                                <button class="cart-qty-btn h-6 w-6" onclick="modificarCantidad('${safeIdentificador}', 1)"><i class="fa-solid fa-plus text-[9px]"></i></button>
                            </div>
                            <button class="w-7 h-7 rounded-md text-slate-400 hover:text-rose-500 hover:bg-rose-50 flex items-center justify-center transition-colors ml-1" onclick="eliminarDelCarrito('${safeIdentificador}')" title="Eliminar"><i class="fa-solid fa-trash-can text-[11px]"></i></button>
                        </div>
                    </div>`;
                }); badge.innerText = totalQty; container.innerHTML = html; totalElement.innerText = `$${subtotal.toFixed(2)}`;
            } actualizarBotonesGrid();
        }

        async function enviarPedidoWhatsApp() {
            if(carrito.length === 0) return alert("Agrega herramientas para iniciar la compra.");
            let texto = "Hola IMPROGYP, deseo realizar el siguiente pedido:\n\n"; let subtotal = 0;
            const itemsSimplificados = carrito.map(item => {
                subtotal += (item.cantidad * item.precioNum);
                texto += `🔹 ${item.cantidad}x ${item.nombre} ($${item.precioNum.toFixed(2)})\n`;
                return { nombre: item.nombre, cantidad: item.cantidad, precio: item.precioNum };
            });
            texto += `\n*TOTAL ESTIMADO:* $${subtotal.toFixed(2)}\n_Solicito asistencia para coordinar el pago y envío._`;
            const catPrincipal = carrito.length > 0 ? carrito[0].categoria : 'General';
            
            // Registro silencioso del pedido en la base de datos
            try {
                const source = window.lastSearchSource || 'directo';
                fetch('api_pedido_publico.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        total: subtotal,
                        items: itemsSimplificados,
                        source: source
                    })
                });
            } catch(e) { console.error("Error al registrar métrica de pedido:", e); }

            radarNinja('Checkout Iniciado', `$${subtotal.toFixed(2)}`, catPrincipal);
            const url = `https://wa.me/593991754887?text=${encodeURIComponent(texto)}`;
            window.open(url, '_blank');
        }

        function compartirTienda() {
            const data = {
                title: 'IMPROGYP | E-commerce Inteligente',
                text: '¡Mira la mejor selección de herramientas en IMPROGYP! Compra fácil por WhatsApp.',
                url: window.location.origin + window.location.pathname
            };
            if (navigator.share) {
                navigator.share(data).catch(() => {});
            } else {
                navigator.clipboard.writeText(data.url);
                alert("Enlace copiado al portapapeles");
            }
        }

        function compartirProducto(nombre, categoria) {
            const baseUrl = window.location.origin + window.location.pathname;
            const shareUrl = `${baseUrl}?p=${encodeURIComponent(nombre)}`;
            const data = {
                title: `${nombre} | IMPROGYP`,
                text: `¡Mira este herramienta que encontré en IMPROGYP: ${nombre}!`,
                url: shareUrl
            };
            if (navigator.share) {
                navigator.share(data).catch(() => {});
            } else {
                navigator.clipboard.writeText(shareUrl);
                alert("Enlace del producto copiado");
            }
        }

        function filtrarPorTexto(query) {
            const normalize = (str) => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            const q = normalize(query.trim());
            
            // Sincronizar el valor en el otro buscador
            document.querySelectorAll('.omni-input-field').forEach(el => { if(el.value !== query) el.value = query; });
            
            if (q.length === 0) {
                const catActiva = localStorage.getItem('improgyp_memoria_cat') || 'Todos';
                let base = catActiva === 'Todos' ? catalogoCompleto : catalogoCompleto.filter(p => p.categoria === catActiva);
                renderizarGrid(base);
                cerrarBurbujaIA();
                return;
            }

            // Filtrado en vivo insensible a tildes
            const filtrados = catalogoCompleto.filter(p => 
                normalize(p.nombre).includes(q) || 
                normalize(p.categoria).includes(q) ||
                (p.marca && normalize(p.marca).includes(q)) ||
                (p.codigo && normalize(p.codigo).includes(q))
            );
            
            renderizarGrid(filtrados);
            if(q.length > 2) {
                // Sensor Ninja: Búsqueda Live (Throttled)
                if(window.liveSearchTimer) clearTimeout(window.liveSearchTimer);
                window.liveSearchTimer = setTimeout(() => {
                    radarNinja('Búsqueda Live', query, 'Live');
                }, 1500);
            }
            if(q.length > 2) cerrarBurbujaIA(); 
        }

        async function buscarConIA(queryDirecto = null, specificInput = null) {
            const inputField = specificInput || document.querySelector('.omni-input-field'); 
            const mensajeUsuario = queryDirecto || inputField.value.trim(); if(!mensajeUsuario) return;
            window.lastSearchSource = 'IA: ' + mensajeUsuario;
            radarNinja('Búsqueda IA', mensajeUsuario, 'AI'); 
            
            // Sincronizar todos los inputs con el mismo valor (opcional pero recomendado)
            document.querySelectorAll('.omni-input-field').forEach(el => el.value = ''); 

            const iconBtns = document.querySelectorAll('.btn-send-icon'); 
            const bubble = document.getElementById('ai-bubble'); 
            const bubbleText = document.getElementById('ai-bubble-text');
            
            inputField.disabled = true; 
            iconBtns.forEach(btn => btn.className = 'fa-solid fa-circle-notch fa-spin'); 
            bubble.classList.add('show'); 
            bubbleText.innerHTML = '<span class="text-slate-400"><i class="fa-solid fa-brain fa-pulse mr-2"></i>Analizando el catálogo...</span>';
            try {
                const catalogoLigero = catalogoCompleto.map(p => ({ nombre: p.nombre, categoria: p.categoria }));
                const response = await fetch('api_tienda.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ mensaje: mensajeUsuario }) });
                const data = await response.json(); 
                // Sanitizar respuesta de la IA antes de renderizar (Seguridad Fase 1)
                const mensajeLimpio = DOMPurify.sanitize(data.mensaje_voz, { ALLOWED_TAGS: ['b', 'i', 'strong', 'br', 'span'], ALLOWED_ATTR: ['class'] });
                bubbleText.innerHTML = mensajeLimpio;
                if(data.skus_recomendados && data.skus_recomendados.length > 0) {
                    const productosRecomendados = catalogoCompleto.filter(p => data.skus_recomendados.includes(p.nombre));
                    const tituloH1 = document.getElementById('titulo-principal'); tituloH1.classList.remove('fade-in'); void tituloH1.offsetWidth; tituloH1.classList.add('fade-in');
                    tituloH1.innerHTML = `Selección <br class="hidden md:block"> <span class="laser-text">Inteligente.</span>`; document.getElementById('subtitulo-principal').innerHTML = `Resultados recomendados específicamente para ti.`;
                    document.querySelectorAll('.cat-pill').forEach(btn => { btn.classList.remove('active', 'border-transparent'); btn.classList.add('border-slate-200'); }); document.querySelectorAll('.bs-cat-btn').forEach(btn => btn.classList.remove('active')); document.getElementById('mobile-cat-label').innerText = "Selección de la IA";
                    if(productosRecomendados.length > 0) {
                        localStorage.setItem('improgyp_ai_cat', productosRecomendados[0].categoria);
                    }
                    renderizarGrid(productosRecomendados); window.scrollTo({ top: 0, behavior: 'smooth' });
                } else if (data.skus_recomendados && data.skus_recomendados.length === 0) { bubbleText.innerHTML += "<br><span class='text-[11px] text-rose-500 font-bold mt-2 block'>No encontré productos exactos para esto, intenta otra búsqueda.</span>"; }
            } catch(error) { bubbleText.innerHTML = "Hubo una desconexión temporal. Intenta de nuevo."; } finally { iconBtns.forEach(btn => btn.className = (btn.classList.contains('text-[10px]') ? 'fa-solid fa-paper-plane text-[10px]' : 'fa-solid fa-paper-plane text-sm')); inputField.disabled = false; inputField.focus(); }
        }
        function cerrarBurbujaIA() { document.getElementById('ai-bubble').classList.remove('show'); }

        function abrirModalProducto(identificador) {
            const prod = catalogoCompleto.find(p => p.nombre === identificador || p.codigo === identificador); if(!prod) return; radarNinja('Ver Producto', prod.nombre, prod.categoria); 
            
            // SOPORTE PARA BOTÓN ATRÁS (NAV)
            if (!history.state || !history.state.modal) {
                history.pushState({modal: true, productName: identificador}, "");
            }

            document.getElementById('modal-title').innerText = prod.nombre; 
            document.getElementById('modal-cat').innerText = prod.categoria; 
            document.getElementById('modal-marca-label').innerText = prod.marca || 'IMPROGYP';
            document.getElementById('modal-sku-label').innerText = prod.codigo ? `REF: ${prod.codigo}` : '';
            if(!prod.codigo) document.getElementById('modal-sku-label').classList.add('hidden');
            else document.getElementById('modal-sku-label').classList.remove('hidden');
            
            // --- IMPLEMENTACIÓN DE DOMINIO ABSOLUTO ---
            document.getElementById('modal-img').src = getAbsoluteImgUrl(prod.imagen); 
            // ------------------------------------------

            document.getElementById('modal-desc').innerHTML = prod.desc_larga ? prod.desc_larga : "Sin descripción adicional.";
            let presHTML = ''; let precioInicial = "Consultar";
            if(prod.presentaciones && prod.presentaciones.length > 0) {
                prod.presentaciones.forEach((pres, index) => {
                    let precioLimpio = pres.precio.split('|')[0].trim(); if(!precioLimpio) precioLimpio = "Consultar"; if(index === 0) precioInicial = precioLimpio;
                    let activeClass = index === 0 ? 'bg-[#1B263B] text-white border-[#1B263B]' : 'bg-white text-slate-500 border-slate-200 hover:border-[#1B263B] hover:text-[#1B263B]';
                    presHTML += `<button class="px-3 py-1.5 border rounded-lg text-[11px] font-bold transition-colors ${activeClass}" onclick="cambiarPrecioModal('${precioLimpio}', this)">${pres.opcion}</button>`;
                });
            } else { presHTML = `<span class="text-[11px] text-slate-400 italic">Presentación única</span>`; }
            document.getElementById('modal-presentations').innerHTML = presHTML; 
            document.getElementById('modal-price').innerText = (precioInicial !== "Consultar" && !precioInicial.toString().includes('$')) ? `$${precioInicial}` : precioInicial;
            const safeName = prod.nombre.replace(/'/g, "\\'").replace(/"/g, "&quot;"); const btnWishlist = document.getElementById('modal-btn-wishlist'); const enWishlist = wishlist.some(w => w.nombre === prod.nombre);
            if(enWishlist) { btnWishlist.className = 'w-10 h-10 rounded-lg flex items-center justify-center text-lg transition-colors border border-slate-200 shadow-sm text-rose-500 bg-rose-50'; btnWishlist.innerHTML = '<i class="fa-solid fa-heart"></i>'; } 
            else { btnWishlist.className = 'w-10 h-10 rounded-lg flex items-center justify-center text-lg transition-colors border border-slate-200 shadow-sm text-slate-400 bg-white hover:text-rose-400 hover:border-rose-200'; btnWishlist.innerHTML = '<i class="fa-regular fa-heart"></i>'; }
            btnWishlist.setAttribute('onclick', `toggleWishlist('${safeName}', null, true)`); 

            const btnShare = document.getElementById('modal-btn-share');
            const safeCategory = prod.categoria.replace(/'/g, "\\'").replace(/"/g, "&quot;");
            btnShare.setAttribute('onclick', `compartirProducto('${safeName}', '${safeCategory}')`);

            // --- UPSELLING: PRODUCTOS RELACIONADOS ---
            const relacionados = catalogoCompleto
                .filter(p => p.categoria === prod.categoria && p.nombre !== prod.nombre)
                .sort(() => 0.5 - Math.random()) // Mezclar
                .slice(0, 3); // Top 3

            let relatedHTML = '';
            if (relacionados.length > 0) {
                relatedHTML = `
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <button id="btn-toggle-related" onclick="toggleRelacionados()" class="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-slate-50 to-white border border-slate-200 text-[#1B263B] font-black text-[12px] flex items-center justify-between hover:border-[#1B263B]/30 hover:shadow-md transition-all group uppercase tracking-widest">
                        <span class="flex items-center gap-3"><span class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-600 flex items-center justify-center text-sm"><i class="fa-solid fa-wand-magic-sparkles"></i></span> Complementa tu kit</span>
                        <i id="icon-toggle-related" class="fa-solid fa-chevron-down text-slate-400 group-hover:text-[#1B263B] transition-transform"></i>
                    </button>
                    
                    <div id="modal-related-content" class="hidden mt-5 fade-in">
                        <div class="grid grid-cols-3 gap-3">
                            ${relacionados.map(r => {
                                const rIdentificador = (r.codigo || r.nombre).replace(/'/g, "\\'").replace(/"/g, "&quot;");
                                const rImg = getAbsoluteImgUrl(r.imagen);
                                return `
                                <div class="group cursor-pointer" onclick="abrirModalProducto('${rIdentificador}')">
                                    <div class="aspect-square bg-slate-50 rounded-xl p-2 mb-2 border border-slate-100 group-hover:border-[#1B263B]/30 transition-all">
                                        <img src="${rImg}" class="w-full h-full object-contain mix-blend-multiply group-hover:scale-110 transition-transform" onerror="this.onerror=null; this.src='favicon-app.png?v=5';">
                                    </div>
                                    <p class="text-[9px] font-bold text-slate-800 line-clamp-2 leading-tight">${r.nombre}</p>
                                </div>`;
                            }).join('')}
                        </div>
                    </div>
                </div>`;
            }
            document.getElementById('modal-related-container').innerHTML = relatedHTML;

            actualizarBotonesGrid(); 
            const modal = document.getElementById('product-modal'); modal.classList.remove('hidden'); void modal.offsetWidth; modal.classList.add('show'); document.body.style.overflow = 'hidden'; 
        }

        window.addEventListener('popstate', (e) => {
            if (!e.state || !e.state.modal) {
                cerrarModalProducto(null, true);
            } else if (e.state.productName) {
                abrirModalProducto(e.state.productName);
            }
        });

        function cerrarModalProducto(e, fromPopState = false) { 
            if (!fromPopState && history.state && history.state.modal) {
                history.back();
                return;
            }
            const modal = document.getElementById('product-modal'); modal.classList.remove('show'); setTimeout(() => { modal.classList.add('hidden'); document.body.style.overflow = 'auto'; }, 300); 
        }

        function toggleRelacionados() {
            const content = document.getElementById('modal-related-content');
            const icon = document.getElementById('icon-toggle-related');
            const isHidden = content.classList.contains('hidden');
            
            if (isHidden) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }
        function cambiarPrecioModal(precioNuevo, btnSeleccionado) { 
            document.getElementById('modal-price').innerText = (precioNuevo !== "Consultar" && !precioNuevo.toString().includes('$')) ? `$${precioNuevo}` : precioNuevo;
            const contenedor = btnSeleccionado.parentElement; 
            contenedor.querySelectorAll('button').forEach(b => { 
                b.className = 'px-3 py-1.5 border rounded-lg text-[11px] font-bold transition-colors bg-white text-slate-500 border-slate-200 hover:border-[#1B263B] hover:text-[#1B263B]'; 
            }); 
            btnSeleccionado.className = 'px-3 py-1.5 border rounded-lg text-[11px] font-bold transition-colors bg-[#1B263B] text-white border-[#1B263B]'; 
        }

        function toggleWishlist(identificador, btnElement, desdeModal = false) {
            const index = wishlist.findIndex(w => (w.codigo && w.codigo === identificador) || w.nombre === identificador); 
            if (index > -1) { wishlist.splice(index, 1); } 
            else { 
                const prod = catalogoCompleto.find(p => p.codigo === identificador || p.nombre === identificador); 
                if (prod) { wishlist.push(prod); radarNinja('Añadir a Wishlist', prod.nombre, prod.categoria); } 
            }
            localStorage.setItem('improgyp_wishlist', JSON.stringify(wishlist)); actualizarUIWishlist(); if(desdeModal) { abrirModalProducto(identificador); }
            const catActiva = document.querySelector('.cat-pill.active') ? document.querySelector('.cat-pill.active').getAttribute('data-cat') : 'Todos';
            let prodFilt = catalogoCompleto; if(document.getElementById('titulo-principal').innerHTML.includes('Deseados')) { prodFilt = wishlist; } else if(document.getElementById('titulo-principal').innerHTML.includes('Inteligente')) { return; } else if (catActiva !== 'Todos') { prodFilt = catalogoCompleto.filter(p => p.categoria === catActiva); }
            renderizarGrid(prodFilt);
        }
        function actualizarUIWishlist() {
            const badge = document.getElementById('wishlist-badge'); const container = document.getElementById('wishlist-items-container');
            if (wishlist.length > 0) {
                badge.innerText = wishlist.length; badge.classList.remove('hidden'); let html = '';
                [...wishlist].reverse().forEach(prod => { let precioBase = "Consultar"; if(prod.presentaciones && prod.presentaciones.length > 0) { const p = prod.presentaciones[0].precio.split('|')[0].trim(); if(p) precioBase = p; }
                    
                    // --- IMPLEMENTACIÓN DE DOMINIO ABSOLUTO ---
                    const imgUrl = getAbsoluteImgUrl(prod.imagen); 
                    // ------------------------------------------

                    const safeIdentificador = (prod.codigo || prod.nombre).replace(/'/g, "\\'").replace(/"/g, "&quot;");
                    html += `
                    <div class="wishlist-item group">
                        <img src="${imgUrl}" alt="${prod.nombre}" class="cursor-pointer" onclick="abrirModalProducto('${safeIdentificador}')" onerror="this.onerror=null; this.src='favicon-app.png?v=5';">
                        <div class="wishlist-item-info cursor-pointer" onclick="abrirModalProducto('${safeIdentificador}')">
                            <div class="wishlist-item-title">${prod.nombre}</div>
                            <div class="text-[9px] text-slate-400 font-bold">${prod.codigo ? 'REF: ' + prod.codigo : ''}</div>
                            <div class="wishlist-item-price">${(precioBase !== "Consultar" && !precioBase.toString().includes('$')) ? '$' + precioBase : precioBase}</div>
                        </div>
                        <div class="flex gap-1">
                            <button class="bg-slate-100 text-slate-400 hover:bg-rose-500 hover:text-white p-2 rounded-lg transition-colors" onclick="toggleWishlist('${safeIdentificador}');" title="Eliminar"><i class="fa-solid fa-trash-can text-[10px]"></i></button>
                            <button class="bg-[#1B263B]/10 text-[#1B263B] hover:bg-[#1B263B] hover:text-white p-2 rounded-lg transition-colors" onclick="agregarAlCarrito('${safeIdentificador}');" title="Añadir a bolsa"><i class="fa-solid fa-cart-plus text-[10px]"></i></button>
                        </div>
                    </div>`;
                }); container.innerHTML = html;
            } else { badge.classList.add('hidden'); container.innerHTML = `<div class="wishlist-empty"><i class="fa-regular fa-heart text-3xl text-slate-200 mb-3 block"></i>Aún no tienes herramientas favoritos.</div>`; }
        }
        function toggleWishlistModal(e) { if(e) e.stopPropagation(); document.getElementById('wishlist-modal').classList.toggle('show'); }
        document.addEventListener('click', function(event) { const modal = document.getElementById('wishlist-modal'); const btn = modal.previousElementSibling; if (!modal.contains(event.target) && !btn.contains(event.target)) { modal.classList.remove('show'); } });

        function actualizarTextos(categoria) {
            const data = textosMarketing[categoria] || textosMarketing['Todos'];
            document.getElementById('titulo-principal').innerHTML = data.tit;
            document.getElementById('subtitulo-principal').textContent = data.sub;
            
            const mobileLabel = document.getElementById('mobile-cat-label');
            if(mobileLabel) mobileLabel.textContent = categoria === 'Todos' ? 'Todos los productos' : categoria;
        }
        function mostrarWishlistCompleta() {
            document.getElementById('wishlist-modal').classList.remove('show'); document.getElementById('titulo-principal').innerHTML = `Tus herramientas <br class="hidden md:block"> <span class="laser-text">Deseados.</span>`; document.getElementById('subtitulo-principal').innerHTML = 'Tu selección personal lista para comprar.';
            document.querySelectorAll('.cat-pill').forEach(btn => { btn.classList.remove('active', 'border-transparent'); btn.classList.add('border-slate-200'); }); document.querySelectorAll('.bs-cat-btn').forEach(btn => btn.classList.remove('active')); document.getElementById('mobile-cat-label').innerText = "Deseados";
            renderizarGrid(wishlist); window.scrollTo({ top: 0, behavior: 'smooth' }); cerrarBurbujaIA();
        }

        function filtrarCategoria(categoriaSeleccionada) {
            cerrarBurbujaIA(); localStorage.setItem('improgyp_memoria_cat', categoriaSeleccionada); 
            // Limpiar buscadores al cambiar de categoría
            document.querySelectorAll('.omni-input-field').forEach(el => el.value = ''); 
            document.getElementById('mobile-cat-label').innerText = categoriaSeleccionada === 'Todos' ? 'Todos los productos' : categoriaSeleccionada;
            document.querySelectorAll('.cat-pill').forEach(btn => {
                if(btn.getAttribute('data-cat') === categoriaSeleccionada) { btn.classList.add('active', 'border-transparent'); btn.classList.remove('border-slate-200'); btn.scrollIntoView({ behavior: "smooth", block: "nearest", inline: "center" }); } 
                else { btn.classList.remove('active', 'border-transparent'); btn.classList.add('border-slate-200'); }
            });
            document.querySelectorAll('.bs-cat-btn').forEach(btn => { if(btn.getAttribute('data-cat') === categoriaSeleccionada) { btn.classList.add('active'); } else { btn.classList.remove('active'); } });
            
            // RESET MARCAS (Filtro Único)
            marcaActiva = 'Todas';
            document.querySelectorAll('.brand-pill').forEach(btn => {
                if(btn.getAttribute('data-brand') === 'Todas') btn.classList.add('active');
                else btn.classList.remove('active');
            });

            const sheet = document.getElementById('category-bottom-sheet'); if(sheet.classList.contains('show')) { toggleBottomSheet(); }
            const tituloH1 = document.getElementById('titulo-principal'); const subtitulo = document.getElementById('subtitulo-principal'); tituloH1.classList.remove('fade-in'); void tituloH1.offsetWidth; tituloH1.classList.add('fade-in');
            const textData = textosMarketing[categoriaSeleccionada]; const fallbackData = textosMarketing['Todos'];
            if(textData && textData.tit) { tituloH1.innerHTML = textData.tit; subtitulo.innerHTML = textData.sub; } else if(fallbackData && fallbackData.tit) { tituloH1.innerHTML = fallbackData.tit; subtitulo.innerHTML = fallbackData.sub; } 
            else { tituloH1.innerHTML = `Lo mejor en ${categoriaSeleccionada} <br class="hidden md:block"> para <span class="laser-text">resultados reales.</span>`; subtitulo.innerHTML = `Explora nuestra selección especializada de ${categoriaSeleccionada}.`; }
            if(categoriaSeleccionada !== 'Todos') {
                localStorage.removeItem('improgyp_ai_cat'); // Al navegar manual, bajamos prioridad de IA
            }
            let prodFilt = categoriaSeleccionada !== 'Todos' ? catalogoCompleto.filter(p => p.categoria === categoriaSeleccionada) : catalogoCompleto;
            renderizarGrid(prodFilt); window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function filtrarMarca(marca) {
            cerrarBurbujaIA();
            marcaActiva = marca;
            
            // UI Update Marcas
            document.querySelectorAll('.brand-pill').forEach(btn => {
                if(btn.getAttribute('data-brand') === marca) btn.classList.add('active');
                else btn.classList.remove('active');
            });

            // UI Update Categorías (Reset to Todos - Filtro Único)
            if(marca !== 'Todas') {
                document.querySelectorAll('.cat-pill').forEach(btn => {
                    if(btn.getAttribute('data-cat') === 'Todos') { btn.classList.add('active', 'border-transparent'); btn.classList.remove('border-slate-200'); }
                    else { btn.classList.remove('active', 'border-transparent'); btn.classList.add('border-slate-200'); }
                });
                document.querySelectorAll('.bs-cat-btn').forEach(btn => {
                    if(btn.getAttribute('data-cat') === 'Todos') btn.classList.add('active');
                    else btn.classList.remove('active');
                });
                document.getElementById('mobile-cat-label').innerText = 'Todos';
                localStorage.setItem('improgyp_memoria_cat', 'Todos');
            }

            let prodFilt = marca !== 'Todas' ? catalogoCompleto.filter(p => p.marca === marca) : catalogoCompleto;
            renderizarGrid(prodFilt);
            window.scrollTo({ top: 0, behavior: 'smooth' });

            radarNinja('Filtrar Marca', marca);
        }

        function clickAdCard(linkAd) {
            if(!linkAd) return;
            if(linkAd.startsWith('http')) { window.open(linkAd, '_blank'); }
            else { abrirModalProducto(linkAd); }
        }

        // ==========================================
        // RENDERIZADO DEL CATÁLOGO Y PAUTAS
        // ==========================================
        // RENDERIZADO DEL CATÁLOGO Y PAUTAS
        function renderizarGrid(arrayProductos) {
            ultimosProductosRenderizados = arrayProductos;
            const productosUnicos = arrayProductos.filter((prod, index, self) => index === self.findIndex((p) => p.nombre === prod.nombre) );
            
            // --- SMART SORTING ---
            productosUnicos.sort((a, b) => {
                const aBoost = datosRanking.impulsados.includes(a.nombre) ? 1 : 0;
                const bBoost = datosRanking.impulsados.includes(b.nombre) ? 1 : 0;
                if (aBoost !== bBoost) return bBoost - aBoost;
                const aTrend = (datosRanking.tendencias || []).find(t => t.nombre === a.nombre)?.clics || 0;
                const bTrend = (datosRanking.tendencias || []).find(t => t.nombre === b.nombre)?.clics || 0;
                if (aTrend !== bTrend) return bTrend - aTrend;
                return 0;
            });

            const grid = document.getElementById('grid-productos');
            if (productosUnicos.length === 0) { grid.innerHTML = `<div class="col-span-full text-center py-16 text-slate-500 font-medium">No hay productos para mostrar.</div>`; return; }

            // POOL DE ANUNCIOS ACTIVOS (Videos + Banners)
            let activeVideos = (adData.videos || []).filter(v => v.activo && v.pos);
            let activeBanners = (adData.banners || []).filter(b => b.activo && b.pos);
            let b2bInyectado = false;
            const posB2b = (adData && adData.b2b_pos) ? parseInt(adData.b2b_pos) : 0;

            let htmlBuffer = '';
            let totalItemsEnGrid = 0; // Productos + Videos + B2B

            const getCols = () => {
                const w = window.innerWidth;
                if (w >= 1024) return 3;
                if (w >= 768) return 2;
                return 2; 
            };
            const currentCols = getCols();

            const renderVideoCard = (data) => {
                const textoDestino = data.link ? "Ver Promoción" : "Saber Más";
                const safeLink = data.link ? data.link.replace(/'/g, "\\'") : '';
                const videoUrl = getAbsoluteImgUrl(data.url);
                return `
                <article class="flex flex-col h-full relative overflow-hidden cursor-pointer group shadow-lg rounded-[20px] transition-all duration-300 hover:-translate-y-[5px] hover:shadow-2xl border border-slate-700 hover:border-[#1B263B]/50 bg-black min-h-[320px]" onclick="clickAdCard('${safeLink}')">
                    <video src="${videoUrl}" autoplay loop muted playsinline preload="lazy" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 pointer-events-none z-0"></video>
                    <div class="absolute top-4 left-4 bg-[#1B263B] text-white text-[10px] font-black px-3 py-1 rounded-md shadow-lg z-20 uppercase tracking-widest flex items-center gap-1">
                        <i class="fa-solid fa-star"></i> DESTACADO
                    </div>
                    <div class="absolute inset-x-0 bottom-0 p-4 z-20 flex flex-col justify-end">
                        <button class="w-full bg-slate-900/90 backdrop-blur-sm text-white hover:bg-[#1B263B] hover:text-slate-900 font-bold text-[13px] py-2.5 rounded-xl transition-all flex items-center justify-center gap-1.5 h-10 md:h-9">
                            <i class="fa-solid fa-bolt text-current text-sm"></i> <span>${textoDestino}</span>
                        </button>
                    </div>
                </article>`;
            };

            const renderRompetrafico = (data) => {
                const etiqueta = data.etiqueta || 'Official';
                const titulo = data.titulo || '';
                const desc = data.desc || '';
                const link = data.link || '';
                const onclickAttr = link ? `onclick="window.open('${link}', '_blank')"` : '';
                
                return `
                <div class="col-span-full rompetrafico mt-8 mb-8 cursor-pointer group" ${onclickAttr}>
                    <div class="rt-content">
                        <div class="rt-glass-pill">${etiqueta}</div>
                        <h3 class="rt-title laser-text">${titulo}</h3>
                        <p class="rt-desc">${desc}</p>
                    </div>
                </div>`;
            };


            const renderB2BCard = () => {
                const etiqueta = adData.b2b_etiqueta || 'MAYORISTAS';
                const btnText = adData.b2b_btn || 'Acceder al Portal';
                const bgImg = adData.b2b_img_url ? getAbsoluteImgUrl(adData.b2b_img_url) : 'favicon-app.png?v=5';
                return `
                <article class="flex flex-col h-full relative overflow-hidden cursor-pointer group shadow-lg rounded-[20px] transition-all duration-300 hover:-translate-y-[5px] hover:shadow-2xl border border-slate-200 bg-slate-900 min-h-[320px]" onclick="window.location.href='b2b/'">
                    <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105 z-0" style="background-image: url('${bgImg}');"></div>
                    <div class="absolute top-4 left-4 bg-amber-500 text-slate-900 text-[10px] font-black px-3 py-1 rounded-md shadow-lg z-20 uppercase tracking-widest flex items-center gap-1">
                        <i class="fa-solid fa-crown"></i> ${etiqueta}
                    </div>
                    <div class="absolute inset-x-0 bottom-0 p-4 z-20 flex flex-col justify-end">
                        <button class="w-full bg-slate-900/90 backdrop-blur-sm text-white hover:bg-[#1B263B] hover:text-slate-900 font-bold text-[13px] py-2.5 rounded-xl transition-all flex items-center justify-center gap-1.5 h-10 md:h-9 shadow-lg">
                            <i class="fa-solid fa-right-to-bracket text-sm"></i> <span>${btnText}</span>
                        </button>
                    </div>
                </article>`;
            };

            // PROCESAR PRODUCTOS
            productosUnicos.forEach((prod, index) => {
                // 1. Verificar Inyección de VIDEOS y B2B por posición absoluta de CARD
                let adInyectadoEnEsteSlot = false;
                do {
                    adInyectadoEnEsteSlot = false;
                    const cardActual = totalItemsEnGrid + 1;
                    const vAd = activeVideos.find(v => parseInt(v.pos) === cardActual);
                    if (vAd) {
                        htmlBuffer += renderVideoCard(vAd);
                        totalItemsEnGrid++;
                        adInyectadoEnEsteSlot = true;
                        activeVideos = activeVideos.filter(v => v !== vAd);
                    }
                    if (adData && adData.b2b_activo && !b2bInyectado && posB2b === cardActual) {
                        htmlBuffer += renderB2BCard();
                        totalItemsEnGrid++;
                        b2bInyectado = true;
                        adInyectadoEnEsteSlot = true;
                    }
                } while (adInyectadoEnEsteSlot);

                // 2. Inyectar el PRODUCTO actual
                let precioBase = "Consultar"; 
                if(prod.presentaciones && prod.presentaciones.length > 0 && prod.presentaciones[0].precio) { 
                    const p = prod.presentaciones[0].precio.split('|')[0].trim(); 
                    if(p) precioBase = p; 
                }
                const imgUrl = getAbsoluteImgUrl(prod.imagen); 
                let descCorta = "Herramienta nutricional."; if(prod.desc_larga) { descCorta = prod.desc_larga.substring(0, 65) + "..."; }
                const safeIdentificador = (prod.codigo || prod.nombre).replace(/'/g, "\\'").replace(/"/g, "&quot;"); 
                const enWishlist = wishlist.some(w => (w.codigo && w.codigo === prod.codigo) || w.nombre === prod.nombre);
                const btnClass = enWishlist ? 'active' : ''; const iconClass = enWishlist ? 'fa-solid' : 'fa-regular';
                const idGridBtn = getSafeId(prod.nombre); 
                let fomoBadgeHTML = ''; 
                const impulsado = (datosRanking.impulsados || []).includes(prod.nombre);
                const tendenciaData = (datosRanking.tendencias || []).find(t => t.nombre === prod.nombre);

                if (impulsado) {
                    fomoBadgeHTML = `<div class="absolute top-[10px] left-[10px] bg-[#1B263B]/90 backdrop-blur-md text-white text-[10px] font-black px-2 py-1 rounded-md shadow-lg z-10 flex items-center gap-1 border border-[#1B263B] animate-pulse"><i class="fa-solid fa-bolt-lightning text-white"></i> TOP</div>`;
                } else if (tendenciaData && tendenciaData.clics > 1) {
                    fomoBadgeHTML = `<div class="absolute top-[10px] left-[10px] bg-rose-500/90 backdrop-blur-md text-white text-[10px] font-black px-2 py-1 rounded-md shadow-lg z-10 flex items-center gap-1 border border-rose-400"><i class="fa-solid fa-fire text-yellow-300"></i> TENDENCIA</div>`;
                }

                htmlBuffer += `
                    <article class="glass-card">
                        <div class="product-img-wrapper" onclick="abrirModalProducto('${safeIdentificador}')">
                            ${fomoBadgeHTML ? fomoBadgeHTML : `<span class="badge bg-white/90 text-slate-500 hidden sm:block">${prod.categoria}</span>`}
                            <button class="btn-wishlist ${btnClass}" onclick="event.stopPropagation(); toggleWishlist('${safeIdentificador}', this)" title="Añadir a deseos"><i class="${iconClass} fa-heart"></i></button>
                            <img src="${imgUrl}" alt="${prod.nombre}" class="product-img cursor-pointer" onerror="this.onerror=null; this.src='favicon-app.png?v=5';">
                        </div>
                        <div class="flex flex-col flex-grow cursor-pointer" onclick="abrirModalProducto('${safeIdentificador}')">
                            <span class="brand-label">${prod.marca || 'IMPROGYP'}</span>
                            <h4 class="text-slate-900 font-bold mb-1 text-[14px] md:text-base leading-tight line-clamp-2 min-h-[36px] flex items-center md:h-auto">${prod.nombre}</h4>
                            ${prod.codigo ? `<span class="sku-label self-start mb-2">${prod.codigo}</span>` : ''}
                            <p class="hidden md:block text-[14px] text-slate-500 mb-3 flex-grow leading-relaxed">${descCorta}</p>
                            <div class="flex justify-between items-end mt-auto mb-3">
                                <div><p class="text-[9px] md:text-[10px] text-slate-400 font-bold uppercase mb-0.5">Precio</p><span class="text-[15px] md:text-[17px] font-black text-slate-800">${(precioBase !== "Consultar" && !precioBase.toString().includes('$')) ? '$' + precioBase : precioBase}</span></div>
                            </div>
                        </div>
                        <div id="${idGridBtn}" class="w-full mt-auto"></div>
                    </article>`;
                
                totalItemsEnGrid++;

                // 3. Verificar Inyección de BANNERS por FILA
                if (totalItemsEnGrid % currentCols === 0) {
                    const filaCompletada = totalItemsEnGrid / currentCols;
                    const bAd = activeBanners.find(b => parseInt(b.pos) === filaCompletada);
                    if (bAd) {
                        htmlBuffer += renderRompetrafico(bAd);
                        activeBanners = activeBanners.filter(b => b !== bAd);
                    }
                }
            });

            // COMPLETO LA ÚLTIMA FILA CON ESPACIOS VACÍOS SI ES NECESARIO
            const resto = totalItemsEnGrid % currentCols;
            if (resto > 0) {
                const faltantes = currentCols - resto;
                for (let i = 0; i < faltantes; i++) {
                    htmlBuffer += `<div class="hidden md:block"></div>`;
                }
            }


            // 4. Inyectar remanentes (Rompetráfico)
            activeBanners.forEach(b => { htmlBuffer += renderRompetrafico(b); });

            // 5. BANNER IA AL FINAL (Estado Original)
            if (adData && adData.ia_activo) {
                const etiqueta = adData.ia_etiqueta || 'Asesoría Gratuita';
                const titulo = adData.ia_titulo || '¿No encuentras lo que buscas?';
                const desc = adData.ia_desc || 'Deja que nuestro Asesor IA analice tus objetivos y te recomiende el combo perfecto en segundos.';
                const btnText = adData.ia_btn || 'Consultar a la IA';
                htmlBuffer += `
                <div class="col-span-full mt-8 mb-6 cursor-pointer" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); setTimeout(()=>document.getElementById('omni-input-field').focus(), 500);">
                    <div class="glass-panel p-8 rounded-3xl text-center border border-[#1B263B]/30 shadow-[0_15px_40px_rgba(122,193,66,0.1)] relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[#1B263B]/5 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                        <span class="inline-block bg-[#1B263B]/10 text-[#1B263B] text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest mb-4 border border-[#1B263B]/20"><i class="fa-solid fa-robot"></i> ${etiqueta}</span>
                        <h3 class="text-2xl md:text-3xl font-black text-slate-900 mb-3"><span class="laser-text">${titulo}</span></h3>
                        <p class="text-slate-500 text-sm max-w-lg mx-auto mb-6">${desc}</p>
                        <button class="bg-slate-900 text-white hover:bg-[#1B263B] hover:text-slate-900 font-bold py-3.5 px-8 rounded-xl transition-all inline-flex items-center gap-2 shadow-lg">
                            <i class="fa-solid fa-magnifying-glass-chart"></i> ${btnText}
                        </button>
                    </div>
                </div>`;
            }

            grid.innerHTML = htmlBuffer; actualizarBotonesGrid(); 
            console.log("Grid renderizado con", totalItemsEnGrid, "espacios.");
        }
    </script>
</body>
</html>