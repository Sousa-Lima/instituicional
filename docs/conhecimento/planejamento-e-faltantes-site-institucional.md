# Planejamento e faltantes — site institucional SLC

Atualizado conforme decisões de produto e stack. Referências: [stack-tecnico-slc.md](../definicoes/stack-tecnico-slc.md), [integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md), [projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md), [conteudo-juridico-semente.md](../definicoes/conteudo-juridico-semente.md).

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

---

## Fases sugeridas (ordem lógica)

| Fase | Entregas | Notas |
|------|----------|--------|
| **1. Conteúdo + compliance** | Mapa do site, copy v1, políticas; **banner/categorias** de cookies implementáveis | LGPD exige técnica + jurídico |
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

- [ ] Mapa do site fechado (páginas e URLs).
- [ ] Copy substituindo placeholders (após semente da referência).
- [ ] Políticas revisadas por advogado; **consentimento de cookies** implementado conforme [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md).

### Design

- [ ] UI kit / Figma alinhado à paleta.
- [ ] Decisão **www** canônico ([dominios-e-ambiente.md](../definicoes/dominios-e-ambiente.md)).

### Código

- [ ] **Repositório frontend (site):** Astro + Tailwind; CI próprio (build, artefato estático).
- [ ] **Repositório admin:** Laravel + Inertia + React; API e painel; CI próprio (testes, deploy).
- [ ] **Contrato de API** implementado conforme [contrato-api-build-time-slc.md](contrato-api-build-time-slc.md) (slugs, interfaces TS, imagens com `width`/`height`, `API_READ_TOKEN`, payload do webhook).
- [ ] **Webhook / dispatch** do admin para acionar **pipeline do frontend** quando conteúdo mudar ([integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md)).
- [ ] **Otimização de assets:** uso do **`<Image />` do Astro** para logo e imagens (WebP/AVIF, dimensões) — metas PSI ([projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md)).
- [ ] Implementação e testes de performance (PSI).

### Qualidade (acréscimos)

- [ ] **WCAG:** validar **contraste** da [paleta](../definicoes/paleta-cores.md), especialmente com **glassmorphism** e fundos variáveis.
- [ ] **Schema.org:** JSON-LD **`Organization`** e **`ProfessionalService`** (e `LocalBusiness` se aplicável) no Astro ([projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md)).

### Infra

- [ ] `docker stack deploy` (ou equivalente) documentado para o Swarm.
- [ ] **Healthcheck** nos serviços Docker para o Swarm reiniciar/rotear corretamente (Laravel/API; artefato estático Astro conforme modelo de deploy).
- [ ] **CI/CD** em **cada** repositório (frontend: build Astro + deploy; admin: testes Laravel + deploy API/painel).
- [ ] Secrets e variáveis em produção.

### Explicitamente fora do momento

- **Área do cliente** (fase posterior).
- **Pós go-live** ampliado (Search Console contínuo, etc.).

---

## Próximo passo recomendado

(1) Mapa do site + copy v1; (2) **dois repositórios** (frontend + admin), contrato de API e **webhook** entre pipelines; (3) stack Swarm com **healthcheck** e CI em cada repo.

Atualize os checklists quando itens forem concluídos.
