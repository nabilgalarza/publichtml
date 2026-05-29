# Demos IMPROGYP

Páginas de prototipo para revisar antes de implementar en producción.

## Megamenú v2 (recomendado)

**URL:** `http://localhost:8888/publichtml/demo/megamenu-v2.php`

Usa datos **reales** de:

- `../config_header.json` (`megamenu`, `nivel3_menu`)
- `../catalogo.json` (categorías y conteos)
- Misma normalización que `components/header.php` + `megamenu_config.php`

**No escribe** en producción (guardado demo solo en `sessionStorage` del navegador).

| Pestaña | Contenido |
|---------|-----------|
| 1 · Producción | iframe `productos.php` + aviso si `megamenu: []` |
| 2 · Propuesta desktop | Menú mejorado en marco, datos reales |
| 3 · Propuesta móvil | Sheet + scroll interno + acordeón |
| 4 · Admin propuesto | Editor + preview + huérfanas |
| 5 · Sincronización | Catálogo vs menú, enlaces OK/rotos |
| 6 · Checklist | Aprobación por fases |

## Megamenú v1 (anterior)

**URL:** `http://localhost:8888/publichtml/demo/megamenu-mejoras.html`

HTML estático con datos mock. Conservado como referencia.
