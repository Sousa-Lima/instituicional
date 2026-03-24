#!/usr/bin/env bash
# Republica o site estático (Astro): gera frontend/dist e, opcionalmente, força o serviço nginx no Swarm.
#
# Uso (a partir da raiz do repositório):
#   ./deploy/republish-frontend.sh
#   ./deploy/republish-frontend.sh --force-service
#   ./deploy/republish-frontend.sh --force-service --service slc_frontend
#
# Comportamento para ambientes containerizados:
# - Se Docker daemon estiver acessível: usa ./frontend/scripts/docker-node.sh run build
# - Se Docker daemon não estiver acessível, mas houver npm local: usa npm run build

set -euo pipefail

FORCE_SERVICE=false
SERVICE_NAME="slc_frontend"

while [[ $# -gt 0 ]]; do
	case "$1" in
		--force-service)
			FORCE_SERVICE=true
			shift
			;;
		--service)
			if [[ -z "${2:-}" ]]; then
				echo "Erro: --service requer um nome de serviço." >&2
				exit 1
			fi
			SERVICE_NAME="$2"
			shift 2
			;;
		-h|--help)
			echo "Uso: ./deploy/republish-frontend.sh [--force-service] [--service <nome>]"
			exit 0
			;;
		*)
			echo "Erro: argumento desconhecido '$1'." >&2
			echo "Use --help para ver as opções." >&2
			exit 1
			;;
	esac
done

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
FRONTEND_DIR="$ROOT/frontend"
DIST_DIR="$FRONTEND_DIR/dist"

if [[ ! -d "$FRONTEND_DIR" ]]; then
	echo "Erro: diretório frontend não encontrado em $FRONTEND_DIR" >&2
	exit 1
fi

cd "$FRONTEND_DIR"

has_docker() {
	command -v docker >/dev/null 2>&1
}

docker_ready() {
	has_docker && docker info >/dev/null 2>&1
}

has_npm() {
	command -v npm >/dev/null 2>&1
}

echo "[republish] Build do frontend..."
if docker_ready; then
	echo "[republish] Docker disponível; executando build via container Node."
	./scripts/docker-node.sh run build
elif has_npm; then
	echo "[republish] Docker indisponível; executando build com npm local (container atual)."
	npm run build
else
	echo "Erro: não foi possível construir o frontend (sem Docker acessível e sem npm local)." >&2
	exit 1
fi

if [[ ! -f "$DIST_DIR/index.html" ]]; then
	echo "Erro: build concluído sem gerar $DIST_DIR/index.html" >&2
	exit 1
fi

echo ""
echo "OK: artefactos em $DIST_DIR"

if [[ "$FORCE_SERVICE" == true ]]; then
	if ! has_docker; then
		echo "Erro: 'docker' não encontrado; não foi possível forçar serviço '$SERVICE_NAME'." >&2
		exit 1
	fi

	if ! docker info >/dev/null 2>&1; then
		echo "Erro: Docker encontrado, mas daemon indisponível (sem acesso ao socket/contexto)." >&2
		exit 1
	fi

	if ! docker service inspect "$SERVICE_NAME" >/dev/null 2>&1; then
		echo "Erro: serviço '$SERVICE_NAME' não encontrado no Swarm." >&2
		exit 1
	fi

	echo "[republish] Forçando atualização do serviço '$SERVICE_NAME'..."
	docker service update --force "$SERVICE_NAME"
	echo "Serviço '$SERVICE_NAME' atualizado (forçado)."
else
	echo "Dica: se o nginx não refletir ficheiros novos de imediato, correr:"
	echo "  ./deploy/republish-frontend.sh --force-service"
fi
