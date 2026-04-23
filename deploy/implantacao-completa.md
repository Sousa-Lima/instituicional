# Documento de Implantação — SLC (Docker Swarm)

**Stack:** `slc` · **Ficheiro:** `deploy/slc.yaml` · **Traefik:** `le` (ACME/Let's Encrypt)

Este documento cobre o ciclo completo: primeiro deploy em servidor novo, atualização, integração LinkedIn OAuth e operações recorrentes.

---

## Índice

1. [Arquitetura](#1-arquitetura)
2. [Domínios e DNS](#2-domínios-e-dns)
3. [Pré-requisitos no servidor](#3-pré-requisitos-no-servidor)
4. [Secrets Swarm](#4-secrets-swarm)
5. [Configurações não secretas (`slc.yaml`)](#5-configurações-não-secretas-slcyaml)
6. [Imagem Laravel e dependências](#6-imagem-laravel-e-dependências)
7. [Build do frontend (Astro)](#7-build-do-frontend-astro)
8. [Deploy e verificação](#8-deploy-e-verificação)
9. [Integração LinkedIn OAuth](#9-integração-linkedin-oauth)
10. [Publicação de posts (fila)](#10-publicação-de-posts-fila)
11. [Atualizar a stack](#11-atualizar-a-stack)
12. [Rotação de secrets](#12-rotação-de-secrets)
13. [Operações recorrentes](#13-operações-recorrentes)
14. [Rollback](#14-rollback)
15. [Checklist rápida](#15-checklist-rápida)

---

## 1. Arquitetura

```
Internet
   │  (80/443)
   ▼
Traefik (TLS ACME "le", entrypoints web/websecure)
   ├── sousalimaconsultoria.com.br            → slc_frontend   (nginx + Astro SSG)
   ├── www.sousalimaconsultoria.com.br        → 301 → apex
   ├── static.sousalimaconsultoria.com.br     → slc_static     (nginx, storage/app/public)
   └── api.sousalimaconsultoria.com.br        → slc_api_nginx  (nginx FastCGI → slc_app:9000)
                                                                 │
                                              ┌──────────────────┴──────────────────┐
                                              │          rede interna (overlay)      │
                                              │  slc_postgres  slc_redis             │
                                              │  slc_app (PHP-FPM 8.4)               │
                                              │  slc_queue (queue:work)              │
                                              │  slc_scheduler (schedule:work)       │
                                              └─────────────────────────────────────┘
```

| Serviço Swarm | Imagem | Réplicas | Função |
|---|---|---|---|
| `app` | `eolimabr/php8.4-sousalima-multitenant:latest` | 1 | Laravel API + Filament (PHP-FPM :9000) |
| `api_nginx` | `nginx:1.25-alpine` | 1 | FastCGI proxy → `slc_app:9000` |
| `queue` | idem `app` | 1 | `php artisan queue:work --tries=3 --timeout=90` |
| `scheduler` | idem `app` | 1 | `php artisan schedule:work` |
| `frontend` | `nginx:1.25-alpine` | 3 | Site estático Astro (bind `frontend/dist`) |
| `static` | `nginx:1.25-alpine` | 1 | Assets públicos Laravel (`storage/app/public`) |
| `postgres` | `postgres:16-alpine` | 1 | Banco de dados (bind `/srv/sistemas/slc/data/postgres`) |
| `redis` | `redis:7-alpine` | 1 | Cache / sessão / filas (AOF bind `/srv/sistemas/slc/data/redis`) |

**PHP-FPM** corre como `www-data` **UID/GID 82** (Alpine). O Traefik aplica headers HSTS e redirect www→apex.

---

## 2. Domínios e DNS

| Hostname | Tipo DNS | Destino |
|---|---|---|
| `sousalimaconsultoria.com.br` | A | IP público do servidor |
| `www.sousalimaconsultoria.com.br` | A | IP público do servidor |
| `static.sousalimaconsultoria.com.br` | A | IP público do servidor |
| `api.sousalimaconsultoria.com.br` | A | IP público do servidor |

Todos os registos devem apontar para o mesmo IP que recebe 80/443 (nó Traefik). TTL baixo (300 s) durante a primeira implantação; aumentar depois.

---

## 3. Pré-requisitos no servidor

### 3.1 Docker Swarm iniciado

```bash
docker info | grep -i swarm   # deve mostrar "Swarm: active"
# Se necessário:
docker swarm init
```

### 3.2 Rede overlay partilhada com Traefik

```bash
docker network create --driver overlay traefik-public
# (ignorar erro se a rede já existir)
```

### 3.3 Traefik em execução

O Traefik deve estar na rede `traefik-public` e expor:
- Entrypoint `web` (80)
- Entrypoint `websecure` (443)
- Resolver ACME chamado **`le`** (certificresolver usado em todas as labels do `slc.yaml`)

Exemplo de deploy do proxy (adaptar ao seu `proxy.yml`):

```bash
docker stack deploy -c /srv/swarm/proxy.yml proxy
```

### 3.4 Diretórios de persistência no host

Criar **antes do primeiro deploy** (no nó que irá executar os serviços com bind mount):

```bash
sudo mkdir -p /srv/sistemas/slc/data/storage/app/public \
             /srv/sistemas/slc/data/postgres \
             /srv/sistemas/slc/data/redis
```

Permissões por serviço:

```bash
# PHP-FPM (uid/gid 82 em Alpine)
docker run --rm -v /srv/sistemas/slc/admin:/app alpine \
  chown -R 82:82 /app/storage /app/bootstrap/cache
docker run --rm -v /srv/sistemas/slc/data/storage/app:/app alpine \
  chown -R 82:82 /app

# PostgreSQL 16 Alpine (uid 70)
sudo chown -R 70:70 /srv/sistemas/slc/data/postgres

# Redis 7 Alpine (uid 999)
sudo chown -R 999:1000 /srv/sistemas/slc/data/redis
```

> **Atenção:** repetir o `chown 82:82` sempre que `composer install` ou `php artisan` forem executados como root no host, pois eles podem recriar ficheiros com o uid de root em `storage/`.

### 3.5 Clone do repositório no servidor

```bash
git clone <url-do-repositório> /srv/sistemas/slc
cd /srv/sistemas/slc
```

Para atualizar:

```bash
cd /srv/sistemas/slc && git pull
```

### 3.6 Dependências PHP (sem PHP local)

```bash
docker run --rm \
  -v /srv/sistemas/slc/admin:/app \
  -w /app \
  composer:latest \
  composer install --no-dev --optimize-autoloader
```

### 3.7 `acme.json` para o Traefik

```bash
sudo mkdir -p /srv/swarm/letsencrypt
sudo touch /srv/swarm/letsencrypt/acme.json
sudo chmod 600 /srv/swarm/letsencrypt/acme.json
```

---

## 4. Secrets Swarm

Os secrets têm o prefixo **`slc_sousalima_`** para não colidir com outras stacks no mesmo servidor. São referenciados por `slc.yaml` — não alterar os nomes sem editar o ficheiro.

### 4.1 Tabela completa de secrets

| Nome do secret | Conteúdo | Estado |
|---|---|---|
| `slc_sousalima_db_password` | Password PostgreSQL do `slc_user` | Obrigatório |
| `slc_sousalima_app_key` | `APP_KEY` Laravel (`base64:...`) | Obrigatório |
| `slc_sousalima_jwt_secret` | String aleatória para JWT (≥48 chars) | Obrigatório |
| `slc_sousalima_smtp_password` | Password caixa Microsoft 365 SMTP | Obrigatório |
| `slc_sousalima_linkedin_client_id` | Client ID do app LinkedIn Developer | Obrigatório (LinkedIn) |
| `slc_sousalima_linkedin_client_secret` | Client Secret do app LinkedIn Developer | Obrigatório (LinkedIn) |
| `slc_sousalima_linkedin_token` | Access Token OAuth LinkedIn (pessoa) | Obrigatório (LinkedIn) |
| `slc_sousalima_instagram_token` | Access Token Instagram (desativado no YAML atual) | Inativo |

### 4.2 Criar cada secret

#### `slc_sousalima_db_password`

```bash
echo -n 'SUA_PASSWORD_POSTGRESQL' | docker secret create slc_sousalima_db_password -
```

#### `slc_sousalima_app_key` — APP_KEY Laravel

Gerar com Artisan (preferir este método — produz a chave no formato correto):

```bash
docker run --rm \
  -v /srv/sistemas/slc/admin:/var/www/html \
  -w /var/www/html \
  eolimabr/php8.4-sousalima-multitenant:latest \
  php artisan key:generate --show
```

O comando imprime `base64:XXXXXXXXXX`. Criar o secret sem `\n` no final:

```bash
echo -n 'base64:COLE_AQUI_A_CHAVE_GERADA' | docker secret create slc_sousalima_app_key -
```

#### `slc_sousalima_jwt_secret`

```bash
openssl rand -base64 48 | tr -d '\n' | docker secret create slc_sousalima_jwt_secret -
```

#### `slc_sousalima_smtp_password` — Microsoft 365

```bash
echo -n 'SUA_PASSWORD_SMTP_O365' | docker secret create slc_sousalima_smtp_password -
```

> O SMTP usa `smtp.office365.com:587 TLS`. Se a conta tiver MFA, criar uma **password de aplicação** no portal Microsoft 365 e usá-la aqui.

#### `slc_sousalima_linkedin_client_id` e `slc_sousalima_linkedin_client_secret`

Valores obtidos no [LinkedIn Developer Portal](https://www.linkedin.com/developers/apps):

```bash
echo -n 'CLIENT_ID_DO_APP_LINKEDIN' | docker secret create slc_sousalima_linkedin_client_id -
echo -n 'CLIENT_SECRET_DO_APP_LINKEDIN' | docker secret create slc_sousalima_linkedin_client_secret -
```

#### `slc_sousalima_linkedin_token` — Access Token OAuth

Este token é obtido **após** o primeiro deploy e execução do fluxo OAuth (ver §9). Na primeira vez, criar um placeholder para o deploy não falhar:

```bash
echo -n 'placeholder' | docker secret create slc_sousalima_linkedin_token -
```

Após obter o token real (§9.3), substituir:

```bash
docker secret rm slc_sousalima_linkedin_token
echo -n 'TOKEN_REAL_OBTIDO' | docker secret create slc_sousalima_linkedin_token -
```

### 4.3 Verificar secrets criados

```bash
docker secret ls | grep slc_sousalima
```

Deve listar todos os secrets ativos, sem mostrar os valores.

---

## 5. Configurações não secretas (`slc.yaml`)

Estas variáveis estão em texto claro no `deploy/slc.yaml` (secção `x-app-defaults.environment`). Editar conforme o ambiente:

| Variável | Valor atual | Observação |
|---|---|---|
| `APP_URL` | `https://api.sousalimaconsultoria.com.br` | URL base da API/admin |
| `DB_DATABASE` | `slc_admin` | Nome do banco |
| `DB_USERNAME` | `slc_user` | Usuário PostgreSQL |
| `MAIL_USERNAME` | `everton.lima@sousalimaconsultoria.com.br` | Caixa autenticada no SMTP M365 |
| `MAIL_FROM_ADDRESS` | `noreply@sousalimaconsultoria.com.br` | Remetente exibido |
| `MAIL_FROM_NAME` | `Sousa Lima Consultoria` | Nome do remetente |
| `FILAMENT_ADMIN_EMAILS` | `admin@sousalimaconsultoria.com.br,eolimabr@gmail.com` | E-mails com acesso ao Filament |
| `LINKEDIN_AUTHOR_URN` | `urn:li:person:SEU_PERSON_ID` | **Substituir pelo ID real** (ver §9.4) |
| `LINKEDIN_SCOPES` | `w_member_social,r_liteprofile` | Escopos OAuth LinkedIn |
| `TZ` / `APP_TIMEZONE` | `America/Sao_Paulo` | Fuso horário |
| `extra_hosts` | `api.sousalimaconsultoria.com.br:172.17.0.1` | Resolução interna do host; verificar o IP com `docker network inspect bridge` |

> `LINKEDIN_AUTHOR_URN` começa como placeholder. Após o passo §9.4, atualizar para `urn:li:person:ID_REAL` e fazer novo deploy.

---

## 6. Imagem Laravel e dependências

A imagem usada é:

```
eolimabr/php8.4-sousalima-multitenant:latest
```

- PHP 8.4 Alpine, PHP-FPM na porta 9000, `www-data` = **uid/gid 82**.
- O código Laravel (`admin/`) é montado via bind mount em `/var/www/html` — não está embutido na imagem.
- Preferir **tag fixa** (ex.: `eolimabr/php8.4-sousalima-multitenant:1.2.3`) em produção estável.

### Instalar dependências Composer no servidor

```bash
docker run --rm \
  -v /srv/sistemas/slc/admin:/app \
  -w /app \
  composer:latest \
  composer install --no-dev --optimize-autoloader

# Restaurar permissões após composer
docker run --rm -v /srv/sistemas/slc/admin:/app alpine chown -R 82:82 /app/storage /app/bootstrap/cache
```

### Migrations e seeders (via serviço app após deploy)

```bash
docker exec -it $(docker ps -q -f name=slc_app) php artisan migrate --force
# Seeders apenas se necessário (dados de referência, não em produção com dados reais):
# docker exec -it $(docker ps -q -f name=slc_app) php artisan db:seed --force
```

---

## 7. Build do frontend (Astro)

O serviço `frontend` monta **`/srv/sistemas/slc/frontend/dist`** em `/usr/share/nginx/html` (read-only). O conteúdo deve ser gerado antes do deploy.

### No servidor (com Node.js instalado)

```bash
cd /srv/sistemas/slc/frontend
npm ci && npm run build
```

### Sem Node.js no servidor — container temporário

```bash
cd /srv/sistemas/slc/frontend
docker run --rm -v "$PWD":/app -w /app node:22-alpine sh -c "npm ci && npm run build"
```

### Build externo → rsync para o servidor

```bash
# Na máquina de desenvolvimento:
npm run build
rsync -avz --delete ./dist/ servidor:/srv/sistemas/slc/frontend/dist/
```

### Verificar build

```bash
test -f /srv/sistemas/slc/frontend/dist/index.html && echo "OK" || echo "FALTANDO index.html"
```

Se `index.html` não existir após o deploy, o `frontend` retorna 404/403 para o apex.

---

## 8. Deploy e verificação

### 8.1 Primeiro deploy (ou atualização de YAML)

```bash
cd /srv/sistemas/slc
docker stack deploy -c deploy/slc.yaml slc
```

O Swarm puxa as imagens e inicia os serviços. Aguardar ~60 s para healthchecks estabilizarem.

### 8.2 Verificar estado da stack

```bash
# Estado resumido de todos os serviços
docker stack services slc

# Tarefas e histórico de restarts
docker stack ps slc --no-trunc

# Serviços específicos
docker service ps slc_app slc_queue slc_scheduler --no-trunc
```

Todos os serviços devem mostrar `Running` (não `Failed` ou `Shutdown`).

### 8.3 Logs em tempo real

```bash
# Laravel (erros e jobs)
docker service logs slc_app -f --tail 100
docker service logs slc_queue -f --tail 100

# Nginx
docker service logs slc_api_nginx -f --tail 50
docker service logs slc_frontend -f --tail 30

# Scheduler
docker service logs slc_scheduler -f --tail 50
```

Com `LOG_CHANNEL=stack` e `LOG_STACK=single,stderr` no YAML, os logs de nível `debug` vão tanto para `storage/logs/laravel.log` (bind mount) quanto para stderr do contentor (visível via `docker service logs`).

### 8.4 Endpoint de saúde

```bash
curl -I https://api.sousalimaconsultoria.com.br
# Esperado: HTTP/2 200 ou redirect do Filament para login
```

### 8.5 Migrations pós-deploy

```bash
docker exec -it $(docker ps -q -f name=slc_app) php artisan migrate --force
```

### 8.6 Cache de configuração (produção)

```bash
docker exec -it $(docker ps -q -f name=slc_app) php artisan config:cache
docker exec -it $(docker ps -q -f name=slc_app) php artisan route:cache
docker exec -it $(docker ps -q -f name=slc_app) php artisan view:cache
```

> Se o `.env` ou o `slc.yaml` mudar, é necessário reexecutar o cache ou reiniciar o serviço.

---

## 9. Integração LinkedIn OAuth

O admin inclui um fluxo OAuth 2.0 completo para obter o access token e o `person_id` do LinkedIn.

### 9.1 Configurar o app no LinkedIn Developer Portal

1. Aceder a [https://www.linkedin.com/developers/apps](https://www.linkedin.com/developers/apps).
2. Selecionar o app da Sousa Lima Consultoria.
3. Em **Auth** → **Authorized redirect URLs**, adicionar **exatamente**:
   ```
   https://api.sousalimaconsultoria.com.br/admin/integrations/linkedin/callback
   ```
4. Em **Products**, ativar **Share on LinkedIn** (concede `w_member_social`) e **Sign In with LinkedIn** (concede `r_liteprofile`).
5. Aguardar aprovação dos escopos (pode levar alguns minutos).

### 9.2 Garantir que `client_id` e `client_secret` estão nos secrets

```bash
docker secret ls | grep linkedin
# Devem existir:
# slc_sousalima_linkedin_client_id
# slc_sousalima_linkedin_client_secret
# slc_sousalima_linkedin_token   (pode ser placeholder neste passo)
```

Se ainda não criados, ver §4.2.

### 9.3 Executar o fluxo OAuth para obter o token

1. **Fazer login** no Filament admin: `https://api.sousalimaconsultoria.com.br/admin`
2. **Iniciar a autorização**: abrir no browser o url:
   ```
   https://api.sousalimaconsultoria.com.br/admin/integrations/linkedin/connect
   ```
3. O LinkedIn solicita permissão. Autorizar com a conta da Sousa Lima Consultoria.
4. O browser redireciona para o callback e exibe:
   - O **Access Token** e sua validade.
   - O comando `docker secret create` pronto para copiar.

5. Copiar o token exibido e criar/recriar o secret:

```bash
# Remover o placeholder (ou secret antigo)
docker secret rm slc_sousalima_linkedin_token

# Criar com o token real (colar exatamente como apareceu na tela)
echo -n 'TOKEN_EXIBIDO_NO_CALLBACK' | docker secret create slc_sousalima_linkedin_token -
```

### 9.4 Obter o `person_id` e atualizar o YAML

Com o token obtido no passo anterior:

```bash
curl -s "https://api.linkedin.com/v2/me?projection=(id)" \
  -H "Authorization: Bearer TOKEN_OBTIDO_NO_CALLBACK"
```

A resposta retorna:

```json
{"id": "XXXXXXXX"}
```

Editar [`deploy/slc.yaml`](slc.yaml) e substituir o placeholder pelo ID real:

```yaml
LINKEDIN_AUTHOR_URN: "urn:li:person:XXXXXXXX"
```

### 9.5 Redeployar para aplicar o URN e o token

```bash
cd /srv/sistemas/slc
docker stack deploy -c deploy/slc.yaml slc
docker service update --force slc_queue
docker service update --force slc_app
```

### 9.6 Verificar publicação

Agendar ou publicar um post no Filament admin e monitorar:

```bash
docker service logs slc_queue -f --tail 100
```

Uma publicação bem-sucedida termina com `Job processado` (ou similar) sem `RuntimeException`.

---

## 10. Publicação de posts (fila)

O job `PublishLinkedinPost` (e futuramente `PublishInstagramPost`) são despachados para a fila Redis e processados pelo serviço `queue`.

### Dependências obrigatórias para o LinkedIn funcionar

| Variável / Secret | Conteúdo esperado |
|---|---|
| `LINKEDIN_AUTHOR_URN` | `urn:li:person:ID_REAL` (não organização, não placeholder) |
| `slc_sousalima_linkedin_token` | Token OAuth válido com escopos `w_member_social,r_liteprofile` |
| `slc_sousalima_linkedin_client_id` | Client ID do app LinkedIn |
| `slc_sousalima_linkedin_client_secret` | Client Secret do app LinkedIn |

### Comportamento do job

- Lê a imagem de `Storage::disk()->get()` (leitura local — sem chamada HTTP que possa dar timeout).
- Faz upload para `/v2/assets?action=registerUpload`.
- Publica via `POST /v2/ugcPosts` com header `X-Restli-Protocol-Version: 2.0.0`.
- Se `author_urn` ou `token` estiverem em branco, lança `RuntimeException` imediatamente (sem chamar a API).
- Logs registados em `storage/logs/laravel.log` e stderr (nível `debug`).

---

## 11. Atualizar a stack

### Alterar variáveis ou réplicas no YAML

Editar `deploy/slc.yaml` e executar:

```bash
cd /srv/sistemas/slc
docker stack deploy -c deploy/slc.yaml slc
```

O Swarm aplica rolling update somente nos serviços cujas definições mudaram.

### Forçar reinício de um serviço (sem alterar YAML)

```bash
docker service update --force slc_app
docker service update --force slc_queue
docker service update --force slc_scheduler
```

Útil quando um secret foi recriado com o mesmo nome (o serviço precisa ser reiniciado para ler o novo valor).

### Atualizar código Laravel (sem alterar imagem)

O código está em bind mount (`/srv/sistemas/slc/admin`). Basta:

```bash
cd /srv/sistemas/slc && git pull

# Instalar dependências se o composer.json mudou
docker run --rm -v /srv/sistemas/slc/admin:/app -w /app composer:latest \
  composer install --no-dev --optimize-autoloader

# Restaurar permissões
docker run --rm -v /srv/sistemas/slc/admin:/app alpine chown -R 82:82 /app/storage /app/bootstrap/cache

# Reexecutar migrations se existirem novas
docker exec -it $(docker ps -q -f name=slc_app) php artisan migrate --force

# Limpar caches
docker exec -it $(docker ps -q -f name=slc_app) php artisan config:clear
docker exec -it $(docker ps -q -f name=slc_app) php artisan route:clear
docker exec -it $(docker ps -q -f name=slc_app) php artisan view:clear

# Reiniciar worker para pegar novas classes de jobs
docker service update --force slc_queue
```

### Republicar só o frontend (Astro)

```bash
cd /srv/sistemas/slc
./deploy/republish-frontend.sh
```

O script regenera o build e faz reload do serviço. Sem alterar o `slc.yaml`, não é necessário novo `docker stack deploy`.

---

## 12. Rotação de secrets

O Docker Swarm **não permite atualizar** um secret in-place. O fluxo correto é criar um novo secret com nome diferente, atualizar o YAML e remover o antigo:

```bash
# 1. Criar o novo secret
echo -n 'NOVO_VALOR' | docker secret create slc_sousalima_db_password_v2 -

# 2. Editar deploy/slc.yaml: substituir todas as ocorrências de
#    slc_sousalima_db_password por slc_sousalima_db_password_v2

# 3. Redeployar
docker stack deploy -c deploy/slc.yaml slc

# 4. Quando estável, remover o antigo
docker secret rm slc_sousalima_db_password
```

**Exceção — LinkedIn token:** o token expira periodicamente. Para renovar:

```bash
# Repetir o fluxo OAuth (§9.2 e §9.3) para obter novo token
docker secret rm slc_sousalima_linkedin_token
echo -n 'NOVO_TOKEN' | docker secret create slc_sousalima_linkedin_token -
docker service update --force slc_queue
docker service update --force slc_app
```

---

## 13. Operações recorrentes

### Ver estado geral

```bash
docker stack services slc
docker stack ps slc
```

### Entrar no container app (diagnóstico)

```bash
docker exec -it $(docker ps -q -f name=slc_app) sh
```

### Logs Laravel em arquivo (no host)

```bash
tail -f /srv/sistemas/slc/admin/storage/logs/laravel.log
```

### Logs do worker de filas

```bash
docker service logs slc_queue -f --tail 200
```

### Verificar filas pendentes no Redis

```bash
docker exec -it $(docker ps -q -f name=slc_redis) redis-cli llen queues:default
```

### Limpar cache de configuração (após mudança de `.env` / YAML)

```bash
docker exec -it $(docker ps -q -f name=slc_app) php artisan config:clear
docker exec -it $(docker ps -q -f name=slc_app) php artisan cache:clear
```

### Backup do banco (dump PostgreSQL)

```bash
docker exec $(docker ps -q -f name=slc_postgres) \
  pg_dump -U slc_user slc_admin \
  > /srv/backups/slc_admin_$(date +%Y%m%d_%H%M%S).sql
```

---

## 14. Rollback

### Rollback de serviço específico (imagem anterior)

```bash
docker service rollback slc_app
docker service rollback slc_queue
```

### Rollback de YAML (via Git)

```bash
cd /srv/sistemas/slc
git log --oneline deploy/slc.yaml   # identificar commit anterior
git checkout <commit-hash> -- deploy/slc.yaml
docker stack deploy -c deploy/slc.yaml slc
```

### Remover a stack completamente (último recurso)

> **Destrutivo para serviços** — os dados de `data/postgres` e `data/redis` são preservados nos bind mounts do host.

```bash
docker stack rm slc
```

Para resubir: garantir secrets, permissões e diretórios (§3) e repetir §8.1.

---

## 15. Checklist rápida

### Antes do primeiro deploy

- [ ] Docker Swarm iniciado (`docker info | grep Swarm`)
- [ ] Rede `traefik-public` criada
- [ ] Traefik em execução com resolver `le`
- [ ] DNS apontado para o servidor
- [ ] Diretórios `data/{storage,postgres,redis}` criados com permissões corretas
- [ ] `frontend/dist/index.html` gerado (`npm run build`)
- [ ] Todos os secrets criados (`docker secret ls | grep slc_sousalima`)
- [ ] `LINKEDIN_CLIENT_ID` e `LINKEDIN_CLIENT_SECRET` nos secrets
- [ ] `slc_sousalima_linkedin_token` criado (placeholder OK neste momento)
- [ ] `LINKEDIN_AUTHOR_URN` no YAML (placeholder OK neste momento)
- [ ] `MAIL_USERNAME` correto no YAML
- [ ] `FILAMENT_ADMIN_EMAILS` com e-mails corretos
- [ ] `extra_hosts` com o IP correto do host (`docker network inspect bridge | grep Gateway`)

### Após deploy inicial

- [ ] `docker stack services slc` — todos `1/1` (ou réplicas esperadas)
- [ ] `https://api.sousalimaconsultoria.com.br` responde 200/redirect login
- [ ] `https://sousalimaconsultoria.com.br` mostra o site Astro
- [ ] Migrations executadas sem erro
- [ ] Concluir fluxo OAuth LinkedIn (§9) para substituir placeholders
- [ ] Redeployar com `LINKEDIN_AUTHOR_URN` real e token real

### Antes de cada deploy de atualização

- [ ] Secrets novos criados se necessário
- [ ] Permissões de `storage/` corretas (se `composer install` foi executado)
- [ ] `frontend/dist` atualizado se houve mudança no frontend
- [ ] `docker stack deploy` executado
- [ ] Logs verificados (`docker service logs slc_app --tail 50`)

---

*Documentação gerada em 04/04/2026. Ficheiro de stack: [`deploy/slc.yaml`](slc.yaml). Guia operacional complementar: [`guia-deploy-e-atualizacao.md`](guia-deploy-e-atualizacao.md).*
