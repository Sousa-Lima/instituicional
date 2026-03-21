# Copy v1 — verticais de serviço (SLC)

Proposta de texto para as **três páginas internas** de serviços (`/servicos/[slug]`), alinhada à estratégia **MVP 30–90 dias**, ao perfil **enterprise** ([Venturus](referencia-layout-sites.md)) e a [conteudo-mensagens-referencia.md](conteudo-mensagens-referencia.md), [mapa-site-mvp-slc.md](mapa-site-mvp-slc.md) e [copy-home-v1-slc.md](copy-home-v1-slc.md).

**Nota técnica:** o conteúdo deve ser **fornecido pela API do admin (Laravel)** e **consumido pelo Astro no build** (SSG), conforme contrato **`ServiceContract`** (`§2.1` em [contrato-api-build-time-slc.md](contrato-api-build-time-slc.md), [copy-home-v1-slc.md](copy-home-v1-slc.md)).

**KPIs com X% ou metas absolutas** são placeholders até haver dados reais; revisar antes de publicar.

---

## 1. Consultoria de processos e eficiência

**Foco:** redução de retrabalho e otimização de fluxos de negócio.

| Campo | Texto |
|-------|--------|
| **Título (H1)** | Consultoria de processos: eficiência operacional orientada a resultados. |
| **Proposta de valor** | Transformamos gargalos operacionais em fluxos ágeis. O nosso foco não é apenas documentar, mas executar mudanças que reduzam custos e aumentem a produtividade da sua **equipe**. |

**O que entregamos**

- **Diagnóstico de gargalos:** identificação de pontos de fricção que atrasam a entrega final.
- **Desenho de fluxos “to-be”:** reestruturação de processos com foco em automação e eliminação de tarefas manuais.
- **Acompanhamento de implementação:** suporte na **adoção** das novas metodologias pela equipe.

**Métrica de sucesso (KPI):** redução média de **X%** no tempo de ciclo de processos internos.

---

## 2. Desenvolvimento de software e MVPs

**Foco:** velocidade de lançamento e stack moderna (**Laravel** + **React** / **Inertia**; site institucional em **Astro** — [stack-tecnico-slc.md](../definicoes/stack-tecnico-slc.md)).

| Campo | Texto |
|-------|--------|
| **Título (H1)** | Desenvolvimento de software: do conceito ao MVP em tempo recorde. |
| **Proposta de valor** | Construímos soluções de software robustas e escaláveis. Especialistas em levar a sua ideia ao mercado em **30 a 90 dias**, com boas práticas de **clean code** e arquitetura. |

**O que entregamos**

- **Desenvolvimento de MVP:** foco nas funcionalidades críticas para validação rápida de mercado.
- **Sistemas enterprise:** aplicações complexas com frontend em **React/Inertia** e backend **Laravel** ([stack-tecnico-slc.md](../definicoes/stack-tecnico-slc.md)).
- **Refatoração e escalabilidade:** evolução de sistemas legados para arquiteturas modernas.

**Métrica de sucesso (KPI):** lançamento da primeira versão funcional em até **90 dias** (conforme escopo).

---

## 3. Cloud e infraestrutura de alta disponibilidade

**Foco:** segurança, resiliência e orquestração com **Docker Swarm** ([base-publicacao.md](base-publicacao.md), [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md)).

| Campo | Texto |
|-------|--------|
| **Título (H1)** | Infraestrutura cloud: estabilidade e segurança para o seu ecossistema. |
| **Proposta de valor** | Garantimos que a sua aplicação esteja sempre disponível e protegida. Implementamos arquiteturas modernas que suportam picos de tráfego sem comprometer a performance. |

**O que entregamos**

- **Orquestração com Docker Swarm:** gestão de containers para alta disponibilidade.
- **Segurança e compliance:** cabeçalhos (**CSP**, **HSTS**), alinhamento a **LGPD** onde aplicável — [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md), [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md).
- **Monitorização e performance:** otimização de **TTFB** e tempos de resposta — [qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md).

**Métrica de sucesso (KPI):** meta de **99,9%** de uptime e conformidade técnica acordada por escopo (revisar com operações antes de publicar).

---

## Notas técnicas para implementação (Astro + Laravel)

| Tema | Orientação |
|------|------------|
| **Imagens e LCP / CLS** | Contrato da API com **largura e altura** explícitas para imagens de cada página de serviço; usar **`<Image />` do Astro** — [contrato-api-build-time-slc.md](contrato-api-build-time-slc.md), [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md). |
| **SEO semântico** | Injetar **JSON-LD** por página (`Service` / `ProfessionalService` / `Offer` conforme modelo, além de **`Organization`**) — [projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md), [mapa-site-mvp-slc.md](mapa-site-mvp-slc.md) §6. |
| **Conversão (INP)** | CTA com feedback visual em **menos de 100 ms** (hover/active) — [diretrizes-qualidade-site-slc.md](../definicoes/diretrizes-qualidade-site-slc.md), [qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md). |
| **Formulário de lead** | Interesse pré-selecionado por vertical; envio ao Laravel — [formulario-contato-lead-slc.md](formulario-contato-lead-slc.md), [contrato-api-build-time-slc.md](contrato-api-build-time-slc.md) §6. |

---

## Semântica

- **Um H1** por página de vertical; blocos “O que entregamos” com **H2** ou lista estruturada conforme o design system.
- Breadcrumbs: Início → Serviços → [Nome da vertical].
