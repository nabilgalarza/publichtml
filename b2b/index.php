<?php
require_once dirname(__DIR__) . '/lib/b2b_config.php';

if (!improgyp_b2b_portal_habilitado()) {
    require __DIR__ . '/mantenimiento_portal.php';
    exit;
}

$target = __DIR__ . '/index.html';
if (!is_file($target)) {
    http_response_code(404);
    echo 'Portal B2B no encontrado.';
    exit;
}

header('Content-Type: text/html; charset=utf-8');
readfile($target);
