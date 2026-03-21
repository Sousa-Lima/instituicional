# Deploy — Docker Swarm (SLC)

- **Stack Compose:** [`slc.yaml`](slc.yaml) — `docker stack deploy -c deploy/slc.yaml slc`
- **Passo a passo (secrets, variáveis no YAML, primeiro deploy, atualizar stack, rotação de secrets):** [`guia-deploy-e-atualizacao.md`](guia-deploy-e-atualizacao.md)
- **Procedimento completo (DNS, secrets, Traefik, build Astro, verificação, rollback):** [docs/conhecimento/procedimento-deploy-producao-slc.md](../docs/conhecimento/procedimento-deploy-producao-slc.md)

Persistência em disco no host: `/srv/sistemas/slc/data/` — inclui `storage/`, `frontend/dist`, **`postgres/`** (dados PG) e **`redis/`** (AOF). Ver comentários no topo de `slc.yaml`.

- **Scheduler:** serviço `scheduler` (`php artisan schedule:work`, 1 réplica).
- **Laravel:** snippets para `database.php` em [`laravel/README.md`](laravel/README.md).
- **Secrets Swarm:** prefixo `slc_sousalima_*` (`db_password`, `app_key`, `jwt_secret`, `smtp_password`) — ver `slc.yaml` e [laravel/README.md](laravel/README.md).
- **E-mail:** Microsoft 365 — `smtp.office365.com:587` TLS; `MAIL_USERNAME` = caixa no M365; password no secret `slc_sousalima_smtp_password`.
