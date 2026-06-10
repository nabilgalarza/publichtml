<?php
/**
 * Importa catalogo.json → improgyp_catalogo (despliegue Hostinger sin dump SQL).
 */

function improgyp_presentaciones_to_raw(array $presentaciones): string
{
    $lines = [];
    foreach ($presentaciones as $p) {
        if (!is_array($p)) {
            continue;
        }
        $opcion = trim((string) ($p['opcion'] ?? ''));
        if ($opcion === '') {
            continue;
        }
        $precio = preg_replace('/[^\d.]/', '', (string) ($p['precio'] ?? ''));
        $lines[] = $opcion . ':' . $precio;
    }
    if ($lines === []) {
        $lines[] = 'Presentación Única:';
    }
    return implode("\n", $lines);
}

/**
 * @return array{ok:bool,message:string,inserted:int,updated:int,skipped:int,total_json:int}
 */
function improgyp_sync_catalogo_json_to_db(PDO $pdo, ?string $jsonPath = null): array
{
    $path = $jsonPath ?? dirname(__DIR__) . '/catalogo.json';
    $empty = static fn (string $msg) => [
        'ok' => false,
        'message' => $msg,
        'inserted' => 0,
        'updated' => 0,
        'skipped' => 0,
        'total_json' => 0,
    ];

    if (!file_exists($path)) {
        return $empty('No se encontró catalogo.json en el servidor.');
    }

    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data) || $data === []) {
        return $empty('catalogo.json está vacío o no es válido.');
    }

    $findByCodigo = $pdo->prepare(
        'SELECT id FROM improgyp_catalogo WHERE codigo = ? AND codigo IS NOT NULL AND codigo != "" LIMIT 1'
    );
    $findByNombre = $pdo->prepare('SELECT id FROM improgyp_catalogo WHERE nombre = ? LIMIT 1');
    $insert = $pdo->prepare(
        'INSERT INTO improgyp_catalogo (nombre, codigo, marca, categoria, presentaciones_raw, desc_larga, imagen_url, publicado)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1)'
    );
    $update = $pdo->prepare(
        'UPDATE improgyp_catalogo SET nombre=?, codigo=?, marca=?, categoria=?, presentaciones_raw=?, desc_larga=?, imagen_url=?, publicado=1 WHERE id=?'
    );
    $insCat = $pdo->prepare('INSERT IGNORE INTO categorias_admin (nombre) VALUES (?)');
    $insMarca = $pdo->prepare('INSERT IGNORE INTO marcas_admin (nombre) VALUES (?)');

    $inserted = 0;
    $updated = 0;
    $skipped = 0;

    foreach ($data as $row) {
        if (!is_array($row)) {
            $skipped++;
            continue;
        }
        $nombre = trim((string) ($row['nombre'] ?? ''));
        if ($nombre === '') {
            $skipped++;
            continue;
        }

        $codigo = trim((string) ($row['codigo'] ?? ''));
        $marca = trim((string) ($row['marca'] ?? ''));
        $categoria = trim((string) ($row['categoria'] ?? ''));
        $imagen = trim((string) ($row['imagen'] ?? ''));
        $desc = trim((string) ($row['desc_larga'] ?? ''));
        $presRaw = improgyp_presentaciones_to_raw($row['presentaciones'] ?? []);

        $id = false;
        if ($codigo !== '') {
            $findByCodigo->execute([$codigo]);
            $id = $findByCodigo->fetchColumn();
        }
        if (!$id) {
            $findByNombre->execute([$nombre]);
            $id = $findByNombre->fetchColumn();
        }

        if ($id) {
            $update->execute([$nombre, $codigo, $marca, $categoria, $presRaw, $desc, $imagen, (int) $id]);
            $updated++;
        } else {
            $insert->execute([$nombre, $codigo, $marca, $categoria, $presRaw, $desc, $imagen]);
            $inserted++;
        }

        if ($categoria !== '') {
            $insCat->execute([$categoria]);
        }
        if ($marca !== '') {
            $insMarca->execute([$marca]);
        }
    }

    return [
        'ok' => true,
        'message' => sprintf(
            'Sincronizado: %d nuevos, %d actualizados, %d omitidos (de %d en JSON).',
            $inserted,
            $updated,
            $skipped,
            count($data)
        ),
        'inserted' => $inserted,
        'updated' => $updated,
        'skipped' => $skipped,
        'total_json' => count($data),
    ];
}

/**
 * Cuenta productos en catalogo.json (para banner de despliegue).
 */
function improgyp_catalogo_json_count(?string $jsonPath = null): int
{
    $path = $jsonPath ?? dirname(__DIR__) . '/catalogo.json';
    if (!file_exists($path)) {
        return 0;
    }
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? count($data) : 0;
}
