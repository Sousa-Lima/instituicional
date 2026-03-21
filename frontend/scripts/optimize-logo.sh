#!/usr/bin/env bash
# Redimensiona a logo em docs/ para frontend/public/ (PNG + WebP opcional).
# Uso (a partir de frontend/): ./scripts/optimize-logo.sh
# Sem Pillow local: usa Docker (python:3.12-slim-bookworm).
#
# Variáveis opcionais:
#   LOGO_MAX_WIDTH   largura máxima em px (predef.: 1000)
#   PYTHON_IMAGE     imagem Docker (predef.: python:3.12-slim-bookworm)

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPO_SLC="$(cd "$ROOT/.." && pwd)"
SRC="${SRC:-$REPO_SLC/docs/sousa-lima-consultoria-logo-horizontal-colorido.png}"
MAX="${LOGO_MAX_WIDTH:-1000}"
PY_IMAGE="${PYTHON_IMAGE:-python:3.12-slim-bookworm}"

if [[ ! -f "$SRC" ]]; then
	echo "Ficheiro de origem não encontrado: $SRC" >&2
	exit 1
fi

run_python_in_docker() {
	docker run --rm \
		-v "$REPO_SLC:/work" \
		-w /work/frontend \
		-e "LOGO_MAX_WIDTH=$MAX" \
		"$PY_IMAGE" \
		bash -c "pip install -q Pillow && python3 scripts/optimize-logo.py --webp"
}

if python3 -c 'import PIL' 2>/dev/null; then
	LOGO_MAX_WIDTH="$MAX" python3 "$ROOT/scripts/optimize-logo.py" --webp
else
	echo "Pillow não encontrado localmente; a usar Docker ($PY_IMAGE)…"
	run_python_in_docker
fi

echo "Feito. Voltar a correr: npm run build (e atualizar o serviço nginx/Swarm se aplicável)."
