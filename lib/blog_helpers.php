<?php
/**
 * Blog — PDO, artículos y utilidades compartidas.
 */

function blog_get_pdo() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $envFile = dirname(__DIR__) . '/.env';
    if (!file_exists($envFile)) return null;
    $env = [];
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v);
    }
    if (empty($env['DB_HOST'])) return null;
    try {
        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $env['DB_HOST'], $env['DB_PORT'] ?? '3306', $env['DB_NAME']),
            $env['DB_USER'] ?? 'root',
            $env['DB_PASS'] ?? '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } catch (Throwable $e) {
        return null;
    }
    return $pdo;
}

function blog_ensure_table(PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS improgyp_blog (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        categoria VARCHAR(100) DEFAULT 'General',
        tiempo_lectura VARCHAR(50) DEFAULT '5 min',
        resumen TEXT,
        contenido LONGTEXT,
        portada VARCHAR(255) DEFAULT 'favicon-app.png?v=5',
        borrador TINYINT(1) DEFAULT 0,
        visitas INT DEFAULT 0,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_borrador (borrador),
        INDEX idx_fecha (fecha)
    )");
}

function blog_slugify($text) {
    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'articulo';
}

function blog_unique_slug(PDO $pdo, $titulo, $excludeId = 0) {
    $base = blog_slugify($titulo);
    $slug = $base;
    $n = 1;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM improgyp_blog WHERE slug = ? AND id != ? LIMIT 1');
        $stmt->execute([$slug, (int) $excludeId]);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . (++$n);
    }
}

function blog_fetch_public($limit = 30) {
    $pdo = blog_get_pdo();
    if (!$pdo) return [];
    blog_ensure_table($pdo);
    $limit = max(1, min(50, (int) $limit));
    $stmt = $pdo->query(
        "SELECT id, titulo, slug, categoria, tiempo_lectura, resumen, portada, fecha, visitas
         FROM improgyp_blog WHERE borrador = 0 ORDER BY id DESC LIMIT $limit"
    );
    return $stmt->fetchAll();
}

function blog_fetch_by_slug($slug) {
    $pdo = blog_get_pdo();
    if (!$pdo) return null;
    blog_ensure_table($pdo);
    $stmt = $pdo->prepare(
        'SELECT * FROM improgyp_blog WHERE slug = ? AND borrador = 0 LIMIT 1'
    );
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    if ($row) {
        $pdo->prepare('UPDATE improgyp_blog SET visitas = visitas + 1 WHERE id = ?')->execute([$row['id']]);
    }
    return $row ?: null;
}

function blog_img_url($ruta, $base_url = '') {
    if (!$ruta || $ruta === 'logos/logo-claro.png') return ($base_url ?: '') . 'favicon-app.png?v=5';
    if (preg_match('~^https?://~i', $ruta)) return $ruta;
    return ($base_url ?: '') . ltrim(str_replace('./', '', $ruta), '/');
}

function blog_seed_if_empty(PDO $pdo) {
    if ((int) $pdo->query('SELECT COUNT(*) FROM improgyp_blog')->fetchColumn() > 0) return;
    $samples = [
        [
            'titulo' => 'Guía rápida: herramientas esenciales para drywall',
            'slug' => 'guia-herramientas-drywall',
            'categoria' => 'Drywall',
            'tiempo_lectura' => '6 min',
            'resumen' => 'Conoce las herramientas imprescindibles para instalar placas de yeso con acabado profesional.',
            'contenido' => '<p>En IMPROGYP reunimos las herramientas que todo instalador necesita: cuchillas, atornilladores, cintas y lijadoras especializadas.</p>',
            'portada' => 'img_catalogo/20MAGDS125.webp',
        ],
        [
            'titulo' => 'Cómo elegir la lijadora correcta para tu obra',
            'slug' => 'elegir-lijadora-obra',
            'categoria' => 'Herramientas',
            'tiempo_lectura' => '4 min',
            'resumen' => 'Comparativa entre lijadoras orbitales y de paneles según el tipo de superficie.',
            'contenido' => '<p>La elección depende del material, el polvo generado y la productividad que busques en obra.</p>',
            'portada' => 'img_catalogo/20MSCDW120.webp',
        ],
        [
            'titulo' => 'MAXXT en Ecuador: soporte técnico IMPROGYP',
            'slug' => 'maxxt-soporte-improgyp',
            'categoria' => 'Marcas',
            'tiempo_lectura' => '3 min',
            'resumen' => 'Repuestos originales, garantía y asesoría en todas nuestras sucursales.',
            'contenido' => '<p>Compra con confianza: catálogo oficial y asesores en tienda y por WhatsApp.</p>',
            'portada' => 'img_catalogo/20MAODW120.webp',
        ],
    ];
    $ins = $pdo->prepare(
        'INSERT INTO improgyp_blog (titulo, slug, categoria, tiempo_lectura, resumen, contenido, portada, borrador)
         VALUES (?,?,?,?,?,?,?,0)'
    );
    foreach ($samples as $s) {
        $ins->execute([$s['titulo'], $s['slug'], $s['categoria'], $s['tiempo_lectura'], $s['resumen'], $s['contenido'], $s['portada']]);
    }
}
