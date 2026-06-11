<?php
/**
 * Redes sociales del footer — config_footer.json
 */

function improgyp_footer_config_path(): string
{
    return dirname(__DIR__) . '/config_footer.json';
}

/** @return list<string> */
function improgyp_footer_social_brand_icons(): array
{
    return [
        'fa-whatsapp', 'fa-instagram', 'fa-facebook', 'fa-facebook-f', 'fa-tiktok',
        'fa-youtube', 'fa-linkedin', 'fa-x-twitter', 'fa-telegram', 'fa-pinterest',
        'fa-threads', 'fa-snapchat', 'fa-spotify',
    ];
}

/** @return array<string, array{etiqueta: string, icono: string}> */
function improgyp_footer_social_presets(): array
{
    return [
        'whatsapp' => ['etiqueta' => 'WhatsApp', 'icono' => 'fa-whatsapp'],
        'instagram' => ['etiqueta' => 'Instagram', 'icono' => 'fa-instagram'],
        'facebook' => ['etiqueta' => 'Facebook', 'icono' => 'fa-facebook'],
        'tiktok' => ['etiqueta' => 'TikTok', 'icono' => 'fa-tiktok'],
        'youtube' => ['etiqueta' => 'YouTube', 'icono' => 'fa-youtube'],
        'linkedin' => ['etiqueta' => 'LinkedIn', 'icono' => 'fa-linkedin'],
        'x' => ['etiqueta' => 'X (Twitter)', 'icono' => 'fa-x-twitter'],
        'telegram' => ['etiqueta' => 'Telegram', 'icono' => 'fa-telegram'],
        'pinterest' => ['etiqueta' => 'Pinterest', 'icono' => 'fa-pinterest'],
        'custom' => ['etiqueta' => '', 'icono' => 'fa-link'],
    ];
}

function improgyp_footer_normalize_icon(string $raw): string
{
    $raw = trim(strtolower($raw));
    if ($raw === '') {
        return 'fa-link';
    }
    if (preg_match('/^fa-[a-z0-9\-]+$/', $raw)) {
        return $raw;
    }
    if (preg_match('/^[a-z0-9\-]+$/', $raw)) {
        return 'fa-' . $raw;
    }
    return 'fa-link';
}

function improgyp_footer_social_icon_family(string $icono): string
{
    return in_array($icono, improgyp_footer_social_brand_icons(), true) ? 'brands' : 'solid';
}

/**
 * @param array<string, mixed> $row
 * @return array{id: string, etiqueta: string, url: string, icono: string, familia: string, orden: int, activo: bool}|null
 */
function improgyp_footer_normalize_social_row(array $row, int $index = 0): ?array
{
    $url = trim((string) ($row['url'] ?? ''));
    if ($url === '') {
        return null;
    }
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . ltrim($url, '/');
    }

    $icono = improgyp_footer_normalize_icon((string) ($row['icono'] ?? 'fa-link'));
    $etiqueta = trim((string) ($row['etiqueta'] ?? ''));
    if ($etiqueta === '') {
        $etiqueta = 'Enlace';
    }

    $id = trim((string) ($row['id'] ?? ''));
    if ($id === '') {
        $id = 'red-' . ($index + 1);
    }
    $id = preg_replace('/[^a-z0-9\-_]/i', '', $id) ?: ('red-' . ($index + 1));

    $orden = (int) ($row['orden'] ?? ($index + 1));
    if ($orden < 1) {
        $orden = $index + 1;
    }

    $activo = !isset($row['activo']) || !empty($row['activo']);

    return [
        'id' => $id,
        'etiqueta' => $etiqueta,
        'url' => $url,
        'icono' => $icono,
        'familia' => improgyp_footer_social_icon_family($icono),
        'orden' => $orden,
        'activo' => $activo,
    ];
}

/**
 * @param mixed $raw
 * @return list<array{id: string, etiqueta: string, url: string, icono: string, familia: string, orden: int, activo: bool}>
 */
function improgyp_footer_normalize_social_list($raw): array
{
    if (!is_array($raw)) {
        return [];
    }
    $out = [];
    foreach ($raw as $i => $row) {
        if (!is_array($row)) {
            continue;
        }
        $norm = improgyp_footer_normalize_social_row($row, (int) $i);
        if ($norm !== null) {
            $out[] = $norm;
        }
    }
    usort($out, static function ($a, $b) {
        return $a['orden'] <=> $b['orden'];
    });
    return $out;
}

/** @return list<array{id: string, etiqueta: string, url: string, icono: string, familia: string, orden: int, activo: bool}> */
function improgyp_footer_social_defaults(): array
{
    $text = rawurlencode('Hola IMPROGYP, necesito asesoría.');
    return improgyp_footer_normalize_social_list([
        [
            'id' => 'whatsapp',
            'etiqueta' => 'WhatsApp',
            'url' => 'https://wa.me/593991754887?text=' . $text,
            'icono' => 'fa-whatsapp',
            'orden' => 1,
            'activo' => true,
        ],
    ]);
}

/**
 * @return array{redes_sociales: list<array>}
 */
function improgyp_footer_config_read(): array
{
    $path = improgyp_footer_config_path();
    if (!file_exists($path)) {
        return ['redes_sociales' => improgyp_footer_social_defaults()];
    }
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data)) {
        return ['redes_sociales' => improgyp_footer_social_defaults()];
    }
    $list = improgyp_footer_normalize_social_list($data['redes_sociales'] ?? []);
    if ($list === []) {
        $list = improgyp_footer_social_defaults();
    }
    return ['redes_sociales' => $list];
}

/**
 * @param mixed $raw
 * @return list<array>
 */
function improgyp_footer_social_items_active($raw = null): array
{
    if ($raw === null) {
        $cfg = improgyp_footer_config_read();
        $raw = $cfg['redes_sociales'];
    }
    $list = improgyp_footer_normalize_social_list($raw);
    return array_values(array_filter($list, static function ($row) {
        return !empty($row['activo']);
    }));
}

/**
 * @param mixed $raw
 */
function improgyp_footer_save_social_config($raw): bool
{
    $list = improgyp_footer_normalize_social_list($raw);
    $payload = ['redes_sociales' => $list];
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    return file_put_contents(improgyp_footer_config_path(), $json) !== false;
}

function improgyp_footer_social_icon_classes(string $icono): string
{
    $map = [
        'fa-whatsapp' => 'hover:bg-emerald-500/20 text-emerald-400',
        'fa-instagram' => 'hover:bg-pink-500/20 text-pink-400',
        'fa-facebook' => 'hover:bg-blue-500/20 text-blue-400',
        'fa-facebook-f' => 'hover:bg-blue-500/20 text-blue-400',
        'fa-tiktok' => 'hover:bg-slate-300/20 text-slate-200',
        'fa-youtube' => 'hover:bg-red-500/20 text-red-400',
        'fa-linkedin' => 'hover:bg-sky-500/20 text-sky-400',
        'fa-x-twitter' => 'hover:bg-slate-300/20 text-slate-200',
        'fa-telegram' => 'hover:bg-sky-400/20 text-sky-300',
        'fa-pinterest' => 'hover:bg-rose-500/20 text-rose-400',
    ];
    return $map[$icono] ?? 'hover:bg-white/20 text-slate-300';
}

function improgyp_footer_social_render_icon(array $row, string $sizeClass = 'text-lg'): string
{
    $fam = ($row['familia'] ?? 'solid') === 'brands' ? 'fa-brands' : 'fa-solid';
    $icon = htmlspecialchars($row['icono'] ?? 'fa-link', ENT_QUOTES, 'UTF-8');
    $sz = htmlspecialchars($sizeClass, ENT_QUOTES, 'UTF-8');
    return '<i class="' . $fam . ' ' . $icon . ' ' . $sz . '" aria-hidden="true"></i>';
}
