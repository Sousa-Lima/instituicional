#!/usr/bin/env bash
# Gera public/og-social.png (1200×630) para pré-visualizações em redes sociais.
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPO_SLC="$(cd "$ROOT/.." && pwd)"
PY_IMAGE="${PYTHON_IMAGE:-python:3.12-slim-bookworm}"

if python3 -c 'import PIL' 2>/dev/null; then
	python3 "$ROOT/scripts/generate-og-share.py"
else
	docker run --rm \
		-v "$REPO_SLC:/work" \
		-w /work/frontend \
		"$PY_IMAGE" \
		bash -c "pip install -q Pillow && python3 scripts/generate-og-share.py"
fi
