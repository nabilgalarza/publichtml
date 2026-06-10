<?php
if (!function_exists('doc_link')) {
function doc_link(string $view, string $label, string $extra = ''): string {
    global $docDashboardBase;
    $href = htmlspecialchars($docDashboardBase . '?view=' . rawurlencode($view) . $extra, ENT_QUOTES, 'UTF-8');
    return '<a class="doc-cta" href="' . $href . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ' <i class="fa-solid fa-arrow-up-right-from-square"></i></a>';
}
function doc_section_open(string $id, string $title, string $subtitle = ''): void {
    global $docSeccionActiva, $docModoCompleto;
    $activa = $docSeccionActiva ?? 'inicio';
    $active = (!empty($docModoCompleto) || $id === $activa) ? ' active' : '';
    echo '<section id="doc-' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" class="doc-section' . $active . '">';
    echo '<h2>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
    if ($subtitle !== '') {
        echo '<p class="doc-lead">' . htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') . '</p>';
    }
}
function doc_section_close(): void {
    echo '</section>';
}
}
