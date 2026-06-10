<?php
/**
 * Normaliza teléfonos móviles de Ecuador al formato wa.me (593 + 9 dígitos).
 */
function improgyp_normalize_whatsapp($num, $fallback = '593991754887') {
    $digits = preg_replace('/\D/', '', (string) $num);
    if ($digits === '') {
        return $fallback;
    }

    // 5939XXXXXXXX (12 dígitos)
    if (preg_match('/^5939\d{8}$/', $digits)) {
        return $digits;
    }

    // 09XXXXXXXX (celular local)
    if (preg_match('/^09\d{8}$/', $digits)) {
        return '593' . substr($digits, 1);
    }

    // 9XXXXXXXX (sin cero ni país)
    if (preg_match('/^9\d{8}$/', $digits)) {
        return '593' . $digits;
    }

    // 593 con dígitos de más (recortar a 12)
    if (strpos($digits, '593') === 0 && strlen($digits) >= 12) {
        $trimmed = substr($digits, 0, 12);
        if (preg_match('/^5939\d{8}$/', $trimmed)) {
            return $trimmed;
        }
    }

    return $fallback;
}
