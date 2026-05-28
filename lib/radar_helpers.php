<?php
/**
 * Radar de ventas — consultas y periodo (dashboard).
 */

function improgyp_radar_periodo_valido(string $raw): string
{
    return in_array($raw, ['7d', '30d', 'todo'], true) ? $raw : '7d';
}

function improgyp_radar_sql_fecha(string $periodo, string $alias = ''): string
{
    $col = ($alias !== '') ? $alias . '.fecha' : 'fecha';
    if ($periodo === '7d') {
        return " AND {$col} >= (NOW() - INTERVAL 7 DAY)";
    }
    if ($periodo === '30d') {
        return " AND {$col} >= (NOW() - INTERVAL 30 DAY)";
    }
    return '';
}

function improgyp_radar_periodo_label(string $periodo): string
{
    return match ($periodo) {
        '30d' => 'Últimos 30 días',
        'todo' => 'Histórico completo',
        default => 'Últimos 7 días',
    };
}

/**
 * @return array<string, mixed>
 */
function improgyp_radar_load(PDO $pdo, string $periodo): array
{
    $periodo = improgyp_radar_periodo_valido($periodo);
    $df = improgyp_radar_sql_fecha($periodo);

    $out = [
        'periodo' => $periodo,
        'periodo_label' => improgyp_radar_periodo_label($periodo),
        'radar_error' => null,
        'total_eventos' => 0,
        'visitantes_unicos' => 0,
        'paginas_vistas' => 0,
        'checkouts' => 0,
        'pedidos_publicos' => 0,
        'top_carrito' => [],
        'top_wishlist' => [],
        'top_ia' => [],
        'top_vistas' => [],
        'top_categorias' => [],
        'top_regiones' => [],
        'productos_fantasma' => [],
        'heatmap' => array_fill(0, 24, 0),
        'heatmap_dow' => array_fill(1, 7, 0),
        'max_heat' => 1,
        'max_heat_dow' => 1,
        'funnel' => [
            'visita' => 0,
            'ver_producto' => 0,
            'carrito' => 0,
            'checkout' => 0,
        ],
    ];

    try {
        $out['total_eventos'] = (int) $pdo->query("SELECT COUNT(*) FROM metricas_b2c WHERE 1=1{$df}")->fetchColumn();

        $out['visitantes_unicos'] = (int) $pdo->query(
            "SELECT COUNT(DISTINCT COALESCE(NULLIF(visitor_id,''), CONCAT('ip:', ip)))
             FROM metricas_b2c WHERE evento = 'Visita'{$df}"
        )->fetchColumn();

        $out['paginas_vistas'] = (int) $pdo->query(
            "SELECT COUNT(*) FROM metricas_b2c WHERE evento = 'Visita'{$df}"
        )->fetchColumn();

        $out['checkouts'] = (int) $pdo->query(
            "SELECT COUNT(*) FROM metricas_b2c WHERE evento = 'Checkout Iniciado'{$df}"
        )->fetchColumn();

        try {
            $dfPed = str_replace('fecha', 'fecha', improgyp_radar_sql_fecha($periodo));
            $out['pedidos_publicos'] = (int) $pdo->query(
                "SELECT COUNT(*) FROM pedidos_publicos WHERE 1=1{$dfPed}"
            )->fetchColumn();
        } catch (Throwable $e) {
            $out['pedidos_publicos'] = 0;
        }

        $out['top_carrito'] = $pdo->query(
            "SELECT valor, COUNT(*) as total FROM metricas_b2c
             WHERE evento = 'Añadir a Carrito'{$df} GROUP BY valor ORDER BY total DESC LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);

        $out['top_wishlist'] = $pdo->query(
            "SELECT valor, COUNT(*) as total FROM metricas_b2c
             WHERE evento = 'Añadir a Wishlist'{$df} GROUP BY valor ORDER BY total DESC LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);

        $out['top_ia'] = $pdo->query(
            "SELECT valor, COUNT(*) as total FROM metricas_b2c
             WHERE evento IN ('Búsqueda IA', 'Búsqueda Live'){$df}
             GROUP BY valor ORDER BY total DESC LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);

        $out['top_vistas'] = $pdo->query(
            "SELECT valor, COUNT(*) as total FROM metricas_b2c
             WHERE evento = 'Ver Producto'{$df} GROUP BY valor ORDER BY total DESC LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);

        $out['top_categorias'] = $pdo->query(
            "SELECT categoria, COUNT(*) as total FROM metricas_b2c
             WHERE categoria != '' AND categoria NOT IN ('Pageview','AI','Live','General'){$df}
             GROUP BY categoria ORDER BY total DESC LIMIT 6"
        )->fetchAll(PDO::FETCH_ASSOC);

        $top_regiones = $pdo->query(
            "SELECT COALESCE(NULLIF(region,''), 'Desconocida') as region_nombre,
                    MAX(ip) as ip_sample, COUNT(*) as total
             FROM metricas_b2c WHERE 1=1{$df}
             GROUP BY region_nombre ORDER BY total DESC LIMIT 6"
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($top_regiones as &$tr) {
            $tr['region'] = $tr['region_nombre'];
            $tr['ip'] = $tr['ip_sample'];
        }
        unset($tr);
        $out['top_regiones'] = $top_regiones;

        $out['productos_fantasma'] = $pdo->query(
            "SELECT c.nombre, c.categoria, c.imagen_url
             FROM improgyp_catalogo c
             LEFT JOIN metricas_b2c m ON c.nombre = m.valor AND m.evento IN ('Ver Producto','Añadir a Carrito','Añadir a Wishlist')
             WHERE c.publicado = 1 AND m.id IS NULL
             ORDER BY c.id DESC LIMIT 10"
        )->fetchAll(PDO::FETCH_ASSOC);

        $impulsados_raw = $pdo->query(
            "SELECT nombre_producto FROM productos_impulsados WHERE fecha_limite > NOW()"
        )->fetchAll(PDO::FETCH_COLUMN);
        $impulsados_set = array_flip($impulsados_raw);
        foreach ($out['productos_fantasma'] as &$f) {
            $f['impulsado'] = isset($impulsados_set[$f['nombre']]);
        }
        unset($f);

        $heatmap_raw = $pdo->query(
            "SELECT HOUR(fecha) as hora, COUNT(*) as total FROM metricas_b2c WHERE 1=1{$df} GROUP BY hora ORDER BY hora ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($heatmap_raw as $row) {
            $h = (int) $row['hora'];
            $out['heatmap'][$h] = (int) $row['total'];
            if ($out['heatmap'][$h] > $out['max_heat']) {
                $out['max_heat'] = $out['heatmap'][$h];
            }
        }

        $dow_raw = $pdo->query(
            "SELECT DAYOFWEEK(fecha) as dow, COUNT(*) as total FROM metricas_b2c WHERE 1=1{$df} GROUP BY dow"
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($dow_raw as $row) {
            $d = (int) $row['dow'];
            $out['heatmap_dow'][$d] = (int) $row['total'];
            if ($out['heatmap_dow'][$d] > $out['max_heat_dow']) {
                $out['max_heat_dow'] = $out['heatmap_dow'][$d];
            }
        }

        $funnelMap = [
            'visita' => 'Visita',
            'ver_producto' => 'Ver Producto',
            'carrito' => 'Añadir a Carrito',
            'checkout' => 'Checkout Iniciado',
        ];
        foreach ($funnelMap as $key => $evento) {
            $out['funnel'][$key] = (int) $pdo->query(
                "SELECT COUNT(*) FROM metricas_b2c WHERE evento = " . $pdo->quote($evento) . $df
            )->fetchColumn();
        }
    } catch (Exception $e) {
        $out['radar_error'] = 'Advertencia: algunos datos del Radar no pudieron cargarse. ' . substr($e->getMessage(), 0, 120);
    }

    return $out;
}

/** Etiquetas MySQL DAYOFWEEK: 1=Dom … 7=Sáb */
function improgyp_radar_dow_labels(): array
{
    return [
        1 => 'Dom',
        2 => 'Lun',
        3 => 'Mar',
        4 => 'Mié',
        5 => 'Jue',
        6 => 'Vie',
        7 => 'Sáb',
    ];
}
