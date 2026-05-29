# Importar base de datos en MAMP

## Error #1046 “Base de datos no seleccionada”

Ocurre si importas el `.sql` **sin** haber creado y seleccionado la base. El dump solo trae `CREATE TABLE`, no `CREATE DATABASE`.

## Pasos en phpMyAdmin (`http://localhost:8888/phpMyAdmin/` — MySQL puerto **8889**)

1. Pestaña **Bases de datos** → crear **`u718580158_improgyp`** (utf8mb4_unicode_ci).
2. Clic en **`u718580158_improgyp`** en el panel izquierdo (debe quedar seleccionada).
3. Pestaña **Importar** → archivo de respaldo `.sql` (no está en el repositorio por seguridad; usar copia offline del equipo) → **Continuar**.
4. Ejecutar también `database/01_improgyp_blog.sql` si la tabla `improgyp_blog` no viene en el dump.

Alternativa paso 1: ejecutar `database/00_crear_base.sql` en SQL (nivel servidor), luego importar dentro de la base.

## Terminal (cliente MySQL de MAMP)

```bash
/Applications/MAMP/Library/bin/mysql80/bin/mysql -uroot -proot -S /Applications/MAMP/tmp/mysql/mysql.sock < database/00_crear_base.sql
/Applications/MAMP/Library/bin/mysql80/bin/mysql -uroot -proot -S /Applications/MAMP/tmp/mysql/mysql.sock u718580158_improgyp < /ruta/a/tu/respaldo.sql
/Applications/MAMP/Library/bin/mysql80/bin/mysql -uroot -proot -S /Applications/MAMP/tmp/mysql/mysql.sock u718580158_improgyp < database/01_improgyp_blog.sql
```

## Después del import

1. Copiar configuración: `cp .env.example .env` (o usar el `.env` ya generado).
2. Ajustar `GEMINI_API_KEY` y `GEMINI_MODEL=gemini-2.5-flash-lite` en `.env` si usas IA en tienda/dashboard.
3. Sincronizar catálogo JSON (opcional):  
   `php scripts/sync_catalogo_from_db.php`
4. Probar: `http://localhost:8888/publichtml/dashboard.php`  
   - Usuario en BD: **`admin`** (contraseña la del backup de producción; `ADMIN_PASSWORD` en `.env` solo aplica si no hay filas en `usuarios_admin`).

## Verificar conexión

Abrir el dashboard y el catálogo en la tienda. Si hay error PDO, revisar `DB_HOST`, `DB_PORT` y `DB_NAME` en `.env`.
