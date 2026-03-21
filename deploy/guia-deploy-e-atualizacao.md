# Guia — definir variáveis, secrets e atualizar a stack `slc`

Passo a passo para **primeiro deploy** e para **atualizações** posteriores no Docker Swarm. Complementa [procedimento-deploy-producao-slc.md](../docs/conhecimento/procedimento-deploy-producao-slc.md) com foco operacional.

**Ficheiro da stack:** [`slc.yaml`](slc.yaml)  
**Nome da stack:** `slc`

---

## Visão geral da ordem

| Ordem | O quê |
|-------|--------|
| 1 | Infra mínima (Swarm, rede `traefik-public`, Traefik com `le`) |
| 2 | Diretórios em `/srv/sistemas/slc/data/` no host |
| 3 | **Secrets** Swarm (`slc_sousalima_*`) — valores sensíveis |
| 4 | Ajustar **variáveis não secretas** no `slc.yaml` (ou ficheiro por cima) |
| 5 | `docker stack deploy` |
| 6 | Para atualizar: alterar YAML e/ou imagem e/ou artefactos e voltar ao passo 5 |

---

## 1. Pré-requisitos (uma vez)

```bash
docker info | grep -i Swarm   # deve indicar Swarm: active
docker network create --driver overlay traefik-public   # ignorar erro se já existir
```

O proxy Traefik deve estar a correr com resolver ACME nomeado **`le`** (igual às labels do `slc.yaml`).

---

## 2. Diretórios no servidor (persistência local)

Executar no **nó** onde os bind mounts existem (e em todos os nós se houver réplicas noutros nós):

```bash
sudo mkdir -p /srv/sistemas/slc/data/storage/app/public \
             /srv/sistemas/slc/data/frontend/dist \
             /srv/sistemas/slc/data/postgres \
             /srv/sistemas/slc/data/redis

sudo chown -R www-data:www-data /srv/sistemas/slc/data/storage
sudo chown -R 70:70 /srv/sistemas/slc/data/postgres
sudo chown -R 999:1000 /srv/sistemas/slc/data/redis
```

(Confirmar UID do PHP na tua imagem; ajustar `www-data` se necessário.)

---

## 3. Definir os **secrets** Swarm

Os nomes são **fixos** no [`slc.yaml`](slc.yaml) (secção `secrets:` no final). Não podem ser alterados no YAML sem editar o ficheiro.

| Nome do secret | Conteúdo típico |
|----------------|-----------------|
| `slc_sousalima_db_password` | Password do utilizador PostgreSQL (`slc_user`) |
| `slc_sousalima_app_key` | `APP_KEY` Laravel (ex.: `base64:...`) |
| `slc_sousalima_jwt_secret` | Segredo JWT (se a app usar) |
| `slc_sousalima_smtp_password` | Password da caixa Microsoft 365 (SMTP) |

### Criar a partir da linha de comandos (exemplos genéricos)

```bash
echo -n 'Slc@pgsql@2026' | docker secret create slc_sousalima_db_password -
echo -n 'Comum@2026' | docker secret create slc_sousalima_smtp_password -
```

Para **APP_KEY** e **JWT**, preferir os métodos abaixo (valores gerados de forma correta).

### `slc_sousalima_app_key` — `APP_KEY` Laravel

Gere a chave no **ambiente de desenvolvimento** (ou num contentor temporário com o mesmo projeto Laravel):

```bash
php artisan key:generate --show
```

O comando imprime uma linha no formato `base64:...`. **Sem** nova linha no final, crie o secret no nó Swarm (manager):

```bash
echo -n "base64:COLE_AQUI_A_CHAVE_GERADA" | docker secret create slc_sousalima_app_key -
```

Substitua `base64:COLE_AQUI_A_CHAVE_GERADA` pelo valor completo que o Artisan mostrou.

### `slc_sousalima_jwt_secret` — segredo JWT (string aleatória)

Gere uma string aleatória (ex.: 48 bytes em Base64 ≈ 64 caracteres úteis) e crie o secret **numa única linha** (sem `\n` no fim):

```bash
openssl rand -base64 48 | tr -d '\n' | docker secret create slc_sousalima_jwt_secret -
```

### Criar a partir de ficheiros (evita histórico de shell)

```bash
docker secret create slc_sousalima_db_password ./secrets/db.txt
docker secret create slc_sousalima_app_key ./secrets/app_key.txt
# etc.
```

**Não** versionar estes ficheiros no Git.

### Listar (sem ver valores)

```bash
docker secret ls | grep slc_sousalima
```

---

## 4. Variáveis **não secretas** no `slc.yaml`

Editar o repositório no servidor ou no teu PC e fazer `git pull` no servidor antes do deploy.

| Onde no YAML | Exemplos |
|--------------|----------|
| `x-app-defaults` → `environment` | `APP_URL`, `MAIL_USERNAME`, `MAIL_FROM_ADDRESS`, `DB_DATABASE`, … |
| `MAIL_USERNAME` | Endereço **completo** da caixa Microsoft 365 que autentica no SMTP |
| Imagem | `image: eolimabr/php8.4-sousalima-multitenant:TAG` — preferir **tag** fixa em produção |

O **SMTP** (host, porta, TLS) já está orientado a **Office 365** no YAML; a password vem **só** do secret `slc_sousalima_smtp_password`.

No **Laravel**, garantir que `config/database.php`, `config/app.php`, `config/mail.php` leem os ficheiros em `/run/secrets/slc_sousalima_*` conforme [`laravel/README.md`](laravel/README.md).

---

## 5. Primeiro deploy da stack

Na pasta do repositório (ou com caminho absoluto ao `slc.yaml`):

```bash
cd /srv/sistemas/slc
docker stack deploy -c deploy/slc.yaml slc
```

Verificar:

```bash
docker stack services slc
docker service ps slc_app --no-trunc
```

---

## 6. Atualizar a stack (dia a dia)

Sempre que alterares o **`deploy/slc.yaml`** (réplicas, variáveis, imagem, labels Traefik):

```bash
cd /srv/sistemas/slc
docker stack deploy -c deploy/slc.yaml slc
```

O Swarm faz **rolling update** dos serviços afetados; não é obrigatório remover a stack.

### Atualizar só a **imagem** Laravel

1. Alterar a linha `image:` em `x-app-defaults` (ex.: nova tag).
2. `docker stack deploy -c deploy/slc.yaml slc`
3. Opcional: `docker service update --force slc_app` se precisares de puxar a mesma tag outra vez (cache).

### Atualizar o **site estático** (Astro)

Copiar o build para o caminho do bind mount (o nginx `frontend` lê daí):

```bash
rsync -avz --delete ./dist/ servidor:/srv/sistemas/slc/data/frontend/dist/
```

Não precisa de `docker stack deploy` só por causa do HTML, a menos que mudes o YAML.

---

## 7. Alterar um **secret** (rotação)

No Docker Swarm **não** se atualiza um secret in-place. Fluxo habitual:

1. Criar um secret **novo** com outro nome (ex.: `slc_sousalima_db_password_v2`).
2. Editar o `slc.yaml`: em `secrets:` e em `source:` dos serviços, apontar para o novo nome.
3. `docker stack deploy -c deploy/slc.yaml slc`
4. Quando tudo estiver estável, `docker secret rm slc_sousalima_db_password` (o antigo), se já não for referenciado.

Ou manter os mesmos nomes e usar o truque de **remover o serviço da stack**, atualizar secret com mesmo nome (não é possível — `docker secret` não permite update). Por isso o padrão é **nome novo** ou **stack down** (não recomendado em produção). O mais seguro é sufixo `_v2` no nome do secret e atualizar o YAML.

---

## 8. Checklist rápido antes de cada deploy

- [ ] Secrets necessários criados (`docker secret ls`).
- [ ] Pastas `data/*` existem e permissões corretas.
- [ ] `MAIL_USERNAME` e remetentes corretos no YAML.
- [ ] Imagem Laravel com tag desejada.
- [ ] Traefik e DNS a apontar para o servidor.

---

## 9. Comandos úteis

```bash
# Estado da stack
docker stack services slc
docker stack ps slc

# Logs
docker service logs slc_app -f --tail 100
docker service logs slc_queue -f --tail 50

# Remover a stack (cuidado — para o serviço)
docker stack rm slc
```

---

## Documentos relacionados

| Ficheiro | Conteúdo |
|----------|----------|
| [`slc.yaml`](slc.yaml) | Definição completa dos serviços |
| [`laravel/README.md`](laravel/README.md) | Leitura de secrets no Laravel |
| [procedimento-deploy-producao-slc.md](../docs/conhecimento/procedimento-deploy-producao-slc.md) | Runbook alargado (DNS, Traefik, rollback) |
