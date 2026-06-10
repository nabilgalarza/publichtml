#!/usr/bin/env bash
# Une historial MacBook + GitHub (Mac mini) y sube a origin.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "══════════════════════════════════════════"
echo " IMProGYP — Sync MacBook → GitHub"
echo "══════════════════════════════════════════"
echo "Repo: $ROOT"
echo ""

if ! git remote get-url origin >/dev/null 2>&1; then
  git remote add origin https://github.com/nabilgalarza/publichtml.git
fi

echo "→ Fetch origin..."
git fetch origin

echo ""
echo "→ Pull (unir con Mac mini)..."
if ! git pull origin main --allow-unrelated-histories --no-edit 2>/dev/null; then
  echo ""
  echo "⚠️  Hay conflictos o merge manual. Resuélvelos y luego:"
  echo "   git add ."
  echo "   git commit -m 'Unir MacBook con Mac mini'"
  echo "   git push origin main"
  exit 1
fi

echo ""
echo "→ Push origin main..."
git push -u origin main

echo ""
echo "✅ Listo. Revisa: https://github.com/nabilgalarza/publichtml"
echo "   Luego Hostinger hPanel → Git → Deploy"
