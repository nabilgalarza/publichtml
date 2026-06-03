<?php
/**
 * Elimina solo productos marca IMPLED y regenera catalogo.json.
 *
 * @return array{eliminados: int, fotos_borradas: int}
 */
function improgyp_purgar_impled(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT id, imagen_url FROM improgyp_catalogo WHERE UPPER(TRIM(marca)) = 'IMPLED'"
    );
    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $fotos = 0;

    foreach ($filas as $fila) {
        $img = trim((string) ($fila['imagen_url'] ?? ''));
        if ($img !== '') {
            borrarFotoFisica($img);
            $fotos++;
        }
    }

    $eliminados = 0;
    if ($filas !== []) {
        $eliminados = $pdo->exec("DELETE FROM improgyp_catalogo WHERE UPPER(TRIM(marca)) = 'IMPLED'");
        if ($eliminados === false) {
            $eliminados = 0;
        }
    }

    try {
        $pdo->prepare("DELETE FROM marcas_admin WHERE UPPER(TRIM(nombre)) = 'IMPLED'")->execute();
    } catch (Exception $e) {
        /* tabla opcional */
    }

    if (function_exists('regenerarJSON')) {
        regenerarJSON($pdo);
    }

    if (function_exists('improgyp_bulk_imagen_map_clear')) {
        improgyp_bulk_imagen_map_clear();
    }

    return ['eliminados' => (int) $eliminados, 'fotos_borradas' => $fotos];
}
