<?php
/**
 * Generación de copy con Gemini (catálogo + encabezados home).
 */

function improgyp_gemini_model_default(): string
{
    return 'gemini-2.5-flash-lite';
}

function improgyp_gemini_api_key(array $env): string
{
    $paid = trim((string) ($env['GEMINI_API_KEY_PAID'] ?? ''));
    if ($paid !== '') {
        return $paid;
    }
    return trim((string) ($env['GEMINI_API_KEY'] ?? ''));
}

function improgyp_gemini_esta_configurado(array $env): bool
{
    return improgyp_gemini_api_key($env) !== '';
}

function improgyp_gemini_model(array $env): string
{
    $m = trim((string) ($env['GEMINI_MODEL'] ?? ''));
    return $m !== '' ? $m : improgyp_gemini_model_default();
}

function improgyp_frases_copy_evitar(): string
{
    $fijas = ['Potencia Total', 'Acabado profesional', 'Precisión Angular', 'Rendimiento Superior', 'para tu máximo nivel'];
    $extra = [];
    $path = dirname(__DIR__) . '/textos_tienda.json';
    if (file_exists($path)) {
        $textos = json_decode(file_get_contents($path), true);
        if (is_array($textos)) {
            foreach ($textos as $item) {
                if (!empty($item['tit']) && preg_match('/<span[^>]*>(.*?)<\/span>/i', $item['tit'], $m)) {
                    $frase = trim(strip_tags($m[1]));
                    if ($frase !== '' && strlen($frase) < 40) {
                        $extra[] = $frase;
                    }
                }
            }
        }
    }
    $lista = array_unique(array_merge($fijas, $extra));
    return implode(' | ', array_slice($lista, 0, 18));
}

function improgyp_construir_prompt_copy(array $data): string
{
    $categoria = trim($data['categoria'] ?? '');
    $seccion = trim($data['seccion'] ?? '');
    $titAct = trim($data['tit_actual'] ?? '');
    $resAct = trim($data['resal_actual'] ?? '');
    $subAct = trim($data['sub_actual'] ?? '');
    $regenerar = !empty($data['regenerar']);
    $evitar = improgyp_frases_copy_evitar();

    $ctxActual = '';
    if ($titAct !== '' || $resAct !== '' || $subAct !== '') {
        $ctxActual = "\nTEXTO ACTUAL (genera una versión claramente distinta):\n"
            . "- tit_normal: $titAct\n- tit_resaltado: $resAct\n- sub: $subAct\n";
    }
    $instrRegen = $regenerar
        ? "\nIMPORTANTE: REGENERACIÓN — usa ángulo creativo distinto.\n"
        : '';

    if ($seccion !== '') {
        $seccion_nombres = [
            'categorias'   => 'Categorías de Equipos (Drywall, Acabados, Construcción en seco)',
            'tendencias'   => 'Productos en Tendencia (demanda actual en obra)',
            'mas_vendidos' => 'Los Más Vendidos (prueba social comercial)',
            'logos'        => 'Marcas Aliadas / fabricantes oficiales',
            'blog'         => 'Bloque Blog en el home (guías y novedades)',
        ];
        $descr = $seccion_nombres[$seccion] ?? $seccion;
        return "Rol: Copywriter Senior E-commerce para IMPROGYP (Ecuador).\n"
            . "Encabezado HOME — sección: $descr.\n"
            . $ctxActual . $instrRegen
            . "NO uses: $evitar\n"
            . "REGLAS: tit_normal max 4 palabras; tit_resaltado max 2; sub max 14 palabras.\n"
            . 'JSON: {"tit_normal":"...","tit_resaltado":"...","sub":"..."}';
    }

    $ctxCat = $categoria === 'Todos'
        ? 'vista Todos del CATÁLOGO (productos.php), NO la home.'
        : "categoría: $categoria";

    return "Rol: Copywriter Senior E-commerce IMPROGYP (Ecuador).\n"
        . "Copy para $ctxCat.\n"
        . $ctxActual . $instrRegen
        . "NO uses: $evitar\n"
        . "Evita 'Lo mejor en' y 'Potencia Total'.\n"
        . "REGLAS: tit_normal max 5 palabras; tit_resaltado max 2; sub max 12.\n"
        . 'JSON: {"tit_normal":"...","tit_resaltado":"...","sub":"..."}';
}

function improgyp_gemini_generar_copy(string $prompt, array $env, bool $reintentar = true): array
{
    $apiKey = improgyp_gemini_api_key($env);
    if ($apiKey === '') {
        return ['error' => 'GEMINI_API_KEY no configurada en .env'];
    }

    $modelName = improgyp_gemini_model($env);
    $urlGemini = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . $apiKey;
    $payload = [
        'contents' => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => [
            'temperature' => 0.95,
            'topP' => 0.92,
            'responseMimeType' => 'application/json',
            'maxOutputTokens' => 256,
        ],
    ];

    $curlOpts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 55,
    ];
    if (file_exists(dirname(__DIR__) . '/cacert.pem')) {
        $curlOpts[CURLOPT_CAINFO] = dirname(__DIR__) . '/cacert.pem';
        $curlOpts[CURLOPT_SSL_VERIFYPEER] = true;
    } else {
        $curlOpts[CURLOPT_SSL_VERIFYPEER] = false;
    }

    $intentar = static function () use ($urlGemini, $curlOpts): array {
        $ch = curl_init($urlGemini);
        curl_setopt_array($ch, $curlOpts);
        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['body' => $response, 'curl_err' => $curlErr, 'http' => $httpCode];
    };

    $res = $intentar();
    if ($res['curl_err']) {
        return ['error' => 'Error de conexión IA: ' . $res['curl_err']];
    }

    if (in_array($res['http'], [429, 503], true) && $reintentar) {
        usleep(2000000);
        $res = $intentar();
        if ($res['curl_err']) {
            return ['error' => 'Error de conexión IA (reintento): ' . $res['curl_err']];
        }
    }

    if ($res['http'] !== 200) {
        $decodedErr = json_decode($res['body'], true);
        $msg = $decodedErr['error']['message'] ?? "HTTP {$res['http']}";
        if ($res['http'] === 429) {
            $msg = 'Cuota Gemini agotada. Espera unos segundos (un botón IA a la vez).';
        }
        return ['error' => 'Gemini API: ' . $msg];
    }

    $jsonRes = json_decode($res['body'], true);
    if (!is_array($jsonRes)) {
        return ['error' => 'Respuesta inválida del servidor IA'];
    }
    if (!empty($jsonRes['error']['message'])) {
        return ['error' => 'Gemini: ' . $jsonRes['error']['message']];
    }

    $candidate = $jsonRes['candidates'][0] ?? null;
    if (!$candidate) {
        return ['error' => 'Gemini no devolvió candidatos.'];
    }

    $raw = $candidate['content']['parts'][0]['text'] ?? '';
    $raw = preg_replace('/^```json\s*|\s*```$/i', '', trim($raw));
    if ($raw === '') {
        return ['error' => 'Gemini devolvió texto vacío.'];
    }

    $parsed = json_decode($raw, true);
    if (!is_array($parsed) || empty($parsed['tit_normal']) || !isset($parsed['sub'])) {
        return ['error' => 'Formato JSON incorrecto de la IA.'];
    }

    return [
        'tit_normal' => trim($parsed['tit_normal']),
        'tit_resaltado' => trim($parsed['tit_resaltado'] ?? ''),
        'sub' => trim($parsed['sub']),
    ];
}
