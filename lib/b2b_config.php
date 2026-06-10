<?php
/**
 * Configuración maestra del módulo B2B (portal mayoristas + métricas).
 */

function improgyp_b2b_estado_path(): string
{
    return dirname(__DIR__) . '/estado_tienda.json';
}

/** @return array{mantenimiento?:bool,b2b_publico_activo?:bool,b2b_pilot_rucs?:string|array} */
function improgyp_b2b_estado_load(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $path = improgyp_b2b_estado_path();
    if (!file_exists($path)) {
        $cache = ['mantenimiento' => false, 'b2b_publico_activo' => true, 'b2b_pilot_rucs' => []];
        return $cache;
    }
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data)) {
        $data = [];
    }
    if (!array_key_exists('b2b_publico_activo', $data)) {
        $data['b2b_publico_activo'] = true;
    }
    $cache = $data;
    return $cache;
}

function improgyp_b2b_publico_activo(): bool
{
    $data = improgyp_b2b_estado_load();
    return !empty($data['b2b_publico_activo']);
}

/** @return string[] RUCs en lista piloto (acceso aunque el módulo público esté apagado). */
function improgyp_b2b_pilot_rucs(): array
{
    $raw = improgyp_b2b_estado_load()['b2b_pilot_rucs'] ?? [];
    if (is_string($raw)) {
        $raw = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
    if (!is_array($raw)) {
        return [];
    }
    $out = [];
    foreach ($raw as $ruc) {
        $r = strtoupper(trim((string) $ruc));
        if ($r !== '') {
            $out[] = $r;
        }
    }
    return array_values(array_unique($out));
}

function improgyp_b2b_ruc_permitido(string $ruc): bool
{
    if (improgyp_b2b_publico_activo()) {
        return true;
    }
    $ruc = strtoupper(trim($ruc));
    return $ruc !== '' && in_array($ruc, improgyp_b2b_pilot_rucs(), true);
}

/** Portal y APIs: ¿puede operar este RUC (o sin RUC aún)? */
function improgyp_b2b_portal_habilitado(?string $ruc = null): bool
{
    if (improgyp_b2b_publico_activo()) {
        return true;
    }
    if ($ruc !== null && $ruc !== '') {
        return improgyp_b2b_ruc_permitido($ruc);
    }
    return count(improgyp_b2b_pilot_rucs()) > 0;
}

function improgyp_b2b_api_denegado_respuesta(): void
{
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'status' => 'b2b_disabled',
        'message' => 'El portal mayorista no está disponible en este momento. Contacta a tu asesor IMPROGYP.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Enlaces B2B en tienda pública (header, footer, hero, tarjeta ads). */
function improgyp_b2b_mostrar_en_tienda(): bool
{
    return improgyp_b2b_publico_activo();
}

/** Menú admin Clientes VIP / Pedidos B2B. */
function improgyp_b2b_admin_ver_modulo(): bool
{
    if (improgyp_b2b_publico_activo()) {
        return true;
    }
    return isset($_SESSION['admin_rol']) && $_SESSION['admin_rol'] === 'master';
}

/** Crear/editar/eliminar mayoristas y credenciales. */
function improgyp_b2b_admin_puede_gestionar(): bool
{
    return improgyp_b2b_admin_ver_modulo() && (
        improgyp_b2b_publico_activo()
        || (isset($_SESSION['admin_rol']) && $_SESSION['admin_rol'] === 'master')
    );
}

function improgyp_b2b_hash_pin(string $pin): string
{
    return password_hash($pin, PASSWORD_DEFAULT);
}

function improgyp_b2b_pin_para_guardar(string $pin, ?string $pin_actual_bd): string
{
    $pin = trim($pin);
    if ($pin === '') {
        return $pin_actual_bd ?? '';
    }
    if ($pin_actual_bd && password_get_info($pin_actual_bd)['algo'] && password_verify($pin, $pin_actual_bd)) {
        return $pin_actual_bd;
    }
    return improgyp_b2b_hash_pin($pin);
}
