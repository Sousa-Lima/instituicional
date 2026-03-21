# Site institucional — Sousa Lima Consultoria (Astro)

Frontend **SSG** com **Astro 6**, **Tailwind CSS v4** e **React** (ilhas). Consome a API do repositório `admin/` (Laravel) — ver `deploy/laravel/README.md` e `docs/conhecimento/contrato-api-build-time-slc.md`.

## Requisitos

- **Node.js** ≥ 22.12 **ou** apenas **Docker** (recomendado no servidor de desenvolvimento sem Node local).

## Sem Node local — Docker

Na pasta `frontend/`:

```bash
cp .env.example .env   # ajustar PUBLIC_API_BASE_URL

./scripts/docker-node.sh install
./scripts/docker-node.sh run dev      # http://localhost:4321
./scripts/docker-node.sh run build
./scripts/docker-node.sh run preview  # http://localhost:4321
```

- Imagem predefinida: `node:22-bookworm-slim`. Para outra: `NODE_IMAGE=node:22-alpine ./scripts/docker-node.sh run build`
- Se existir `.env`, é montado com `--env-file` no contentor (útil para `PUBLIC_*`).
- `dev` / `preview` expõem a porta **4321** no host.

## Com Node instalado

```bash
cp .env.example .env
npm install
npm run dev
npm run build
npm run preview
```

## Variáveis de ambiente

| Variável | Descrição |
|----------|-----------|
| `PUBLIC_API_BASE_URL` | Base da API (ex. `https://api.sousalimaconsultoria.com.br`), **sem** `/` final. |

Segredos (`API_READ_TOKEN`, etc.) **não** vão no `.env` do site público: usam-se apenas no **CI** ou num **server** que faça o fetch no build.

### Porque ainda aparecia “não definido” ou valor errado?

No Astro (Vite), variáveis **`PUBLIC_*` não são lidas do `.env` em runtime** no site estático: são **substituídas no momento do `astro build`**. Ou seja:

1. **`npm run dev`** — o Vite carrega o `.env` da pasta `frontend/`; vês o valor certo no browser.
2. **`npm run build`** — o valor de `PUBLIC_API_BASE_URL` no **ambiente desse comando** é que fica “gravado” no HTML/JS em `dist/`. Se o build correr **sem** essa variável (CI sem secret, Docker sem `--env-file`, `.env` noutro caminho), o resultado publicado continua com **string vazia**, mesmo que cries o `.env` depois.
3. **`npm run preview`** — serve o `dist/` já gerado; **não** relê o `.env`. Só muda após um **novo build** com o env correcto.
4. **`./scripts/docker-node.sh run build`** — só passa `--env-file` se existir **`frontend/.env`**. Sem ficheiro ou com nome errado, o build sai sem `PUBLIC_*`.

**Resumo:** depois de alterar `.env`, volta a correr **`run build`** (e em produção, publicar o novo `dist/`). Em CI, define `PUBLIC_API_BASE_URL` nas variáveis do pipeline ou num ficheiro env injectado nesse job.

## Estrutura (inicial)

- `scripts/docker-node.sh` — npm via Docker.
- `src/layouts/BaseLayout.astro` — layout global, fontes, meta.
- `src/pages/` — rotas (file-based).
- `src/lib/api.ts` — helpers de URL da API.
- `src/styles/global.css` — Tailwind + tokens de marca SLC.

## Documentação do projeto

Stack e integração: `docs/definicoes/stack-tecnico-slc.md`, `docs/conhecimento/projeto-astro-laravel-institutional.md`.
