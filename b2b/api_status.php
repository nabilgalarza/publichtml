<?php
require_once dirname(__DIR__) . '/lib/b2b_config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

echo json_encode([
    'publico_activo' => improgyp_b2b_publico_activo(),
    'portal_habilitado' => improgyp_b2b_portal_habilitado(),
    'piloto' => improgyp_b2b_pilot_rucs(),
], JSON_UNESCAPED_UNICODE);
