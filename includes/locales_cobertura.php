<?php
/**
 * Parsea y normaliza el campo cobertura de sucursales (ciudades atendidas en domicilio).
 */
function improgyp_parse_cobertura_post($raw) {
    if (is_array($raw)) {
        $items = $raw;
    } else {
        $items = preg_split('/[,;]+/u', (string) $raw);
    }

    $out = [];
    foreach ($items as $item) {
        $item = trim((string) $item);
        if ($item !== '') {
            $out[] = $item;
        }
    }

    return array_values(array_unique($out));
}

/**
 * Asegura que la ciudad sede esté incluida en cobertura.
 */
function improgyp_merge_cobertura_ciudad(array $cobertura, $ciudad) {
    $ciudad = trim((string) $ciudad);
    if ($ciudad === '') {
        return $cobertura;
    }
    if (!in_array($ciudad, $cobertura, true)) {
        array_unshift($cobertura, $ciudad);
    }
    return $cobertura;
}
