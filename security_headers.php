<?php
// security_headers.php - Cabeceras de seguridad globales para IMPROGYP OS

// 1. Prevenir que el sitio sea embebido en iframes (Evita Clickjacking)
header("X-Frame-Options: SAMEORIGIN");

// 2. Prevenir que el navegador trate de adivinar el tipo de contenido (Evita MIME sniffing)
header("X-Content-Type-Options: nosniff");

// 3. Habilitar filtro XSS en navegadores antiguos
header("X-XSS-Protection: 1; mode=block");

// 4. Política de Referencia (Privacidad)
header("Referrer-Policy: strict-origin-when-cross-origin");

// 5. Opcional: HSTS (Solo si tienes SSL ya configurado)
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// 6. Configuración de Sesiones Seguras (PHP 7.3+)
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
        'cookie_samesite' => 'Lax',
    ]);
}
