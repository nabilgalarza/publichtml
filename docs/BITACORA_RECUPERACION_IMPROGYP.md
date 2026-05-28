# Bitácora — Recuperación arquitectura IMPROGYP

**Fecha:** 2026-05-26  
**Base:** `/Applications/MAMP/htdocs/publichtml`  
**Referencia:** transcript Cursor `467c9373-c79d-4db8-a4cc-f26a4d78c84e`  
**Respaldo monolito:** `index_monolith_backup.php` (home + tienda en un solo archivo)

---

## Mapa de sitio (objetivo recuperado)

| URL / archivo | Rol |
|---------------|-----|
| `index.php` | **HOME** — landing desde `config_landing.json` |
| `productos.php` | **TIENDA** — catálogo, filtros, IA, carrito, modales |
| `blog.php` | Blog público (`config_blog.json`) |
| `core_init.php` | Mantenimiento, SEO, `?p=` producto, `$base_url` |
| `components/header.php` | Nav, megamenú, wishlist, carrito, modal checkout |
| `components/footer.php` | Pie con enlaces |
| `components/megamenu_config.php` | Defaults + normalización megamenú |
| `components/apariencia_megamenu.php` | Editor dashboard |
| `components/checkout_modal.php` | Modal checkout compacto (formulario + resumen IVA) |
| `components/checkout_scripts.php` | `IMPROGYP_CHECKOUT` + carga JS (vía `footer.php`) |
| `config_checkout.json` | IVA, bancos/logos, textos transferencia |
| `lib/checkout_helpers.php` | Lectura de config checkout PHP/JS |
| `js/checkout_wa.js` | Carrito, totales IVA, WhatsApp, sucursales, eliminar ítem |
| `js/cart_checkout.js` | Bolsa drawer + sync con checkout |
| `includes/whatsapp_normalize.php` | `5939XXXXXXXX` |
| `includes/locales_cobertura.php` | Parseo campo `cobertura` |
| `config_header.json` | Megamenú persistido |
| `config_landing.json` | Textos portada |
| `config_blog.json` | Layout y posts blog |
| `locales.json` | Sucursales + `cobertura[]` + WhatsApp |
| `dashboard.php` | `guardar_megamenu`, sucursales con cobertura |

---

## Flujos clave

### Megamenú
- Desktop: pestañas bajo el nav → panel 2 columnas con enlaces a `productos.php?cat=` o `?q=`.
- Móvil: acordeón simplificado (primera división + enlace Catálogo).
- Admin: **Dashboard → Megamenú B2C** (`?view=apariencia&sub=megamenu`).

### Checkout WhatsApp
1. Cliente abre carrito → **Finalizar cotización**.
2. Retiro: elige sucursal (lista desde `locales.json`); WhatsApp del asesor = campo `whatsapp` de esa sucursal.
3. Domicilio: dirección + ciudad → match por `cobertura` (prioridad) luego `ciudad` sede.
4. Sin match → Matriz `gye-matriz`.
5. Mensaje plantilla en `buildCheckoutWhatsAppMessage()` (contacto, recepción, pago, productos, neto + IVA).
6. Validación unificada `validateCheckoutForm()`; sucursales se recargan al abrir modal y al volver a la pestaña.

### Dedup catálogo
- En grid: único por **SKU** (`codigo`); si no hay código, por `nombre`.

### Deep links tienda
- `productos.php?p=` — modal producto  
- `productos.php?cat=` — categoría  
- `productos.php?q=` — búsqueda  
- `productos.php?wishlist=1` — lista de deseos  

---

## Checklist post-recuperación

- [x] Importar `u718580158_improgyp.sql` en MAMP (ver `docs/IMPORTAR_BD_MAMP.md`)
- [x] `.env` local creado (MAMP: puerto 8889, base `u718580158_improgyp`)
- [ ] Actualizar **WhatsApp reales** por sucursal en Dashboard → Red de Sucursales
- [ ] Revisar **cobertura** (ej. Manta: Portoviejo, Jipijapa) en cada local
- [ ] Probar checkout retiro y domicilio (ciudad con y sin cobertura)
- [ ] Guardar megamenú en dashboard y verificar enlaces en tienda
- [ ] `git init` + commit + push para no perder de nuevo esta versión

---

## Archivos generados en esta recuperación

```
publichtml/
├── index.php                    # HOME (nuevo)
├── productos.php                # TIENDA
├── blog.php
├── core_init.php
├── index_monolith_backup.php    # referencia
├── components/
│   ├── header.php
│   ├── footer.php
│   ├── head_store.php
│   ├── tienda_body.php
│   ├── tienda_scripts.php
│   ├── checkout_modal.php
│   ├── megamenu_config.php
│   └── apariencia_megamenu.php
├── js/checkout_wa.js
├── includes/
│   ├── whatsapp_normalize.php
│   └── locales_cobertura.php
├── config_header.json
├── config_landing.json
├── config_blog.json
└── docs/BITACORA_RECUPERACION_IMPROGYP.md
```

---

## Pendiente / fases siguientes (chat)

- Aviso ciudades duplicadas en cobertura (dashboard)
- Unificar botón “Comprar por WhatsApp” en ficha producto con misma lógica de cobertura
- Blog completo (`api_blog_ai.php`, `home_blog_section.php`, editor apariencia blog)
- Higiene: `.env.example`, `README.md`, fases en `PLAN_HIGIENE_FASES.md`

---

## Notas

- `header.php` del chat original (~2200 líneas) **no** estaba en transcript como `Write` completo; se reconstruyó desde monolito + parches documentados.
- Backup zip `backups-improgyp/publichtml-20260520-*` no encontrado en disco al recuperar.
