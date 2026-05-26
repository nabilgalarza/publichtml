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
