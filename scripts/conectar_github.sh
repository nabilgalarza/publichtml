#!/usr/bin/env bash
# Conecta el repo local con GitHub y hace el primer push.
# Uso: bash scripts/conectar_github.sh https://github.com/TU_USUARIO/improgyp-web.git
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ $# -lt 1 ]]; then
  echo "Uso: bash scripts/conectar_github.sh https://github.com/TU_USUARIO/improgyp-web.git"
  exit 1
fi

URL="$1"

if ! git rev-parse --git-dir >/dev/null 2>&1; then
  echo "Error: no hay repositorio git en $ROOT"
  exit 1
fi

if git remote get-url origin >/dev/null 2>&1; then
  echo "Remote 'origin' ya existe:"
  git remote -v
  read -r -p "¿Reemplazar origin con $URL? [s/N] " ans
  if [[ "${ans,,}" != "s" && "${ans,,}" != "y" ]]; then
    echo "Cancelado."
    exit 0
  fi
  git remote set-url origin "$URL"
else
  git remote add origin "$URL"
fi

git branch -M main

echo ""
echo "── Subiendo a GitHub ──"
git push -u origin main

echo ""
echo "Listo. Siguiente paso en Hostinger hPanel → Git → conectar:"
echo "  $URL"
echo "  Rama: main | Carpeta: public_html"
echo "  No borres el .env del servidor."
