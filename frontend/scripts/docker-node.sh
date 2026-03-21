#!/usr/bin/env bash
# Executa npm no Node 22 via Docker (sem Node local).
# Uso (a partir de frontend/): ./scripts/docker-node.sh install | ./scripts/docker-node.sh run dev | ...
# Variável opcional: NODE_IMAGE (predef.: node:22-bookworm-slim)

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
IMAGE="${NODE_IMAGE:-node:22-bookworm-slim}"

# Se o primeiro argumento não for "npm", assumimos atalhos (ex.: install -> npm install)
if [[ $# -eq 0 ]]; then
	set -- npm run dev
elif [[ "$1" != "npm" ]]; then
	set -- npm "$@"
fi

PORTS=()
joined="$*"
if [[ "$joined" == *" run dev"* ]] || [[ "$joined" == *" run preview"* ]]; then
	PORTS+=( -p 4321:4321 )
fi

ENV_ARGS=()
if [[ -f "$ROOT/.env" ]]; then
	ENV_ARGS+=( --env-file "$ROOT/.env" )
fi

TTY=()
if [[ -t 1 ]]; then
	TTY+=( -it )
fi

exec docker run --rm "${TTY[@]}" "${PORTS[@]}" "${ENV_ARGS[@]}" \
	-v "$ROOT:/app" \
	-w /app \
	"$IMAGE" \
	"$@"
