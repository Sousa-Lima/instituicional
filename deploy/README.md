# Deploy — Docker Swarm (SLC)

- **Stack Compose:** [`slc.yaml`](slc.yaml) — `docker stack deploy -c deploy/slc.yaml slc`
- **Passo a passo (secrets, variáveis no YAML, primeiro deploy, atualizar stack, rotação de secrets):** [`guia-deploy-e-atualizacao.md`](guia-deploy-e-atualizacao.md)
- **Procedimento completo (DNS, secrets, Traefik, build Astro, verificação, rollback):** [docs/conhecimento/procedimento-deploy-producao-slc.md](../docs/conhecimento/procedimento-deploy-producao-slc.md)

Persistência em disco no host: `/srv/sistemas/slc/data/` — inclui `storage/`, `frontend/dist`, **`postgres/`** (dados PG) e **`redis/`** (AOF). Ver comentários no topo de `slc.yaml`.

- **HTTP (API):** a imagem PHP é só **PHP-FPM** (`:9000`). O Traefik envia tráfego para o serviço **`api_nginx`** (`:80`), que faz FastCGI para **`slc_app:9000`**. Config: [`nginx/laravel-api.conf`](nginx/laravel-api.conf).
- **Código no host:** bind de `admin/` em `/var/www/html` até empacotar tudo na imagem (ver comentários no `slc.yaml`).
- **Composer sem PHP local:** `docker run --rm -v /srv/sistemas/slc/admin:/app -w /app composer:latest composer install --no-dev -o`
- **Scheduler:** serviço `scheduler` (`php artisan schedule:work`, 1 réplica).
- **Laravel:** snippets para `database.php` em [`laravel/README.md`](laravel/README.md). **Redis:** `REDIS_CLIENT=predis` na stack (imagem pode não ter extensão `phpredis`).
- **Permissões FPM:** na imagem `eolimabr/php8.4-sousalima-multitenant`, `www-data` é **uid/gid 82** (Alpine). `storage/` e `bootstrap/cache` no host têm de ser graváveis por esse UID — ver [guia-deploy-e-atualizacao.md](guia-deploy-e-atualizacao.md) §2.
- **Secrets Swarm:** prefixo `slc_sousalima_*` (`db_password`, `app_key`, `jwt_secret`, `smtp_password`) — ver `slc.yaml` e [laravel/README.md](laravel/README.md).
- **E-mail:** Microsoft 365 — `smtp.office365.com:587` TLS; `MAIL_USERNAME` = caixa no M365; password no secret `slc_sousalima_smtp_password`.
