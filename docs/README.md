# Sousa Lima Consultoria — conhecimento e definições

Base de documentação interna para evoluir até a publicação do site e dos serviços associados. **Código:** dois projetos separados — **frontend (site Astro)** e **admin (Laravel)** —, não monorepo ([stack-tecnico-slc.md](definicoes/stack-tecnico-slc.md)).

## Estrutura

| Pasta | Uso |
|--------|-----|
| `conhecimento/` | Arquitetura, publicação, runbooks e notas técnicas. |
| `definicoes/` | Glossário, decisões de produto, domínio e convenções. |
| `admin/` | App **Laravel** (API/painel, base); [admin/README.md](../admin/README.md); pode tornar-se repositório Git separado. |
| `deploy/` | Stack Swarm (`slc.yaml`), snippets Laravel (`deploy/laravel/`), [guia-deploy-e-atualizacao.md](../deploy/guia-deploy-e-atualizacao.md); ver [deploy/README.md](../deploy/README.md) e [procedimento-deploy-producao-slc.md](conhecimento/procedimento-deploy-producao-slc.md). |

## Documentos

| Documento | Descrição |
|-----------|-----------|
| [conhecimento/base-publicacao.md](conhecimento/base-publicacao.md) | Visão de infraestrutura, checklist e evolução para produção. |
| [conhecimento/procedimento-deploy-producao-slc.md](conhecimento/procedimento-deploy-producao-slc.md) | Runbook: Swarm, Traefik, DNS, secrets, dados no host, build Astro, stack `slc`, verificação e rollback. |
| [conhecimento/normas-arquitetura-backend-infra.md](conhecimento/normas-arquitetura-backend-infra.md) | SSG/ISR, imagens, terceiros e security headers (CSP, HSTS, framing). |
| [conhecimento/projeto-astro-laravel-institutional.md](conhecimento/projeto-astro-laravel-institutional.md) | Stack Astro + Laravel 12: frontend, Core Web Vitals, API, JWT, SEO, WCAG, clean code. |
| [definicoes/dominios-e-ambiente.md](definicoes/dominios-e-ambiente.md) | Marca, domínio canónico (apex), `www`→301, ambientes e variáveis em nível conceitual. |
| [conhecimento/guia-copy-juridico-lgpd-slc.md](conhecimento/guia-copy-juridico-lgpd-slc.md) | Copy, jurídico (políticas/termos) e cookies LGPD (gate de scripts, checklist). |
| [definicoes/stack-tecnico-slc.md](definicoes/stack-tecnico-slc.md) | Dois repos: frontend (Astro + Tailwind) e admin (Laravel + Filament). |
| [conhecimento/integracao-astro-ssg-laravel.md](conhecimento/integracao-astro-ssg-laravel.md) | SSG: API só no build; webhook/rebuild; risco de conteúdo defasado. |
| [conhecimento/contrato-api-build-time-slc.md](conhecimento/contrato-api-build-time-slc.md) | Contrato primeiro; slugs; Services + Cases (`GET /api/v1/cases`); LCP/CLS; webhook; Bearer; CSP; leads `POST /api/v1/lead/contact` (§6). |
| [conhecimento/formulario-contato-lead-slc.md](conhecimento/formulario-contato-lead-slc.md) | Formulário segmentado B2B (ilha Astro); `POST /api/v1/lead/contact`; WCAG, LGPD, CSRF, reCAPTCHA; INP &lt; 100 ms. |
| [conhecimento/exemplo-fixture-case-enterprise-slc.md](conhecimento/exemplo-fixture-case-enterprise-slc.md) | JSON de exemplo Case “Enterprise” + notas de teste no Astro. |
| [definicoes/conteudo-juridico-semente.md](definicoes/conteudo-juridico-semente.md) | Conteúdo/jurídico: referência inicial e políticas. |
| [definicoes/compliance-ferramentas.md](definicoes/compliance-ferramentas.md) | LGPD: cookies, analytics, documentos legais. |
| [definicoes/paleta-cores.md](definicoes/paleta-cores.md) | Base de cores do logo SLC, tokens e variáveis CSS para site/sistema. |
| [definicoes/tokens-animacao-framer-slc.md](definicoes/tokens-animacao-framer-slc.md) | Easings, durações, presets Framer Motion, `prefers-reduced-motion`, Astro Islands e Partytown. |
| [definicoes/qualidade-web-core-vitals.md](definicoes/qualidade-web-core-vitals.md) | Core Web Vitals (LCP, INP, CLS) e métricas de diagnóstico (FCP, TBT, SI, TTFB). |
| [definicoes/diretrizes-qualidade-site-slc.md](definicoes/diretrizes-qualidade-site-slc.md) | Diretrizes SLC: semântica, budget 1,5 MB, CTA/formulários, SEO e breadcrumbs. |
| [conhecimento/referencia-layout-sites.md](conhecimento/referencia-layout-sites.md) | Inspiração de estrutura e UX ([Sagitta Digital](https://sagittadigital.com.br/), [Venturus](https://www.venturus.org.br/)). |
| [conhecimento/conteudo-mensagens-referencia.md](conhecimento/conteudo-mensagens-referencia.md) | Mensagens: MVP, prazos, resultados e credibilidade (alinhado às referências). |
| [conhecimento/planejamento-e-faltantes-site-institucional.md](conhecimento/planejamento-e-faltantes-site-institucional.md) | Fases do projeto, o que já está definido e checklist do que falta. |
| [conhecimento/mapa-site-mvp-slc.md](conhecimento/mapa-site-mvp-slc.md) | Mapa do site MVP: navegação, legal, SEO, acessibilidade. |
| [conhecimento/copy-home-v1-slc.md](conhecimento/copy-home-v1-slc.md) | Copy v1 da Home: hero, serviços, método, prova social, FAQ, rodapé. |
| [conhecimento/copy-servicos-verticais-v1-slc.md](conhecimento/copy-servicos-verticais-v1-slc.md) | Copy v1: Consultoria, Software/MVP, Cloud (API + Astro). |
