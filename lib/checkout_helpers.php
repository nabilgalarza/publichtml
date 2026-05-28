<?php
/**
 * Checkout tienda pública — configuración y helpers PHP.
 */

function improgyp_checkout_config_path(): string
{
    return dirname(__DIR__) . '/config_checkout.json';
}

/**
 * @return array<string, mixed>
 */
function improgyp_checkout_config(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }

    $defaults = [
        'iva_rate' => 0.15,
        'logos_base' => 'logos_bancos/',
        'deuna_logo' => 'Deuna!_icono.svg.png',
        'advisor_whatsapp' => '593991754887',
        'transfer_hint' => 'Al confirmar por WhatsApp te enviamos los datos de cuenta.',
        'banks_transfer' => [],
    ];

    $path = improgyp_checkout_config_path();
    if (!is_file($path)) {
        $cfg = $defaults;
        return $cfg;
    }

    $raw = json_decode((string) file_get_contents($path), true);
    if (!is_array($raw)) {
        $cfg = $defaults;
        return $cfg;
    }

    $cfg = array_merge($defaults, $raw);
    if (!is_array($cfg['banks_transfer'])) {
        $cfg['banks_transfer'] = $defaults['banks_transfer'];
    }

    return $cfg;
}

/**
 * @return list<array{file: string, name: string}>
 */
function improgyp_checkout_banks(): array
{
    $banks = improgyp_checkout_config()['banks_transfer'] ?? [];
    $out = [];
    foreach ($banks as $b) {
        if (!is_array($b) || empty($b['file'])) {
            continue;
        }
        $out[] = [
            'file' => (string) $b['file'],
            'name' => (string) ($b['name'] ?? pathinfo($b['file'], PATHINFO_FILENAME)),
        ];
    }
    return $out;
}

function improgyp_checkout_logo_url(string $filename, ?string $base_url = null): string
{
    $base = improgyp_checkout_config()['logos_base'] ?? 'logos_bancos/';
    $path = rtrim($base, '/') . '/' . ltrim($filename, '/');
    if ($base_url !== null && $base_url !== '') {
        return rtrim($base_url, '/') . '/' . $path;
    }
    return $path;
}

/**
 * Config expuesta al front (checkout_wa.js).
 *
 * @return array<string, mixed>
 */
function improgyp_checkout_js_config(?string $base_url = null): array
{
    $cfg = improgyp_checkout_config();
    return [
        'iva_rate' => (float) ($cfg['iva_rate'] ?? 0.15),
        'logos_base' => (string) ($cfg['logos_base'] ?? 'logos_bancos/'),
        'deuna_logo' => (string) ($cfg['deuna_logo'] ?? 'Deuna!_icono.svg.png'),
        'advisor_whatsapp' => (string) ($cfg['advisor_whatsapp'] ?? '593991754887'),
        'transfer_hint' => (string) ($cfg['transfer_hint'] ?? ''),
        'banks_transfer' => improgyp_checkout_banks(),
        'base_url' => $base_url ?? '',
    ];
}
