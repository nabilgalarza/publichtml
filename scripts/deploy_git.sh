#!/usr/bin/env bash
# Ayudante: commit + push a GitHub (Hostinger hace pull/deploy desde hPanel o webhook).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if ! git rev-parse --git-dir >/dev/null 2>&1; then
  echo "Error: no hay repositorio git. Ejecuta primero: git init"
  exit 1
fi

if [[ $# -lt 1 ]]; then
  echo "Uso: bash scripts/deploy_git.sh \"mensaje del commit\""
  echo ""
  echo "Ejemplo:"
  echo "  bash scripts/deploy_git.sh \"fix: ranking cache en Hostinger\""
  exit 1
fi

MSG="$1"

echo "── Cambios pendientes ──"
git status --short

if [[ -z "$(git status --porcelain)" ]]; then
  echo "No hay cambios para commitear."
  exit 0
fi

git add -A
git commit -m "$MSG"

if git remote get-url origin >/dev/null 2>&1; then
  echo ""
  echo "── Push a origin/main ──"
  git push origin main
  echo ""
  echo "Listo. Si tienes webhook en Hostinger, el deploy es automático."
  echo "Si no: hPanel → Git → Deploy / Pull"
else
  echo ""
  echo "Commit creado. Aún no hay remote 'origin'."
  echo "Añade GitHub: git remote add origin https://github.com/TU_USUARIO/improgyp-web.git"
  echo "Luego: git push -u origin main"
fi
