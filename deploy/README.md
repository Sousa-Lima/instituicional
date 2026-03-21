# Deploy — Docker Swarm (SLC)

- **Stack Compose:** [`slc.yaml`](slc.yaml) — `docker stack deploy -c deploy/slc.yaml slc`
- **Procedimento completo (DNS, secrets, Traefik, build Astro, verificação, rollback):** [docs/conhecimento/procedimento-deploy-producao-slc.md](../docs/conhecimento/procedimento-deploy-producao-slc.md)

Persistência em disco no host: `/srv/sistemas/slc/data/` (ver comentários no topo de `slc.yaml`).
