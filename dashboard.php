<?php
// dashboard.php - IMPROGYP OS
session_start();
ini_set('memory_limit', '512M'); // Aumento Global de Memoria para Procesamiento de Imágenes
date_default_timezone_set('America/Guayaquil');
require_once __DIR__ . '/security_headers.php';
require_once __DIR__ . '/lib/b2b_config.php';

// Función auxiliar para limpiar rutas de imagen (Cero Impacto - Misma lógica que index.php)
function getCleanImgUrl($ruta) {
    if (!$ruta) return 'favicon-app.png?v=5';
    if (strpos($ruta, 'http') === 0) return $ruta;
    return ltrim(str_replace('./', '', $ruta), '/');
}

/**
 * Borra físicamente un archivo de imagen si existe y no es la imagen por defecto.
 */
function borrarFotoFisica($ruta) {
    if (!$ruta || strpos($ruta, 'http') === 0 || $ruta === 'favicon-app.png' || $ruta === 'favicon-app.png?v=5') return;
    $ruta_absoluta = __DIR__ . '/' . ltrim($ruta, '/');
    if (file_exists($ruta_absoluta) && is_file($ruta_absoluta)) {
        @unlink($ruta_absoluta);
    }
}

function cargarEnv($ruta) {
    if (!file_exists($ruta)) return false;
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lineas as $linea) {
        if (strpos(trim($linea), '#') === 0 || strpos($linea, '=') === false) continue;
        list($nombre, $valor) = explode('=', $linea, 2);
        $env[trim($nombre)] = trim($valor);
    }
    return $env;
}
$env = cargarEnv(__DIR__ . '/.env');
if (!is_array($env)) {
    $env = [];
}

if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrf_token = $_SESSION['csrf_token'];

// --- BASE DE DATOS: CONFIGURACIÓN Y TABLAS ---
$db_host = $env['DB_HOST'] ?? 'localhost';
$db_port = $env['DB_PORT'] ?? '3306';
$db_name = $env['DB_NAME'] ?? '';
$db_user = $env['DB_USER'] ?? '';
$db_pass = $env['DB_PASS'] ?? '';

if (strpos($db_host, ':') !== false) { 
    list($h, $p) = explode(':', $db_host); 
    $dsn = "mysql:host=$h;port=$p;dbname=$db_name;charset=utf8mb4"; 
} else { 
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4"; 
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // OPTIMIZACIÓN: Solo inicializar tablas si es necesario
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios_admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        rol ENUM('master', 'gestor') DEFAULT 'gestor',
        activo TINYINT(1) DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Crear admin inicial si la tabla está vacía
    if ($pdo->query("SELECT COUNT(*) FROM usuarios_admin")->fetchColumn() == 0) {
        $admin_pass = $env['ADMIN_PASSWORD'] ?? null;
        if (!$admin_pass) die("Configuración incompleta: ADMIN_PASSWORD no definido en .env");
        $pass_inicial = password_hash($admin_pass, PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO usuarios_admin (usuario, password_hash, nombre, rol) VALUES (?, ?, ?, 'master')")->execute(['admin', $pass_inicial, 'Administrador Maestro']);
    }

    // Metadatos y otras tablas
    $pdo->exec("CREATE TABLE IF NOT EXISTS metricas_b2c (id INT AUTO_INCREMENT PRIMARY KEY, evento VARCHAR(50) NOT NULL, valor VARCHAR(255) NOT NULL, categoria VARCHAR(50) DEFAULT 'General', ip VARCHAR(45) DEFAULT NULL, region VARCHAR(100) DEFAULT 'Desconocida', fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_evento (evento), INDEX idx_categoria (categoria), INDEX idx_fecha (fecha))");
    
    // TABLAS PARA GESTIÓN DE PEDIDOS (B2B y PÚBLICO)
    $pdo->exec("CREATE TABLE IF NOT EXISTS pedidos_b2b (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ruc_cliente VARCHAR(50) NOT NULL,
        nombre_cliente VARCHAR(100) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        items_json LONGTEXT NOT NULL,
        status ENUM('pendiente', 'confirmado', 'despachado', 'cancelado') DEFAULT 'pendiente',
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pedidos_publicos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        total DECIMAL(10,2) NOT NULL,
        items_json LONGTEXT NOT NULL,
        status ENUM('contacto_iniciado', 'completado', 'cancelado') DEFAULT 'contacto_iniciado',
        source VARCHAR(255) DEFAULT 'directo',
        ip VARCHAR(45) DEFAULT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_pedidos_publicos_fecha (fecha),
        INDEX idx_pedidos_publicos_ip (ip)
    )");

    try {
        if (!$pdo->query("SHOW COLUMNS FROM pedidos_publicos LIKE 'ip'")->fetch()) {
            $pdo->exec("ALTER TABLE pedidos_publicos ADD COLUMN ip VARCHAR(45) DEFAULT NULL AFTER source");
        }
    } catch (Exception $e) { /* columna ya existe o tabla nueva */ }
    try {
        if (!$pdo->query("SHOW INDEX FROM pedidos_publicos WHERE Key_name = 'idx_pedidos_publicos_fecha'")->fetch()) {
            $pdo->exec("ALTER TABLE pedidos_publicos ADD INDEX idx_pedidos_publicos_fecha (fecha)");
        }
    } catch (Exception $e) { /* índice ya existe */ }

    // Soporte para instalaciones existentes: Asegurar columnas nuevas (MariaDB/MySQL Retrocompatibility)
    foreach(['categoria' => "VARCHAR(50) DEFAULT 'General' AFTER valor", 'ip' => "VARCHAR(45) DEFAULT NULL AFTER categoria", 'region' => "VARCHAR(100) DEFAULT 'Desconocida' AFTER ip"] as $col => $def) {
        try { if (!$pdo->query("SHOW COLUMNS FROM metricas_b2c LIKE '$col'")->fetch()) { $pdo->exec("ALTER TABLE metricas_b2c ADD COLUMN $col $def"); } } catch(Exception $e) {}
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS b2b_historial_chat (id INT AUTO_INCREMENT PRIMARY KEY, ruc_cliente VARCHAR(50) NOT NULL, mensaje TEXT NOT NULL, remitente ENUM('cliente', 'ia') NOT NULL, fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_ruc (ruc_cliente))");
    
    // Esquema de sesiones_b2b (añadir columnas faltantes si se borró la tabla anterior)
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS sesiones_b2b (
            session_id VARCHAR(128) PRIMARY KEY,
            ruc_cliente VARCHAR(50),
            clic_whatsapp TINYINT(1) DEFAULT 0,
            gestionado_admin TINYINT(1) DEFAULT 0,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        // Verificar si la columna gestionado_admin ya existe, si no, añadirla
        $checkCol = $pdo->query("SHOW COLUMNS FROM sesiones_b2b LIKE 'gestionado_admin'")->fetch();
        if (!$checkCol) { $pdo->exec("ALTER TABLE sesiones_b2b ADD COLUMN gestionado_admin TINYINT(1) DEFAULT 0 AFTER clic_whatsapp"); }
    } catch (Exception $e) { }

    $pdo->exec("CREATE TABLE IF NOT EXISTS metricas_cotizaciones (id INT AUTO_INCREMENT PRIMARY KEY, session_id VARCHAR(128), ruc_cliente VARCHAR(50), producto_nombre VARCHAR(255), cantidad INT, precio_unitario DECIMAL(10,2), subtotal DECIMAL(10,2), fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_session (session_id), INDEX idx_ruc (ruc_cliente))");
    
    // Soporte para instalaciones existentes: Asegurar columnas nuevas en métricas de cotización
    try { if (!$pdo->query("SHOW COLUMNS FROM metricas_cotizaciones LIKE 'ruc_cliente'")->fetch()) { $pdo->exec("ALTER TABLE metricas_cotizaciones ADD COLUMN ruc_cliente VARCHAR(50) AFTER session_id, ADD INDEX idx_ruc (ruc_cliente)"); } } catch(Exception $e) {}
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS productos_impulsados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre_producto VARCHAR(255) NOT NULL,
        fecha_limite DATETIME NOT NULL,
        UNIQUE KEY idx_nombre_unique (nombre_producto)
    )");
    try {
        if (!$pdo->query("SHOW INDEX FROM productos_impulsados WHERE Key_name = 'idx_nombre_unique'")->fetch()) {
            $pdo->exec("ALTER TABLE productos_impulsados ADD UNIQUE KEY idx_nombre_unique (nombre_producto)");
        }
    } catch (Exception $e) {}
    try {
        if (!$pdo->query("SHOW COLUMNS FROM metricas_b2c LIKE 'visitor_id'")->fetch()) {
            $pdo->exec("ALTER TABLE metricas_b2c ADD COLUMN visitor_id VARCHAR(36) DEFAULT NULL AFTER region, ADD INDEX idx_visitor_id (visitor_id)");
        }
    } catch (Exception $e) {}

    require_once __DIR__ . '/lib/blog_helpers.php';
    blog_ensure_table($pdo);
    blog_seed_if_empty($pdo);

    // Nueva tabla para gestión centralizada de categorías
    $pdo->exec("CREATE TABLE IF NOT EXISTS categorias_admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) UNIQUE NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Nueva tabla para gestión centralizada de marcas
    $pdo->exec("CREATE TABLE IF NOT EXISTS marcas_admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) UNIQUE NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // TABLA MAESTRA DE PRODUCTOS (CATÁLOGO)
    $pdo->exec("CREATE TABLE IF NOT EXISTS improgyp_catalogo (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        codigo VARCHAR(100),
        marca VARCHAR(100),
        categoria VARCHAR(100),
        presentaciones_raw TEXT,
        desc_larga TEXT,
        imagen_url TEXT,
        publicado TINYINT(1) DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_categoria (categoria),
        INDEX idx_marca (marca),
        INDEX idx_codigo (codigo)
    )");
    // Soporte para instalaciones existentes: Asegurar columnas nuevas en el catálogo
    try { 
        if (!$pdo->query("SHOW COLUMNS FROM improgyp_catalogo LIKE 'codigo'")->fetch()) { 
            $pdo->exec("ALTER TABLE improgyp_catalogo ADD COLUMN codigo VARCHAR(100) AFTER nombre, ADD INDEX idx_codigo (codigo)"); 
        } 
        if (!$pdo->query("SHOW COLUMNS FROM improgyp_catalogo LIKE 'marca'")->fetch()) { 
            $pdo->exec("ALTER TABLE improgyp_catalogo ADD COLUMN marca VARCHAR(100) AFTER codigo, ADD INDEX idx_marca (marca)"); 
        }
    } catch(Exception $e) {}

    // TABLA DE CLIENTES B2B
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios_b2b (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ruc VARCHAR(50) UNIQUE NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        pin VARCHAR(255) NOT NULL,
        descuento INT DEFAULT 0,
        telefono VARCHAR(20),
        activo TINYINT(1) DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ruc (ruc)
    )");

    // Migración inicial: Si la tabla está vacía, poblarla con las categorías base y actuales
    if ($pdo->query("SELECT COUNT(*) FROM categorias_admin")->fetchColumn() == 0) {
        $base_cats = ["Balance y Equilibrio", "Entrenamiento", "Construcción Deportiva", "Salud Integral", "Salud cardiovascular y cerebrovascular", "Minerales", "Salud hepática", "Belleza y Cuidado Personal", "Aminos", "Vitaminas", "Salud digestiva", "Multivitaminas", "Otros"];
        $db_cats = $pdo->query("SELECT DISTINCT categoria FROM improgyp_catalogo WHERE categoria != ''")->fetchAll(PDO::FETCH_COLUMN);
        $todas = array_unique(array_merge($base_cats, $db_cats));
        $stmt_ins = $pdo->prepare("INSERT IGNORE INTO categorias_admin (nombre) VALUES (?)");
        foreach ($todas as $c) { $stmt_ins->execute([trim($c)]); }
    }

} catch (PDOException $e) { die("Error de conexión: " . $e->getMessage()); }

$RECOVERY_KEY = $env['MASTER_RECOVERY_KEY'] ?? null;

// --- PROTECCIÓN BRUTE FORCE ---
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['last_login_attempt'])) $_SESSION['last_login_attempt'] = 0;

$tiempo_bloqueo = 300; 
$intentos_max = 5;
$segundos_restantes = ($_SESSION['last_login_attempt'] + $tiempo_bloqueo) - time();

if (isset($_GET['logout'])) { session_destroy(); header("Location: dashboard.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $user_input = trim($_POST['usuario'] ?? '');
    $pass_input = $_POST['password'];

    if ($segundos_restantes > 0 && $_SESSION['login_attempts'] >= $intentos_max) {
        $error_login = "Demasiados intentos. Espera " . ceil($segundos_restantes / 60) . " min.";
    } elseif (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) { 
        $error_login = "Error de seguridad (CSRF)."; 
    } else {
        // 1. Verificar contra Recovery Key (Acceso de Emergencia)
        if ($pass_input === $RECOVERY_KEY) {
            session_regenerate_id(true);
            $_SESSION['admin_logueado'] = true;
            $_SESSION['admin_id'] = 0;
            $_SESSION['admin_user'] = 'recovery_master';
            $_SESSION['admin_nombre'] = 'Recuperación Maestra';
            $_SESSION['admin_rol'] = 'master';
            $_SESSION['login_attempts'] = 0;
            header("Location: dashboard.php"); exit;
        }

        // 2. Verificar contra Base de Datos
        $stmt_u = $pdo->prepare("SELECT * FROM usuarios_admin WHERE usuario = ?");
        $stmt_u->execute([$user_input]);
        $user_db = $stmt_u->fetch(PDO::FETCH_ASSOC);

        if ($user_db && password_verify($pass_input, $user_db['password_hash'])) {
            if ($user_db['activo'] == 0) {
                $error_login = "Tu cuenta ha sido desactivada temporalmente.";
            } else {
                session_regenerate_id(true);
                $_SESSION['admin_logueado'] = true;
                $_SESSION['admin_id'] = $user_db['id'];
                $_SESSION['admin_user'] = $user_db['usuario'];
                $_SESSION['admin_nombre'] = $user_db['nombre'];
                $_SESSION['admin_rol'] = $user_db['rol'];
                $_SESSION['login_attempts'] = 0;
                header("Location: dashboard.php"); exit;
            }
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_login_attempt'] = time();
            $intentos_quedan = $intentos_max - $_SESSION['login_attempts'];
            $error_login = "Acceso denegado. " . ($intentos_quedan > 0 ? "Te quedan $intentos_quedan intentos." : "Sistema bloqueado por 5 min."); 
        }
    }
}

if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | IMPROGYP OS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 h-screen flex items-center justify-center font-sans">
    <div class="bg-white p-10 rounded-[2.5rem] w-full max-w-sm border border-slate-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-[#1B263B] blur-[80px] opacity-10"></div>
        <div class="text-center mb-10 relative z-10">
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">IMPROGYP <span class="text-[#1B263B] font-light italic">OS</span></h2>
            <p class="text-slate-400 text-xs mt-2 font-bold uppercase tracking-widest">Acceso Administrativo</p>
        </div>
        <?php if(isset($error_login)): ?>
            <div class="bg-rose-50 text-rose-500 p-4 rounded-2xl text-xs mb-6 text-center font-black border border-rose-100 italic animate-pulse">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> <?= $error_login ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="" class="relative z-10 space-y-5">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="relative">
                <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-sm"></i>
                <input type="text" name="usuario" placeholder="Usuario" class="w-full pl-11 pr-4 py-4 rounded-2xl bg-slate-50 border border-slate-100 text-slate-900 focus:bg-white focus:border-[#1B263B] focus:ring-4 focus:ring-[#1B263B]/10 outline-none transition-all placeholder:text-slate-300 text-sm font-bold" required autofocus>
            </div>
            <div class="relative">
                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-sm"></i>
                <input type="password" name="password" placeholder="••••••••" class="w-full pl-11 pr-4 py-4 rounded-2xl bg-slate-50 border border-slate-100 text-slate-900 focus:bg-white focus:border-[#1B263B] focus:ring-4 focus:ring-[#1B263B]/10 outline-none transition-all placeholder:text-slate-300 font-mono" required>
            </div>
            <button type="submit" class="w-full bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-4 rounded-2xl transition-all active:scale-[0.98] uppercase tracking-widest text-sm shadow-lg shadow-[#1B263B]/20">Entrar al Sistema</button>
        </form>
    </div>

</body>
</html>
<?php exit; }

$vista = $_GET['view'] ?? 'catalogo';
if ($vista === 'social') {
    header('Location: dashboard.php?view=catalogo');
    exit;
}

$sub_vista = $_GET['sub'] ?? '';
$menu_apariencia_abierto = ($vista === 'apariencia' || $vista === 'blog');
$menu_inventario_abierto = ($vista === 'catalogo');

if ($vista === 'importacion') {
    header('Location: dashboard.php?view=catalogo');
    exit;
}

if (in_array($vista, ['distribuidores', 'pedidos'], true) && !improgyp_b2b_admin_ver_modulo()) {
    header('Location: dashboard.php?view=catalogo&msg=b2b_oculto');
    exit;
}

if ($vista === 'apariencia' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
    if ($sub_vista === '') {
        header('Location: dashboard.php?view=apariencia&sub=home');
        exit;
    }
    if (in_array($sub_vista, ['portada', 'secciones'], true)) {
        $hash = $sub_vista === 'secciones' ? '#bloque-categorias' : '';
        header('Location: dashboard.php?view=apariencia&sub=home' . $hash);
        exit;
    }
}

$db_host = $env['DB_HOST'] ?? 'localhost'; 
$db_name = $env['DB_NAME'] ?? ''; 
$db_user = $env['DB_USER'] ?? ''; 
$db_pass = $env['DB_PASS'] ?? '';

$protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocolo . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/\\');

    // --- FUNCIONALIDAD CSV + MULTI-IMAGEN ---
    if (isset($_GET['action']) && $_GET['action'] === 'exportar_ejemplo_csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=ejemplo_catalogo_IMPROGYP.csv');
        $output = fopen('php://output', 'w');
        // ESTRUCTURA ESTÁNDAR: ID, Nombre, Codigo, Marca, Categoria, Unidad_Precios, Descripcion_Larga, Datos_Tecnicos, Archivo_Imagen, Publicado
        fputcsv($output, ['ID', 'Nombre', 'Codigo', 'Marca', 'Categoria', 'Unidad_Precios', 'Descripcion_Larga', 'Datos_Tecnicos', 'Archivo_Imagen', 'Publicado']);
        fputcsv($output, ['0', 'Atornillador Gypsum Inalámbrico', '20MDSG20V', 'MAXXT', 'Herramientas Drywall', "Presentación Única: 385.11", 'Potencia tu productividad con el Atornillador Gypsum Inalámbrico...', 'Fuerza: 20V | Velocidad: 4200rpm', '20MDSG20V.webp', '1']);
        fputcsv($output, ['0', 'Taladro Percutor 1/2', 'TB550-B3', 'BLACK+DECKER', 'Taladros Percutores', "Sin Maleta: 65.00\nCon Maleta: 75.00", 'Taladro potente para mampostería y madera.', 'Potencia: 550W | Mandril: 1/2"', 'tb550.webp', '1']);
        fclose($output); exit;
    }
    if (isset($_GET['action']) && $_GET['action'] === 'exportar_csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=catalogo_IMPROGYP.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Nombre', 'Codigo', 'Marca', 'Categoria', 'Unidad_Precios', 'Descripcion_Larga', 'Datos_Tecnicos', 'Archivo_Imagen', 'Publicado']);
        
        // Selección explícita para garantizar el orden de las columnas
        $stmt = $pdo->query("SELECT id, nombre, codigo, marca, categoria, presentaciones_raw, desc_larga, datos_tecnicos, imagen_url, publicado FROM improgyp_catalogo ORDER BY id ASC");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
            // LIMPIEZA DE PRECIO PARA EXCEL: Eliminar '$' de presentaciones_raw si existe
            if (!empty($row['presentaciones_raw'])) {
                $lines = explode("\n", $row['presentaciones_raw']);
                foreach ($lines as &$line) {
                    if (strpos($line, ':') !== false) {
                        list($opt, $pr) = explode(':', $line, 2);
                        $line = trim($opt) . ": " . preg_replace('/[^\d.]/', '', $pr);
                    }
                }
                $row['presentaciones_raw'] = implode("\n", $lines);
            }
            fputcsv($output, array_values($row)); 
        }
        fclose($output); exit;
    }

    // NUEVO: EXPORTAR INFORME B2B
    if (isset($_GET['action']) && $_GET['action'] === 'exportar_b2b') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=informe_cotizaciones_b2b.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Fecha', 'RUC Cliente', 'Cliente', 'Producto', 'Cantidad', 'Precio Unit.', 'Subtotal', 'Sesion ID']);
        
        $stmt = $pdo->query("SELECT c.fecha, c.ruc_cliente, u.nombre as cliente, c.producto_nombre, c.cantidad, c.precio_unitario, c.subtotal, c.session_id 
                             FROM metricas_cotizaciones c 
                             LEFT JOIN usuarios_b2b u ON c.ruc_cliente = u.ruc 
                             ORDER BY c.fecha DESC");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { fputcsv($output, $row); }
        fclose($output); exit;
    }

        if (($_POST['action'] ?? '') === 'actualizar_masivo_csv' && isset($_FILES['archivo_csv'])) {
        require_once __DIR__ . '/lib/bulk_catalogo_helpers.php';
        set_time_limit(300); 
        ini_set('memory_limit', '512M'); 
        
        $upd_count = 0; $new_count = 0; $err_count = 0;
        $img_ok = 0; $img_sin = 0;
        
        if (empty($_POST['bulk_confirm_ids'])) {
            $resumenIds = improgyp_bulk_csv_resumen_ids($_FILES['archivo_csv']['tmp_name']);
            if ($resumenIds['tiene_id'] && $resumenIds['filas_con_id'] >= 5) {
                $_SESSION['bulk_csv_needs_confirm'] = [
                    'filas_con_id' => (int) $resumenIds['filas_con_id'],
                    'filas_datos' => (int) $resumenIds['filas_datos'],
                ];
                header('Location: dashboard.php?view=catalogo&msg=bulk_csv_confirm');
                exit;
            }
        }
        unset($_SESSION['bulk_csv_needs_confirm']);

        $mapeo_imagenes = improgyp_bulk_mapeo_desde_post();
        $img_staged = improgyp_bulk_archivos_staging_count();

        $file = $_FILES['archivo_csv']['tmp_name'];
        if (($handle = fopen($file, "r")) !== FALSE) {
            $first_line = fgets($handle);
            $delim = (strpos($first_line, ';') !== false) ? ';' : ',';
            rewind($handle);

            $headers = fgetcsv($handle, 1000, $delim);
            if ($headers) {
                $idx = array_flip(array_map('strtolower', $headers));

                while (($data = fgetcsv($handle, 1000, $delim)) !== FALSE) {
                    try {
                        $id = isset($idx['id']) ? (int)($data[$idx['id']] ?? 0) : 0;
                        $nombre = isset($idx['nombre']) ? trim($data[$idx['nombre']] ?? '') : '';
                        $codigo = isset($idx['codigo']) ? trim($data[$idx['codigo']] ?? '') : (isset($idx['sku']) ? trim($data[$idx['sku']] ?? '') : '');
                        $marca = isset($idx['marca']) ? trim($data[$idx['marca']] ?? '') : '';
                        $cat = isset($idx['categoria']) ? trim($data[$idx['categoria']] ?? '') : (isset($idx['categoría']) ? trim($data[$idx['categoría']] ?? '') : '');
                        
                        $pres = '';
                        $rawP = '';
                        if (isset($idx['unidad_precios'])) {
                            $rawP = trim($data[$idx['unidad_precios']] ?? '');
                        } else {
                            $rawP = isset($idx['presentaciones_raw']) ? trim($data[$idx['presentaciones_raw']] ?? '') : (isset($idx['presentaciones_precios']) ? trim($data[$idx['presentaciones_precios']] ?? '') : '');
                        }

                        if (!empty($rawP)) {
                            // NORMALIZACIÓN: Precio: 45 -> Presentación Única: 45
                            if (stripos($rawP, 'Precio:') === 0) {
                                $rawP = "Presentación Única: " . trim(str_ireplace('Precio:', '', $rawP));
                            }
                            
                            // LIMPIEZA DE MONEDA ($): Extraer solo números y puntos
                            $lines = explode("\n", $rawP);
                            foreach ($lines as &$line) {
                                if (strpos($line, ':') !== false) {
                                    list($opt, $pr) = explode(':', $line, 2);
                                    $pr_limpio = preg_replace('/[^\d.]/', '', $pr);
                                    $line = trim($opt) . ": " . $pr_limpio;
                                }
                            }
                            $pres = implode("\n", $lines);
                        }

                        $desc_base = isset($idx['descripcion_larga']) ? trim($data[$idx['descripcion_larga']] ?? '') : (isset($idx['desc_larga']) ? trim($data[$idx['desc_larga']] ?? '') : '');
                        $datos_tec = isset($idx['datos_tecnicos']) ? trim($data[$idx['datos_tecnicos']] ?? '') : '';
                        $desc = $desc_base . (!empty($datos_tec) ? "\n\nDATOS TÉCNICOS:\n" . $datos_tec : "");

                        $img_csv = isset($idx['archivo_imagen']) ? trim($data[$idx['archivo_imagen']] ?? '') : (isset($idx['imagen_url']) ? trim($data[$idx['imagen_url']] ?? '') : '');

                        if (empty($nombre)) continue;

                        $img_csv = improgyp_bulk_resolver_imagen_ruta($img_csv, $codigo, $mapeo_imagenes);
                        $tieneImagen = $img_csv !== ''
                            && (strpos($img_csv, 'http') === 0 || strpos($img_csv, 'img_catalogo/') === 0);
                        if ($tieneImagen) {
                            $img_ok++;
                        } elseif ($codigo !== '') {
                            $img_sin++;
                        }

                        // INTELIGENCIA: Si no hay ID, buscar por SKU (Código)
                        if ($id <= 0 && !empty($codigo)) {
                            $stmt_search = $pdo->prepare("SELECT id FROM improgyp_catalogo WHERE codigo = ? LIMIT 1");
                            $stmt_search->execute([$codigo]);
                            $id = (int)$stmt_search->fetchColumn();
                        }

                        if ($id > 0) {
                            // LIMPIEZA: Borrar foto física anterior si se está reemplazando
                            if (!empty($img_csv) && (strpos($img_csv, 'http') === 0 || strpos($img_csv, 'img_catalogo/') === 0)) {
                                $stmt_old = $pdo->prepare("SELECT imagen_url FROM improgyp_catalogo WHERE id = ?");
                                $stmt_old->execute([$id]);
                                $old_img = $stmt_old->fetchColumn();
                                if ($old_img && $old_img !== $img_csv) { borrarFotoFisica($old_img); }
                                
                                $pdo->prepare("UPDATE improgyp_catalogo SET nombre=?, codigo=?, marca=?, categoria=?, presentaciones_raw=?, desc_larga=?, imagen_url=? WHERE id=?")->execute([$nombre, $codigo, $marca, $cat, $pres, $desc, $img_csv, $id]);
                            } else {
                                $pdo->prepare("UPDATE improgyp_catalogo SET nombre=?, codigo=?, marca=?, categoria=?, presentaciones_raw=?, desc_larga=? WHERE id=?")->execute([$nombre, $codigo, $marca, $cat, $pres, $desc, $id]);
                            }
                            $upd_count++;
                        } else {
                            $pdo->prepare("INSERT INTO improgyp_catalogo (nombre, codigo, marca, categoria, presentaciones_raw, desc_larga, imagen_url, publicado) VALUES (?, ?, ?, ?, ?, ?, ?, 1)")->execute([$nombre, $codigo, $marca, $cat, $pres, $desc, $img_csv]);
                            $new_count++;
                        }
                        if (!empty($cat)) { $pdo->prepare("INSERT IGNORE INTO categorias_admin (nombre) VALUES (?)")->execute([$cat]); }
                        if (!empty($marca)) { $pdo->prepare("INSERT IGNORE INTO marcas_admin (nombre) VALUES (?)")->execute([$marca]); }
                    } catch (Exception $e) { $err_count++; }
                }
            }
            fclose($handle);
            regenerarJSON($pdo);
            improgyp_bulk_imagen_map_clear();
            header("Location: dashboard.php?view=catalogo&msg=csv_procesado&upd=$upd_count&new=$new_count&err=$err_count&img_ok=$img_ok&img_sin=$img_sin&img_staged=$img_staged"); exit;
        }
    }

function regenerarJSON($pdo) {
    $stmt = $pdo->query("SELECT nombre, codigo, marca, categoria, imagen_url as imagen, presentaciones_raw, desc_larga FROM improgyp_catalogo WHERE publicado = 1 ORDER BY id DESC");
    $productos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $catalogoOficial = [];
    foreach ($productos_db as $row) {
        $presentaciones = []; 
        $lineas = explode("\n", $row['presentaciones_raw'] ?? '');
        foreach ($lineas as $l) { 
            if (!empty(trim($l))) { 
                $p = explode(":", $l, 2); 
                $presentaciones[] = ["opcion" => trim($p[0]), "precio" => trim($p[1] ?? "")]; 
            } 
        }
        if (empty($presentaciones)) { $presentaciones[] = ["opcion" => "Presentación Única", "precio" => ""]; }
        $catalogoOficial[] = [
            "nombre" => $row['nombre'], 
            "codigo" => $row['codigo'],
            "marca" => $row['marca'],
            "categoria" => $row['categoria'], 
            "imagen" => $row['imagen'], 
            "presentaciones" => $presentaciones, 
            "desc_larga" => $row['desc_larga']
        ];
    }
    file_put_contents(__DIR__ . '/catalogo.json', json_encode($catalogoOficial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    require_once __DIR__ . '/components/megamenu_config.php';
    improgyp_megamenu_refresh_orphan_session();
}

require_once __DIR__ . '/lib/copy_ia_helpers.php';

if (isset($_GET['ajax']) && $_GET['ajax'] === 'generar_copy') {
    header('Content-Type: application/json; charset=utf-8');
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $categoria = trim($data['categoria'] ?? '');
    $seccion = trim($data['seccion'] ?? '');

    if ($categoria === '' && $seccion === '') {
        echo json_encode(['error' => 'Parámetros vacíos'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $prompt = improgyp_construir_prompt_copy($data);
    $resultado = improgyp_gemini_generar_copy($prompt, $env, true);
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'cargar_chat_b2b') {
    header('Content-Type: application/json');
    $ruc = $_GET['ruc'] ?? '';
    if(!$ruc) { echo json_encode([]); exit; }
    $stmt = $pdo->prepare("SELECT * FROM b2b_historial_chat WHERE ruc_cliente = ? ORDER BY fecha ASC");
    $stmt->execute([$ruc]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

require_once __DIR__ . '/lib/bulk_catalogo_helpers.php';

if (isset($_GET['ajax']) && $_GET['ajax'] === 'bulk_staging_reset') {
    header('Content-Type: application/json; charset=utf-8');
    improgyp_bulk_imagen_map_clear();
    $payload = improgyp_bulk_staging_json_payload();
    $payload['status'] = 'success';
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'bulk_staging_count') {
    header('Content-Type: application/json; charset=utf-8');
    $payload = improgyp_bulk_staging_json_payload();
    $payload['status'] = 'success';
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'bulk_imagen_lote') {
    header('Content-Type: application/json; charset=utf-8');
    if (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) {
        echo json_encode(['error' => 'Error de seguridad (CSRF).'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $files = $_FILES['fotos'] ?? $_FILES['fotos_lote'] ?? null;
    if (!$files || !isset($files['tmp_name'])) {
        echo json_encode(['error' => 'No se recibieron archivos.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $count = is_array($files['tmp_name']) ? count($files['tmp_name']) : 1;
    if ($count > IMPROGYP_BULK_LOTE_MAX) {
        echo json_encode([
            'error' => 'Máximo ' . IMPROGYP_BULK_LOTE_MAX . ' fotos por lote. El sistema sube automáticamente en varias tandas.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $res = improgyp_bulk_procesar_files_array($files);
    if ($res['ok'] === 0 && $res['skip'] > 0) {
        $payload = improgyp_bulk_staging_json_payload(0, $res['skip'], $res['warnings']);
        $payload['error'] = 'Ninguna imagen del lote se pudo guardar. Revisa formato (JPG/PNG/WebP/GIF) y que GD/WebP esté activo en PHP.';
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
    $payload = improgyp_bulk_staging_json_payload($res['ok'], $res['skip'], $res['warnings']);
    $payload['status'] = 'success';
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'impulsar_producto') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $nombre = trim((string) ($data['nombre'] ?? ''));
    if ($nombre === '') {
        echo json_encode(['error' => 'Nombre de producto vacío']);
        exit;
    }

    $activos = (int) $pdo->query("SELECT COUNT(*) FROM productos_impulsados WHERE fecha_limite > NOW()")->fetchColumn();
    $chk = $pdo->prepare("SELECT id FROM productos_impulsados WHERE nombre_producto = ? LIMIT 1");
    $chk->execute([$nombre]);
    $ya_existe = (bool) $chk->fetchColumn();

    if ($activos >= 8 && !$ya_existe) {
        echo json_encode(['error' => 'Máximo 8 productos impulsados activos. Espera a que expire alguno.']);
        exit;
    }

    $fecha_limite = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $stmt = $pdo->prepare("INSERT INTO productos_impulsados (nombre_producto, fecha_limite) VALUES (?, ?) ON DUPLICATE KEY UPDATE fecha_limite = ?");
    $stmt->execute([$nombre, $fecha_limite, $fecha_limite]);

    echo json_encode(['status' => 'success', 'fecha_limite' => $fecha_limite]);
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'marcar_gestionado_b2b') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $session_id = $data['session_id'] ?? '';
    if (!$session_id) { echo json_encode(['error' => 'ID de sesión vacío']); exit; }
    $stmt = $pdo->prepare("UPDATE sesiones_b2b SET gestionado_admin = 1 WHERE session_id = ?");
    $stmt->execute([$session_id]);
    echo json_encode(['status' => 'success']);
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'limpiar_cache') {
    header('Content-Type: application/json');
    $archivos = [
        __DIR__ . '/cache_ranking.json',
        __DIR__ . '/cache_catalogo_lite.json',
        __DIR__ . '/b2b/cache_b2b_catalogo.json'
    ];
    $borrados = 0;
    foreach ($archivos as $f) {
        if (file_exists($f)) {
            unlink($f);
            $borrados++;
        }
    }
    echo json_encode(['status' => 'success', 'borrados' => $borrados]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) die("Error CSRF.");

    if ($_POST['action'] === 'limpiar_historial_b2b') {
        $ruc = trim($_POST['ruc_cliente']);
        $pdo->prepare("DELETE FROM b2b_historial_chat WHERE ruc_cliente = ?")->execute([$ruc]);
        header("Location: dashboard.php?view=distribuidores&msg=historial_limpio"); exit;
    }

    if ($_POST['action'] === 'guardar_mantenimiento') {
        $estado = (int)$_POST['estado'];
        $est = improgyp_b2b_estado_load();
        $est['mantenimiento'] = ($estado === 1);
        file_put_contents(improgyp_b2b_estado_path(), json_encode($est, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: dashboard.php?view=sistema&msg=estado_actualizado"); exit;
    }

    if ($_POST['action'] === 'guardar_b2b_estado') {
        $est = improgyp_b2b_estado_load();
        $est['b2b_publico_activo'] = isset($_POST['b2b_publico_activo']);
        $est['b2b_pilot_rucs'] = trim((string)($_POST['b2b_pilot_rucs'] ?? ''));
        file_put_contents(improgyp_b2b_estado_path(), json_encode($est, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: dashboard.php?view=sistema&msg=b2b_estado"); exit;
    }

    if ($_POST['action'] === 'eliminar_impulso_ad') {
        $nombre = $_POST['nombre_producto'];
        $pdo->prepare("DELETE FROM productos_impulsados WHERE nombre_producto = ?")->execute([$nombre]);
        header("Location: dashboard.php?view=ads&msg=impulso_eliminado"); exit;
    }

    // GUARDADO DINÁMICO DE ADS (MULTI-AD SUPPORT)
    if ($_POST['action'] === 'guardar_ad') {
        $ad_data = ['videos' => [], 'banners' => []];
        
        // 1. PROCESAR VIDEOS (Discovery Ads)
        if (isset($_POST['video_link'])) {
            foreach ($_POST['video_link'] as $i => $link) {
                $url_actual = $_POST['video_url_actual'][$i] ?? '';
                $activo = isset($_POST['video_activo'][$i]);
                $pos = (int)($_POST['video_pos'][$i] ?? 0);

                if (isset($_FILES['video_archivo']['tmp_name'][$i]) && $_FILES['video_archivo']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['video_archivo']['tmp_name'][$i];
                    
                    // Seguridad: Validación de Mime Type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmp_name);
                    finfo_close($finfo);
                    $valid_mimes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-matroska'];
                    
                    if (in_array($mime, $valid_mimes)) {
                        $ext = strtolower(pathinfo($_FILES['video_archivo']['name'][$i], PATHINFO_EXTENSION));
                        $nombre_archivo = "v_" . time() . "_" . $i . "." . $ext;
                        if (!is_dir(__DIR__ . '/ads_media')) mkdir(__DIR__ . '/ads_media', 0755, true);
                        
                        if (move_uploaded_file($tmp_name, __DIR__ . '/ads_media/' . $nombre_archivo)) {
                            // Limpieza física del video anterior
                            if (!empty($url_actual)) borrarFotoFisica($url_actual);
                            $url_actual = "ads_media/" . $nombre_archivo;
                        }
                    }
                }
                if (!empty($url_actual)) {
                    $ad_data['videos'][] = [
                        'url'    => $url_actual,
                        'link'   => trim($link),
                        'activo' => $activo,
                        'pos'    => $pos
                    ];
                }
            }
        }

        // 2. PROCESAR BANNERS (Rompetráfico)
        if (isset($_POST['banner_titulo'])) {
            foreach ($_POST['banner_titulo'] as $i => $titulo) {
                if (!empty(trim($titulo))) {
                    $img_url = $_POST['banner_img_actual'][$i] ?? '';
                    
                    // Procesar nueva imagen si existe
                    if (isset($_FILES['banner_img']['tmp_name'][$i]) && $_FILES['banner_img']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['banner_img']['tmp_name'][$i];
                        $img_gd = @imagecreatefromstring(file_get_contents($tmp_name));
                        if ($img_gd !== false) {
                            if (!is_dir(__DIR__ . '/ads_media')) mkdir(__DIR__ . '/ads_media', 0755, true);
                            $nombre_archivo = "banner_" . time() . "_" . $i . ".webp";
                            if (imagewebp($img_gd, __DIR__ . '/ads_media/' . $nombre_archivo, 85)) {
                                if (!empty($img_url)) borrarFotoFisica($img_url);
                                $img_url = "ads_media/" . $nombre_archivo;
                            }
                            imagedestroy($img_gd);
                        }
                    }

                    $estiloRaw = $_POST['banner_estilo'][$i] ?? 'respiracion';
                    $estilosOk = ['respiracion', 'split', 'marquee', 'glass'];
                    $cadaNFilas = (int)($_POST['banner_cada_n_filas'][$i] ?? 4);
                    $cadaNFilas = max(1, min(20, $cadaNFilas));

                    $ad_data['banners'][] = [
                        'estilo'       => in_array($estiloRaw, $estilosOk, true) ? $estiloRaw : 'respiracion',
                        'etiqueta'     => trim($_POST['banner_etiqueta'][$i] ?? ''),
                        'titulo'       => trim($titulo),
                        'desc'         => trim($_POST['banner_desc'][$i] ?? ''),
                        'extra'        => trim($_POST['banner_extra'][$i] ?? ''),
                        'img_url'      => $img_url,
                        'link'         => trim($_POST['banner_link'][$i] ?? ''),
                        'activo'       => isset($_POST['banner_activo'][$i]),
                        'cada_n_filas' => $cadaNFilas
                    ];
                }
            }
        }

        // 3. PAUTA B2B (Mayoristas)
        $ad_data['b2b_activo'] = isset($_POST['b2b_activo']);
        $ad_data['b2b_pos']    = (int)($_POST['b2b_pos'] ?? 25);
        $ad_data['b2b_etiqueta'] = trim($_POST['b2b_etiqueta'] ?? 'MAYORISTAS');
        $ad_data['b2b_btn']      = trim($_POST['b2b_btn'] ?? 'Acceder al Portal');
        $ad_data['b2b_img_url']  = $_POST['b2b_img_url_actual'] ?? '';
        
        if (isset($_FILES['b2b_img']) && $_FILES['b2b_img']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['b2b_img']['tmp_name']; 
            $img_gd = @imagecreatefromstring(file_get_contents($tmp_name));
            if ($img_gd !== false) {
                if (!is_dir(__DIR__ . '/ads_media')) mkdir(__DIR__ . '/ads_media', 0755, true);
                $nombre_archivo = "b2b_bg_" . time() . ".webp"; 
                if (imagewebp($img_gd, __DIR__ . '/ads_media/' . $nombre_archivo, 85)) {
                    // Limpieza física de la imagen B2B anterior
                    if (!empty($ad_data['b2b_img_url'])) borrarFotoFisica($ad_data['b2b_img_url']);
                    $ad_data['b2b_img_url'] = "ads_media/" . $nombre_archivo;
                }
                imagedestroy($img_gd);
            }
        }

        // 4. PAUTA IA (Asistente)
        $ad_data['ia_activo']   = isset($_POST['ia_activo']);
        $ad_data['ia_etiqueta'] = trim($_POST['ia_etiqueta'] ?? 'Asesoría Gratuita');
        $ad_data['ia_titulo']   = trim($_POST['ia_titulo'] ?? '');
        $ad_data['ia_desc']     = trim($_POST['ia_desc'] ?? '');
        $ad_data['ia_btn']      = trim($_POST['ia_btn'] ?? 'Consultar a la IA');

        // PERSISTENCIA
        file_put_contents(__DIR__ . '/ads.json', json_encode($ad_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        header("Location: dashboard.php?view=ads&msg=ad_guardado"); exit;
    }

    if ($_POST['action'] === 'guardar_seo') {
        $seo_data = []; 
        if (file_exists(__DIR__ . '/seo.json')) { 
            $seo_data = json_decode(file_get_contents(__DIR__ . '/seo.json'), true); 
        }
        $seo_data['titulo'] = trim($_POST['titulo_seo']); 
        $seo_data['descripcion'] = trim($_POST['desc_seo']);
        
        if (isset($_FILES['foto_seo']) && $_FILES['foto_seo']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['foto_seo']['tmp_name']; 
            $img_gd = @imagecreatefromstring(file_get_contents($tmp_name));
            if ($img_gd !== false) {
                if (!is_dir(__DIR__ . '/img_seo')) mkdir(__DIR__ . '/img_seo', 0755, true);
                $nombre_archivo = "cover_" . time() . ".jpg"; 
                $ruta_fisica = __DIR__ . '/img_seo/' . $nombre_archivo;
                imagepalettetotruecolor($img_gd); 
                imagealphablending($img_gd, true); 
                imagejpeg($img_gd, $ruta_fisica, 90); 
                imagedestroy($img_gd);
                $seo_data['imagen_url'] = "img_seo/" . $nombre_archivo;
            }
        }
        file_put_contents(__DIR__ . '/seo.json', json_encode($seo_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        header("Location: dashboard.php?view=seo&msg=seo_guardado"); exit;
    }

    if ($_POST['action'] === 'guardar_megamenu') {
        require_once __DIR__ . '/components/megamenu_config.php';
        $existing = file_exists(__DIR__ . '/config_header.json') ? json_decode(file_get_contents(__DIR__ . '/config_header.json'), true) : [];
        if (!is_array($existing)) $existing = [];
        $raw = json_decode($_POST['megamenu_json'] ?? '[]', true);
        $existing['megamenu'] = improgyp_normalize_megamenu($raw);
        if (isset($_POST['nivel3_json'])) {
            $n3raw = json_decode($_POST['nivel3_json'] ?? '[]', true);
            $existing['nivel3_menu'] = improgyp_normalize_nivel3_menu($n3raw);
        }
        file_put_contents(__DIR__ . '/config_header.json', json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        improgyp_megamenu_refresh_orphan_session();
        header("Location: dashboard.php?view=apariencia&sub=megamenu&msg=guardado"); exit;
    }

    if (
        $_POST['action'] === 'guardar_home_landing'
        || $_POST['action'] === 'guardar_landing'
    ) {
        require_once __DIR__ . '/lib/landing_save.php';
        $applyEncabezados = $_POST['action'] === 'guardar_home_landing';
        $payload = improgyp_landing_build_payload_from_post($_POST, $_FILES, $applyEncabezados);
        improgyp_landing_write_config($payload);
        header('Location: dashboard.php?view=apariencia&sub=home&msg=home_guardado');
        exit;
    }

    if ($_POST['action'] === 'guardar_secciones_landing') {
        require_once __DIR__ . '/lib/landing_save.php';
        improgyp_landing_save_encabezados_only($_POST);
        header('Location: dashboard.php?view=apariencia&sub=home&msg=home_guardado');
        exit;
    }

    if ($_POST['action'] === 'guardar_apariencia_blog') {
        require_once __DIR__ . '/lib/blog_layout_slots.php';
        $accent = trim($_POST['accent'] ?? '#3a86ff');
        $hex = ltrim($accent, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $cfg = [
            'layout' => blog_layout_normalize($_POST['layout'] ?? 'editorial'),
            'accent' => $accent,
            'accentRgb' => "$r, $g, $b",
            'font' => in_array($_POST['font'] ?? 'sans', ['sans', 'serif', 'mono'], true) ? $_POST['font'] : 'sans',
            'showDate' => isset($_POST['showDate']),
            'showReadTime' => isset($_POST['showReadTime']),
            'showViews' => isset($_POST['showViews']),
        ];
        file_put_contents(__DIR__ . '/config_blog.json', json_encode($cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        header('Location: dashboard.php?view=apariencia&sub=blog&msg=blog_guardado'); exit;
    }

    if ($_POST['action'] === 'blog_guardar') {
        require_once __DIR__ . '/lib/blog_helpers.php';
        blog_ensure_table($pdo);
        $id = (int) ($_POST['blog_id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        if ($titulo === '') {
            header('Location: dashboard.php?view=blog&msg=error_titulo'); exit;
        }
        $slug = blog_unique_slug($pdo, $titulo, $id);
        $portada = trim($_POST['portada_actual'] ?? 'favicon-app.png?v=5');
        if (!empty($_FILES['portada']['tmp_name']) && is_uploaded_file($_FILES['portada']['tmp_name'])) {
            $dir = __DIR__ . '/uploads/blog';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['portada']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) $ext = 'webp';
            $fname = 'blog_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
            if (move_uploaded_file($_FILES['portada']['tmp_name'], $dir . '/' . $fname)) {
                $portada = 'uploads/blog/' . $fname;
            }
        }
        $data = [
            trim($_POST['categoria'] ?? 'General'),
            trim($_POST['tiempo_lectura'] ?? '5 min'),
            trim($_POST['resumen'] ?? ''),
            $_POST['contenido'] ?? '',
            $portada,
            isset($_POST['borrador']) ? 1 : 0,
        ];
        if ($id > 0) {
            $pdo->prepare(
                'UPDATE improgyp_blog SET titulo=?, slug=?, categoria=?, tiempo_lectura=?, resumen=?, contenido=?, portada=?, borrador=? WHERE id=?'
            )->execute(array_merge([$titulo, $slug], $data, [$id]));
        } else {
            $pdo->prepare(
                'INSERT INTO improgyp_blog (titulo, slug, categoria, tiempo_lectura, resumen, contenido, portada, borrador) VALUES (?,?,?,?,?,?,?,?)'
            )->execute(array_merge([$titulo, $slug], $data));
        }
        header('Location: dashboard.php?view=blog&msg=guardado'); exit;
    }

    if ($_POST['action'] === 'blog_eliminar') {
        $id = (int) ($_POST['blog_id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM improgyp_blog WHERE id = ?')->execute([$id]);
        }
        header('Location: dashboard.php?view=blog&msg=eliminado'); exit;
    }

    if ($_POST['action'] === 'guardar_local') {
        require_once __DIR__ . '/includes/locales_cobertura.php';
        require_once __DIR__ . '/includes/whatsapp_normalize.php';
        require_once __DIR__ . '/lib/locales_imagen.php';
        $locales_path = __DIR__ . '/locales.json';
        $locales = file_exists($locales_path) ? json_decode(file_get_contents($locales_path), true) : [];
        if (!is_array($locales)) {
            $locales = [];
        }

        $id = trim($_POST['id'] ?? '');
        $newId = $id ?: ('loc_' . bin2hex(random_bytes(4)));
        $ciudad = trim($_POST['ciudad']);
        $cobertura = improgyp_merge_cobertura_ciudad(
            improgyp_parse_cobertura_post($_POST['cobertura'] ?? ''),
            $ciudad
        );

        $imagenActual = '';
        if ($id) {
            foreach ($locales as $l) {
                if (($l['id'] ?? '') === $id) {
                    $imagenActual = trim((string) ($l['imagen'] ?? ''));
                    break;
                }
            }
        }
        $quitarImagen = !empty($_POST['quitar_imagen']);
        $imagen = improgyp_local_imagen_guardar(
            $_FILES['imagen'] ?? null,
            $newId,
            $imagenActual ?: trim($_POST['imagen_actual'] ?? ''),
            $quitarImagen
        );

        $nuevo_local = [
            'id' => $newId,
            'nombre' => trim($_POST['nombre']),
            'direccion' => trim($_POST['direccion']),
            'ciudad' => $ciudad,
            'cobertura' => $cobertura,
            'telefono' => trim($_POST['telefono']),
            'email' => trim($_POST['email']),
            'lat' => (float) $_POST['lat'],
            'lng' => (float) $_POST['lng'],
            'whatsapp' => improgyp_normalize_whatsapp(trim($_POST['whatsapp'] ?: '593991754887')),
            'whatsapp_msj' => trim($_POST['whatsapp_msj'] ?? ''),
            'maps' => trim($_POST['maps']),
            'horario' => trim($_POST['horario']),
        ];
        if ($imagen !== '') {
            $nuevo_local['imagen'] = $imagen;
        }

        if ($id) {
            $found = false;
            foreach ($locales as $idx => $l) {
                if (($l['id'] ?? '') === $id) {
                    $locales[$idx] = $nuevo_local;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $locales[] = $nuevo_local;
            }
        } else {
            $locales[] = $nuevo_local;
        }

        file_put_contents($locales_path, json_encode(array_values($locales), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        header('Location: dashboard.php?view=locales&msg=local_guardado');
        exit;
    }

    if ($_POST['action'] === 'eliminar_local') {
        require_once __DIR__ . '/lib/locales_imagen.php';
        $id = $_POST['id'];
        $locales_path = __DIR__ . '/locales.json';
        $locales = file_exists($locales_path) ? json_decode(file_get_contents($locales_path), true) : [];
        if (!is_array($locales)) {
            $locales = [];
        }
        foreach ($locales as $l) {
            if (($l['id'] ?? '') === $id && !empty($l['imagen'])) {
                improgyp_local_imagen_borrar($l['imagen']);
                break;
            }
        }
        $locales = array_values(array_filter($locales, static function ($l) use ($id) {
            return ($l['id'] ?? '') !== $id;
        }));
        file_put_contents($locales_path, json_encode($locales, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        header('Location: dashboard.php?view=locales&msg=local_eliminado');
        exit;
    }

    if ($_POST['action'] === 'guardar_textos_ia') {
        $textos = [];
        foreach ($_POST['tit_normal'] as $cat => $tit_norm) { 
            $resaltado = $_POST['tit_resaltado'][$cat] ?? ''; 
            $sub = $_POST['subtitulos'][$cat] ?? '';
            $tit_completo = trim($tit_norm) . " <br class='hidden md:block'> <span class='laser-text'>" . trim($resaltado) . "</span>";
            $textos[$cat] = ['tit' => $tit_completo, 'sub' => trim($sub)]; 
        }
        $tit_todos_completo = trim($_POST['tit_todos_normal']) . " <br class='hidden md:block'> <span class='laser-text'>" . trim($_POST['tit_todos_resaltado']) . "</span>";
        $textos['Todos'] = ['tit' => $tit_todos_completo, 'sub' => trim($_POST['sub_todos'])];
        file_put_contents(__DIR__ . '/textos_tienda.json', json_encode($textos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: dashboard.php?view=marketing&msg=textos_guardados"); exit;
    }

    if ($_POST['action'] === 'crear_usuario_b2b') {
        if (!improgyp_b2b_admin_puede_gestionar()) {
            header("Location: dashboard.php?view=distribuidores&err=b2b_solo_lectura"); exit;
        }
        $ruc = trim($_POST['ruc']);
        $nombre = trim($_POST['nombre']);
        $pin = trim($_POST['pin'] ?? '');
        $descuento = (float)$_POST['descuento'];
        $telefono = preg_replace('/[^0-9]/', '', $_POST['telefono']);
        $stmtEx = $pdo->prepare("SELECT id, pin FROM usuarios_b2b WHERE ruc = ? LIMIT 1");
        $stmtEx->execute([$ruc]);
        $existente = $stmtEx->fetch(PDO::FETCH_ASSOC);
        if ($existente) {
            if ($pin === '') {
                $pdo->prepare("UPDATE usuarios_b2b SET nombre=?, descuento=?, telefono=? WHERE ruc=?")->execute([$nombre, $descuento, $telefono, $ruc]);
            } else {
                $pinHash = improgyp_b2b_hash_pin($pin);
                $pdo->prepare("UPDATE usuarios_b2b SET nombre=?, pin=?, descuento=?, telefono=? WHERE ruc=?")->execute([$nombre, $pinHash, $descuento, $telefono, $ruc]);
            }
        } else {
            if ($pin === '') {
                header("Location: dashboard.php?view=distribuidores&err=pin_requerido"); exit;
            }
            $pinHash = improgyp_b2b_hash_pin($pin);
            $pdo->prepare("INSERT INTO usuarios_b2b (ruc, nombre, pin, descuento, telefono, activo) VALUES (?, ?, ?, ?, ?, 1)")->execute([$ruc, $nombre, $pinHash, $descuento, $telefono]);
            $_SESSION['flash_b2b_pin'] = ['ruc' => $ruc, 'nombre' => $nombre, 'pin' => $pin];
        }
        header("Location: dashboard.php?view=distribuidores&msg=b2b_creado"); exit;
    }

    if ($_POST['action'] === 'toggle_usuario_b2b') {
        if (!improgyp_b2b_admin_puede_gestionar()) {
            header("Location: dashboard.php?view=distribuidores&err=b2b_solo_lectura"); exit;
        }
        $pdo->prepare("UPDATE usuarios_b2b SET activo = ? WHERE id = ?")->execute([(int)$_POST['activo'], (int)$_POST['id_b2b']]);
        header("Location: dashboard.php?view=distribuidores&msg=b2b_estado_usuario"); exit;
    }
    
    if ($_POST['action'] === 'eliminar_usuario_b2b') {
        if (!improgyp_b2b_admin_puede_gestionar()) {
            header("Location: dashboard.php?view=distribuidores&err=b2b_solo_lectura"); exit;
        }
        $pdo->prepare("DELETE FROM usuarios_b2b WHERE id = ?")->execute([(int)$_POST['id_b2b']]); 
        header("Location: dashboard.php?view=distribuidores&msg=b2b_eliminado"); exit; 
    }

    if ($_POST['action'] === 'toggle_publicado') { 
        $pdo->prepare("UPDATE improgyp_catalogo SET publicado = ? WHERE id = ?")->execute([(int)$_POST['estado'], (int)$_POST['id_producto']]); 
        regenerarJSON($pdo); echo "ok"; exit; 
    }
    
    if ($_POST['action'] === 'crear_producto' || $_POST['action'] === 'editar_producto') {
        $nombre = trim($_POST['nombre']); 
        $codigo = trim($_POST['codigo']);
        $marca = trim($_POST['marca']);
        $categoria = $_POST['categoria']; 
        $desc_larga = trim($_POST['desc_larga']); 
        $url_local = $_POST['imagen_actual'] ?? ''; 
        $id_editar = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
        
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['foto']['tmp_name']; 
            $img_gd = @imagecreatefromstring(file_get_contents($tmp_name));
            if ($img_gd !== false) {
                if (!is_dir(__DIR__ . '/img_catalogo')) mkdir(__DIR__ . '/img_catalogo', 0755, true);
                $nombre_archivo = "webp_" . time() . "_" . substr(preg_replace('/[^A-Za-z0-9\-]/', '_', strtolower($nombre)), 0, 15) . ".webp"; 
                $ruta_fisica = __DIR__ . '/img_catalogo/' . $nombre_archivo;
                imagepalettetotruecolor($img_gd); imagealphablending($img_gd, false); imagesavealpha($img_gd, true);
                imagewebp($img_gd, $ruta_fisica, 75); imagedestroy($img_gd);
                $url_local = "img_catalogo/" . $nombre_archivo;
            }
        }

        // Procesar presentaciones dinámicas
        $presentaciones = [];
        if (isset($_POST['pres_opcion'])) {
            foreach ($_POST['pres_opcion'] as $i => $opcion) {
                $precio = $_POST['pres_precio'][$i] ?? '';
                if (!empty(trim($opcion))) {
                    $presentaciones[] = trim($opcion) . ":" . preg_replace('/[^\d.]/', '', $precio);
                }
            }
        }
        $pres_final = implode("\n", $presentaciones);
        
        if ($_POST['action'] === 'crear_producto') { 
            $pdo->prepare("INSERT INTO improgyp_catalogo (nombre, codigo, marca, categoria, presentaciones_raw, desc_larga, imagen_url, publicado) VALUES (?, ?, ?, ?, ?, ?, ?, 1)")->execute([$nombre, $codigo, $marca, $categoria, $pres_final, $desc_larga, $url_local]);
        } else {
            // Limpieza de foto física anterior si se subió una nueva
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $stmt_old = $pdo->prepare("SELECT imagen_url FROM improgyp_catalogo WHERE id = ?");
                $stmt_old->execute([$id_editar]);
                $old_img = $stmt_old->fetchColumn();
                if ($old_img && $old_img !== $url_local) {
                    borrarFotoFisica($old_img);
                }
            }
            $pdo->prepare("UPDATE improgyp_catalogo SET nombre=?, codigo=?, marca=?, categoria=?, presentaciones_raw=?, desc_larga=?, imagen_url=? WHERE id=?")->execute([$nombre, $codigo, $marca, $categoria, $pres_final, $desc_larga, $url_local, $id_editar]); 
        }
        if (!empty($categoria)) { $pdo->prepare("INSERT IGNORE INTO categorias_admin (nombre) VALUES (?)")->execute([$categoria]); }
        if (!empty($marca)) { $pdo->prepare("INSERT IGNORE INTO marcas_admin (nombre) VALUES (?)")->execute([$marca]); }
        regenerarJSON($pdo); 
        header("Location: dashboard.php?view=catalogo&msg=guardado"); exit;
    }

    if ($_POST['action'] === 'crear_categoria') {
        $nombre = trim($_POST['nombre_categoria']);
        if (!empty($nombre)) {
            $pdo->prepare("INSERT IGNORE INTO categorias_admin (nombre) VALUES (?)")->execute([$nombre]);
        }
        header("Location: dashboard.php?view=" . $vista . "&msg=categoria_creada"); exit;
    }

    if ($_POST['action'] === 'eliminar_categoria') {
        $id_cat = (int)$_POST['id_categoria'];
        $pdo->prepare("DELETE FROM categorias_admin WHERE id = ?")->execute([$id_cat]);
        header("Location: dashboard.php?view=" . $vista . "&msg=categoria_eliminada"); exit;
    }
    
    if ($_POST['action'] === 'eliminar_producto') { 
        $id_del = (int)$_POST['id_producto'];
        $stmt_img = $pdo->prepare("SELECT imagen_url FROM improgyp_catalogo WHERE id = ?");
        $stmt_img->execute([$id_del]);
        borrarFotoFisica($stmt_img->fetchColumn());
        
        $pdo->prepare("DELETE FROM improgyp_catalogo WHERE id = ?")->execute([$id_del]); 
        regenerarJSON($pdo); 
        header("Location: dashboard.php?view=catalogo&msg=eliminado"); exit; 
    }

    if ($_POST['action'] === 'eliminar_masivo') {
        $ids = $_POST['ids_productos'] ?? [];
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            // Limpieza de fotos físicas en lote
            $stmt_imgs = $pdo->prepare("SELECT imagen_url FROM improgyp_catalogo WHERE id IN ($placeholders)");
            $stmt_imgs->execute($ids);
            while($img = $stmt_imgs->fetchColumn()) { borrarFotoFisica($img); }

            $stmt = $pdo->prepare("DELETE FROM improgyp_catalogo WHERE id IN ($placeholders)");
            $stmt->execute(array_map('intval', $ids));
            regenerarJSON($pdo);
            header("Location: dashboard.php?view=catalogo&msg=eliminado_masivo"); exit;
        }
    }

    if ($_POST['action'] === 'vaciar_catalogo') {
        $pdo->exec("TRUNCATE TABLE improgyp_catalogo");
        regenerarJSON($pdo);
        header("Location: dashboard.php?view=catalogo&msg=purga_completa"); exit;
    }

    if ($_POST['action'] === 'crear_marca') {
        $nombre = trim($_POST['nombre_marca']);
        if (!empty($nombre)) {
            $pdo->prepare("INSERT IGNORE INTO marcas_admin (nombre) VALUES (?)")->execute([$nombre]);
        }
        header("Location: dashboard.php?view=catalogo&msg=marca_creada"); exit;
    }

    if ($_POST['action'] === 'eliminar_marca') {
        $nombre = $_POST['nombre_marca'];
        $pdo->prepare("DELETE FROM marcas_admin WHERE nombre = ?")->execute([$nombre]);
        header("Location: dashboard.php?view=catalogo&msg=marca_eliminada"); exit;
    }

    if ($_POST['action'] === 'actualizar_status_pedido') {
        $stmt = $pdo->prepare("UPDATE pedidos_b2b SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['nuevo_status'], (int)$_POST['id_pedido']]);
        header("Location: dashboard.php?view=pedidos&msg=status_actualizado"); exit;
    }

    // --- ACCIONES DE GESTIÓN DE USUARIOS (SOLO MASTER) ---
    if (isset($_SESSION['admin_rol']) && $_SESSION['admin_rol'] === 'master') {
        if ($_POST['action'] === 'crear_usuario_admin') {
            $stmt_count = $pdo->query("SELECT COUNT(*) FROM usuarios_admin");
            if ($stmt_count->fetchColumn() >= 4) { header("Location: dashboard.php?view=usuarios&err=limite"); exit; }
            $user = trim($_POST['usuario']);
            $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $nombre = trim($_POST['nombre']);
            $stmt = $pdo->prepare("INSERT INTO usuarios_admin (usuario, password_hash, nombre, rol) VALUES (?, ?, ?, 'gestor')");
            $stmt->execute([$user, $pass, $nombre]);
            header("Location: dashboard.php?view=usuarios&msg=creado"); exit;
        }
        if ($_POST['action'] === 'editar_usuario_admin') {
            $id_u = $_POST['id_usuario'];
            $nombre = trim($_POST['nombre']);
            $user = trim($_POST['usuario']);
            if (!empty($_POST['password'])) {
                $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE usuarios_admin SET nombre = ?, usuario = ?, password_hash = ? WHERE id = ?")->execute([$nombre, $user, $pass, $id_u]);
            } else {
                $pdo->prepare("UPDATE usuarios_admin SET nombre = ?, usuario = ? WHERE id = ?")->execute([$nombre, $user, $id_u]);
            }
            header("Location: dashboard.php?view=usuarios&msg=editado"); exit;
        }
        if ($_POST['action'] === 'toggle_usuario_admin') {
            $id_u = (int)$_POST['id_usuario'];
            $estado = (int)$_POST['estado'];
            if ($id_u != $_SESSION['admin_id']) { $pdo->prepare("UPDATE usuarios_admin SET activo = ? WHERE id = ?")->execute([$estado, $id_u]); }
            exit;
        }
        if ($_POST['action'] === 'eliminar_usuario_admin') {
            $id_u = (int)$_POST['id_usuario'];
            if ($id_u != $_SESSION['admin_id']) { $pdo->prepare("DELETE FROM usuarios_admin WHERE id = ? AND rol != 'master'")->execute([$id_u]); }
            header("Location: dashboard.php?view=usuarios&msg=eliminado"); exit;
        }
    }
}

// LÓGICA DE VISTAS
function menuActivo($vista_actual, $menu) { 
    return $vista_actual === $menu ? 'bg-[#1B263B]/10 text-[#1B263B] border-[#1B263B]' : 'text-slate-500 hover:bg-slate-50 hover:text-[#1B263B] border-transparent'; 
}

function menuActivoRadar(string $vista_actual): string {
    return in_array($vista_actual, ['radar', 'inventario_fantasma'], true)
        ? 'bg-[#1B263B]/10 text-[#1B263B] border-[#1B263B]'
        : 'text-slate-500 hover:bg-slate-50 hover:text-[#1B263B] border-transparent';
}

function menuSubApariencia(bool $activo): string {
    return $activo
        ? 'bg-violet-50 text-violet-700 border-violet-400'
        : 'border-transparent hover:bg-slate-50 text-slate-500 hover:text-violet-700';
}

// Obtener categorías centralizadas de la DB
$stmtCats = $pdo->query("SELECT nombre FROM categorias_admin ORDER BY nombre ASC");
$categorias_admin = $stmtCats->fetchAll(PDO::FETCH_COLUMN) ?: [];

// Obtener marcas centralizadas de la DB
$stmtMarcas = $pdo->query("SELECT nombre FROM marcas_admin ORDER BY nombre ASC");
$marcas_admin = $stmtMarcas->fetchAll(PDO::FETCH_COLUMN) ?: [];

$categorias_maestras = $pdo->query("SELECT * FROM categorias_admin ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
$nombres_categorias = array_column($categorias_maestras, 'nombre');

if ($vista === 'blog' && isset($_GET['ajax']) && $_GET['ajax'] === 'articulo') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM improgyp_blog WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => (bool) $row, 'data' => $row], JSON_UNESCAPED_UNICODE);
    exit;
}

elseif ($vista === 'catalogo') { 
    $catalogo_local = $pdo->query("SELECT * FROM improgyp_catalogo ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    require_once __DIR__ . '/components/megamenu_config.php';
    improgyp_megamenu_refresh_orphan_session();
}
elseif ($vista === 'seo') { 
    $seo_guardado = []; 
    if (file_exists(__DIR__ . '/seo.json')) { $seo_guardado = json_decode(file_get_contents(__DIR__ . '/seo.json'), true); } 
}
elseif ($vista === 'ads') { 
    $ad_guardado = []; 
    if (file_exists(__DIR__ . '/ads.json')) { $ad_guardado = json_decode(file_get_contents(__DIR__ . '/ads.json'), true); } 
    $catalogo_local = $pdo->query("SELECT nombre FROM improgyp_catalogo WHERE publicado = 1 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
    $productos_impulsados = $pdo->query("SELECT * FROM productos_impulsados WHERE fecha_limite > NOW() ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
}
elseif ($vista === 'radar') {
    require_once __DIR__ . '/lib/radar_helpers.php';
    $periodo = improgyp_radar_periodo_valido($_GET['periodo'] ?? '7d');
    extract(improgyp_radar_load($pdo, $periodo), EXTR_OVERWRITE);
}
elseif ($vista === 'inventario_fantasma') {
    require_once __DIR__ . '/lib/radar_helpers.php';
    $fantasma_q = trim((string) ($_GET['q'] ?? ''));
    $fantasma_page = max(1, (int) ($_GET['page'] ?? 1));
    $fantasma_per_page = IMPROGYP_FANTASMA_PAGE_SIZE;
    $fantasma_offset = ($fantasma_page - 1) * $fantasma_per_page;
    $fantasmaPack = improgyp_productos_fantasma_fetch($pdo, $fantasma_per_page, $fantasma_offset, $fantasma_q);
    $productos_fantasma = $fantasmaPack['items'];
    $productos_fantasma_total = $fantasmaPack['total'];
    $fantasma_pages = max(1, (int) ceil($productos_fantasma_total / $fantasma_per_page));
    if ($fantasma_page > $fantasma_pages && $productos_fantasma_total > 0) {
        header('Location: dashboard.php?' . http_build_query([
            'view' => 'inventario_fantasma',
            'page' => $fantasma_pages,
            'q' => $fantasma_q,
        ]));
        exit;
    }
}

// LÓGICA COMPARTIDA B2B (Mesa de Dinero y KPIs VIP)
$pedidos_b2b_recientes = [];
if ($vista === 'radar' || $vista === 'distribuidores') {
    $dinero_mesa = []; $top_vips_conversion = []; $ticket_promedio_b2b = 0; $productos_estrella_b2b = [];
    try {
        // Agrupamos por session_id para detectar cotizaciones coherentes
        // COALESCE asegura que si no hay sesión linkera (clic_whatsapp), igual se cuente como 0
        // Usamos MAX() para compatibilidad con ONLY_FULL_GROUP_BY
        $dinero_mesa = $pdo->query("SELECT MAX(c.ruc_cliente) as ruc_cliente, SUM(c.subtotal) as total_cotizado, SUM(c.cantidad) as items, MAX(c.fecha) as fecha_cot, MAX(COALESCE(s.clic_whatsapp, 0)) as convertido, MAX(COALESCE(s.gestionado_admin, 0)) as gestionado, c.session_id as main_sess, MAX(u.nombre) as cliente_nombre, MAX(u.telefono) as cliente_telefono, MAX(u.descuento) as cliente_descuento 
                                    FROM metricas_cotizaciones c 
                                    LEFT JOIN sesiones_b2b s ON c.session_id = s.session_id 
                                    LEFT JOIN usuarios_b2b u ON c.ruc_cliente = u.ruc 
                                    GROUP BY c.session_id 
                                    HAVING convertido = 0 AND gestionado = 0 
                                    ORDER BY fecha_cot DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

        $top_vips_conversion = $pdo->query("SELECT MAX(u.nombre) as nombre, COUNT(DISTINCT c.session_id) as total_cotizaciones,
                                            COUNT(DISTINCT CASE WHEN COALESCE(s.clic_whatsapp,0) = 1 THEN c.session_id END) as convertidas
                                            FROM usuarios_b2b u 
                                            LEFT JOIN metricas_cotizaciones c ON u.ruc = c.ruc_cliente 
                                            LEFT JOIN sesiones_b2b s ON c.session_id = s.session_id 
                                            GROUP BY u.ruc 
                                            HAVING total_cotizaciones > 0
                                            ORDER BY convertidas DESC, total_cotizaciones DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

        // Ticket Promedio: Filtramos los que tengan 0 para no sesgar la media de ventas reales
        $ticket_promedio_b2b = $pdo->query("SELECT AVG(total_pedido) FROM (SELECT SUM(subtotal) as total_pedido FROM metricas_cotizaciones GROUP BY session_id HAVING total_pedido > 1) as sub")->fetchColumn() ?: 0;
        
        $productos_estrella_b2b = $pdo->query("SELECT producto_nombre, SUM(cantidad) as total FROM metricas_cotizaciones GROUP BY producto_nombre ORDER BY total DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

        if ($vista === 'distribuidores') {
            $pedidos_b2b_recientes = $pdo->query("SELECT id, ruc_cliente, nombre_cliente, total, status, fecha FROM pedidos_b2b ORDER BY fecha DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) { 
        // Debug temporal si falla algo (se puede comentar después)
        error_log("Dash B2B Error: " . $e->getMessage());
    }
}

// INICIALIZACIÓN DE VARIABLES DE VISTA (Evitar Undefined Variable)
$pedidos = []; $pedidos_publicos = []; $usuarios_b2b = []; $catalogo_local = []; 
$seo_guardado = []; $ad_guardado = [];

if ($vista === 'distribuidores') { 
    try { $usuarios_b2b = $pdo->query("SELECT * FROM usuarios_b2b ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC); } catch(Exception $e) {}
}
elseif ($vista === 'marketing') {
    $textos_guardados = []; 
    if (file_exists(__DIR__ . '/textos_tienda.json')) { $textos_guardados = json_decode(file_get_contents(__DIR__ . '/textos_tienda.json'), true); }
}
elseif ($vista === 'catalogo') { 
    try { $catalogo_local = $pdo->query("SELECT * FROM improgyp_catalogo ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC); } catch(Exception $e) {}
}
elseif ($vista === 'seo') { 
    if (file_exists(__DIR__ . '/seo.json')) { $seo_guardado = json_decode(file_get_contents(__DIR__ . '/seo.json'), true); } 
}
elseif ($vista === 'ads') { 
    if (file_exists(__DIR__ . '/ads.json')) { $ad_guardado = json_decode(file_get_contents(__DIR__ . '/ads.json'), true); } 
    try { $catalogo_local = $pdo->query("SELECT DISTINCT nombre, codigo FROM improgyp_catalogo WHERE publicado = 1 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC); } catch(Exception $e) {}
}
elseif ($vista === 'pedidos') {
    try { $pedidos = $pdo->query("SELECT * FROM pedidos_b2b ORDER BY fecha DESC")->fetchAll(PDO::FETCH_ASSOC); } catch(Exception $e) {}
}
elseif ($vista === 'pedidos_publicos') {
    try { $pedidos_publicos = $pdo->query("SELECT * FROM pedidos_publicos ORDER BY fecha DESC")->fetchAll(PDO::FETCH_ASSOC); } catch(Exception $e) {}
}
function extraerTextos($html) {
    if (empty($html)) return ['', ''];
    preg_match('/<span[^>]*>(.*?)<\/span>/i', $html, $matches); 
    $resaltado = $matches[1] ?? '';
    $normal = trim(preg_replace('/<span[^>]*>.*?<\/span>/i', '', $html));
    $normal = trim(str_replace(["<br class='hidden md:block'>", '<br class="hidden md:block">', '<br>'], '', $normal));
    return [$normal, $resaltado];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMPROGYP OS | Central de Comando</title>
    <link rel="icon" type="image/png" href="favicon-app.png?v=5">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="components/documentacion/assets/doc.css">
    <style>
        :root {
            --bg-body: #F1F5F9;
            --bg-aside: #FFFFFF;
            --bg-card: #FFFFFF;
            --border-base: #E2E8F0;
            --text-title: #0F172A;
            --text-body: #475569;
            --accent: #1B263B;
        }

        body { background-color: var(--bg-body); color: var(--text-body); }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }

        /* Estilos Globales Elite */
        .glass-card {
            background: #FFFFFF;
            border: 1px solid #F1F5F9;
            border-radius: 2rem;
            position: relative;
            overflow: hidden;
        }

        .premium-input {
            background-color: #F8FAFC !important;
            border: 1px solid #E2E8F0 !important;
            color: #0F172A !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .premium-input:focus {
            background-color: #FFFFFF !important;
            border-color: #1B263B !important;
            box-shadow: 0 0 0 4px rgba(27, 38, 59, 0.08) !important;
            outline: none;
        }

        /* Overrides para asegurar pureza del diseño */
        .text-slate-900 { color: #0F172A !important; }
        .text-slate-800 { color: #1E293B !important; }
        .text-slate-700 { color: #334155 !important; }
        .text-slate-600 { color: #475569 !important; }
        .text-slate-500 { color: #64748B !important; }
        .text-slate-400 { color: #94A3B8 !important; }

        /* Forzar texto blanco en botones verdes */
        button[class*="bg-[#1B263B]"], 
        .bg-[#1B263B] { color: white !important; }

        .nav-collapse-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .nav-collapse-trigger:hover { background: #f8fafc; }
        .nav-collapse.is-open .nav-collapse-chevron { transform: rotate(180deg); }
        .nav-collapse-chevron {
            color: #94a3b8;
            font-size: 10px;
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }
        .nav-collapse-panel {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 0.22s ease;
        }
        .nav-collapse.is-open .nav-collapse-panel { grid-template-rows: 1fr; }
        .nav-collapse-panel-inner {
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            min-height: 0;
            padding-top: 0.125rem;
        }
        .nav-collapse[data-nav-collapse="improgyp_nav_apariencia"].is-open .nav-collapse-trigger {
            color: #6d28d9;
        }
        .nav-collapse[data-nav-collapse="improgyp_nav_inventario"].is-open .nav-collapse-trigger {
            color: #1B263B;
        }
        .nav-collapse[data-nav-collapse="improgyp_nav_inventario"].is-open .nav-collapse-trigger span {
            color: #1B263B;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-700 font-sans flex h-screen overflow-hidden selection:bg-[#1B263B] selection:text-white">

    <aside class="w-64 bg-white text-slate-700 flex flex-col border-r border-slate-200 z-20">
        <div class="p-6 border-b border-slate-100 flex items-center justify-center">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">IMPROGYP <span class="text-[#1B263B] font-light">OS</span></h2>
        </div>
        <nav class="p-4 flex flex-col gap-2 flex-1 overflow-y-auto custom-scrollbar">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-4 mt-2 mb-1">Público (B2C)</p>
            <a href="?view=radar" class="<?= menuActivoRadar($vista) ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                <i class="fa-solid fa-chart-pie w-4"></i> Radar de Ventas
            </a>
            <a href="?view=pedidos_publicos" class="<?= menuActivo($vista, 'pedidos_publicos') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                <i class="fa-solid fa-cart-shopping w-4 text-[#1B263B]"></i> Pedidos Públicos <span class="text-[9px] bg-[#1B263B]/20 text-[#1B263B] px-1.5 py-0.5 rounded ml-auto">IA</span>
            </a>
            <div class="nav-collapse<?= $menu_inventario_abierto ? ' is-open' : '' ?>" data-nav-collapse="improgyp_nav_inventario">
                <button type="button" class="nav-collapse-trigger" aria-expanded="<?= $menu_inventario_abierto ? 'true' : 'false' ?>" aria-controls="nav-panel-inventario">
                    <span class="flex items-center gap-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        <i class="fa-solid fa-boxes-stacked w-4 text-[#1B263B] text-sm normal-case"></i>
                        Inventario Web
                    </span>
                    <i class="fa-solid fa-chevron-down nav-collapse-chevron" aria-hidden="true"></i>
                </button>
                <div id="nav-panel-inventario" class="nav-collapse-panel">
                    <div class="nav-collapse-panel-inner">
                        <a href="?view=catalogo" class="<?= menuSubApariencia($vista === 'catalogo') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                            <i class="fa-solid fa-list w-4 text-[#1B263B]"></i> Productos
                        </a>
                    </div>
                </div>
            </div>
            <a href="?view=ads" class="<?= menuActivo($vista, 'ads') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                <i class="fa-solid fa-bullhorn w-4"></i> Gestor de Pautas
            </a>
            <a href="?view=marketing" class="<?= menuActivo($vista, 'marketing') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                <i class="fa-solid fa-wand-magic-sparkles w-4"></i> Marketing IA
            </a>
            <a href="?view=seo" class="<?= menuActivo($vista, 'seo') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                <i class="fa-solid fa-magnifying-glass-chart w-4"></i> SEO Dinámico
            </a>
            <div class="nav-collapse mt-6<?= $menu_apariencia_abierto ? ' is-open' : '' ?>" data-nav-collapse="improgyp_nav_apariencia">
                <button type="button" class="nav-collapse-trigger" aria-expanded="<?= $menu_apariencia_abierto ? 'true' : 'false' ?>" aria-controls="nav-panel-apariencia">
                    <span class="flex items-center gap-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        <i class="fa-solid fa-palette w-4 text-violet-500 text-sm normal-case"></i>
                        Apariencia web
                    </span>
                    <i class="fa-solid fa-chevron-down nav-collapse-chevron" aria-hidden="true"></i>
                </button>
                <div id="nav-panel-apariencia" class="nav-collapse-panel">
                    <div class="nav-collapse-panel-inner">
                        <a href="?view=apariencia&sub=home" class="<?= menuSubApariencia($vista === 'apariencia' && in_array($sub_vista, ['home', 'portada', 'secciones'], true)) ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                            <i class="fa-solid fa-house-chimney w-4 text-violet-500"></i> Editor del Home
                        </a>
                        <a href="?view=apariencia&sub=megamenu" class="<?= menuSubApariencia($vista === 'apariencia' && $sub_vista === 'megamenu') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                            <i class="fa-solid fa-bars-staggered w-4 text-violet-500"></i> Megamenú B2C
                        </a>
                        <a href="?view=apariencia&sub=blog" class="<?= menuSubApariencia($vista === 'apariencia' && $sub_vista === 'blog') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                            <i class="fa-solid fa-square-rss w-4 text-orange-400"></i> Apariencia Blog
                        </a>
                        <a href="?view=blog" class="<?= menuActivo($vista, 'blog') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                            <i class="fa-solid fa-pen-nib w-4 text-orange-500"></i> Gestor de Blog
                        </a>
                    </div>
                </div>
            </div>
            <a href="?view=locales" class="<?= menuActivo($vista, 'locales') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                <i class="fa-solid fa-map-location-dot w-4 text-indigo-500"></i> Red de Sucursales
            </a>
            
            <?php if (improgyp_b2b_admin_ver_modulo()): ?>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-4 mt-6 mb-1">Mayoristas (B2B)<?php if (!improgyp_b2b_publico_activo()): ?> <span class="text-amber-500">· OFF</span><?php endif; ?></p>
            <a href="?view=distribuidores" class="<?= menuActivo($vista, 'distribuidores') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                <i class="fa-solid fa-users-gear w-4"></i> Clientes VIP
            </a>
            <a href="?view=pedidos" class="<?= menuActivo($vista, 'pedidos') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 text-sm transition-colors">
                <i class="fa-solid fa-file-invoice-dollar w-4 text-[#1B263B]"></i> Pedidos B2B <span class="text-[9px] bg-[#1B263B]/20 text-[#1B263B] px-1.5 py-0.5 rounded ml-auto">NUEVO</span>
            </a>
            <?php endif; ?>
            
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 px-4 mt-6">Sala de Máquinas</p>
            <a href="?view=sistema" class="<?= menuActivo($vista, 'sistema') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 transition-colors text-sm">
                <i class="fa-solid fa-toggle-on w-4"></i> Estado del Sistema
            </a>
            <a href="?view=ayuda" class="<?= menuActivo($vista, 'ayuda') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 transition-colors text-sm text-indigo-600 font-black">
                <i class="fa-solid fa-book-open-reader w-4"></i> Documentación <span class="text-[9px] bg-indigo-50 text-indigo-500 px-1.5 py-0.5 rounded ml-auto">AYUDA</span>
            </a>
            <?php if (isset($_SESSION['admin_rol']) && $_SESSION['admin_rol'] === 'master'): ?>
                <a href="?view=usuarios" class="<?= menuActivo($vista, 'usuarios') ?> px-4 py-3 rounded-lg font-medium border-l-2 flex items-center gap-3 transition-colors text-sm border-amber-400/30">
                    <i class="fa-solid fa-user-shield w-4 text-amber-500"></i> Gestión de Usuarios
                </a>
            <?php endif; ?>
        </nav>
        <div class="p-4 border-t border-slate-100 flex flex-col gap-3">
            <a href="?logout=1" class="w-full flex items-center justify-center gap-2 text-slate-400 hover:text-rose-500 text-sm font-semibold transition-colors py-2">
                <i class="fa-solid fa-power-off"></i> Salir
            </a>
        </div>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto relative custom-scrollbar pb-32">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-[#1B263B] rounded-full mix-blend-screen filter blur-[128px] opacity-5 pointer-events-none"></div>

        <?php if ($vista !== 'ayuda'): ?>
        <header class="mb-10 border-b border-slate-100 pb-8 flex justify-between items-end relative z-10">
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tighter uppercase">
                    <?php 
                        if($vista=='marketing') echo 'Marketing <span class="text-[#1B263B] font-light">IA</span>'; 
                        elseif($vista=='seo') echo 'SEO <span class="text-[#1B263B] font-light">Dinámico</span>'; 
                        elseif($vista=='radar') echo 'Radar de <span class="text-[#1B263B] font-light">Ventas</span>';
                        elseif($vista=='inventario_fantasma') echo 'Limpieza de <span class="text-[#1B263B] font-light">Inventario</span>';
                        elseif($vista=='distribuidores') echo 'Directorio <span class="text-[#1B263B] font-light">VIP</span>'; 
                        elseif($vista=='sistema') echo 'Estado del <span class="text-[#1B263B] font-light">Sistema</span>'; 
                        elseif($vista=='ads') echo 'Gestor de <span class="text-[#1B263B] font-light">Pautas</span>'; 
                        elseif($vista=='usuarios') echo 'Gestión de <span class="text-[#1B263B] font-light">Usuarios</span>';
                        elseif($vista=='pedidos') echo 'Gestión de <span class="text-[#1B263B] font-light">Pedidos B2B</span>';
                        elseif($vista=='pedidos_publicos') echo 'Gestión de <span class="text-[#1B263B] font-light">Pedidos Públicos</span>';
                        elseif($vista=='locales') echo 'Red de <span class="text-[#1B263B] font-light">Sucursales</span>';
                        else echo 'Inventario <span class="text-[#1B263B] font-light">Web</span>'; 
                    ?>
                </h1>
            </div>
            <div class="flex gap-3">
                <?php if ($vista === 'catalogo' || $vista === ''): ?>
                    <button type="button" onclick="abrirModalBulk()" class="bg-[#1B263B] hover:bg-[#3A86FF] text-white px-4 py-2 rounded-lg text-sm font-black flex items-center gap-2 transition-all active:scale-95 group">
                        <i class="fa-solid fa-file-csv"></i> Actualización masiva (CSV)
                    </button>
                <?php endif; ?>
                <a href="index.php" target="_blank" class="bg-white hover:bg-slate-50 text-slate-400 hover:text-slate-900 px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest flex items-center gap-3 border border-slate-100 transition-all">
                    <i class="fa-solid fa-store text-[#1B263B] text-sm"></i> Ver Tienda
                </a>
            </div>
        </header>
        <?php endif; ?>

        <?php if($vista === 'ads'): ?>
            <?php if(isset($_GET['msg'])): ?>
                <?php if($_GET['msg'] == 'ad_guardado'): ?>
                    <div class="bg-[#1B263B]/20 border border-[#1B263B]/50 text-[#1B263B] p-4 rounded-2xl mb-6 text-sm font-bold flex items-center gap-2 relative z-10 animate-fade-in">
                        <i class="fa-solid fa-circle-check"></i> Campañas actualizadas y sincronizadas en la tienda.
                    </div>
                <?php endif; ?>
                <?php if($_GET['msg'] == 'impulso_eliminado'): ?>
                    <div class="bg-rose-50 border border-rose-100 text-rose-500 p-4 rounded-2xl mb-6 text-sm font-bold flex items-center gap-2 relative z-10 animate-fade-in">
                        <i class="fa-solid fa-trash-can"></i> Impulso eliminado correctamente.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <form method="POST" action="dashboard.php?view=ads" enctype="multipart/form-data" class="space-y-8">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="guardar_ad">

                <!-- GESTIÓN DE IMPULSOS -->
                <div class="relative z-10">
                    <div class="glass-card p-6 overflow-hidden border-l-4 border-l-amber-500">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-bolt-lightning text-amber-500"></i> Productos Impulsados (TOP)
                        </h3>
                        <div class="max-h-32 overflow-y-auto custom-scrollbar">
                            <?php if(empty($productos_impulsados)): ?>
                                <p class="text-xs text-slate-400 italic py-2 text-center">No hay productos impulsados actualmente.</p>
                            <?php else: ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2">
                                    <?php foreach($productos_impulsados as $imp): ?>
                                        <div class="flex items-center justify-between bg-slate-50 border border-slate-100 p-2 rounded-xl">
                                            <div class="flex items-center gap-2 truncate">
                                                <div class="w-2 h-2 rounded-full bg-[#1B263B] animate-pulse"></div>
                                                <span class="text-xs font-bold text-slate-700 truncate"><?= htmlspecialchars($imp['nombre_producto']) ?></span>
                                            </div>
                                            <button type="button" onclick="confirmarEliminarImpulso('<?= htmlspecialchars($imp['nombre_producto']) ?>')" class="text-slate-300 hover:text-rose-500 transition-colors p-1">
                                                <i class="fa-solid fa-circle-xmark"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- REPEATER: VIDEOS PROMOCIONALES -->
                <div class="glass-card p-8 relative z-10">
                    <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-6">
                        <div>
                            <h2 class="text-lg font-black text-slate-900 flex items-center gap-3 uppercase tracking-tighter">
                                <i class="fa-solid fa-film text-[#1B263B] text-2xl"></i> Biblioteca de Videos (Máx 6)
                            </h2>
                            <p class="text-[11px] text-slate-400 uppercase font-black tracking-widest mt-1">Discovery Ads & Reels Mode</p>
                        </div>
                        <button type="button" onclick="agregarFilaVideo()" class="bg-[#1B263B]/10 text-[#1B263B] hover:bg-[#1B263B] hover:text-white px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Añadir Video
                        </button>
                    </div>

                    <div id="contenedor-videos" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php 
                        $videos = $ad_guardado['videos'] ?? [];
                        if (empty($videos)) $videos = [[]]; // Al menos uno vacío
                        foreach($videos as $index => $v): 
                        ?>
                            <div class="bg-slate-50 border border-slate-100 rounded-[2rem] p-6 relative group hover:border-[#1B263B]/30 transition-all video-row">
                                <div class="absolute top-4 right-4 flex gap-2">
                                    <label class="relative inline-flex items-center cursor-pointer scale-75">
                                        <input type="checkbox" name="video_activo[<?= $index ?>]" class="sr-only peer" <?= (isset($v['activo']) && !$v['activo']) ? '' : 'checked' ?>>
                                        <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-[#1B263B] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                    </label>
                                    <button type="button" onclick="this.closest('.video-row').remove()" class="text-slate-300 hover:text-rose-500 transition-colors"><i class="fa-solid fa-trash"></i></button>
                                </div>

                                <div class="space-y-4 pt-2">
                                    <input type="hidden" name="video_url_actual[<?= $index ?>]" value="<?= htmlspecialchars($v['url'] ?? '') ?>">
                                    <?php if(!empty($v['url'])): ?>
                                        <div class="aspect-video bg-black rounded-2xl overflow-hidden relative mb-4 shadow-inner">
                                            <video src="<?= $v['url'] ?>" class="w-full h-full object-cover opacity-60"></video>
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <a href="<?= $v['url'] ?>" target="_blank" class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-[#1B263B] transition-all"><i class="fa-solid fa-play"></i></a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="aspect-video bg-slate-100 border-2 border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center text-slate-300 gap-2 mb-4">
                                            <i class="fa-solid fa-cloud-arrow-up text-2xl"></i>
                                            <span class="text-[10px] font-bold uppercase tracking-widest">Sin video</span>
                                        </div>
                                    <?php endif; ?>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Archivo MP4/WebM</label>
                                        <input type="file" name="video_archivo[<?= $index ?>]" accept="video/mp4, video/webm" class="w-full text-[10px] file:bg-[#1B263B]/10 file:text-[#1B263B] file:border-0 file:rounded-lg file:px-3 file:py-1 file:font-black cursor-pointer">
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Posición (Card #)</label>
                                            <input type="number" name="video_pos[<?= $index ?>]" value="<?= htmlspecialchars($v['pos'] ?? '') ?>" placeholder="Ej: 4" class="w-full premium-input rounded-xl px-4 py-2 text-xs font-black">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Link de Destino</label>
                                            <input type="text" name="video_link[<?= $index ?>]" value="<?= htmlspecialchars($v['link'] ?? '') ?>" list="productos_lista" placeholder="Producto o URL" class="w-full premium-input rounded-xl px-4 py-2 text-xs">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- REPEATER: BANNERS ROMPETRÁFICO -->
                <div class="glass-card p-8 relative z-10">
                    <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-6">
                        <div>
                            <h2 class="text-lg font-black text-slate-900 flex items-center gap-3 uppercase tracking-tighter">
                                <i class="fa-solid fa-rectangle-ad text-blue-500 text-2xl"></i> Banners Rompetráfico (Máx 6)
                            </h2>
                            <p class="text-[11px] text-slate-400 uppercase font-black tracking-widest mt-1">Solo catálogo · cada N filas · máx. 1 al cierre</p>
                        </div>
                        <button type="button" onclick="agregarFilaBanner()" class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Añadir Banner
                        </button>
                    </div>

                    <div class="mb-6 p-4 rounded-2xl bg-blue-50/80 border border-blue-100 text-[11px] text-slate-600 leading-relaxed">
                        <strong class="text-slate-800">Rompetráfico v2:</strong> elige un estilo por banner. Se inserta cada <strong>N filas de productos</strong> (en desktop ~3 cards por fila). Si varios coinciden, solo uno por fila (prioridad = orden aquí). Al final del listado: como máximo <strong>1</strong> pendiente. Imagen solo en Split y Glass. El campo <strong>Texto del botón (CTA)</strong> personaliza el botón en tienda (vacío = «Ver más»).
                    </div>
                    <div id="contenedor-banners" class="space-y-6">
                        <?php 
                        $banners = $ad_guardado['banners'] ?? [];
                        if (empty($banners)) $banners = [[]];
                        foreach($banners as $index => $b):
                            $estiloB = $b['estilo'] ?? 'respiracion';
                            if (!in_array($estiloB, ['respiracion', 'split', 'marquee', 'glass'], true)) $estiloB = 'respiracion';
                            $cadaNF = (int)($b['cada_n_filas'] ?? 0);
                            if ($cadaNF < 1) {
                                $legacyPos = (int)($b['pos'] ?? 0);
                                $cadaNF = $legacyPos > 0 ? max(1, (int)ceil($legacyPos / 3)) : 4;
                            }
                            $cadaNF = max(1, min(20, $cadaNF));
                            $necesitaImg = in_array($estiloB, ['split', 'glass'], true);
                        ?>
                            <div class="bg-slate-50 border border-slate-100 rounded-[2.5rem] p-8 relative group hover:border-blue-400/30 transition-all banner-row">
                                <div class="absolute top-6 right-8 flex gap-3">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="banner_activo[<?= $index ?>]" class="sr-only peer" <?= (isset($b['activo']) && !$b['activo']) ? '' : 'checked' ?>>
                                        <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-blue-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                    </label>
                                    <button type="button" onclick="this.closest('.banner-row').remove()" class="text-slate-300 hover:text-rose-500 transition-colors"><i class="fa-solid fa-trash-can shadow-sm"></i></button>
                                </div>
                                <input type="hidden" name="banner_img_actual[<?= $index ?>]" value="<?= htmlspecialchars($b['img_url'] ?? '') ?>">

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                    <div class="lg:col-span-1">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Estilo visual</label>
                                        <select name="banner_estilo[<?= $index ?>]" class="w-full premium-input rounded-xl px-3 py-2 text-xs font-black banner-estilo-select" onchange="toggleBannerFields(this)">
                                            <option value="respiracion" <?= $estiloB === 'respiracion' ? 'selected' : '' ?>>A · Respiración</option>
                                            <option value="split" <?= $estiloB === 'split' ? 'selected' : '' ?>>B · Split 50/50</option>
                                            <option value="marquee" <?= $estiloB === 'marquee' ? 'selected' : '' ?>>C · Marquee</option>
                                            <option value="glass" <?= $estiloB === 'glass' ? 'selected' : '' ?>>D · Glass</option>
                                        </select>
                                    </div>
                                    <div class="lg:col-span-1">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Cada N filas</label>
                                        <input type="number" name="banner_cada_n_filas[<?= $index ?>]" min="1" max="20" value="<?= $cadaNF ?>" class="w-full premium-input rounded-xl px-4 py-2 text-xs font-black">
                                        <p class="text-[9px] text-slate-400 mt-1">Ej. 4 ≈ cada 12 cards en desktop</p>
                                    </div>
                                    <div class="lg:col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 italic">Etiqueta (Pill)</label>
                                        <input type="text" name="banner_etiqueta[<?= $index ?>]" value="<?= htmlspecialchars($b['etiqueta'] ?? 'PROMO') ?>" class="w-full premium-input rounded-xl px-4 py-2 text-xs font-black text-blue-600">
                                    </div>

                                    <div class="lg:col-span-4 banner-field-group">
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                            <div class="lg:col-span-2">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Título Principal</label>
                                                <input type="text" name="banner_titulo[<?= $index ?>]" value="<?= htmlspecialchars($b['titulo'] ?? '') ?>" class="w-full premium-input rounded-xl px-4 py-2 text-sm font-black text-slate-900">
                                            </div>
                                            <div class="lg:col-span-2 banner-opt banner-opt-split banner-opt-glass <?= $necesitaImg ? '' : 'hidden' ?>">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Imagen (Split / Glass)</label>
                                                <input type="file" name="banner_img[<?= $index ?>]" accept="image/*" class="w-full premium-input rounded-xl px-3 py-2 text-[10px]">
                                                <?php if (!empty($b['img_url'])): ?>
                                                    <p class="text-[9px] text-emerald-600 font-black mt-1"><i class="fa-solid fa-check"></i> Imagen cargada</p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="lg:col-span-2">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Texto del botón (CTA)</label>
                                                <input type="text" name="banner_extra[<?= $index ?>]" value="<?= htmlspecialchars($b['extra'] ?? '') ?>" placeholder="Ej: Consultar envíos" class="w-full premium-input rounded-xl px-4 py-2 text-xs font-bold">
                                                <p class="text-[9px] text-slate-400 mt-1">Vacío = «Ver más»</p>
                                            </div>
                                            <div class="lg:col-span-2">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Link o WhatsApp</label>
                                                <input type="text" name="banner_link[<?= $index ?>]" value="<?= htmlspecialchars($b['link'] ?? 'https://wa.me/593991754887') ?>" class="w-full premium-input rounded-xl px-4 py-2 text-xs">
                                            </div>
                                            <div class="lg:col-span-4">
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Descripción / Texto Secundario</label>
                                                <textarea name="banner_desc[<?= $index ?>]" rows="2" class="w-full premium-input rounded-2xl px-4 py-3 text-xs leading-relaxed"><?= htmlspecialchars($b['desc'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- OTRAS PAUTAS (B2B) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 relative z-10">
                    <div class="glass-card p-8 flex flex-col h-full hover:border-amber-500/40 transition-colors">
                        <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-4">
                            <h2 class="text-base font-black text-slate-900 flex items-center gap-2"><i class="fa-solid fa-crown text-amber-500"></i> Tarjeta Mayoristas VIP</h2>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="b2b_activo" class="sr-only peer" <?= (isset($ad_guardado['b2b_activo']) && !$ad_guardado['b2b_activo']) ? '' : 'checked' ?>>
                                <div class="w-9 h-5 bg-slate-600 rounded-full peer peer-checked:bg-amber-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>
                        <input type="hidden" name="b2b_img_url_actual" value="<?= htmlspecialchars($ad_guardado['b2b_img_url'] ?? '') ?>">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Posición Estática</label>
                                    <input type="number" name="b2b_pos" value="<?= htmlspecialchars($ad_guardado['b2b_pos'] ?? 25) ?>" class="w-full premium-input rounded-xl px-4 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Etiqueta Corona</label>
                                    <input type="text" name="b2b_etiqueta" value="<?= htmlspecialchars($ad_guardado['b2b_etiqueta'] ?? 'MAYORISTAS') ?>" class="w-full premium-input rounded-xl px-4 py-2 text-amber-600 text-sm font-black">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Texto del Botón</label>
                                <input type="text" name="b2b_btn" value="<?= htmlspecialchars($ad_guardado['b2b_btn'] ?? 'Acceder al Portal') ?>" class="w-full premium-input rounded-xl px-4 py-2 text-sm font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Imagen de Fondo</label>
                                <input type="file" name="b2b_img" accept="image/*" class="w-full premium-input rounded-xl px-3 py-2 text-xs">
                                <?php if(!empty($ad_guardado['b2b_img_url'])): ?>
                                    <p class="text-[9px] text-[#1B263B] font-black mt-1 uppercase"><i class="fa-solid fa-check"></i> Imagen cargada</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-8 flex flex-col h-full hover:border-purple-400/40 transition-colors">
                        <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-4">
                            <h2 class="text-base font-black text-slate-900 flex items-center gap-2"><i class="fa-solid fa-robot text-purple-600"></i> Banner Animado IA</h2>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="ia_activo" class="sr-only peer" <?= (isset($ad_guardado['ia_activo']) && !$ad_guardado['ia_activo']) ? '' : 'checked' ?>>
                                <div class="w-9 h-5 bg-slate-600 rounded-full peer peer-checked:bg-purple-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Etiqueta Superior</label>
                                <input type="text" name="ia_etiqueta" value="<?= htmlspecialchars($ad_guardado['ia_etiqueta'] ?? 'Asesoría Gratuita') ?>" class="w-full premium-input rounded-xl px-4 py-2 text-purple-700 text-sm font-black">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Título Láser</label>
                                    <input type="text" name="ia_titulo" value="<?= htmlspecialchars($ad_guardado['ia_titulo'] ?? '') ?>" class="w-full premium-input rounded-xl px-4 py-2 text-sm font-black">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Texto del Botón</label>
                                    <input type="text" name="ia_btn" value="<?= htmlspecialchars($ad_guardado['ia_btn'] ?? 'Consultar a la IA') ?>" class="w-full premium-input rounded-xl px-4 py-2 text-xs">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Descripción</label>
                                <textarea name="ia_desc" rows="2" class="w-full premium-input rounded-2xl px-4 py-3 text-xs leading-relaxed"><?= htmlspecialchars($ad_guardado['ia_desc'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fixed bottom-8 right-8 z-50">
                    <button type="submit" class="bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-5 px-10 rounded-3xl transition-all flex items-center gap-4 text-sm hover:scale-105 shadow-2xl shadow-[#1B263B]/30 active:scale-95">
                        <i class="fa-solid fa-cloud-arrow-up text-xl"></i> Sincronizar Todas las Pautas
                    </button>
                </div>

                <datalist id="productos_lista">
                    <option value="https://wa.me/593991754887">🔗 WhatsApp IMPROGYP Directo</option>
                    <?php if(isset($catalogo_local)): foreach($catalogo_local as $prod): ?>
                        <option value="<?= htmlspecialchars($prod['codigo'] ?? $prod['nombre']) ?>">📦 [<?= htmlspecialchars($prod['codigo'] ?? 'S/C') ?>] <?= htmlspecialchars($prod['nombre']) ?></option>
                    <?php endforeach; endif; ?>
                </datalist>
            </form>

            <form id="form-eliminar-impulso" method="POST" action="dashboard.php?view=ads">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="eliminar_impulso_ad">
                <input type="hidden" name="nombre_producto" id="input-eliminar-impulso">
            </form>

        <?php elseif($vista === 'sistema'): 
            $est_data = improgyp_b2b_estado_load();
            $mantenimiento_activo = !empty($est_data['mantenimiento']);
            $b2b_publico_activo_ui = improgyp_b2b_publico_activo();
            $b2b_pilot_rucs_ui = is_array($est_data['b2b_pilot_rucs'] ?? null)
                ? implode(', ', $est_data['b2b_pilot_rucs'])
                : (string)($est_data['b2b_pilot_rucs'] ?? '');
        ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'estado_actualizado'): ?>
                <div class="bg-[#1B263B]/20 border border-[#1B263B]/50 text-[#1B263B] p-4 rounded-lg mb-6 text-sm font-bold flex items-center gap-2 relative z-10">
                    <i class="fa-solid fa-circle-check"></i> Estado de la tienda actualizado.
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'b2b_estado'): ?>
                <div class="bg-indigo-50 border border-indigo-200 text-indigo-800 p-4 rounded-lg mb-6 text-sm font-bold flex items-center gap-2 relative z-10">
                    <i class="fa-solid fa-briefcase"></i> Configuración del portal mayoristas actualizada.
                </div>
            <?php endif; ?>

            <div class="glass-card p-10 max-w-2xl relative z-10 border-slate-200 mb-8">
                <div class="mb-8 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-3xl <?= $b2b_publico_activo_ui ? 'bg-[#1B263B]/10 text-[#1B263B]' : 'bg-amber-50 text-amber-600' ?>">
                        <i class="fa-solid <?= $b2b_publico_activo_ui ? 'fa-briefcase' : 'fa-eye-slash' ?>"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 leading-tight uppercase tracking-tighter">Portal mayoristas (B2B)</h2>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Visibilidad pública y acceso al cotizador IA</p>
                    </div>
                </div>
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 mb-6">
                    <p class="text-sm text-slate-500 leading-relaxed font-medium mb-3">
                        Con el módulo <strong>apagado</strong>, se ocultan enlaces en la tienda y el portal muestra “en preparación”. Las métricas y el directorio VIP siguen disponibles para <strong>master</strong>. Los RUC en lista piloto pueden probar el portal aunque esté apagado para el público.
                    </p>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Estado actual: <?= $b2b_publico_activo_ui ? 'Público activo' : 'Solo preparación / piloto' ?></p>
                </div>
                <form method="POST" action="dashboard.php?view=sistema" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="guardar_b2b_estado">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="b2b_publico_activo" value="1" class="w-5 h-5 rounded border-slate-300 text-[#1B263B]" <?= $b2b_publico_activo_ui ? 'checked' : '' ?>>
                        <span class="text-sm font-bold text-slate-700">Portal B2B visible y accesible para todos los mayoristas activos</span>
                    </label>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">RUC piloto (opcional, separados por coma)</label>
                        <input type="text" name="b2b_pilot_rucs" value="<?= htmlspecialchars($b2b_pilot_rucs_ui) ?>" placeholder="1790012345001, 1790099999001" class="w-full premium-input rounded-xl px-4 py-3 text-sm border border-slate-100">
                    </div>
                    <button type="submit" class="w-full bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-4 rounded-xl transition-transform active:scale-95 flex justify-center items-center gap-2 text-sm uppercase tracking-widest">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar módulo B2B
                    </button>
                </form>
            </div>

            <div class="glass-card p-10 max-w-2xl relative z-10 border-slate-200">
                <div class="mb-8 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-3xl <?= $mantenimiento_activo ? 'bg-rose-50 text-rose-500' : 'bg-[#1B263B]/10 text-[#1B263B]' ?>">
                        <i class="fa-solid <?= $mantenimiento_activo ? 'fa-lock' : 'fa-globe' ?>"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 leading-tight uppercase tracking-tighter">Acceso Directo B2C</h2>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Control maestro de disponibilidad</p>
                    </div>
                </div>

                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 mb-8">
                    <p class="text-sm text-slate-500 leading-relaxed font-medium">
                        Al activar el modo mantenimiento, la tienda pública quedará **offline**. Solo los accesos administrativos y VIP podrán operar con normalidad.
                    </p>
                </div>

                <form method="POST" action="dashboard.php?view=sistema" class="flex flex-col sm:flex-row gap-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="guardar_mantenimiento">
                    
                    <?php if($mantenimiento_activo): ?>
                        <input type="hidden" name="estado" value="0">
                        <button type="submit" class="w-full bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-4 rounded-xl transition-transform active:scale-95 flex justify-center items-center gap-2 text-base">
                            <i class="fa-solid fa-unlock"></i> Abrir Tienda (Desactivar Mantenimiento)
                        </button>
                    <?php else: ?>
                        <input type="hidden" name="estado" value="1">
                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-black py-4 rounded-xl transition-transform active:scale-95 flex justify-center items-center gap-2 text-base">
                            <i class="fa-solid fa-lock"></i> Cerrar Tienda (Activar Mantenimiento)
                        </button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="glass-card p-10 max-w-2xl relative z-10 mt-8 border-slate-200">
                <div class="mb-8 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-3xl">
                        <i class="fa-solid fa-broom"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 leading-tight uppercase tracking-tighter">Memoria del Sistema</h2>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Gestión de caché y ranking</p>
                    </div>
                </div>

                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 mb-8">
                    <p class="text-sm text-slate-500 leading-relaxed font-medium">
                        Limpia los archivos temporales para forzar al sistema a reconstruir el catálogo y los rankings inteligentes de IA.
                    </p>
                </div>

                <div id="cache-status-msg" class="hidden mb-4 p-3 rounded-lg text-sm font-bold bg-blue-500/20 text-blue-400 border border-blue-500/50"></div>

                <button type="button" onclick="ejecutarLimpiarCache(this)" class="w-full bg-white hover:bg-slate-200 text-slate-900 font-black py-4 rounded-xl transition-transform active:scale-95 flex justify-center items-center gap-2 text-base">
                    <i class="fa-solid fa-trash-can"></i> Limpiar toda la Caché (IA y Ranking)
                </button>
            </div>

            <script>
                async function ejecutarLimpiarCache(btn) {
                    const originalHtml = btn.innerHTML;
                    const msgDiv = document.getElementById('cache-status-msg');
                    
                    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Limpiando...';
                    btn.disabled = true;
                    
                    try {
                        const response = await fetch('dashboard.php?ajax=limpiar_cache');
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            msgDiv.innerHTML = `<i class="fa-solid fa-circle-check"></i> ¡Éxito! Se han eliminado ${data.borrados} archivos de caché. La tienda ahora mostrará datos 100% frescos.`;
                            msgDiv.classList.remove('hidden');
                            btn.innerHTML = '<i class="fa-solid fa-check"></i> Caché Limpia';
                            btn.classList.replace('bg-white', 'bg-blue-500');
                            btn.classList.add('text-white');
                            setTimeout(() => {
                                msgDiv.classList.add('hidden');
                                btn.innerHTML = originalHtml;
                                btn.classList.replace('bg-blue-500', 'bg-white');
                                btn.classList.remove('text-white');
                                btn.disabled = false;
                            }, 4000);
                        }
                    } catch (e) {
                        alert("Error al limpiar caché");
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }
                }
            </script>

        <?php elseif($vista === 'locales'): 
            $locales_path = __DIR__ . '/locales.json';
            $locales = file_exists($locales_path) ? json_decode(file_get_contents($locales_path), true) : [];
        ?>
            <div class="flex justify-between items-center mb-8 relative z-10">
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Puntos de Distribución Geográfica</p>
                <button onclick="abrirModalLocal()" class="bg-[#1B263B] hover:bg-[#3A86FF] text-white px-5 py-2.5 rounded-xl text-sm font-black flex items-center gap-2 transition-all active:scale-95 shadow-lg shadow-[#1B263B]/20">
                    <i class="fa-solid fa-plus"></i> Nueva Sucursal
                </button>
            </div>

            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'local_guardado'): ?>
                <div class="bg-[#1B263B]/20 border border-[#1B263B]/50 text-[#1B263B] p-4 rounded-xl mb-6 text-sm font-bold flex items-center gap-2 relative z-10 animate-fade-in">
                    <i class="fa-solid fa-circle-check"></i> Sucursal guardada correctamente.
                </div>
            <?php endif; ?>

            <div class="glass-card overflow-hidden relative z-10">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest w-16">Foto</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nombre / Ciudad</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest font-black uppercase">Dirección</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Contacto</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach($locales as $l): ?>
                        <tr class="hover:bg-slate-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <?php
                                $thumbLoc = !empty($l['imagen']) ? getCleanImgUrl($l['imagen']) : '';
                                ?>
                                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-100 overflow-hidden flex items-center justify-center">
                                    <?php if ($thumbLoc): ?>
                                    <img src="<?= htmlspecialchars($thumbLoc) ?>" alt="" class="w-full h-full object-cover" loading="lazy">
                                    <?php else: ?>
                                    <i class="fa-solid fa-store text-slate-300 text-sm"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-black text-slate-900"><?= htmlspecialchars($l['nombre']) ?></div>
                                <div class="text-[10px] text-[#3A86FF] font-black uppercase tracking-widest mt-0.5"><?= htmlspecialchars($l['ciudad']) ?></div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 font-medium max-w-xs truncate" title="<?= htmlspecialchars($l['direccion']) ?>">
                                <?= htmlspecialchars($l['direccion']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="https://wa.me/<?= $l['whatsapp'] ?>" target="_blank" class="w-7 h-7 bg-emerald-50 text-emerald-500 rounded-lg flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-all"><i class="fa-brands fa-whatsapp"></i></a>
                                    <div class="text-xs font-bold text-slate-400"><?= $l['telefono'] ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 text-slate-300">
                                    <?php
                                    $lJson = $l;
                                    if (!empty($l['imagen'])) {
                                        $lJson['imagen_preview'] = getCleanImgUrl($l['imagen']);
                                    }
                                    ?>
                                    <button onclick='editarLocal(<?= json_encode($lJson, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="w-8 h-8 rounded-lg hover:bg-[#1B263B]/10 hover:text-[#1B263B] transition-all flex items-center justify-center"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <button onclick="confirmarEliminarLocal('<?= $l['id'] ?>')" class="w-8 h-8 rounded-lg hover:bg-rose-50 hover:text-rose-500 transition-all flex items-center justify-center"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($locales)): ?>
                        <tr><td colspan="5" class="px-6 py-10 text-center text-slate-400 italic">No hay sucursales configuradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- MODAL LOCALES -->
            <div id="modal-local" class="fixed inset-0 bg-[#1B263B]/60 backdrop-blur-md z-50 hidden flex items-center justify-center opacity-0 transition-opacity">
                <div class="bg-white rounded-[2.5rem] w-full max-w-2xl mx-4 overflow-hidden transform scale-95 transition-all shadow-2xl" id="modal-local-content">
                    <form method="POST" action="dashboard.php?view=locales" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="guardar_local">
                        <input type="hidden" name="id" id="local-id">
                        <input type="hidden" name="imagen_actual" id="local-imagen-actual" value="">

                        <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                            <h3 class="font-black text-slate-900 text-xl uppercase tracking-tighter" id="modal-local-titulo">Nueva Sucursal</h3>
                            <button type="button" onclick="cerrarModalLocal()" class="text-slate-300 hover:text-slate-900"><i class="fa-solid fa-xmark text-2xl"></i></button>
                        </div>

                        <div class="p-8 grid grid-cols-2 gap-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre sucursal</label>
                                <input type="text" name="nombre" id="local-nombre" required placeholder="Ej: Sucursal Quito Norte" class="premium-input w-full px-5 py-3 rounded-2xl text-sm font-bold border border-slate-100">
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Ciudad sede</label>
                                <input type="text" name="ciudad" id="local-ciudad" required placeholder="Ej: Quito" class="premium-input w-full px-5 py-3 rounded-2xl text-sm font-bold border border-slate-100">
                            </div>
                            <div class="col-span-2 border border-slate-100 rounded-2xl p-4 bg-slate-50/50">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2"><i class="fa-solid fa-image mr-1"></i> Foto de la sucursal (home y modal)</label>
                                <p class="text-[9px] text-slate-400 mb-3">Recomendado: horizontal 1200×675 px (16:9). JPG, PNG o WebP.</p>
                                <div class="flex flex-wrap gap-4 items-start">
                                    <div id="local-imagen-preview-wrap" class="w-32 h-20 rounded-xl bg-slate-200 border border-slate-100 overflow-hidden hidden">
                                        <img id="local-imagen-preview" src="" alt="" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-[200px] space-y-2">
                                        <input type="file" name="imagen" id="local-imagen-file" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full text-xs file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:bg-[#1B263B]/10 file:text-[#1B263B]">
                                        <label class="flex items-center gap-2 text-[10px] font-bold text-slate-500 cursor-pointer">
                                            <input type="checkbox" name="quitar_imagen" id="local-quitar-imagen" value="1" class="rounded border-slate-300">
                                            Quitar foto (usará imagen genérica por ciudad)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2"><i class="fa-solid fa-truck-fast mr-1"></i> Cobertura domicilio (ciudades, separadas por coma)</label>
                                <textarea name="cobertura" id="local-cobertura" rows="2" placeholder="Ej: Manta, Portoviejo, Jipijapa" class="premium-input w-full px-5 py-3 rounded-2xl text-sm font-bold border border-emerald-100 bg-emerald-50/20"></textarea>
                                <p class="text-[9px] text-slate-400 mt-1">Checkout domicilio enruta el WhatsApp a la sucursal que cubra la ciudad escrita.</p>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Dirección Completa</label>
                                <input type="text" name="direccion" id="local-direccion" required placeholder="Av. Principal N23..." class="premium-input w-full px-5 py-3 rounded-2xl text-sm font-bold border border-slate-100">
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">WhatsApp (Solo números)</label>
                                <input type="text" name="whatsapp" id="local-whatsapp" placeholder="593..." class="premium-input w-full px-5 py-3 rounded-2xl text-sm font-bold border border-slate-100">
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Teléfono fijo / Alterno</label>
                                <input type="text" name="telefono" id="local-telefono" placeholder="(02) 222..." class="premium-input w-full px-5 py-3 rounded-2xl text-sm font-bold border border-slate-100">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Correo Electrónico</label>
                                <input type="email" name="email" id="local-email" placeholder="sucursal@improgyp.com" class="premium-input w-full px-5 py-3 rounded-2xl text-sm font-bold border border-slate-100">
                            </div>
                            <div class="col-span-2 border-t border-slate-50 pt-4">
                                <h4 class="text-[10px] font-black text-[#1B263B] uppercase tracking-widest mb-4"><i class="fa-solid fa-location-crosshairs mr-1"></i> Geolocalización Técnica</h4>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Latitud</label>
                                <input type="text" name="lat" id="local-lat" required step="any" placeholder="-0.12345" class="premium-input w-full px-5 py-3 rounded-2xl text-sm font-bold border border-slate-100">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Longitud</label>
                                <input type="text" name="lng" id="local-lng" required step="any" placeholder="-78.12345" class="premium-input w-full px-5 py-3 rounded-2xl text-sm font-bold border border-slate-100">
                            </div>
                            <div class="col-span-2">
                                <p class="text-[9px] text-slate-400 font-bold mb-4 uppercase leading-tight italic">Puedes obtener estas coordenadas buscando tu local en <a href="https://www.google.com/maps" target="_blank" class="text-[#3A86FF] hover:underline">Google Maps</a> y copiando los números de la URL o haciendo clic derecho en el mapa.</p>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Google Maps URL</label>
                                <input type="text" name="maps" id="local-maps" placeholder="https://goo.gl/maps/..." class="premium-input w-full px-5 py-3 rounded-2xl text-xs font-bold border border-slate-100">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2"><i class="fa-brands fa-whatsapp"></i> Mensaje predefinido de WhatsApp</label>
                                <textarea name="whatsapp_msj" id="local-whatsapp-msj" rows="2" placeholder="Ej: Hola, deseo información de la sucursal..." class="premium-input w-full px-5 py-3 rounded-2xl text-sm font-bold border border-emerald-100 bg-emerald-50/20"></textarea>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Horario de atención</label>
                                <textarea name="horario" id="local-horario" rows="2" placeholder="Lunes a Viernes: 08:30 - 18:00 / Sábados: 08:30 - 13:00" class="premium-input w-full px-5 py-3 rounded-2xl text-sm font-bold border border-slate-100"></textarea>
                            </div>
                        </div>

                        <div class="p-8 flex gap-4">
                            <button type="submit" class="flex-1 bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-[#1B263B]/20 uppercase tracking-widest text-xs">Guardar Sucursal</button>
                            <button type="button" onclick="cerrarModalLocal()" class="px-6 py-4 rounded-xl border border-slate-100 text-slate-400 font-bold hover:bg-slate-50 transition-all text-xs">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>

            <form id="form-eliminar-local" method="POST" action="dashboard.php?view=locales">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="eliminar_local">
                <input type="hidden" name="id" id="input-eliminar-local">
            </form>


        <?php elseif($vista === 'radar'): ?>
            <?php include __DIR__ . '/components/dashboard_radar.php'; ?>

        <?php elseif($vista === 'inventario_fantasma'): ?>
            <?php include __DIR__ . '/components/dashboard_inventario_fantasma.php'; ?>

        <?php elseif($vista === 'apariencia'): ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] === 'guardado'): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 p-4 rounded-lg mb-6 text-sm font-bold"><i class="fa-solid fa-circle-check"></i> Megamenú guardado.</div>
            <?php endif; ?>
            <?php if ($sub_vista === 'home' || $sub_vista === 'portada' || $sub_vista === 'secciones'): ?>
                <?php include __DIR__ . '/components/apariencia_home.php'; ?>
            <?php elseif ($sub_vista === 'megamenu'): ?>
                <?php include __DIR__ . '/components/apariencia_megamenu.php'; ?>
            <?php elseif ($sub_vista === 'blog'): ?>
                <?php include __DIR__ . '/components/apariencia_blog.php'; ?>
            <?php endif; ?>

        <?php elseif($vista === 'blog'): ?>
            <?php include __DIR__ . '/components/dashboard_blog.php'; ?>

        <?php elseif($vista === 'seo'): ?>
            <?php
            $seo_host = parse_url($base_url, PHP_URL_HOST) ?: '';
            $seo_es_local = (bool) preg_match('/^(localhost|127\.0\.0\.1)$/i', $seo_host);
            $seo_url_publica = rtrim($base_url, '/');
            $seo_fb_debug_href = $seo_es_local
                ? 'https://developers.facebook.com/tools/debug/'
                : 'https://developers.facebook.com/tools/debug/?q=' . rawurlencode($seo_url_publica);
            $seo_preview_img = '';
            if (!empty($seo_guardado['imagen_url'])) {
                $raw_img = $seo_guardado['imagen_url'];
                $seo_preview_img = preg_match('~^https?://~i', $raw_img)
                    ? $raw_img
                    : rtrim($base_url, '/') . '/' . ltrim($raw_img, '/');
            }
            ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'seo_guardado'): ?>
                <div class="bg-[#1B263B]/20 border border-[#1B263B]/50 text-[#1B263B] p-4 rounded-lg mb-6 text-sm font-bold flex items-center gap-2 relative z-10">
                    <i class="fa-solid fa-circle-check"></i> SEO y Metadatos actualizados con éxito.
                </div>
            <?php endif; ?>

            <div class="mb-6 p-4 rounded-2xl border text-sm leading-relaxed relative z-10 <?= $seo_es_local ? 'bg-amber-50 border-amber-200 text-amber-950' : 'bg-blue-50 border-blue-200 text-slate-700' ?>">
                <p class="font-black flex items-center gap-2 mb-2">
                    <i class="fa-brands fa-whatsapp text-lg"></i> Vista previa en WhatsApp / Facebook
                </p>
                <?php if ($seo_es_local): ?>
                    <p>En <strong>localhost</strong> los servidores de WhatsApp <strong>no pueden descargar</strong> tu imagen OG ni previsualizar el enlace como en producción. Aquí solo validas título, descripción y que la foto exista en el servidor.</p>
                    <p class="mt-2 text-[12px]">Al publicar en tu dominio con <strong>HTTPS</strong>, usa el depurador de Meta con la URL real del sitio.</p>
                <?php else: ?>
                    <p>Tras guardar cambios, abre el depurador de Meta con tu URL pública y pulsa <strong>Scrape Again</strong> para refrescar la miniatura en WhatsApp.</p>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($seo_fb_debug_href) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 mt-3 text-[11px] font-black uppercase tracking-wider text-[#1B263B] hover:text-[#3A86FF]">
                    <i class="fa-brands fa-facebook"></i> Abrir Sharing Debugger
                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 relative z-10">
                <div class="glass-card p-10 h-fit hover:border-[#1B263B]/40 transition-all duration-300">
                    <h2 class="text-xl font-black text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass-chart text-[#1B263B]"></i> Metadatos Públicos
                    </h2>
                    <p class="text-sm text-slate-500 mb-8 font-medium">Configura cómo se verá tu tienda en Google, WhatsApp y Facebook.</p>
                    
                    <form method="POST" action="dashboard.php?view=seo" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="guardar_seo">
                        
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Título del Sitio Web</label>
                            <input type="text" name="titulo_seo" value="<?= htmlspecialchars($seo_guardado['titulo'] ?? 'IMPROGYP | E-commerce Inteligente') ?>" required class="w-full premium-input rounded-xl px-4 py-3.5 text-slate-900 text-sm font-bold">
                            <p class="text-[10px] text-slate-400 mt-2 font-medium italic">Ideal entre 50 y 60 caracteres.</p>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Descripción Meta</label>
                            <textarea name="desc_seo" required rows="4" class="w-full premium-input rounded-xl px-4 py-3.5 text-slate-900 text-sm font-medium leading-relaxed"><?= htmlspecialchars($seo_guardado['descripcion'] ?? 'La mejor selección de herramientas técnicas y profesionales. Compra fácil, rápido y seguro.') ?></textarea>
                            <p class="text-[10px] text-slate-400 mt-2 font-medium italic">Breve resumen para convencer a los clientes de hacer clic.</p>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Imagen de Portada (OG:Image)</label>
                            <div class="relative group">
                                <input type="file" name="foto_seo" accept="image/*" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-slate-500 text-xs file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:bg-[#1B263B]/10 file:text-[#1B263B] hover:file:bg-[#1B263B] hover:file:text-white transition-all cursor-pointer">
                            </div>
                            <p class="text-[10px] text-slate-400 mt-2 font-medium">Recomendado 1200×630 px, JPG horizontal. Se publica en <code class="text-[9px] bg-slate-100 px-1 rounded">index.php</code> y <code class="text-[9px] bg-slate-100 px-1 rounded">productos.php</code>.</p>
                        </div>

                        <button type="submit" class="w-full bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-4 rounded-2xl transition-all active:scale-95 mt-4 flex justify-center items-center gap-3 text-sm uppercase tracking-widest">
                            <i class="fa-solid fa-cloud-arrow-up text-lg"></i> Actualizar SEO
                        </button>
                    </form>
                </div>

                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 transition-shadow">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 border-b pb-2">Vista previa en Google</h3>
                        <div class="mb-1">
                            <span class="text-[13px] text-slate-800 bg-slate-100 px-2 py-0.5 rounded-full">Patrocinado</span> 
                            <span class="text-[13px] text-slate-500 ml-1"><?= $base_url ?></span>
                        </div>
                        <h4 class="text-[20px] text-[#1a0dab] font-medium leading-tight hover:underline cursor-pointer mb-1"><?= htmlspecialchars($seo_guardado['titulo'] ?? 'IMPROGYP | E-commerce Inteligente') ?></h4>
                        <p class="text-[14px] text-[#4d5156] leading-snug"><?= htmlspecialchars($seo_guardado['descripcion'] ?? 'La mejor selección de herramientas técnicas y profesionales. Compra fácil, rápido y seguro.') ?></p>
                    </div>

                    <div class="bg-[#e5ddd5] p-6 rounded-2xl flex justify-center items-center bg-[url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png')] relative overflow-hidden transition-shadow">
                        <div class="bg-white p-1 rounded-xl w-full max-w-[320px] ml-auto">
                            <?php if($seo_preview_img !== ''): ?>
                                <div class="w-full h-[160px] bg-slate-200 rounded-t-lg bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($seo_preview_img, ENT_QUOTES, 'UTF-8') ?>');"></div>
                            <?php else: ?>
                                <div class="w-full h-[160px] bg-slate-200 rounded-t-lg flex items-center justify-center text-slate-400 text-xs">
                                    <i class="fa-regular fa-image text-3xl"></i>
                                </div>
                            <?php endif; ?>
                            <div class="p-3 bg-[#f0f2f5] rounded-b-lg">
                                <h4 class="text-[15px] text-[#111b21] font-semibold leading-tight truncate"><?= htmlspecialchars($seo_guardado['titulo'] ?? 'IMPROGYP') ?></h4>
                                <p class="text-[13px] text-[#667781] leading-tight line-clamp-1 mt-0.5"><?= htmlspecialchars($seo_guardado['descripcion'] ?? 'La mejor selección de herramientas...') ?></p>
                                <span class="text-[11px] text-[#667781] mt-1 block uppercase"><?= parse_url($base_url, PHP_URL_HOST) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <?php elseif($vista === 'catalogo'): ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'csv_procesado'): 
                $upd = (int)($_GET['upd'] ?? 0);
                $new = (int)($_GET['new'] ?? 0);
                $err = (int)($_GET['err'] ?? 0);
                $img_ok = (int)($_GET['img_ok'] ?? 0);
                $img_sin = (int)($_GET['img_sin'] ?? 0);
                $img_staged = (int)($_GET['img_staged'] ?? 0);
            ?>
                <div class="bg-[#1B263B]/10 border border-[#1B263B]/30 text-[#1B263B] p-5 rounded-2xl mb-6 text-sm font-bold flex items-center gap-4 relative z-10 w-full animate-in fade-in slide-in-from-top-4 duration-500 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-[#1B263B]/20 flex items-center justify-center text-[#1B263B] text-lg shadow-inner"><i class="fa-solid fa-circle-check"></i></div>
                    <div>
                        <p class="text-slate-900">Sincronización de Catálogo Completa</p>
                        <p class="text-[10px] uppercase tracking-widest text-[#1B263B]/70 font-black mt-0.5">
                            <?= $upd ?> Actualizados | <?= $new ?> Nuevos | <span class="<?= $err > 0 ? 'text-rose-500' : '' ?>"><?= $err ?> Errores</span>
                        </p>
                        <?php if ($img_staged > 0 || $img_ok > 0 || $img_sin > 0): ?>
                        <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mt-1">
                            Fotos subidas: <?= $img_staged ?> · Con imagen en CSV: <?= $img_ok ?><?php if ($img_sin > 0): ?> · <span class="text-amber-600">Sin imagen: <?= $img_sin ?></span><?php endif; ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'bulk_csv_confirm'):
                $bulkConfirm = $_SESSION['bulk_csv_needs_confirm'] ?? ['filas_con_id' => 0, 'filas_datos' => 0];
            ?>
                <div class="bg-amber-50 border border-amber-200 text-amber-900 p-5 rounded-2xl mb-6 text-sm font-bold relative z-10 w-full">
                    <p class="font-black">Importación detenida: el CSV trae columna ID</p>
                    <p class="text-[11px] mt-1 font-medium">Hay <?= (int) ($bulkConfirm['filas_con_id'] ?? 0) ?> filas con ID &gt; 0 (de <?= (int) ($bulkConfirm['filas_datos'] ?? 0) ?> productos). Eso <strong>actualiza</strong> productos existentes, no crea productos nuevos. Abre «Actualización masiva», marca la casilla de confirmación y vuelve a importar — o quita la columna ID para solo altas por código.</p>
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'eliminado_masivo'): ?>
                <div class="bg-rose-50 border border-rose-100 text-rose-500 p-4 rounded-2xl mb-6 text-sm font-bold flex items-center gap-2 relative z-10 w-full animate-in fade-in slide-in-from-top-4 duration-500">
                    <i class="fa-solid fa-trash-can"></i> Los productos seleccionados han sido eliminados del catálogo.
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'guardado'): ?>
                <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 p-4 rounded-2xl mb-6 text-sm font-bold flex items-center gap-3 relative z-10 w-full">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>Producto guardado y catálogo público actualizado.</span>
                </div>
            <?php endif; ?>
            <?php
                $megamenu_orphan_n = (int) ($_SESSION['megamenu_orphan_count'] ?? 0);
                if ($megamenu_orphan_n > 0):
                    $megamenu_orphan_sample = $_SESSION['megamenu_orphan_list'] ?? [];
            ?>
                <div class="bg-amber-50 border border-amber-200 text-amber-950 p-5 rounded-2xl mb-6 text-sm relative z-10 w-full flex flex-col md:flex-row md:items-center gap-4">
                    <div class="flex-1">
                        <p class="font-black flex items-center gap-2">
                            <i class="fa-solid fa-sitemap text-amber-600"></i>
                            <?= $megamenu_orphan_n ?> categoría<?= $megamenu_orphan_n === 1 ? '' : 's' ?> no aparecen en «Explorar Divisiones»
                        </p>
                        <p class="text-[11px] mt-1 text-amber-900/90 font-medium">
                            Los productos ya están en la tienda; el megamenú es independiente. Enlázalas en un clic y luego guarda el menú.
                            <?php if (!empty($megamenu_orphan_sample)): ?>
                                <span class="block mt-1 text-[10px] text-amber-800/80">Ej.: <?= htmlspecialchars(implode(', ', array_slice($megamenu_orphan_sample, 0, 4)), ENT_QUOTES, 'UTF-8') ?><?= count($megamenu_orphan_sample) > 4 ? '…' : '' ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="dashboard.php?view=apariencia&sub=megamenu#mm-orphans-panel" class="shrink-0 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-black text-[11px] uppercase tracking-wide rounded-xl transition-colors">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Sincronizar menú
                    </a>
                </div>
            <?php endif; ?>
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 relative z-10">
                
                <div class="glass-card p-6 h-fit xl:sticky xl:top-6 xl:self-start xl:max-h-[calc(100dvh-7rem)] xl:overflow-y-auto custom-scrollbar hover:border-[#1B263B]/40 transition-colors" id="panel-form-producto">
                    <h2 id="titulo-form-prod" class="text-lg font-black text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-plus text-[#1B263B]"></i> Subir Producto
                    </h2>
                    
                    <form method="POST" action="dashboard.php?view=catalogo" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" id="form-action" value="crear_producto">
                        <input type="hidden" name="id_producto" id="form-id-prod" value="0">
                        <input type="hidden" name="imagen_actual" id="form-imagen-actual" value="">
                        
                        <div>
                            <label class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1 block">Nombre del Producto</label>
                            <input type="text" name="nombre" id="form-nombre" required class="w-full premium-input rounded-lg px-3 py-2.5 text-sm" placeholder="Ej: Atornillador Gypsum...">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1 block">Código (SKU)</label>
                                <input type="text" name="codigo" id="form-codigo" class="w-full premium-input rounded-lg px-3 py-2.5 text-sm" placeholder="Ej: 20MDSG20V">
                            </div>
                            <div>
                                <label class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1 block">Marca</label>
                                <input type="text" name="marca" id="form-marca" list="marca-list" autocomplete="off" class="w-full premium-input rounded-lg px-3 py-2.5 text-sm" placeholder="Ej: MAXXT">
                                <datalist id="marca-list">
                                    <?php foreach($marcas_admin as $m): ?>
                                        <option value="<?= $m ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1 block">Categoría (Selecciona o Escribe una Nueva)</label>
                            <input type="text" name="categoria" id="form-categoria" list="cat-list" required autocomplete="off" class="w-full premium-input rounded-lg px-3 py-2.5 text-sm" placeholder="Escribe o selecciona...">
                            <datalist id="cat-list">
                                <?php foreach($nombres_categorias as $cat): ?>
                                    <option value="<?= $cat ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div>
                            <label class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1 block">Presentaciones y Precios</label>
                            <div id="contenedor-presentaciones" class="space-y-2 mb-2">
                                <!-- Filas dinámicas aquí -->
                            </div>
                            <button type="button" onclick="agregarFilaPresentacion()" class="w-full py-2 border-2 border-dashed border-slate-200 rounded-lg text-slate-400 hover:border-[#1B263B] hover:text-[#1B263B] transition-all text-[11px] font-black uppercase tracking-widest flex items-center justify-center gap-2">
                                <i class="fa-solid fa-plus text-[10px]"></i> Añadir Opción
                            </button>
                        </div>

                        <div>
                            <label class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1 block">Descripción Larga</label>
                            <textarea name="desc_larga" id="form-desc" rows="3" class="w-full premium-input rounded-lg px-3 py-2.5 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1 block">Foto del Producto</label>
                            <input type="file" name="foto" accept="image/*" class="w-full premium-input rounded-lg px-3 py-2 text-slate-500 text-xs file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-black file:bg-[#1B263B]/10 file:text-[#1B263B] hover:file:bg-[#1B263B]/20">
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" id="btn-submit-prod" class="flex-1 bg-[#1B263B] text-white hover:bg-[#3A86FF] font-bold py-3 rounded-xl transition-colors">Guardar Producto</button>
                            <button type="button" id="btn-cancel-prod" onclick="limpiarFormProd()" class="hidden bg-slate-700 text-white hover:bg-slate-600 font-bold py-3 px-4 rounded-xl transition-colors" title="Cancelar">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="xl:col-span-2 glass-card overflow-hidden flex flex-col hover:border-[#1B263B]/40 transition-colors">
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row gap-4 items-center relative">
                        <!-- Barra de Acciones Masivas (Oculta por defecto) -->
                        <div id="bulk-actions-bar" class="absolute inset-0 bg-white z-20 px-6 flex items-center justify-between transition-all opacity-0 pointer-events-none translate-y-2">
                            <div class="flex items-center gap-4">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest"><span id="selected-count" class="text-[#1B263B]">0</span> Seleccionados</span>
                                <button type="button" onclick="deseleccionarTodo()" class="text-[10px] font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">Deshacer</button>
                            </div>
                            <form method="POST" action="dashboard.php?view=catalogo" id="form-bulk-delete" onsubmit="return confirm('¿Eliminar todos los productos seleccionados? Esta acción no se puede deshacer.');">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="action" value="eliminar_masivo">
                                <div id="bulk-ids-container"></div>
                                <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg text-xs font-black flex items-center gap-2 transition-all active:scale-95">
                                    <i class="fa-solid fa-trash-can"></i> Eliminar en Lote
                                </button>
                            </form>
                        </div>

                        <div class="relative flex-1 w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                            </div>
                            <input type="text" id="buscador-inventario" onkeyup="filtrarInventario()" class="w-full bg-white border border-slate-200 rounded-lg pl-10 pr-4 py-2.5 text-slate-700 text-sm focus:border-[#1B263B] outline-none transition-colors" placeholder="Buscar producto por nombre o categoría...">
                        </div>
                        <button type="button" onclick="abrirModalCats()" class="whitespace-nowrap bg-white border border-slate-200 text-slate-600 hover:text-[#1B263B] hover:border-[#1B263B] px-4 py-2.5 rounded-lg text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-tags text-[#1B263B]"></i> Gestionar Categorías
                        </button>
                        <button type="button" onclick="abrirModalMarcas()" class="whitespace-nowrap bg-white border border-slate-200 text-slate-600 hover:text-[#1B263B] hover:border-[#1B263B] px-4 py-2.5 rounded-lg text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-copyright text-[#1B263B]"></i> Gestionar Marcas
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left" id="tabla-inventario">
                            <thead class="text-slate-500 border-b border-slate-100 bg-slate-50/50 text-xs uppercase tracking-wider font-black">
                                <tr>
                                    <th class="p-4 w-12 text-center">
                                        <input type="checkbox" id="check-all" onchange="seleccionarTodos(this.checked)" class="w-4 h-4 rounded border-slate-300 text-[#1B263B] focus:ring-[#1B263B]">
                                    </th>
                                    <th class="p-4 w-20 text-center">Estado</th>
                                    <th class="p-4 w-24">Imagen</th>
                                    <th class="p-4">Información del Producto</th>
                                    <th class="p-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach($catalogo_local as $p): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4 text-center align-middle">
                                        <input type="checkbox" name="select_prod" value="<?= $p['id'] ?>" onchange="actualizarSeleccion()" class="check-prod w-4 h-4 rounded border-slate-300 text-[#1B263B] focus:ring-[#1B263B]">
                                    </td>
                                    <td class="p-4 text-center align-middle">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" <?= $p['publicado'] ? 'checked' : '' ?> onchange="togglePublicado(<?= $p['id'] ?>, this.checked ? 1 : 0)">
                                            <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-[#1B263B] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                        </label>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center p-1 border border-slate-100">
                                            <img src="<?= getCleanImgUrl($p['imagen_url']) ?>" class="max-h-full max-w-full object-contain" onerror="this.onerror=null; this.src='favicon-app.png';">
                                        </div>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <p class="font-black text-slate-900 text-[15px]"><?= $p['nombre'] ?></p>
                                        <p class="text-xs text-slate-500 font-bold uppercase tracking-tight">
                                            <?= $p['categoria'] ?> 
                                            <?= !empty($p['codigo']) ? "<span class='text-[#1B263B]/60 ml-2'>· ID: ".$p['codigo']."</span>" : "" ?>
                                        </p>
                                    </td>
                                    <td class="p-4 align-middle text-right space-x-2 whitespace-nowrap">
                                        <?php 
                                            // Lógica segura para pasar datos a JS sin romper el template
                                            $js_prod = [
                                                'id' => $p['id'],
                                                'nombre' => $p['nombre'],
                                                'codigo' => $p['codigo'],
                                                'marca' => $p['marca'],
                                                'categoria' => $p['categoria'],
                                                'presRaw' => $p['presentaciones_raw'],
                                                'desc' => $p['desc_larga'],
                                                'imagen' => $p['imagen_url']
                                            ];
                                            $json_prod = htmlspecialchars(json_encode($js_prod), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <button type="button" 
                                                 onclick="editarProductoDesdeJSON(this.dataset.prod)" 
                                                 data-prod='<?= $json_prod ?>'
                                                 class="w-8 h-8 bg-blue-500/10 text-blue-400 rounded-lg hover:bg-blue-500 hover:text-white transition-colors">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="POST" action="dashboard.php?view=catalogo" onsubmit="return confirm('¿Eliminar producto permanentemente?');" class="inline-block">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                            <input type="hidden" name="action" value="eliminar_producto">
                                            <input type="hidden" name="id_producto" value="<?= $p['id'] ?>">
                                            <button type="submit" class="w-8 h-8 bg-rose-500/10 text-rose-400 rounded-lg hover:bg-rose-500 hover:text-white transition-colors">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <script>
                function filtrarInventario() {
                    let input = document.getElementById("buscador-inventario");
                    // Normalizar: eliminar tildes y convertir a minúsculas
                    let filter = input.value.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
                    let rows = document.querySelectorAll("#tabla-inventario tbody tr");
                    rows.forEach(row => {
                        let text = row.innerText.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
                        row.style.display = text.includes(filter) ? "" : "none";
                    });
                }

                function seleccionarTodos(checked) {
                    const checks = document.querySelectorAll('.check-prod');
                    checks.forEach(c => {
                        if (c.closest('tr').style.display !== 'none') {
                            c.checked = checked;
                        }
                    });
                    actualizarSeleccion();
                }

                function deseleccionarTodo() {
                    document.getElementById('check-all').checked = false;
                    seleccionarTodos(false);
                }

                function actualizarSeleccion() {
                    const checks = document.querySelectorAll('.check-prod:checked');
                    const bar = document.getElementById('bulk-actions-bar');
                    const count = document.getElementById('selected-count');
                    const container = document.getElementById('bulk-ids-container');
                    
                    count.innerText = checks.length;
                    container.innerHTML = '';
                    
                    if (checks.length > 0) {
                        bar.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-2');
                        checks.forEach(c => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'ids_productos[]';
                            input.value = c.value;
                            container.appendChild(input);
                        });
                    } else {
                        bar.classList.add('opacity-0', 'pointer-events-none', 'translate-y-2');
                    }
                }

                async function togglePublicado(id, estado) { 
                    const formData = new URLSearchParams(); 
                    formData.append('action', 'toggle_publicado'); 
                    formData.append('csrf_token', '<?= $csrf_token ?>'); 
                    formData.append('id_producto', id); 
                    formData.append('estado', estado); 
                    
                    try {
                        let response = await fetch('dashboard.php?view=catalogo', { method: 'POST', body: formData });
                        if (!response.ok) throw new Error('Error en red');
                    } catch (e) {
                        console.error("Falla de sincronización:", e);
                    }
                } 

                function parseLineaPresentacion(linea) {
                    const i = linea.indexOf(':');
                    if (i === -1) {
                        return { opcion: linea.trim(), precio: '' };
                    }
                    return {
                        opcion: linea.slice(0, i).trim(),
                        precio: linea.slice(i + 1).trim()
                    };
                }

                function agregarFilaPresentacion(opcion = '', precio = '') {
                    const cont = document.getElementById('contenedor-presentaciones');
                    const div = document.createElement('div');
                    div.className = 'flex gap-2 animate-in fade-in slide-in-from-left-2 duration-300';

                    const inputOpcion = document.createElement('input');
                    inputOpcion.type = 'text';
                    inputOpcion.name = 'pres_opcion[]';
                    inputOpcion.value = opcion;
                    inputOpcion.placeholder = 'Ej. 18" o Frasco 60 caps';
                    inputOpcion.required = true;
                    inputOpcion.className = 'flex-1 premium-input rounded-lg px-3 py-2 text-xs font-bold';

                    const wrapPrecio = document.createElement('div');
                    wrapPrecio.className = 'relative w-20';
                    const spanPrecio = document.createElement('span');
                    spanPrecio.className = 'absolute left-2 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]';
                    spanPrecio.textContent = '$';
                    const inputPrecio = document.createElement('input');
                    inputPrecio.type = 'text';
                    inputPrecio.name = 'pres_precio[]';
                    inputPrecio.value = precio;
                    inputPrecio.placeholder = '0.00';
                    inputPrecio.className = 'w-full premium-input rounded-lg pl-5 pr-2 py-2 text-xs font-bold';
                    wrapPrecio.appendChild(spanPrecio);
                    wrapPrecio.appendChild(inputPrecio);

                    const btnEliminar = document.createElement('button');
                    btnEliminar.type = 'button';
                    btnEliminar.className = 'w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-400 hover:bg-rose-500 hover:text-white rounded-lg transition-all border border-rose-100';
                    btnEliminar.addEventListener('click', () => eliminarFilaPresentacion(btnEliminar));
                    btnEliminar.innerHTML = '<i class="fa-solid fa-trash-can text-[10px]"></i>';

                    div.appendChild(inputOpcion);
                    div.appendChild(wrapPrecio);
                    div.appendChild(btnEliminar);
                    cont.appendChild(div);
                }

                function eliminarFilaPresentacion(btn) {
                    btn.closest('div').remove();
                }

                function limpiarFormProd() {
                    document.getElementById('form-action').value = 'crear_producto';
                    document.getElementById('form-id-prod').value = 0;
                    document.getElementById('form-nombre').value = '';
                    document.getElementById('form-codigo').value = '';
                    document.getElementById('form-marca').value = '';
                    document.getElementById('form-categoria').value = '';
                    document.getElementById('form-desc').value = '';
                    document.getElementById('contenedor-presentaciones').innerHTML = '';
                    agregarFilaPresentacion(); // Una fila vacía por defecto
                    document.getElementById('btn-submit-prod').innerText = 'Guardar Producto';
                    document.getElementById('btn-cancel-prod').classList.add('hidden');
                    document.getElementById('form-imagen-actual').value = '';
                }

                function editarProductoDesdeJSON(jsonStr) {
                    try {
                        if (!jsonStr) return;
                        const p = JSON.parse(jsonStr);
                        editarProducto(p.id, p.nombre, p.codigo, p.marca, p.categoria, p.presRaw, p.desc, p.imagen);
                    } catch(e) { 
                        console.error("Error al cargar producto para editar:", e, jsonStr); 
                        alert("Error al cargar los datos del producto."); 
                    }
                }

                function editarProducto(id, nombre, codigo, marca, categoria, presRaw, desc, imagen) { 
                    document.getElementById('form-action').value = 'editar_producto'; 
                    document.getElementById('form-id-prod').value = id; 
                    document.getElementById('form-nombre').value = nombre;
                    document.getElementById('form-codigo').value = codigo;
                    document.getElementById('form-marca').value = marca;
                    document.getElementById('form-categoria').value = categoria;
                    document.getElementById('form-desc').value = desc;
                    document.getElementById('form-imagen-actual').value = imagen || '';
                    
                    const cont = document.getElementById('contenedor-presentaciones');
                    cont.innerHTML = '';
                    if (presRaw) {
                        const lineas = presRaw.split('\n');
                        lineas.forEach(linea => {
                            if (linea.trim()) {
                                const { opcion, precio } = parseLineaPresentacion(linea);
                                agregarFilaPresentacion(opcion, precio);
                            }
                        });
                    }
                    if (cont.innerHTML === '') agregarFilaPresentacion();

                    document.getElementById('btn-submit-prod').innerText = 'Actualizar Producto';
                    document.getElementById('btn-cancel-prod').classList.remove('hidden');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }

                // Inicializar con una fila
                document.addEventListener('DOMContentLoaded', () => {
                    if (document.getElementById('contenedor-presentaciones') && document.getElementById('contenedor-presentaciones').innerHTML === '') {
                        agregarFilaPresentacion();
                    }
                });
            </script>
        
        <?php elseif($vista === 'distribuidores'): 
            $b2b_solo_lectura = !improgyp_b2b_admin_puede_gestionar();
            ?>
            <?php if (!improgyp_b2b_publico_activo()): ?>
                <div class="bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-xl mb-6 text-sm font-bold flex flex-wrap items-center gap-3 relative z-10">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Portal B2B <strong>apagado</strong> para el público. Aquí sigues viendo métricas y mayoristas. Actívalo en <a href="?view=sistema" class="underline text-[#1B263B]">Estado del Sistema</a>.</span>
                </div>
            <?php endif; ?>
            <?php if ($b2b_solo_lectura): ?>
                <div class="bg-slate-100 border border-slate-200 text-slate-600 p-4 rounded-xl mb-6 text-xs font-bold relative z-10">
                    <i class="fa-solid fa-lock"></i> Modo solo lectura: el módulo está en preparación. Solo master puede crear o editar mayoristas.
                </div>
            <?php endif; ?>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 relative z-10">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter uppercase italic">Directorio VIP <span class="text-[#1B263B]">B2B</span></h1>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Inteligencia de Datos y Gestión de Mayoristas</p>
                </div>
                <div class="flex gap-3">
                    <a href="dashboard.php?action=exportar_b2b" class="bg-white hover:bg-[#1B263B] hover:text-white text-[#1B263B] px-6 py-3 rounded-2xl text-xs font-black transition-all duration-300 flex items-center gap-3 border border-slate-100 shadow-sm hover:shadow-xl group" title="Descargar historial de cotizaciones B2B en Excel">
                        <i class="fa-solid fa-file-csv text-lg group-hover:scale-110 transition-transform"></i> Descargar Informe B2B
                    </a>
                </div>
            </div>

            <!-- DASHBOARD DE INTELIGENCIA B2B (NUEVO) -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 relative z-10 mb-8">
                
                <!-- RECUADRO 1: RESCATE B2B (Movido de Radar) -->
                <div class="glass-card p-6 flex flex-col hover:border-amber-400/40 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-50">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg border border-amber-100">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-slate-900 leading-tight uppercase tracking-tighter">Rescate B2B</h2>
                            <p class="text-[9px] text-slate-400 uppercase font-bold tracking-widest">Mesa de Dinero Abandonada</p>
                        </div>
                    </div>
                    <div class="flex-1 flex flex-col gap-3 overflow-y-auto custom-scrollbar max-h-[320px] pr-2">
                        <?php if(empty($dinero_mesa)): ?>
                            <div class="text-center py-10 opacity-40">
                                <i class="fa-solid fa-check-double text-2xl mb-2 text-[#1B263B]"></i>
                                <p class="text-[10px] font-black uppercase tracking-widest">Cartera al día</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($dinero_mesa as $cot): 
                                $nombre_cliente = !empty($cot['cliente_nombre']) ? htmlspecialchars($cot['cliente_nombre']) : 'Postulante VIP';
                                $telefono = !empty($cot['cliente_telefono']) ? $cot['cliente_telefono'] : '';
                                $descuento = !empty($cot['cliente_descuento']) ? $cot['cliente_descuento'] : '0';
                                $mensaje_wa = "Hola {$nombre_cliente}, notamos que estuviste cotizando en nuestro portal mayorista. Recuerda que tienes un {$descuento}% de descuento esperándote. ¿Deseas que terminemos de procesar tu pedido?";
                                $link_wa = $telefono ? "https://wa.me/593" . ltrim($telefono, '0') . "?text=" . urlencode($mensaje_wa) : "#";
                            ?>
                                <div class="bg-slate-50/80 p-3 rounded-xl border border-slate-100 hover:border-amber-500/30 transition-all group">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1 pr-2">
                                            <p class="text-slate-900 font-black text-xs truncate max-w-[140px]"><?= $nombre_cliente ?></p>
                                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter italic"><?= date('d M, h:i A', strtotime($cot['fecha_cot'])) ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-amber-600 font-black text-sm">$<?= number_format($cot['total_cotizado'], 0) ?></p>
                                            <p class="text-[8px] text-slate-400 uppercase font-black"><?= $cot['items'] ?> Unidades</p>
                                        </div>
                                    </div>
                                    <?php if($telefono): ?>
                                        <div class="flex gap-1.5 mt-1">
                                            <a href="<?= $link_wa ?>" target="_blank" class="flex-1 bg-[#25D366]/10 hover:bg-[#25D366] hover:text-white text-[#25D366] text-[10px] font-black py-1.5 rounded-lg text-center transition-all flex items-center justify-center gap-1.5 uppercase tracking-widest">
                                                <i class="fa-brands fa-whatsapp"></i> Impulsar
                                            </a>
                                            <button type="button" onclick="resolverB2B('<?= $cot['main_sess'] ?>', this)" class="bg-slate-100 hover:bg-[#1B263B] hover:text-white text-slate-400 text-[10px] font-black px-3 py-1.5 rounded-lg transition-all" title="Marcar como Gestionado">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- RECUADRO 2: FIDELIDAD VIP (TOP VIPS) -->
                <div class="glass-card p-6 flex flex-col hover:border-indigo-400/40 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-50">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg border border-indigo-100">
                            <i class="fa-solid fa-crown"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-slate-900 leading-tight uppercase tracking-tighter">Fidelidad VIP</h2>
                            <p class="text-[9px] text-slate-400 uppercase font-bold tracking-widest">Mayores tasas de conversión</p>
                        </div>
                    </div>
                    <div class="flex-1 space-y-5 py-2">
                        <?php if(empty($top_vips_conversion)): ?>
                            <p class="text-[10px] text-slate-400 text-center py-10 uppercase tracking-widest font-black">Esperando interacciones...</p>
                        <?php else: ?>
                            <?php foreach($top_vips_conversion as $idx => $vip): 
                                $tasa = $vip['total_cotizaciones'] > 0 ? ($vip['convertidas'] / $vip['total_cotizaciones']) * 100 : 0;
                            ?>
                                <div>
                                    <div class="flex justify-between items-center mb-1.5">
                                        <span class="text-xs font-black text-slate-800 flex items-center gap-2">
                                            <span class="w-4 h-4 rounded bg-slate-100 text-[9px] flex items-center justify-center text-slate-400"><?= $idx+1 ?></span>
                                            <?= htmlspecialchars($vip['nombre']) ?>
                                        </span>
                                        <span class="text-[10px] font-black <?= $tasa > 50 ? 'text-[#1B263B]' : 'text-indigo-500' ?>"><?= round($tasa) ?>% Conv.</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-indigo-500 to-[#1B263B] transition-all duration-1000" style="width: <?= $tasa ?>% shadow-sm"></div>
                                    </div>
                                    <div class="flex justify-between mt-1 opacity-60">
                                        <span class="text-[8px] uppercase font-bold tracking-wider text-slate-400"><?= $vip['total_cotizaciones'] ?> Cotizados</span>
                                        <span class="text-[8px] uppercase font-bold tracking-wider text-slate-400"><?= $vip['convertidas'] ?> Cerrados</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Ticket Promedio B2B:</span>
                        <span class="text-xs font-black text-slate-900 bg-slate-50 px-2 py-1 rounded-lg">$<?= number_format($ticket_promedio_b2b, 0) ?></span>
                    </div>
                </div>

                <!-- RECUADRO 3: PRODUCTOS ESTRELLA B2B -->
                <div class="glass-card p-6 flex flex-col hover:border-[#1B263B]/40 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-50">
                        <div class="w-10 h-10 rounded-xl bg-[#1B263B]/10 text-[#1B263B] flex items-center justify-center text-lg border border-[#1B263B]/20">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-slate-900 leading-tight uppercase tracking-tighter">Estrellas VIP</h2>
                            <p class="text-[9px] text-slate-400 uppercase font-bold tracking-widest">Lo más solicitado por mayoristas</p>
                        </div>
                    </div>
                    <div class="flex-1 space-y-4">
                        <?php if(empty($productos_estrella_b2b)): ?>
                             <p class="text-[10px] text-slate-400 text-center py-10 uppercase tracking-widest font-black">Sin datos de volumen...</p>
                        <?php else: ?>
                            <?php foreach($productos_estrella_b2b as $p): ?>
                                <div class="flex items-center gap-3 bg-slate-50/50 p-2.5 rounded-xl border border-slate-100">
                                    <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-[#1B263B] text-xs font-black border border-slate-200">
                                        <?= $p['total'] ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-black text-slate-800 truncate"><?= htmlspecialchars($p['producto_nombre']) ?></p>
                                        <p class="text-[8px] text-slate-400 uppercase font-bold tracking-wider">Unidades Cotizadas</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="mt-6 p-4 rounded-2xl bg-gradient-to-br from-[#1B263B]/20 to-indigo-500/10 border border-[#1B263B]/20">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-bolt-lightning text-[#1B263B] animate-bounce"></i>
                            <div>
                                <p class="text-[9px] font-black text-slate-800 uppercase tracking-tighter">Oportunidad VIP</p>
                                <p class="text-[8px] text-slate-500 leading-tight">Sugiere packs basados en interés real.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <?php if (!empty($pedidos_b2b_recientes)): ?>
            <div class="glass-card p-6 mb-8 relative z-10 border-[#1B263B]/20">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-[#1B263B]"></i> Últimos pedidos formales B2B
                    </h3>
                    <a href="?view=pedidos" class="text-[10px] font-black uppercase tracking-widest text-[#1B263B] hover:underline">Ver todos</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-[10px] font-black text-slate-400 uppercase">
                            <tr><th class="py-2 pr-4">#</th><th class="py-2 pr-4">Cliente</th><th class="py-2 pr-4">Total</th><th class="py-2">Estado</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach ($pedidos_b2b_recientes as $pb): ?>
                            <tr>
                                <td class="py-2 font-mono text-xs">#<?= (int)$pb['id'] ?></td>
                                <td class="py-2"><span class="font-bold text-slate-800"><?= htmlspecialchars($pb['nombre_cliente']) ?></span><br><span class="text-[10px] text-slate-400"><?= htmlspecialchars($pb['ruc_cliente']) ?></span></td>
                                <td class="py-2 font-black text-[#1B263B]">$<?= number_format((float)$pb['total'], 2) ?></td>
                                <td class="py-2"><span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-slate-100"><?= htmlspecialchars($pb['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_b2b_pin'])): $fb = $_SESSION['flash_b2b_pin']; unset($_SESSION['flash_b2b_pin']); ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 p-4 rounded-xl mb-6 text-sm relative z-10">
                    <p class="font-black mb-1"><i class="fa-solid fa-key"></i> Mayorista creado — copia el PIN ahora (no se volverá a mostrar)</p>
                    <p class="font-mono text-xs mt-2"><strong><?= htmlspecialchars($fb['nombre']) ?></strong> · RUC <?= htmlspecialchars($fb['ruc']) ?> · PIN <span class="text-lg font-black"><?= htmlspecialchars($fb['pin']) ?></span></p>
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'historial_limpio'): ?>
                <div class="bg-[#1B263B]/20 border border-[#1B263B]/50 text-[#1B263B] p-4 rounded-lg mb-6 text-sm font-bold flex items-center gap-2 relative z-10">
                    <i class="fa-solid fa-broom"></i> Historial del cliente limpiado con éxito.
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'b2b_estado_usuario'): ?>
                <div class="bg-[#1B263B]/10 border border-[#1B263B]/30 text-[#1B263B] p-4 rounded-lg mb-6 text-sm font-bold relative z-10"><i class="fa-solid fa-toggle-on"></i> Estado del mayorista actualizado.</div>
            <?php endif; ?>
            <?php if(isset($_GET['err']) && $_GET['err'] === 'b2b_solo_lectura'): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-lg mb-6 text-sm font-bold relative z-10">No tienes permiso para modificar mayoristas mientras el módulo está en preparación.</div>
            <?php endif; ?>
            <?php if(isset($_GET['err']) && $_GET['err'] === 'pin_requerido'): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-lg mb-6 text-sm font-bold relative z-10">El PIN es obligatorio al crear un mayorista nuevo.</div>
            <?php endif; ?>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 relative z-10">
                
                <?php if (!$b2b_solo_lectura): ?>
                <div class="glass-card p-6 h-fit hover:border-[#1B263B]/40 transition-colors" id="panel-form-b2b">
                    <h2 id="titulo-form-b2b" class="text-lg font-black text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-user-plus text-[#1B263B]"></i> Nuevo Mayorista
                    </h2>
                    <form method="POST" action="dashboard.php?view=distribuidores" class="space-y-4" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="crear_usuario_b2b">
                        
                        <div>
                            <label class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1 block">RUC / ID</label>
                            <input type="text" name="ruc" id="b2b_ruc" required autocomplete="off" class="w-full premium-input rounded-lg px-3 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1 block">Nombre Negocio</label>
                            <input type="text" name="nombre" id="b2b_nombre" required autocomplete="off" class="w-full premium-input rounded-lg px-3 py-2.5 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1 block">PIN Clave</label>
                                <input type="password" name="pin" id="b2b_pin" autocomplete="new-password" placeholder="Obligatorio al crear" class="w-full premium-input rounded-lg px-3 py-2.5 text-sm font-mono">
                            </div>
                            <div>
                                <label class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1 block">Dscto (%)</label>
                                <input type="number" step="0.01" name="descuento" id="b2b_descuento" required class="w-full premium-input rounded-lg px-3 py-2.5 text-[#1B263B] text-sm font-black">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1 block">Teléfono WP</label>
                            <input type="text" name="telefono" id="b2b_telefono" required placeholder="099..." autocomplete="off" class="w-full premium-input rounded-lg px-3 py-2.5 text-sm">
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" id="btn-submit-b2b" class="flex-1 bg-[#1B263B] text-white hover:bg-[#3A86FF] font-black py-3 rounded-xl transition-all shadow-lg shadow-[#1B263B]/20 uppercase tracking-widest text-xs">Generar Acceso B2B</button>
                            <button type="button" id="btn-cancel-b2b" onclick="limpiarFormB2B()" class="hidden bg-slate-100 text-slate-500 hover:bg-slate-200 font-bold py-3 px-4 rounded-xl transition-colors border border-slate-200" title="Cancelar">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <div class="glass-card p-6 h-fit border-slate-200 text-center text-slate-400">
                    <i class="fa-solid fa-lock text-2xl mb-3"></i>
                    <p class="text-xs font-black uppercase tracking-widest">Alta de mayoristas deshabilitada</p>
                </div>
                <?php endif; ?>

                <div class="<?= $b2b_solo_lectura ? 'xl:col-span-3' : 'xl:col-span-2' ?> glass-card overflow-hidden flex flex-col hover:border-[#1B263B]/40 transition-colors">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-slate-500 border-b border-slate-100 bg-slate-50/50 text-xs uppercase tracking-wider font-black">
                                <tr>
                                    <th class="p-4">Cliente VIP</th>
                                    <th class="p-4">Credenciales</th>
                                    <th class="p-4 text-center">Beneficio</th>
                                    <th class="p-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach($usuarios_b2b as $u): 
                                    $tel_wa = preg_replace('/[^0-9]/', '', $u['telefono']); 
                                    if (strpos($tel_wa, '09') === 0) { $tel_wa = '593' . substr($tel_wa, 1); } 
                                    $link_app = $base_url . "/b2b/"; 
                                    $mensaje_wp = rawurlencode("¡Hola *{$u['nombre']}*! Bienvenido al Club VIP de IMPROGYP.\n\n📱 Accede a tu cotizador IA:\n{$link_app}\n\n*Usuario (RUC):* {$u['ruc']}\n*Beneficio:* {$u['descuento']}% OFF\n\nTu PIN te lo compartimos por este medio de forma segura."); 
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4 align-middle">
                                        <p class="font-black text-slate-900 text-[15px]"><?= htmlspecialchars($u['nombre']) ?></p>
                                        <p class="text-[11px] text-slate-500 font-bold uppercase tracking-tight"><i class="fa-brands fa-whatsapp text-[#25D366]"></i> <?= $u['telefono'] ?></p>
                                    </td>
                                    <td class="p-4 align-middle font-mono text-slate-600 text-xs">
                                        <span class="text-slate-400">ID:</span> <?= htmlspecialchars($u['ruc']) ?><br>
                                        <span class="text-slate-400">PIN:</span> <span class="tracking-widest">••••••</span>
                                    </td>
                                    <td class="p-4 align-middle text-center">
                                        <span class="bg-[#1B263B]/10 text-[#1B263B] px-2.5 py-1 rounded-lg font-black text-xs border border-[#1B263B]/20"><?= floatval($u['descuento']) ?>%</span>
                                        <?php if ((int)($u['activo'] ?? 1) === 0): ?>
                                            <span class="block mt-1 text-[9px] font-black uppercase text-rose-500">Suspendido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 align-middle text-right space-x-1 whitespace-nowrap">
                                        <button type="button" onclick="abrirChatB2B('<?= htmlspecialchars($u['ruc'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['nombre'], ENT_QUOTES) ?>')" class="w-8 h-8 bg-indigo-500/10 text-indigo-400 rounded-lg hover:bg-indigo-500 hover:text-white transition-colors" title="Ver Historial de Chat IA">
                                            <i class="fa-solid fa-comments"></i>
                                        </button>
                                        <form method="POST" action="dashboard.php?view=distribuidores" onsubmit="return confirm('¿Seguro que deseas vaciar el historial de chat de este cliente?');" class="inline-block">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                            <input type="hidden" name="action" value="limpiar_historial_b2b">
                                            <input type="hidden" name="ruc_cliente" value="<?= htmlspecialchars($u['ruc']) ?>">
                                            <button type="submit" class="w-8 h-8 bg-amber-500/10 text-amber-500 rounded-lg flex items-center justify-center hover:bg-amber-500 hover:text-white transition-colors" title="Limpiar Historial de Chat">
                                                <i class="fa-solid fa-broom"></i>
                                            </button>
                                        </form>
                                        <?php if (!$b2b_solo_lectura): ?>
                                        <a href="https://wa.me/<?= htmlspecialchars($tel_wa) ?>?text=<?= $mensaje_wp ?>" target="_blank" class="inline-flex w-8 h-8 bg-[#25D366]/10 text-[#25D366] rounded-lg items-center justify-center hover:bg-[#25D366] hover:text-white transition-colors" title="Enviar credenciales (incluye PIN actual)">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </a>
                                        <form method="POST" action="dashboard.php?view=distribuidores" class="inline-block" title="<?= (int)($u['activo'] ?? 1) ? 'Suspender acceso' : 'Reactivar' ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                            <input type="hidden" name="action" value="toggle_usuario_b2b">
                                            <input type="hidden" name="id_b2b" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="activo" value="<?= (int)($u['activo'] ?? 1) ? 0 : 1 ?>">
                                            <button type="submit" class="w-8 h-8 <?= (int)($u['activo'] ?? 1) ? 'bg-slate-100 text-slate-500' : 'bg-emerald-500/10 text-emerald-600' ?> rounded-lg hover:bg-[#1B263B] hover:text-white transition-colors">
                                                <i class="fa-solid <?= (int)($u['activo'] ?? 1) ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                            </button>
                                        </form>
                                        <button type="button" onclick="editarB2B('<?= htmlspecialchars($u['ruc'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['nombre'], ENT_QUOTES) ?>', <?= $u['descuento'] ?>, '<?= htmlspecialchars($u['telefono'], ENT_QUOTES) ?>')" class="w-8 h-8 bg-blue-500/10 text-blue-400 rounded-lg hover:bg-blue-500 hover:text-white transition-colors">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="POST" action="dashboard.php?view=distribuidores" onsubmit="return confirm('¿Revocar acceso?');" class="inline-block">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                            <input type="hidden" name="action" value="eliminar_usuario_b2b">
                                            <input type="hidden" name="id_b2b" value="<?= $u['id'] ?>">
                                            <button type="submit" class="w-8 h-8 bg-rose-500/10 text-rose-500 rounded-lg flex items-center justify-center hover:bg-rose-500 hover:text-white transition-colors">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="modal-chat-b2b" class="fixed inset-0 bg-white/80 backdrop-blur-xl z-50 hidden flex items-center justify-center opacity-0 transition-opacity">
                    <div class="bg-white rounded-2xl w-full max-w-lg mx-4 flex flex-col h-[600px] max-h-[85vh] transform scale-95 transition-transform" id="modal-chat-content">
                        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-2xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-500 flex items-center justify-center"><i class="fa-solid fa-robot"></i></div>
                                <div>
                                    <h3 class="font-black text-slate-800 text-sm" id="chat-b2b-nombre">Cliente VIP</h3>
                                    <p class="text-[10px] text-slate-500 uppercase tracking-widest" id="chat-b2b-ruc">RUC</p>
                                </div>
                            </div>
                            <button type="button" onclick="cerrarChatB2B()" class="text-slate-400 hover:text-rose-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-rose-50"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <div id="chat-b2b-messages" class="flex-1 overflow-y-auto p-4 bg-slate-50/50 custom-scrollbar">
                        </div>
                        <div class="p-3 border-t border-slate-100 bg-white rounded-b-2xl text-center">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest"><i class="fa-solid fa-lock text-[#1B263B] mr-1"></i> Historial Encriptado</span>
                        </div>
                    </div>
                </div>

                </div>

            <style>
                .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
                .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            </style>

            <script>
                function editarB2B(ruc, nombre, descuento, telefono) { 
                    document.getElementById('b2b_ruc').value = ruc; document.getElementById('b2b_ruc').readOnly = true; document.getElementById('b2b_ruc').classList.add('opacity-50'); 
                    document.getElementById('b2b_nombre').value = nombre; 
                    document.getElementById('b2b_pin').value = ''; 
                    document.getElementById('b2b_pin').placeholder = 'Dejar vacío para mantener';
                    document.getElementById('b2b_descuento').value = descuento; 
                    document.getElementById('b2b_telefono').value = telefono; 
                    document.getElementById('titulo-form-b2b').innerHTML = '<i class="fa-solid fa-pen text-blue-400"></i> Editando Cliente'; 
                    document.getElementById('btn-submit-b2b').innerText = 'Actualizar Datos'; 
                    document.getElementById('btn-cancel-b2b').classList.remove('hidden'); 
                    document.getElementById('panel-form-b2b').scrollIntoView({behavior: 'smooth'}); 
                } 
                function limpiarFormB2B() { 
                    document.getElementById('b2b_ruc').value = ''; document.getElementById('b2b_ruc').readOnly = false; document.getElementById('b2b_ruc').classList.remove('opacity-50'); 
                    document.getElementById('b2b_nombre').value = ''; 
                    document.getElementById('b2b_pin').value = ''; 
                    document.getElementById('b2b_descuento').value = ''; 
                    document.getElementById('b2b_telefono').value = ''; 
                    document.getElementById('titulo-form-b2b').innerHTML = '<i class="fa-solid fa-user-plus text-[#1B263B]"></i> Nuevo Mayorista'; 
                    document.getElementById('btn-submit-b2b').innerText = 'Generar Acceso B2B'; 
                    document.getElementById('btn-cancel-b2b').classList.add('hidden'); 
                }

                async function resolverB2B(sessionId, btn) {
                    if(!confirm('¿Marcar esta cotización como gestionada?')) return;
                    const card = btn.closest('.group');
                    card.style.opacity = '0.5';
                    card.style.pointerEvents = 'none';
                    try {
                        const response = await fetch('dashboard.php?ajax=marcar_gestionado_b2b', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({session_id: sessionId})
                        });
                        const res = await response.json();
                        if (res.status === 'success') {
                            card.style.transform = 'scale(0.8)';
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 300);
                        }
                    } catch (e) { 
                        card.style.opacity = '1'; 
                        card.style.pointerEvents = 'all';
                    }
                }

                async function abrirChatB2B(ruc, nombre) {
                    document.getElementById('chat-b2b-nombre').innerText = nombre;
                    document.getElementById('chat-b2b-ruc').innerText = 'RUC: ' + ruc;
                    const container = document.getElementById('chat-b2b-messages');
                    container.innerHTML = '<div class="text-center text-slate-400 py-10"><i class="fa-solid fa-circle-notch fa-spin text-2xl mb-2"></i><br><span class="text-xs">Cargando historial...</span></div>';
                    
                    const modal = document.getElementById('modal-chat-b2b');
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modal.classList.remove('opacity-0');
                        document.getElementById('modal-chat-content').classList.remove('scale-95');
                    }, 10);

                    try {
                        const response = await fetch(`dashboard.php?ajax=cargar_chat_b2b&ruc=${ruc}`);
                        const mensajes = await response.json();
                        
                        if (mensajes.length === 0) {
                            container.innerHTML = '<div class="text-center text-slate-400 py-10"><i class="fa-regular fa-comments text-3xl mb-3 opacity-50"></i><br><span class="text-sm">No hay mensajes registrados con la IA.</span></div>';
                            return;
                        }

                        let html = '';
                        mensajes.forEach(msg => {
                            const isIA = msg.remitente === 'ia';
                            const time = new Date(msg.fecha).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            
                            let formattedMsg = msg.mensaje
                                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                                .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" class="text-blue-500 underline font-bold">$1</a>');
                                
                            const hasTable = formattedMsg.includes('|---|') || formattedMsg.includes('| Img |');
                            const alignClass = isIA ? 'justify-start' : 'justify-end';
                            const flexDir = isIA ? 'items-start' : 'items-end';
                            const bubbleColor = isIA ? 'bg-white border border-slate-200 text-slate-700 rounded-tr-xl' : 'bg-[#1B263B]/10 text-slate-900 rounded-tl-xl border border-[#1B263B]/20';
                            const textStyle = hasTable ? 'whitespace-pre overflow-x-auto custom-scrollbar font-mono text-[11px]' : 'whitespace-pre-wrap text-[13px] break-words';

                            html += `
                            <div class="flex ${alignClass} mb-4">
                                <div class="max-w-[85%] flex flex-col ${flexDir}">
                                    <div class="px-4 py-3 rounded-b-xl ${bubbleColor} leading-relaxed w-fit max-w-full ${textStyle}">${formattedMsg.trim()}</div>
                                    <div class="text-[9px] text-slate-400 mt-1 flex gap-1 items-center font-bold">
                                        ${isIA ? '<i class="fa-solid fa-robot text-indigo-400"></i> IA' : 'Cliente'} • ${time}
                                    </div>
                                </div>
                            </div>`;
                        });
                        container.innerHTML = html;
                        container.scrollTop = container.scrollHeight;
                    } catch (error) {
                        container.innerHTML = '<div class="text-center text-rose-500 py-10"><i class="fa-solid fa-triangle-exclamation text-2xl mb-2"></i><br><span class="text-xs">Error al cargar el historial.</span></div>';
                    }
                }

                function cerrarChatB2B() {
                    const modal = document.getElementById('modal-chat-b2b');
                    modal.classList.add('opacity-0');
                    document.getElementById('modal-chat-content').classList.add('scale-95');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                }
            </script>
        
        <?php elseif($vista === 'marketing'): ?>
            <?php list($tit_todos_normal, $tit_todos_resaltado) = extraerTextos($textos_guardados['Todos']['tit'] ?? ""); if(empty($tit_todos_normal)) { $tit_todos_normal = "Herramientas profesionales para"; $tit_todos_resaltado = "tu obra."; } ?>
            <div class="glass-card p-8 relative z-10 hover:border-[#1B263B]/40 transition-colors">
                <h2 class="text-xl font-black text-slate-900 mb-2 flex items-center gap-2"><i class="fa-solid fa-wand-magic-sparkles text-[#1B263B]"></i> Textos Persuasivos (IA)</h2>
                <p class="text-sm text-slate-500 mb-2">Publica en <strong>productos.php</strong> vía <code class="text-xs bg-slate-100 px-1 rounded">textos_tienda.json</code>. Los encabezados del <strong>home</strong> (index) se editan en <a href="?view=apariencia&sub=home" class="text-[#3A86FF] font-bold underline">Editor del Home</a>.</p>
                <?php include __DIR__ . '/components/gemini_status_badge.php'; ?>
                <p class="text-[11px] text-slate-400 mb-6">Tras usar IA, pulsa <strong>Guardar y Sincronizar Tienda</strong> para que se vea en el catálogo. Un botón IA a la vez.</p>
                <form method="POST" action="dashboard.php?view=marketing" id="form-marketing-ia">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="guardar_textos_ia">
                    
                    <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 mb-8 hover:border-[#1B263B]/30 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                            <div>
                                <h3 class="font-black text-[#1B263B] uppercase tracking-wider text-xs">Catálogo — vista «Todos»</h3>
                                <p class="text-[10px] text-slate-400 mt-1">No es el home (index); solo el encabezado al ver todo el catálogo.</p>
                            </div>
                            <button type="button" onclick="generarCopyIA('Todos', 'tit_norm_todos', 'tit_resal_todos', 'sub_todos', this)" class="btn-copy-ia-marketing shrink-0 bg-[#1B263B]/10 text-[#1B263B] border border-[#1B263B]/30 rounded-lg py-2.5 px-4 text-xs font-black hover:bg-[#1B263B] hover:text-white transition-colors flex items-center justify-center gap-2">
                                <i class="fa-solid fa-robot"></i> <span class="btn-text">IA</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Texto Principal</label>
                                <input type="text" id="tit_norm_todos" name="tit_todos_normal" value="<?= htmlspecialchars($tit_todos_normal) ?>" class="w-full premium-input rounded-lg px-4 py-2.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-[#1B263B] uppercase tracking-widest mb-1.5">Texto Destacado (Efecto Láser)</label>
                                <input type="text" id="tit_resal_todos" name="tit_todos_resaltado" value="<?= htmlspecialchars($tit_todos_resaltado) ?>" class="w-full bg-[#1B263B]/5 border border-[#1B263B]/20 rounded-lg px-4 py-2.5 text-[#1B263B] font-black text-sm focus:border-[#1B263B] outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Subtítulo Descriptivo</label>
                            <input type="text" id="sub_todos" name="sub_todos" value="<?= htmlspecialchars($textos_guardados['Todos']['sub'] ?? "Explora nuestro catálogo completo o utiliza la consola IA.") ?>" class="w-full premium-input rounded-lg px-4 py-2.5 text-sm">
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <?php foreach($categorias_maestras as $c): $cat = $c['nombre']; 
                            list($tit_cat_norm, $tit_cat_resal) = extraerTextos($textos_guardados[$cat]['tit'] ?? ""); 
                            $sub_cat = $textos_guardados[$cat]['sub'] ?? ""; 
                        ?>
                        <div class="bg-white p-5 rounded-xl border border-slate-100 flex flex-col xl:flex-row gap-4 items-start xl:items-center hover:border-[#1B263B]/40 transition-colors">
                            <div class="w-full xl:w-2/12">
                                <span class="bg-slate-100 px-3 py-1.5 rounded-lg text-[11px] font-black text-slate-700 border border-slate-200 block text-center truncate uppercase tracking-wider"><?= $cat ?></span>
                            </div>
                            <div class="w-full xl:w-8/12 grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block xl:hidden text-[10px] text-slate-500 mb-1">Texto Principal</label>
                                    <input type="text" id="tit_norm_<?= md5($cat) ?>" name="tit_normal[<?= $cat ?>]" value="<?= htmlspecialchars($tit_cat_norm) ?>" placeholder="Ej: Lo mejor en" class="w-full premium-input rounded-lg px-3 py-2 text-xs">
                                </div>
                                <div>
                                    <label class="block xl:hidden text-[10px] text-[#1B263B] mb-1">Texto Láser</label>
                                    <input type="text" id="tit_resal_<?= md5($cat) ?>" name="tit_resaltado[<?= $cat ?>]" value="<?= htmlspecialchars($tit_cat_resal) ?>" placeholder="Ej: construcción deportiva" class="w-full bg-[#1B263B]/5 border border-[#1B263B]/20 rounded-lg px-3 py-2 text-xs text-[#1B263B] font-black focus:border-[#1B263B] outline-none">
                                </div>
                                <div>
                                    <label class="block xl:hidden text-[10px] text-slate-500 mb-1">Subtítulo</label>
                                    <input type="text" id="sub_<?= md5($cat) ?>" name="subtitulos[<?= $cat ?>]" value="<?= htmlspecialchars($sub_cat) ?>" placeholder="Subtítulo corto" class="w-full premium-input rounded-lg px-3 py-2 text-xs">
                                </div>
                            </div>
                            <div class="w-full xl:w-2/12 flex justify-end">
                                <button type="button" onclick="generarCopyIA('<?= $cat ?>', 'tit_norm_<?= md5($cat) ?>', 'tit_resal_<?= md5($cat) ?>', 'sub_<?= md5($cat) ?>', this)" class="btn-copy-ia-marketing w-full xl:w-auto bg-[#1B263B]/10 text-[#1B263B] border border-[#1B263B]/30 rounded-lg py-2.5 px-4 text-xs font-black hover:bg-[#1B263B] hover:text-white transition-colors flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-robot"></i> <span class="btn-text">IA</span>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-8 sticky bottom-4 flex justify-end z-20">
                        <button type="submit" class="bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-4 px-10 rounded-2xl transition-all flex items-center gap-3 text-sm hover:scale-105 transition-transform">
                            <i class="fa-solid fa-cloud-arrow-up text-lg"></i> Guardar y Sincronizar Tienda
                        </button>
                    </div>
                </form>
            </div>
            <script>
                window.iaCopyEnCurso = window.iaCopyEnCurso || false;
                function setEstadoBotonesCopyMarketing(disabled) {
                    document.querySelectorAll('.btn-copy-ia-marketing').forEach(btn => {
                        btn.disabled = disabled;
                        btn.classList.toggle('opacity-40', disabled);
                        btn.classList.toggle('pointer-events-none', disabled);
                    });
                }
                async function generarCopyIA(categoria, idNorm, idResal, idSub, btnElement) {
                    if (window.iaCopyEnCurso) {
                        alert('Espera a que termine la generación anterior (un IA a la vez).');
                        return;
                    }
                    window.iaCopyEnCurso = true;
                    setEstadoBotonesCopyMarketing(true);
                    const icon = btnElement.querySelector('i');
                    const textSpan = btnElement.querySelector('.btn-text');
                    const normEl = document.getElementById(idNorm);
                    const resalEl = document.getElementById(idResal);
                    const subEl = document.getElementById(idSub);
                    icon.className = 'fa-solid fa-circle-notch fa-spin';
                    textSpan.innerText = '...';
                    btnElement.classList.add('bg-[#1B263B]', 'text-white');
                    try {
                        const response = await fetch('dashboard.php?ajax=generar_copy', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                categoria: categoria,
                                tit_actual: normEl?.value || '',
                                resal_actual: resalEl?.value || '',
                                sub_actual: subEl?.value || '',
                                regenerar: true
                            })
                        });
                        const data = await response.json();
                        if (data.error) {
                            alert('Error IA: ' + data.error);
                        } else if (data.tit_normal && data.sub) {
                            normEl.value = data.tit_normal;
                            resalEl.value = data.tit_resaltado || '';
                            subEl.value = data.sub;
                            icon.className = 'fa-solid fa-check';
                            textSpan.innerText = 'Ok';
                        } else {
                            alert('Formato devuelto incorrecto. Reintenta.');
                        }
                    } catch (error) {
                        console.error('AJAX Error:', error);
                        alert('Error de conexión técnica. Verifica el servidor.');
                    } finally {
                        setTimeout(() => {
                            icon.className = 'fa-solid fa-robot';
                            textSpan.innerText = 'IA';
                            btnElement.classList.remove('bg-[#1B263B]', 'text-white');
                            window.iaCopyEnCurso = false;
                            setEstadoBotonesCopyMarketing(false);
                        }, 2000);
                    }
                }
            </script>
        <?php elseif($vista === 'usuarios'): ?>
            <?php 
                if ($_SESSION['admin_rol'] !== 'master') { echo "<script>window.location='dashboard.php';</script>"; exit; }
                $usuarios_db = $pdo->query("SELECT * FROM usuarios_admin ORDER BY rol DESC, usuario ASC")->fetchAll(PDO::FETCH_ASSOC);
                $cupos_restantes = 4 - count($usuarios_db);
            ?>
            <div class="grid grid-cols-1 gap-8 relative z-10">
                <div class="glass-card p-8">
                    <div class="flex justify-between items-center mb-8 border-b border-slate-100 pb-6">
                        <div>
                            <h2 class="text-xl font-black text-slate-900 uppercase">Personal Administrativo</h2>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">
                                <span class="text-[#1B263B]"><?= count($usuarios_db) ?> de 4</span> Cupos Utilizados
                            </p>
                        </div>
                        <?php if ($cupos_restantes > 0): ?>
                            <button onclick="document.getElementById('modal-usuario').classList.remove('hidden'); document.getElementById('form-u-action').value='crear_usuario_admin'; document.getElementById('titulo-modal-u').innerText='Nuevo Gestor'; document.getElementById('form-u-id').value=''; document.getElementById('form-u-nombre').value=''; document.getElementById('form-u-user').value=''; document.getElementById('form-u-pass').required=true;" class="bg-[#1B263B] hover:bg-[#3A86FF] text-white px-5 py-2.5 rounded-xl text-xs font-black flex items-center gap-2 uppercase tracking-widest transition-all">
                                <i class="fa-solid fa-user-plus"></i> Crear Gestor
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <th class="px-4 py-4">Usuario / Nombre</th>
                                    <th class="px-4 py-4">Rol / Rango</th>
                                    <th class="px-4 py-4 text-center">Estado</th>
                                    <th class="px-4 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach($usuarios_db as $u): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-4 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-black text-xs uppercase">
                                                    <?= substr($u['usuario'], 0, 2) ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-black text-slate-900"><?= $u['usuario'] ?></p>
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase"><?= $u['nombre'] ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-5">
                                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider <?= $u['rol'] === 'master' ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600' ?>">
                                                <?= $u['rol'] === 'master' ? 'Master Admin' : 'Gestor' ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-5 text-center">
                                            <?php if($u['rol'] !== 'master'): ?>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" value="" class="sr-only peer" <?= $u['activo'] ? 'checked' : '' ?> onchange="toggleUsuario(<?= $u['id'] ?>, this.checked ? 1 : 0)">
                                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1B263B]"></div>
                                                </label>
                                            <?php else: ?>
                                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center justify-center gap-1">
                                                    <i class="fa-solid fa-shield-halved text-[#1B263B]"></i> Activo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-5 text-right">
                                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button onclick="editarUsuarioAdmin(<?= $u['id'] ?>, '<?= $u['usuario'] ?>', '<?= $u['nombre'] ?>')" class="w-8 h-8 rounded-lg bg-white border border-slate-100 text-slate-400 hover:border-blue-500 hover:text-blue-500 transition-all flex items-center justify-center"><i class="fa-solid fa-pen text-[10px]"></i></button>
                                                <?php if($u['rol'] !== 'master'): ?>
                                                    <a href="dashboard.php?view=usuarios&action=eliminar_usuario_admin&id_usuario=<?= $u['id'] ?>" onclick="return confirm('¿Eliminar cuenta?')" class="w-8 h-8 rounded-lg bg-white border border-slate-100 text-slate-400 hover:border-rose-500 hover:text-rose-500 transition-all flex items-center justify-center"><i class="fa-solid fa-trash text-[10px]"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- MODAL GESTIÓN USUARIO -->
            <div id="modal-usuario" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[100] hidden flex items-center justify-center">
                <div class="bg-white rounded-[2.5rem] w-full max-w-md p-10 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[#1B263B] blur-[80px] opacity-10"></div>
                    <form method="POST" action="dashboard.php?view=usuarios">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" id="form-u-action" value="crear_usuario_admin">
                        <input type="hidden" name="id_usuario" id="form-u-id">
                        
                        <h3 id="titulo-modal-u" class="text-2xl font-black text-slate-900 uppercase tracking-tighter mb-8 italic">Nuevo Gestor</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 block mb-2">Nombre Completo</label>
                                <input type="text" name="nombre" id="form-u-nombre" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-sm font-bold focus:bg-white focus:border-[#1B263B] outline-none transition-all">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 block mb-2">Usuario / Nickname</label>
                                <input type="text" name="usuario" id="form-u-user" required class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-sm font-bold focus:bg-white focus:border-[#1B263B] outline-none transition-all">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 block mb-2">Contraseña (Dejar vacío para mantener)</label>
                                <input type="password" name="password" id="form-u-pass" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4 text-sm font-mono focus:bg-white focus:border-[#1B263B] outline-none transition-all">
                            </div>

                            <div class="flex gap-4 pt-4">
                                <button type="button" onclick="document.getElementById('modal-usuario').classList.add('hidden')" class="flex-1 bg-slate-100 text-slate-400 font-black py-4 rounded-2xl text-xs uppercase tracking-widest hover:bg-slate-200 transition-all">Cancelar</button>
                                <button type="submit" class="flex-1 bg-slate-900 text-white font-black py-4 rounded-2xl text-xs uppercase tracking-widest hover:bg-[#1B263B] transition-all shadow-xl shadow-slate-900/10">Guardar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function toggleUsuario(id, estado) {
                    const fd = new FormData(); fd.append('action', 'toggle_usuario_admin'); fd.append('id_usuario', id); fd.append('estado', estado); fd.append('csrf_token', '<?= $csrf_token ?>');
                    fetch('dashboard.php', { method: 'POST', body: fd });
                }
                function editarUsuarioAdmin(id, user, nombre) {
                    document.getElementById('form-u-id').value = id;
                    document.getElementById('form-u-user').value = user;
                    document.getElementById('form-u-nombre').value = nombre;
                    document.getElementById('form-u-action').value = 'editar_usuario_admin';
                    document.getElementById('titulo-modal-u').innerText = 'Editar Usuario';
                    document.getElementById('form-u-pass').required = false;
                    document.getElementById('modal-usuario').classList.remove('hidden');
                }
            </script>
        <?php elseif($vista === 'pedidos'): ?>
            <div class="glass-card overflow-hidden flex flex-col hover:border-[#1B263B]/40 transition-colors relative z-10">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Listado de Órdenes Recientes</h2>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-white px-3 py-1 rounded-full border border-slate-100">
                        Total: <?= count($pedidos) ?> pedidos
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-slate-500 border-b border-slate-100 bg-slate-50/50 text-xs uppercase tracking-wider font-black">
                            <tr>
                                <th class="p-4">Fecha / ID</th>
                                <th class="p-4">Cliente / RUC</th>
                                <th class="p-4">Productos Detallados</th>
                                <th class="p-4">Total</th>
                                <th class="p-4 text-center">Estado</th>
                                <th class="p-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach($pedidos as $ped): 
                                $items = json_decode($ped['items_json'], true) ?: [];
                                $status_colors = [
                                    'pendiente' => 'bg-amber-100 text-amber-600',
                                    'confirmado' => 'bg-blue-100 text-blue-600',
                                    'despachado' => 'bg-emerald-100 text-emerald-600',
                                    'cancelado' => 'bg-rose-100 text-rose-600'
                                ];
                                $color = $status_colors[$ped['status']] ?? 'bg-slate-100 text-slate-600';
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 align-top">
                                    <p class="font-black text-slate-900">#<?= $ped['id'] ?></p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase"><?= date('d/m/Y H:i', strtotime($ped['fecha'])) ?></p>
                                </td>
                                <td class="p-4 align-top">
                                    <p class="font-black text-slate-900"><?= htmlspecialchars($ped['nombre_cliente']) ?></p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest"><?= $ped['ruc_cliente'] ?></p>
                                </td>
                                <td class="p-4 align-top">
                                    <div class="space-y-1">
                                        <?php foreach($items as $it): ?>
                                            <div class="flex items-center gap-2 text-[11px]">
                                                <span class="w-4 h-4 rounded bg-slate-100 flex items-center justify-center font-black text-[9px]"><?= $it['cantidad'] ?></span>
                                                <span class="text-slate-600 font-medium"><?= htmlspecialchars($it['producto']) ?></span>
                                                <span class="text-slate-400 italic">(<?= $it['precio'] ?>)</span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="p-4 align-top">
                                    <p class="font-black text-[#1B263B] text-lg">$<?= number_format($ped['total'], 2) ?></p>
                                </td>
                                <td class="p-4 align-top text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?= $color ?>">
                                        <?= $ped['status'] ?>
                                    </span>
                                </td>
                                <td class="p-4 align-top text-right">
                                    <form method="POST" action="dashboard.php?view=pedidos" class="flex flex-col gap-2">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="action" value="actualizar_status_pedido">
                                        <input type="hidden" name="id_pedido" value="<?= $ped['id'] ?>">
                                        <select name="nuevo_status" onchange="this.form.submit()" class="text-[10px] font-black uppercase tracking-widest border border-slate-200 rounded-lg px-2 py-1.5 focus:border-[#1B263B] outline-none bg-white shadow-sm">
                                            <option value="pendiente" <?= $ped['status'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                            <option value="confirmado" <?= $ped['status'] == 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                                            <option value="despachado" <?= $ped['status'] == 'despachado' ? 'selected' : '' ?>>Despachado</option>
                                            <option value="cancelado" <?= $ped['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($vista === 'ayuda'): ?>
            <?php include __DIR__ . '/components/documentacion/vista.php'; ?>

        <?php elseif($vista === 'pedidos_publicos'): ?>
            <div class="glass-card overflow-hidden flex flex-col hover:border-[#1B263B]/40 transition-colors relative z-10">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Pedidos de la Tienda (Público)</h2>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-white px-3 py-1 rounded-full border border-slate-100">
                        Total: <?= count($pedidos_publicos) ?> registros
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-slate-500 border-b border-slate-100 bg-slate-50/50 text-xs uppercase tracking-wider font-black">
                            <tr>
                                <th class="p-4">Fecha / ID</th>
                                <th class="p-4">Origen (Marketing)</th>
                                <th class="p-4">Productos Detallados</th>
                                <th class="p-4">Total</th>
                                <th class="p-4 text-center">Estado Captura</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach($pedidos_publicos as $ped): 
                                $items = json_decode($ped['items_json'] ?? '[]', true) ?: [];
                                $is_ai = strpos($ped['source'] ?? '', 'IA:') !== false;
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 align-top">
                                    <p class="font-black text-slate-900">#<?= $ped['id'] ?></p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase"><?= date('d/m/Y H:i', strtotime($ped['fecha'] ?? 'now')) ?></p>
                                </td>
                                <td class="p-4 align-top">
                                    <?php if($is_ai): ?>
                                        <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-500 text-[9px] font-black uppercase tracking-tighter flex items-center gap-1 w-fit mb-1">
                                            <i class="fa-solid fa-brain"></i> IA Advisor
                                        </span>
                                        <p class="text-[11px] text-slate-600 font-medium leading-tight">"<?= htmlspecialchars(str_replace('IA: ', '', $ped['source'] ?? '')) ?>"</p>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-400 text-[9px] font-black uppercase tracking-tighter flex items-center gap-1 w-fit mb-1">
                                            <i class="fa-solid fa-mouse-pointer"></i> Directo
                                        </span>
                                        <p class="text-[11px] text-slate-400 italic">Navegación manual</p>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 align-top">
                                    <div class="space-y-1">
                                        <?php foreach($items as $it): ?>
                                            <div class="flex items-center gap-2 text-[11px]">
                                                <span class="w-4 h-4 rounded bg-slate-100 flex items-center justify-center font-black text-[9px]"><?= $it['cantidad'] ?? 1 ?></span>
                                                <span class="text-slate-600 font-medium"><?= htmlspecialchars($it['nombre'] ?? 'Producto') ?></span>
                                                <span class="text-slate-400 italic">($<?= number_format($it['precio'] ?? 0, 2) ?>)</span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="p-4 align-top">
                                    <p class="font-black text-[#1B263B] text-lg">$<?= number_format($ped['total'] ?? 0, 2) ?></p>
                                </td>
                                <td class="p-4 align-top text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100">
                                        <?= str_replace('_', ' ', $ped['status'] ?? 'contacto_iniciado') ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- MODAL ACTUALIZACIÓN MASIVA -->
    <div id="modal-bulk" class="fixed inset-0 bg-white/80 backdrop-blur-xl z-50 hidden flex items-center justify-center opacity-0 transition-opacity">
        <div class="bg-white border border-slate-100 rounded-[2.5rem] w-full max-w-xl mx-4 overflow-hidden transform scale-95 transition-all duration-300 shadow-2xl" id="modal-bulk-content">
            <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 rounded-2xl bg-[#1B263B]/10 text-[#1B263B] flex items-center justify-center text-3xl border border-[#1B263B]/20"><i class="fa-solid fa-file-csv"></i></div>
                    <div>
                        <h3 class="font-black text-slate-900 text-xl leading-tight uppercase tracking-tighter">Actualización Masiva</h3>
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-black">Solo CSV — fotos en cada producto</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-wrap justify-end">
                    <form method="POST" action="dashboard.php?view=catalogo" onsubmit="return confirm('¿ESTÁS SEGURO? Se borrarán TODOS los productos del catálogo actual. Esta acción es irreversible.');" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="vaciar_catalogo">
                        <button type="submit" class="bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                            <i class="fa-solid fa-broom mr-1"></i> Vaciar Catálogo
                        </button>
                    </form>
                    <button type="button" onclick="cerrarModalBulk()" class="text-slate-300 hover:text-slate-900 transition-colors w-12 h-12 flex items-center justify-center rounded-2xl hover:bg-white border border-transparent hover:border-slate-100"><i class="fa-solid fa-xmark text-2xl"></i></button>
                </div>
            </div>
            <div class="p-10 space-y-10 max-h-[75vh] overflow-y-auto custom-scrollbar">
                <div>
                    <h4 class="text-[11px] font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-[#1B263B] text-white flex items-center justify-center text-[10px] font-black">1</span> Paso 1: Obtener Estructura
                    </h4>
                    <p class="text-sm text-slate-500 mb-4 font-medium leading-relaxed">Una fila = un producto (un código por medida). Para altas nuevas, usa un CSV <strong>sin columna ID</strong>. Las imágenes se cargan al editar cada producto en el catálogo.</p>
                    <div class="grid grid-cols-2 gap-3 mb-2">
                        <a href="dashboard.php?action=exportar_csv" class="bg-white hover:bg-slate-50 text-slate-700 font-black py-4 rounded-2xl text-center transition-all flex items-center justify-center gap-2 border border-slate-100 text-[11px] uppercase tracking-widest">
                            <i class="fa-solid fa-download text-[#1B263B]"></i> Exportar catálogo
                        </a>
                        <a href="dashboard.php?action=exportar_ejemplo_csv" class="bg-white hover:bg-[#3A86FF] hover:text-white text-slate-700 hover:text-white font-black py-4 rounded-2xl text-center transition-all flex items-center justify-center gap-2 border border-slate-100 text-[11px] uppercase tracking-widest">
                            <i class="fa-solid fa-file-csv"></i> Ejemplo CSV
                        </a>
                    </div>
                </div>
                <div class="pt-6 border-t border-slate-50">
                    <form id="form-bulk-csv" method="POST" action="dashboard.php?view=catalogo" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="actualizar_masivo_csv">
                        <div>
                            <h4 class="text-[11px] font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-[#1B263B] text-white flex items-center justify-center text-[10px] font-black">2</span> Subir CSV e importar
                            </h4>
                            <div class="bg-slate-50 border-2 border-dashed border-slate-100 rounded-3xl p-8 text-center group hover:border-[#1B263B] transition-all relative cursor-pointer">
                                <input type="file" name="archivo_csv" accept=".csv" required class="absolute inset-0 opacity-0 cursor-pointer" onchange="actualizarNombreCSV(this)">
                                <div class="w-12 h-12 rounded-2xl bg-white mx-auto mb-3 flex items-center justify-center text-[#1B263B] group-hover:scale-110 transition-transform"><i class="fa-solid fa-file-invoice text-xl"></i></div>
                                <p class="text-[13px] text-slate-500 font-bold" id="txt-csv">Seleccionar archivo CSV</p>
                            </div>
                            <label id="bulk-confirm-ids-wrap" class="hidden flex items-start gap-3 mt-4 p-4 rounded-2xl bg-amber-50 border border-amber-100 cursor-pointer">
                                <input type="checkbox" id="bulk-confirm-ids-cb" class="mt-1 rounded border-amber-300">
                                <span class="text-[10px] text-amber-900 font-bold leading-relaxed">Confirmo: este CSV <strong>actualiza productos por ID</strong> (no es solo alta por código).</span>
                            </label>
                            <input type="hidden" name="bulk_confirm_ids" id="bulk-confirm-ids" value="">
                        </div>
                        <button type="submit" id="btn-bulk-sync" class="w-full bg-[#1B263B] hover:bg-[#3A86FF] text-white font-black py-5 rounded-3xl transition-all active:scale-95 text-sm uppercase tracking-widest">Importar al catálogo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php if ($vista === 'catalogo'): ?>
    <script src="js/bulk_upload.js?v=4"></script>
    <?php endif; ?>
    
    <!-- MODAL GESTIÓN CATEGORÍAS -->
    <div id="modal-cats" class="fixed inset-0 bg-white/80 backdrop-blur-xl z-50 hidden flex items-center justify-center opacity-0 transition-opacity">
        <div class="bg-white border border-slate-100 rounded-[2.5rem] w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-all duration-300 shadow-2xl" id="modal-cats-content">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-black text-slate-900 text-lg uppercase tracking-tighter italic">Gestionar Categorías</h3>
                <button type="button" onclick="cerrarModalCats()" class="text-slate-300 hover:text-slate-900 transition-colors w-10 h-10 flex items-center justify-center rounded-xl hover:bg-white border border-transparent hover:border-slate-100"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-8 space-y-6">
                <!-- Formulario Añadir -->
                <form method="POST" action="dashboard.php?view=catalogo" class="flex gap-2">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="crear_categoria">
                    <input type="text" name="nombre_categoria" placeholder="Nueva categoría..." required class="flex-1 premium-input rounded-xl px-4 py-3 text-sm font-bold">
                    <button type="submit" class="bg-[#1B263B] text-white w-12 h-12 rounded-xl hover:bg-[#3A86FF] transition-all flex items-center justify-center shadow-lg shadow-[#1B263B]/20"><i class="fa-solid fa-plus text-lg"></i></button>
                </form>
                
                <!-- Listado con Scroll -->
                <div class="space-y-2 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                    <?php if (isset($categorias_maestras)): foreach($categorias_maestras as $c): ?>
                    <div class="flex items-center justify-between p-3 bg-slate-50/50 border border-slate-100 rounded-xl group hover:bg-white hover:shadow-sm transition-all">
                        <span class="text-sm font-bold text-slate-700"><?= $c['nombre'] ?></span>
                        <form method="POST" action="dashboard.php?view=catalogo" onsubmit="return confirm('¿Seguro? Los productos con esta categoría no se borrarán, pero la categoría no aparecerá en el listado.');">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="action" value="eliminar_categoria">
                            <input type="hidden" name="id_categoria" value="<?= $c['id'] ?>">
                            <button type="submit" class="text-slate-300 hover:text-rose-500 transition-colors px-2 py-1"><i class="fa-solid fa-trash-can text-xs"></i></button>
                        </form>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL GESTIÓN MARCAS -->
    <div id="modal-marcas" class="fixed inset-0 bg-white/80 backdrop-blur-xl z-50 hidden flex items-center justify-center opacity-0 transition-opacity text-left">
        <div class="bg-white border border-slate-100 rounded-[2.5rem] w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-all duration-300 shadow-2xl" id="modal-marcas-content">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-black text-slate-900 text-lg uppercase tracking-tighter italic">Gestionar Marcas</h3>
                <button type="button" onclick="cerrarModalMarcas()" class="text-slate-300 hover:text-slate-900 transition-colors w-10 h-10 flex items-center justify-center rounded-xl hover:bg-white border border-transparent hover:border-slate-100"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <div class="p-8 space-y-6">
                <!-- Formulario Añadir -->
                <form method="POST" action="dashboard.php?view=catalogo" class="flex gap-2">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="crear_marca">
                    <input type="text" name="nombre_marca" placeholder="Nueva marca..." required class="flex-1 premium-input rounded-xl px-4 py-3 text-sm font-bold">
                    <button type="submit" class="bg-[#1B263B] text-white w-12 h-12 rounded-xl hover:bg-[#3A86FF] transition-all flex items-center justify-center shadow-lg"><i class="fa-solid fa-plus text-lg"></i></button>
                </form>
                
                <!-- Listado con Scroll -->
                <div class="space-y-2 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                    <?php if (isset($marcas_admin)): foreach($marcas_admin as $m): ?>
                    <div class="flex items-center justify-between p-3 bg-slate-50/50 border border-slate-100 rounded-xl group hover:bg-white hover:shadow-sm transition-all">
                        <span class="text-sm font-bold text-slate-700"><?= $m ?></span>
                        <form method="POST" action="dashboard.php?view=catalogo" onsubmit="return confirm('¿Seguro? Los productos con esta marca no se borrarán.');">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="action" value="eliminar_marca">
                            <input type="hidden" name="nombre_marca" value="<?= $m ?>">
                            <button type="submit" class="text-slate-300 hover:text-rose-500 transition-colors px-2 py-1"><i class="fa-solid fa-trash-can text-xs"></i></button>
                        </form>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        /* GESTIÓN DE LOCALES */
        function localImagenPreview(url) {
            const wrap = document.getElementById('local-imagen-preview-wrap');
            const img = document.getElementById('local-imagen-preview');
            const quitar = document.getElementById('local-quitar-imagen');
            if (!wrap || !img) return;
            if (url) {
                img.src = url;
                wrap.classList.remove('hidden');
            } else {
                img.removeAttribute('src');
                wrap.classList.add('hidden');
            }
            if (quitar) quitar.checked = false;
        }
        function abrirModalLocal() { 
            document.getElementById('modal-local-titulo').innerText = 'Nueva Sucursal';
            document.getElementById('local-id').value = '';
            document.getElementById('local-imagen-actual').value = '';
            document.getElementById('modal-local').querySelector('form').reset();
            localImagenPreview('');
            const fileIn = document.getElementById('local-imagen-file');
            if (fileIn) fileIn.value = '';
            const m = document.getElementById('modal-local'); 
            m.classList.remove('hidden'); 
            setTimeout(()=> { m.classList.remove('opacity-0'); document.getElementById('modal-local-content').classList.remove('scale-95'); }, 10); 
        }
        function cerrarModalLocal() { 
            const m = document.getElementById('modal-local'); 
            m.classList.add('opacity-0'); 
            document.getElementById('modal-local-content').classList.add('scale-95'); 
            setTimeout(()=> m.classList.add('hidden'), 300); 
        }
        function editarLocal(data) {
            document.getElementById('modal-local-titulo').innerText = 'Editar Sucursal';
            document.getElementById('local-id').value = data.id || '';
            document.getElementById('local-nombre').value = data.nombre || '';
            document.getElementById('local-ciudad').value = data.ciudad || '';
            const covEl = document.getElementById('local-cobertura');
            if (covEl) covEl.value = Array.isArray(data.cobertura) ? data.cobertura.join(', ') : (data.cobertura || '');
            document.getElementById('local-direccion').value = data.direccion || '';
            document.getElementById('local-whatsapp').value = data.whatsapp || '';
            document.getElementById('local-telefono').value = data.telefono || '';
            document.getElementById('local-email').value = data.email || '';
            document.getElementById('local-lat').value = data.lat || '';
            document.getElementById('local-lng').value = data.lng || '';
            document.getElementById('local-maps').value = data.maps || '';
            document.getElementById('local-whatsapp-msj').value = data.whatsapp_msj || '';
            document.getElementById('local-horario').value = data.horario || '';
            document.getElementById('local-imagen-actual').value = data.imagen || '';
            localImagenPreview(data.imagen_preview || data.imagen || '');
            const fileIn = document.getElementById('local-imagen-file');
            if (fileIn) fileIn.value = '';
            const quitar = document.getElementById('local-quitar-imagen');
            if (quitar) quitar.checked = false;

            const m = document.getElementById('modal-local'); 
            m.classList.remove('hidden'); 
            setTimeout(()=> { m.classList.remove('opacity-0'); document.getElementById('modal-local-content').classList.remove('scale-95'); }, 10); 
        }
        (function () {
            const fileIn = document.getElementById('local-imagen-file');
            if (fileIn) {
                fileIn.addEventListener('change', function () {
                    const f = fileIn.files && fileIn.files[0];
                    if (!f) return;
                    localImagenPreview(URL.createObjectURL(f));
                    const q = document.getElementById('local-quitar-imagen');
                    if (q) q.checked = false;
                });
            }
        })();
        function confirmarEliminarLocal(id) {
            if (confirm('¿Estás seguro de eliminar esta sucursal? Esta acción no se puede deshacer.')) {
                document.getElementById('input-eliminar-local').value = id;
                document.getElementById('form-eliminar-local').submit();
            }
        }

        function abrirModalCats() { const m = document.getElementById('modal-cats'); m.classList.remove('hidden'); setTimeout(()=> { m.classList.remove('opacity-0'); document.getElementById('modal-cats-content').classList.remove('scale-95'); }, 10); }
        function cerrarModalCats() { const m = document.getElementById('modal-cats'); m.classList.add('opacity-0'); document.getElementById('modal-cats-content').classList.add('scale-95'); setTimeout(()=> m.classList.add('hidden'), 300); }
        
        function abrirModalMarcas() { const m = document.getElementById('modal-marcas'); m.classList.remove('hidden'); setTimeout(()=> { m.classList.remove('opacity-0'); document.getElementById('modal-marcas-content').classList.remove('scale-95'); }, 10); }
        function cerrarModalMarcas() { const m = document.getElementById('modal-marcas'); m.classList.add('opacity-0'); document.getElementById('modal-marcas-content').classList.add('scale-95'); setTimeout(()=> m.classList.add('hidden'), 300); }

        // --- GESTIÓN DE PAUTAS (REPEATER) ---
        let videoCounter = <?= isset($videos) && is_array($videos) ? count($videos) : 1 ?>;
        let bannerCounter = <?= (isset($banners) && is_array($banners)) ? count($banners) : 1 ?>;

        function agregarFilaVideo() {
            const contenedor = document.getElementById('contenedor-videos');
            const totalActual = contenedor.querySelectorAll('.video-row').length;
            if (totalActual >= 6) { alert('Máximo 6 videos permitidos.'); return; }

            const html = `
                <div class="bg-slate-50 border border-slate-100 rounded-[2rem] p-6 relative group hover:border-[#1B263B]/30 transition-all video-row animate-fade-in shadow-sm hover:shadow-md">
                    <div class="absolute top-4 right-4 flex gap-2">
                        <label class="relative inline-flex items-center cursor-pointer scale-75">
                            <input type="checkbox" name="video_activo[${videoCounter}]" class="sr-only peer" checked>
                            <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-[#1B263B] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                        </label>
                        <button type="button" onclick="this.closest('.video-row').remove()" class="text-slate-300 hover:text-rose-500 transition-colors"><i class="fa-solid fa-trash"></i></button>
                    </div>
                    <div class="space-y-4 pt-2">
                        <div class="aspect-video bg-slate-100 border-2 border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center text-slate-300 gap-2 mb-4">
                            <i class="fa-solid fa-cloud-arrow-up text-2xl"></i>
                            <span class="text-[10px] font-bold uppercase tracking-widest">Nuevo Video</span>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Archivo MP4/WebM</label>
                            <input type="file" name="video_archivo[${videoCounter}]" accept="video/mp4, video/webm" class="w-full text-[10px] file:bg-[#1B263B]/10 file:text-[#1B263B] file:border-0 file:rounded-lg file:px-3 file:py-1 file:font-black cursor-pointer">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Posición (Card #)</label>
                                <input type="number" name="video_pos[${videoCounter}]" placeholder="Ej: 4" class="w-full premium-input rounded-xl px-4 py-2 text-xs font-black">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Link de Destino</label>
                                <input type="text" name="video_link[${videoCounter}]" list="productos_lista" placeholder="Producto o URL" class="w-full premium-input rounded-xl px-4 py-2 text-xs">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            contenedor.insertAdjacentHTML('beforeend', html);
            videoCounter++;
        }

        function toggleBannerFields(select) {
            const row = select.closest('.banner-row');
            if (!row) return;
            const imgBlock = row.querySelector('.banner-opt-split');
            if (!imgBlock) return;
            if (select.value === 'split' || select.value === 'glass') imgBlock.classList.remove('hidden');
            else imgBlock.classList.add('hidden');
        }

        function agregarFilaBanner() {
            const contenedor = document.getElementById('contenedor-banners');
            const totalActual = contenedor.querySelectorAll('.banner-row').length;
            if (totalActual >= 6) { alert('Máximo 6 banners permitidos.'); return; }

            const html = `
                <div class="bg-slate-50 border border-slate-100 rounded-[2.5rem] p-8 relative group hover:border-blue-400/30 transition-all banner-row animate-fade-in shadow-sm hover:shadow-md">
                    <div class="absolute top-6 right-8 flex gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="banner_activo[${bannerCounter}]" class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-blue-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                        </label>
                        <button type="button" onclick="this.closest('.banner-row').remove()" class="text-slate-300 hover:text-rose-500 transition-colors"><i class="fa-solid fa-trash-can shadow-sm"></i></button>
                    </div>
                    <input type="hidden" name="banner_img_actual[${bannerCounter}]" value="">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="lg:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Estilo visual</label>
                            <select name="banner_estilo[${bannerCounter}]" class="w-full premium-input rounded-xl px-3 py-2 text-xs font-black banner-estilo-select" onchange="toggleBannerFields(this)">
                                <option value="respiracion">A · Respiración</option>
                                <option value="split">B · Split 50/50</option>
                                <option value="marquee">C · Marquee</option>
                                <option value="glass">D · Glass</option>
                            </select>
                        </div>
                        <div class="lg:col-span-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Cada N filas</label>
                            <input type="number" name="banner_cada_n_filas[${bannerCounter}]" min="1" max="20" value="4" class="w-full premium-input rounded-xl px-4 py-2 text-xs font-black">
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 italic">Etiqueta (Pill)</label>
                            <input type="text" name="banner_etiqueta[${bannerCounter}]" value="PROMO" class="w-full premium-input rounded-xl px-4 py-2 text-xs font-black text-blue-600">
                        </div>
                        <div class="lg:col-span-4 banner-field-group">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div class="lg:col-span-2">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Título Principal</label>
                                    <input type="text" name="banner_titulo[${bannerCounter}]" class="w-full premium-input rounded-xl px-4 py-2 text-sm font-black text-slate-900">
                                </div>
                                <div class="lg:col-span-2 banner-opt banner-opt-split hidden">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Imagen (Split / Glass)</label>
                                    <input type="file" name="banner_img[${bannerCounter}]" accept="image/*" class="w-full premium-input rounded-xl px-3 py-2 text-[10px]">
                                </div>
                                <div class="lg:col-span-2">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Texto del botón (CTA)</label>
                                    <input type="text" name="banner_extra[${bannerCounter}]" placeholder="Ej: Consultar envíos" class="w-full premium-input rounded-xl px-4 py-2 text-xs font-bold">
                                </div>
                                <div class="lg:col-span-2">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Link o WhatsApp</label>
                                    <input type="text" name="banner_link[${bannerCounter}]" value="https://wa.me/593991754887" class="w-full premium-input rounded-xl px-4 py-2 text-xs">
                                </div>
                                <div class="lg:col-span-4">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Descripción</label>
                                    <textarea name="banner_desc[${bannerCounter}]" rows="2" class="w-full premium-input rounded-2xl px-4 py-3 text-xs leading-relaxed"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            contenedor.insertAdjacentHTML('beforeend', html);
            bannerCounter++;
        }

        document.querySelectorAll('.banner-estilo-select').forEach(sel => toggleBannerFields(sel));

        function confirmarEliminarImpulso(nombre) {
            if (confirm('¿Seguro que deseas quitar el impulso a "' + nombre + '"? Esto hará que deje de aparecer como sugerencia TOP.')) {
                document.getElementById('input-eliminar-impulso').value = nombre;
                document.getElementById('form-eliminar-impulso').submit();
            }
        }

        document.querySelectorAll('[data-nav-collapse]').forEach((group) => {
            const key = group.getAttribute('data-nav-collapse');
            const trigger = group.querySelector('.nav-collapse-trigger');
            const panel = group.querySelector('.nav-collapse-panel');
            if (!trigger || !panel) return;

            const forzarAbierto = group.classList.contains('is-open');
            if (!forzarAbierto && key) {
                const stored = localStorage.getItem(key);
                if (stored === 'open') group.classList.add('is-open');
                if (stored === 'closed') group.classList.remove('is-open');
            }

            const syncAria = () => {
                const abierto = group.classList.contains('is-open');
                trigger.setAttribute('aria-expanded', abierto ? 'true' : 'false');
            };
            syncAria();

            trigger.addEventListener('click', () => {
                group.classList.toggle('is-open');
                syncAria();
                if (key) {
                    localStorage.setItem(key, group.classList.contains('is-open') ? 'open' : 'closed');
                }
            });
        });
    </script>

    <?php if (in_array($vista, ['radar', 'inventario_fantasma'], true)): ?>
        <?php include __DIR__ . '/components/inventario_fantasma_script.php'; ?>
    <?php endif; ?>

</body>
</html>