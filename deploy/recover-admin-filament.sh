#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="/srv/sistemas/slc"
ADMIN_DIR="$ROOT_DIR/admin"

echo "[1/7] Verificando diretórios..."
[[ -d "$ADMIN_DIR" ]] || { echo "Diretório não encontrado: $ADMIN_DIR"; exit 1; }

echo "[2/7] Instalando dependências PHP (inclui Filament)..."
cd "$ADMIN_DIR"
# O container composer:latest não inclui ext-intl; ignoramos apenas na fase de instalação
# porque a imagem de runtime do app possui intl habilitado.
docker run --rm -v "$ADMIN_DIR":/app -w /app composer:latest \
  composer install --no-dev -o --ignore-platform-req=ext-intl

echo "[3/7] Forçando restart dos serviços Laravel..."
docker service update --force slc_app >/dev/null
docker service update --force slc_api_nginx >/dev/null
docker service update --force slc_queue >/dev/null
docker service update --force slc_scheduler >/dev/null

echo "[4/7] Aguardando container do app ficar disponível..."
APP_CID=""
for _ in {1..30}; do
  APP_CID="$(docker ps -q -f name=slc_app | head -n 1 || true)"
  if [[ -n "$APP_CID" ]]; then
    break
  fi
  sleep 2
done

[[ -n "$APP_CID" ]] || { echo "Não foi possível encontrar container ativo de slc_app"; exit 1; }

echo "[5/7] Limpando e reconstruindo cache Laravel..."
docker exec -i "$APP_CID" php artisan optimize:clear
docker exec -i "$APP_CID" php artisan config:cache
docker exec -i "$APP_CID" php artisan route:cache

echo "[6/7] Instalando assets/migrations do Filament..."
docker exec -i "$APP_CID" php artisan filament:install --no-interaction
docker exec -i "$APP_CID" php artisan migrate --force

echo "[7/7] Validação rápida das rotas..."
docker exec -i "$APP_CID" php artisan route:list --path=admin | head -n 40 || true

echo "Recuperação concluída. Teste: https://api.sousalimaconsultoria.com.br/admin"
