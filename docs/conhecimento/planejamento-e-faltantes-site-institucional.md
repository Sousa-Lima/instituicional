# Planejamento e faltantes — site institucional SLC

Atualizado conforme decisões de produto e stack. Referências: [mapa-site-mvp-slc.md](mapa-site-mvp-slc.md), [stack-tecnico-slc.md](../definicoes/stack-tecnico-slc.md), [integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md), [projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md), [conteudo-juridico-semente.md](../definicoes/conteudo-juridico-semente.md).

---

## Decisões já tomadas

| Tema | Decisão |
|------|---------|
| **Conteúdo / jurídico** | Primeira versão guiada pelos **perfis** da tabela *Referências* em [referencia-layout-sites.md](referencia-layout-sites.md); textos finais e políticas com revisão legal. Detalhe em [conteudo-juridico-semente.md](../definicoes/conteudo-juridico-semente.md). |
| **Logo** | Arquivo oficial (SEO): `docs/sousa-lima-consultoria-logo-horizontal-colorido.png`. |
| **Código** | **Dois repositórios distintos** (não monorepo): **frontend** = site Astro + Tailwind; **admin** = Laravel + **Inertia** + **React** + Tailwind — ver [stack-tecnico-slc.md](../definicoes/stack-tecnico-slc.md). |
| **Produto** | **Área do cliente** fica **para depois**. |
| **Infra (agora)** | Foco em **publicar com Docker Swarm** ([base-publicacao.md](base-publicacao.md)). |
| **Compliance** | Consentimento com **implementação técnica** (gate de scripts), não só texto — [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md). |
| **Pós go-live** | Search Console, sitemap, monitoramento — **fase posterior**. |
| **Mapa do site (MVP)** | Estrutura de navegação, rodapé legal e padrões SEO/a11y — [mapa-site-mvp-slc.md](mapa-site-mvp-slc.md). |
| **Leads (contato)** | Formulário segmentado + `POST /api/v1/lead/contact` — [formulario-contato-lead-slc.md](formulario-contato-lead-slc.md). |
| **URL canónica** | **`https://sousalimaconsultoria.com.br`** (apex); `www` redireciona com 301 — [dominios-e-ambiente.md](../definicoes/dominios-e-ambiente.md), [`deploy/slc.yaml`](../../deploy/slc.yaml) (Traefik). |
| **Design (Figma)** | **Sem** biblioteca Figma obrigatória; entregas baseadas no **designer** (alinhamento à [paleta](../definicoes/paleta-cores.md), Bento/glass conforme [projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md)). |
| **PSI / WCAG / JSON-LD** | Seguir **melhores práticas** já documentadas ([qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md), [diretrizes-qualidade-site-slc.md](../definicoes/diretrizes-qualidade-site-slc.md), [projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md)); **iterar** após go-live com medições reais. |

---

## Fases sugeridas (ordem lógica)

| Fase | Entregas | Notas |
|------|----------|--------|
| **1. Conteúdo + compliance** | [Mapa MVP](mapa-site-mvp-slc.md) como base; copy v1, políticas; **banner/categorias** de cookies implementáveis | LGPD exige técnica + jurídico |
| **2. Design** | UI (Bento + glass + [paleta](../definicoes/paleta-cores.md)); validar **contraste AA** com glass | Ver checklist qualidade |
| **3. Institucional Astro** | SSG, `fetch` no **build**, contrato de API, **webhook/CI** para rebuild ao mudar CMS | Ver [integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md) |
| **4. Admin Laravel** | Repositório separado: API + painel Inertia; consumido pelo build do **outro** repo (Astro) | Paralelo ou após MVP do site |
| **5. Qualidade** | PSI, WCAG, JSON-LD, assets | Checklist abaixo |
| **6. Go-live Swarm** | Stack, TLS, DNS, **healthcheck**, secrets | [dominios-e-ambiente.md](../definicoes/dominios-e-ambiente.md) |

---

## Integração Astro + Laravel (SSG) — validação

- Com **SSG**, a API é consumida **só no build**. Sem **rebuild/deploy** após alterar conteúdo no Laravel, o site **não** reflete o banco.
- **Recomendado:** **webhook** no **admin (Laravel)** disparando o **CI do repositório do frontend** (build Astro + deploy Swarm). Em dois repos, usar **dispatch** entre GitHub/GitLab ou endpoint de deploy com segredo. Alternativas: cron, manual — ver [integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md).
- O **contrato de API** (tipos, campos) continua necessário para o build ser **reproduzível** e tipado (TypeScript).

---

## Stack Laravel + Inertia — validação

- Padrão: **Laravel + Inertia + React** (ou Vue). **Inertia** substitui a necessidade de **Next.js** para o mesmo conjunto de rotas servidas pelo Laravel.
- **Next.js** só faria sentido como **aplicação separada** (micro-serviço), integrada por API — não documentado como padrão SLC.

---

## Checklist — o que ainda falta (institucional)

### Conteúdo e legal

- [ ] **URLs e slugs** finais alinhados ao [mapa-site-mvp-slc.md](mapa-site-mvp-slc.md) (ajustes após copy e jurídico).
- [ ] **Copy** revisada: [copy-home-v1-slc.md](copy-home-v1-slc.md), [copy-servicos-verticais-v1-slc.md](copy-servicos-verticais-v1-slc.md) (KPIs X%, uptime; promessas com base real; tom final).
- [ ] Políticas revisadas por advogado; **consentimento de cookies** implementado conforme [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md) e [guia-copy-juridico-lgpd-slc.md](guia-copy-juridico-lgpd-slc.md).

### Design

- [ ] Entregas do **designer** alinhadas à [paleta](../definicoes/paleta-cores.md) e às diretrizes (Bento, glass) — **sem** obrigatoriedade de ficheiro Figma no repositório.
- [x] **www** canónico: apex principal — [dominios-e-ambiente.md](../definicoes/dominios-e-ambiente.md).

### Código

- [ ] **Repositório frontend (site):** Astro + Tailwind; CI próprio (build, artefato estático).
- [ ] **Repositório admin:** Laravel + Inertia + React; API e painel; CI próprio (testes, deploy).
- [ ] **Contrato de API** implementado conforme [contrato-api-build-time-slc.md](contrato-api-build-time-slc.md) (slugs, interfaces TS, imagens com `width`/`height`, `API_READ_TOKEN`, payload do webhook; leads em [formulario-contato-lead-slc.md](formulario-contato-lead-slc.md) / §6).
- [ ] **Webhook / dispatch** do admin para acionar **pipeline do frontend** quando conteúdo mudar ([integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md)).
- [ ] **Otimização de assets:** uso do **`<Image />` do Astro** para logo e imagens (WebP/AVIF, dimensões) — metas PSI ([projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md)).
- [ ] Implementação e testes de performance (PSI).

### Qualidade (acréscimos)

- [ ] **WCAG:** validar **contraste** da [paleta](../definicoes/paleta-cores.md), especialmente com **glassmorphism** e fundos variáveis — iterar após primeiro deploy.
- [ ] **Schema.org:** JSON-LD **`Organization`** e **`ProfessionalService`** (e `LocalBusiness` se aplicável) no Astro ([projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md)) — seguir boas práticas; refinar com dados reais.
- [ ] **PSI / Core Web Vitals:** medir no ambiente real e melhorar incrementalmente ([qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md)).

### Infra

- [x] `docker stack deploy` documentado — [procedimento-deploy-producao-slc.md](procedimento-deploy-producao-slc.md).
- [x] **Healthcheck** em todos os serviços do [`deploy/slc.yaml`](../../deploy/slc.yaml) (nginx, PHP, Postgres, Redis).
- [x] **Scheduler** Laravel — serviço `scheduler` no `slc.yaml` (1 réplica).
- [ ] Aplicar snippet de [`deploy/laravel/README.md`](../../deploy/laravel/README.md) no repositório **admin** (`config/database.php`).
- [ ] **SMTP (Microsoft 365):** `MAIL_USERNAME` no `slc.yaml`; secret `slc_sousalima_smtp_password`; `config/mail.php` a ler `MAIL_PASSWORD_FILE` — [deploy/laravel/README.md](../../deploy/laravel/README.md).
- [ ] **CI/CD** em **cada** repositório (frontend: build Astro + deploy; admin: testes Laravel + deploy API/painel).
- [ ] Secrets e variáveis em produção.

### Explicitamente fora do momento

- **Área do cliente** (fase posterior).
- **Pós go-live** ampliado (Search Console contínuo, etc.).

---

## Próximo passo recomendado

(1) [guia-copy-juridico-lgpd-slc.md](guia-copy-juridico-lgpd-slc.md) — copy final + jurídico + banner/gate de cookies; (2) **dois repositórios** (frontend + admin), contrato de API e **webhook**; (3) CI em cada repo; (4) após deploy, PSI/WCAG/JSON-LD com medição e iteração.

Atualize os checklists quando itens forem concluídos.
