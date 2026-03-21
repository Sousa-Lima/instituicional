# Procedimento de deploy — produção (SLC)

Runbook para publicar o site estático (Astro), ficheiros públicos Laravel, API/admin (Laravel) e worker no **Docker Swarm**, atrás de **Traefik** com **Let’s Encrypt**. Alinhado a [base-publicacao.md](base-publicacao.md), [stack-tecnico-slc.md](../definicoes/stack-tecnico-slc.md), [integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md) e [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md).

**Stack da aplicação (este repositório):** [`deploy/slc.yaml`](../../deploy/slc.yaml) — stack nomeada **`slc`**.

**Proxy Traefik:** em geral vive noutro caminho no servidor (ex.: `/srv/swarm/proxy.yml`); tem de expor os entrypoints `web` / `websecure` e o resolver ACME **`le`**, igual às labels do `slc.yaml`.

---

## 1. Arquitetura resumida

| Camada | Função |
|--------|--------|
| **Traefik** | TLS (ACME), HTTP→HTTPS, roteamento por `Host`. |
| **frontend** | nginx com artefactos Astro em bind mount (SSG). |
| **static** | nginx a servir `storage/app/public` (uploads / públicos Laravel). |
| **app** | Laravel (API + Filament/Inertia). |
| **queue** | `php artisan queue:work`. |
| **PostgreSQL / Redis** | Definidos em `DB_HOST` / `REDIS_HOST` — **fora** deste YAML (outra stack ou serviços já existentes na rede `internal`). |

---

## 2. Domínios e DNS

Conforme [`deploy/slc.yaml`](../../deploy/slc.yaml); ajustar se os hosts mudarem.

| Hostname | Serviço Swarm |
|----------|----------------|
| `sousalimaconsultoria.com.br`, `www.sousalimaconsultoria.com.br` | `frontend` |
| `static.sousalimaconsultoria.com.br` | `static` |
| `api.sousalimaconsultoria.com.br` | `app` |

Registos **A** (e **AAAA** se aplicável) devem apontar para o IP que recebe **80/tcp** e **443/tcp** (normalmente o nó manager onde o Traefik publica portas em modo `host`, ou load balancer à frente).

---

## 3. Pré-requisitos no servidor

### 3.1 Docker Swarm

```bash
docker info | grep -i swarm   # Swarm: active
# Se necessário: docker swarm init
```

### 3.2 Rede partilhada com o Traefik

```bash
docker network create --driver overlay traefik-public
```

(Ignorar erro se a rede já existir.)

### 3.3 Diretórios no host (persistência)

Bind mounts — **não** usar volumes nomeados Docker para estes dados. Criar no **mesmo nó** (ou em **todos** os nós que executarem réplicas que usem estes paths; ver §8).

```bash
sudo mkdir -p /srv/sistemas/slc/data/storage/app/public
sudo mkdir -p /srv/sistemas/slc/data/frontend/dist
```

Permissões: alinhar o dono ao utilizador PHP dentro da imagem (ex.: `www-data` ou UID exposto pela imagem `eolimabr/php8.4-sousalima-multitenant`).

```bash
# Exemplo (confirmar UID/GID no Dockerfile ou com: docker run --rm ... id)
sudo chown -R www-data:www-data /srv/sistemas/slc/data/storage
```

O diretório `frontend/dist` pode ser propriedade de root ou do utilizador de deploy, desde que o contentor nginx consiga ler (normalmente modo `read_only: true` e `755`/`644`).

### 3.4 Certificados ACME (Traefik)

No host do Traefik (ex.: `/srv/swarm/letsencrypt`):

```bash
sudo mkdir -p /srv/swarm/letsencrypt
sudo touch /srv/swarm/letsencrypt/acme.json
sudo chmod 600 /srv/swarm/letsencrypt/acme.json
```

### 3.5 Secrets do Swarm (Laravel)

Criar **uma vez** (valores não versionados):

```bash
echo -n 'valor-seguro-da-base' | docker secret create slc_db_password -
echo -n 'base64:...' | docker secret create slc_app_key -
echo -n 'segredo-jwt' | docker secret create slc_jwt_secret -
```

O Laravel deve ler `APP_KEY` e palavra-passe da base a partir dos ficheiros montados em `/run/secrets/` conforme o teu `Dockerfile`/entrypoint (o `slc.yaml` mapeia os alvos `db_password`, `app_key`, `jwt_secret`). Ajustar nomes dos secrets se o código esperar outros paths.

### 3.6 PostgreSQL e Redis

A stack `slc` referencia `DB_HOST=postgres` e `REDIS_HOST=redis`. É necessário:

- Serviços com esses **nomes DNS** na rede **`internal`** (overlay comum), **ou**
- Alterar `DB_HOST` / `REDIS_HOST` no YAML (ou via env em evolução futura) para apontar para serviços reais.

Garantir que a base `slc_admin` e o utilizador `slc_user` existem e que a password coincide com `slc_db_password`.

---

## 4. Imagem da aplicação Laravel

O `slc.yaml` usa:

`eolimabr/php8.4-sousalima-multitenant:latest`

Fluxo típico:

1. Build da imagem a partir do repositório **admin (Laravel)** no CI ou localmente.
2. `docker push` para o registo privado (Docker Hub ou outro).
3. Em cada deploy, `docker stack deploy` puxa a tag desejada; **fixar tag** (ex. `:1.2.3`) em produção em vez de `latest` quando possível.

---

## 5. Build do site (Astro) e publicação no host

O frontend não embute o build na imagem nginx: o conteúdo vem de:

`/srv/sistemas/slc/data/frontend/dist`

1. No repositório **frontend (Astro)**, com variáveis de ambiente do build (URL da API, token de leitura se aplicável — ver [contrato-api-build-time-slc.md](contrato-api-build-time-slc.md)):

   ```bash
   npm ci && npm run build
   ```

2. Copiar o conteúdo de `dist/` (ou o diretório de output configurado no `astro.config`) para o servidor:

   ```bash
   rsync -avz --delete ./dist/ servidor:/srv/sistemas/slc/data/frontend/dist/
   ```

3. **Webhook / CI:** após publicar conteúdo no Laravel, o rebuild pode ser automático — [integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md).

---

## 6. Subir ou atualizar stacks

### 6.1 Traefik (proxy)

Exemplo (caminho ilustrativo):

```bash
docker stack deploy -c /srv/swarm/proxy.yml proxy
```

Confirmar que o resolver ACME se chama **`le`** (ou alterar todas as labels `certresolver=` no `slc.yaml` para o nome usado no Traefik).

### 6.2 Aplicação SLC

A partir da raiz deste repositório no servidor (ou com caminho absoluto ao ficheiro):

```bash
cd /srv/sistemas/slc
docker stack deploy -c deploy/slc.yaml slc
```

O Swarm faz **rolling update** dos serviços alterados; não é obrigatório remover a stack antes.

### 6.3 Verificação rápida

```bash
docker stack services slc
docker service ps slc_app --no-trunc
docker service logs slc_app -f
docker service logs slc_frontend -f
```

Testar HTTPS:

- `https://sousalimaconsultoria.com.br`
- `https://api.sousalimaconsultoria.com.br`
- `https://static.sousalimaconsultoria.com.br` (conforme rotas nginx e ficheiros em `storage/app/public`)

---

## 7. Migrações e caches (Laravel)

Após deploy de nova imagem, consoante a prática do projeto:

```bash
docker exec -it $(docker ps -q -f name=slc_app) php artisan migrate --force
docker exec -it $(docker ps -q -f name=slc_app) php artisan config:cache
docker exec -it $(docker ps -q -f name=slc_app) php artisan route:cache
```

(Ajustar o filtro do nome do contentor ao nome real retornado por `docker service ps slc_app`.)

---

## 8. Swarm, réplicas e bind mounts

Os bind mounts apontam para caminhos **locais do nó**. Se `frontend`, `app` ou `static` tiverem **réplicas > 1** em **nós diferentes**, cada nó precisa dos **mesmos** dados em `/srv/sistemas/slc/data/...` **ou** usar **NFS**/storage partilhado **ou** reduzir réplicas para serviços que dependem desses paths.

---

## 9. Rollback

- **Stack:** voltar a uma versão anterior do ficheiro `deploy/slc.yaml` (ou imagem com tag anterior) e repetir `docker stack deploy`.
- **Frontend:** restaurar cópia anterior de `data/frontend/dist` a partir de backup.
- **Base de dados:** política de backup/restauro fora do âmbito deste runbook (definir em [base-publicacao.md](base-publicacao.md) / operações).

---

## 10. Segurança e compliance

- Cabeçalhos (HSTS, CSP, etc.): [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md).
- Formulário de leads e API runtime: [formulario-contato-lead-slc.md](formulario-contato-lead-slc.md).
- LGPD / cookies: [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md).

---

## 11. Documentos relacionados

| Documento | Conteúdo |
|-----------|----------|
| [`deploy/slc.yaml`](../../deploy/slc.yaml) | Serviços, labels Traefik, bind mounts, secrets. |
| [`deploy/README.md`](../../deploy/README.md) | Entrada rápida e link para este procedimento. |
| [base-publicacao.md](base-publicacao.md) | Visão geral e checklist curto. |
| [dominios-e-ambiente.md](../definicoes/dominios-e-ambiente.md) | Marca e variáveis em nível conceitual. |

---

## 12. Histórico de alterações ao procedimento

Registar aqui ou em commit/PR quando mudarem hosts, paths no servidor ou nomes de stack.

| Data | Alteração |
|------|-----------|
| (preencher) | Primeira versão deste runbook. |
