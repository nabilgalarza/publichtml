<?php
/**
 * Variables compartidas para la vista de ayuda (dashboard ?view=ayuda).
 */
$docDashboardBase = 'dashboard.php';
$docEsMaster = isset($_SESSION['admin_rol']) && $_SESSION['admin_rol'] === 'master';
$docVerB2b = function_exists('improgyp_b2b_admin_ver_modulo') && improgyp_b2b_admin_ver_modulo();

require_once __DIR__ . '/sections/_helpers.php';

$docSectionIds = [
    'inicio', 'radar', 'pedidos-publicos', 'inventario', 'pautas', 'marketing', 'seo',
    'apariencia', 'blog', 'locales',
];
if ($docVerB2b) {
    $docSectionIds[] = 'vip';
    $docSectionIds[] = 'pedidos-b2b';
}
$docSectionIds[] = 'sistema';
if ($docEsMaster) {
    $docSectionIds[] = 'usuarios';
}
$docSectionIds[] = 'faq';

$docSeccionActiva = $_GET['seccion'] ?? 'inicio';
if (!in_array($docSeccionActiva, $docSectionIds, true)) {
    $docSeccionActiva = 'inicio';
}
$docModoCompleto = isset($_GET['modo']) && $_GET['modo'] === 'completo';

$sectionsDir = __DIR__ . '/sections';
