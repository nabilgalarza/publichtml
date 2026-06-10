#!/usr/bin/env bash
# Genera improgyp_deploy.zip listo para Hostinger (Administrador de archivos).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$(dirname "$ROOT")/improgyp_deploy.zip"

cd "$ROOT"

echo "Empaquetando IMPROGYP desde: $ROOT"
echo "Destino: $OUT"

rm -f "$OUT"

zip -r "$OUT" . \
  -x "*.git*" \
  -x ".env" \
  -x "*.sql" \
  -x "copia_seguridad_*" \
  -x ".DS_Store" \
  -x "*/.DS_Store" \
  -x "cache_geo/*" \
  -x "cache_*.json" \
  -x "temp/*" \
  -x "img_temp_carga/*" \
  -x "storage/import_tmp/*" \
  -x "node_modules/*" \
  -x ".vscode/*" \
  -x ".idea/*" \
  -x "improgyp_deploy.zip"

SIZE="$(du -h "$OUT" | cut -f1)"
echo ""
echo "Listo: $OUT ($SIZE)"
echo "Sube este ZIP a public_html en Hostinger y extráelo."
