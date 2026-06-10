# IMPROGYP — Despliegue con Git (Hostinger)

Flujo recomendado: **Mac → GitHub → Hostinger (public_html)**.

El archivo `.env` **nunca** va al repositorio. Solo existe en el servidor.

---

## 1. Una sola vez — GitHub

1. Crea un repositorio vacío en GitHub (ej. `improgyp-web`). **Sin** README ni `.gitignore` (ya están en el proyecto).
2. En tu Mac:

```bash
cd /Applications/MAMP/htdocs/publichtml
git remote add origin https://github.com/TU_USUARIO/improgyp-web.git
git branch -M main
git push -u origin main
```

(Sustituye la URL por la de tu repo.)

---

## 2. Una sola vez — Hostinger (hPanel)

1. **Sitios web** → tu dominio → **Git** (o **Avanzado → Git**).
2. **Crear repositorio** / **Conectar repositorio**:
   - URL: `https://github.com/TU_USUARIO/improgyp-web.git`
   - Rama: `main`
   - Directorio de despliegue: `public_html` (raíz del sitio)
3. Si Hostinger clona en una subcarpeta (ej. `public_html/repositories/improgyp-web/`):
   - Copia el contenido a `public_html/` **o** configura el document root según la ayuda de Hostinger.
   - Debe quedar: `public_html/index.php`, no `public_html/publichtml/index.php`.
4. **No borres** el `.env` que ya creaste en el servidor.
5. Primera vez tras el deploy: entra a `dashboard.php` → importar catálogo si la BD está vacía.

### Webhook (opcional — deploy automático al hacer push)

En GitHub → repo → **Settings → Webhooks → Add webhook**:

- Payload URL: la que te da Hostinger en la sección Git
- Content type: `application/json`
- Event: **Just the push event**

---

## 3. Cada cambio — flujo habitual

En tu Mac, después de editar código:

```bash
cd /Applications/MAMP/htdocs/publichtml
git status
git add archivo1.php archivo2.php    # o: git add .
git commit -m "Descripción breve del cambio"
git push origin main
```

En Hostinger:

- Con webhook: espera ~1 min y recarga el sitio.
- Sin webhook: hPanel → **Git** → **Deploy** / **Pull**.

---

## 4. Qué NO sube Git (`.gitignore`)

| Ignorado | Motivo |
|----------|--------|
| `.env` | Secretos (BD, Gemini, admin) |
| `cache_*.json` | Se generan en producción |
| `temp/`, `img_temp_carga/` | Staging de imports |
| `improgyp_deploy.zip` | ZIP manual (opción B) |

**Sí van en Git:** `catalogo.json`, `config_*.json`, `img_catalogo/`, `ads_media/`, PHP, JS, CSS.

---

## 5. Si solo cambiaste un archivo (sin Git)

Sube por FTP / Administrador de archivos el archivo exacto a la misma ruta en `public_html/`.

**No sobrescribas en servidor:** `.env`, `cache_ranking.json` (salvo que quieras forzar refresco de ranking).

---

## 6. Comandos útiles

```bash
# Ver qué cambió
git status
git diff

# Deshacer cambios locales en un archivo (antes de commit)
git checkout -- ruta/al/archivo.php

# Ver historial
git log --oneline -10
```

---

## 7. Resolución de problemas

| Problema | Solución |
|----------|----------|
| `git push` pide usuario/contraseña | Usa **Personal Access Token** de GitHub como contraseña, o SSH |
| Deploy borró `.env` | Restaura `.env` desde copia en hPanel; nunca está en Git |
| Sitio en blanco tras deploy | Revisa que `index.php` esté en raíz de `public_html` |
| Cambios no se ven | Deploy en hPanel + Ctrl+Shift+R; borra `cache_ranking.json` si tocaste impulsos |

---

Documentación ZIP manual: `DESPLIEGUE_HOSTINGER.txt` y `scripts/preparar_subida.sh`.
