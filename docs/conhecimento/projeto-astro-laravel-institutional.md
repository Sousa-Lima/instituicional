# Contexto do projeto — institucional enterprise (Astro + Laravel)

**Objetivo:** site institucional com **excelente performance** (meta: pontuações altas no [PageSpeed Insights](https://pagespeed.web.dev/)), unindo **conversão** (referência [Sagitta Digital](https://sagittadigital.com.br/)) e **autoridade** (referência [Venturus](https://www.venturus.org.br/)).

**Arquitetura de código:** **dois repositórios** — frontend (este escopo: **Astro**) e **admin** (**Laravel** + Inertia + React); não monorepo — ver [stack-tecnico-slc.md](../definicoes/stack-tecnico-slc.md). Este documento foca no **site (frontend)** e na **API do admin** consumida no **build**; **área do cliente** é **posterior**.

Documentos relacionados: [integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md), [referencia-layout-sites.md](referencia-layout-sites.md), [conteudo-mensagens-referencia.md](conteudo-mensagens-referencia.md), [qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md), [diretrizes-qualidade-site-slc.md](../definicoes/diretrizes-qualidade-site-slc.md), [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md), [paleta-cores.md](../definicoes/paleta-cores.md), [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md).

---

## 1. Diretrizes de frontend (Astro)

| Tópico | Diretriz |
|--------|----------|
| **Framework** | **Astro** — **SSG** por padrão; **modo hybrid** (SSR/API) apenas para rotas que exijam conteúdo protegido ou dinâmico no servidor. |
| **Estilo** | **Tailwind CSS**. Usar **Bento Grid** e **glassmorphism** onde fizer sentido visual e de hierarquia, sem prejudicar contraste ([WCAG](#4-seo--acessibilidade-wcag-21)). |
| **Componentes** | **Shadcn/ui** (tipicamente via **React** nas ilhas). Manter interatividade apenas em **Astro Islands** com `client:load` ou `client:visible` conforme necessidade — o restante permanece HTML estático. |
| **Animações** | **Framer Motion** nas ilhas React para entradas suaves de seções; respeitar **`prefers-reduced-motion`** (reduzir ou desativar movimento). |

---

## 2. Métricas de performance (Google PageSpeed Insights)

Alinhado a [qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md). Metas abaixo são para **boa experiência**; a pontuação sintética (0–100) pode variar entre laboratório e dados de campo (CrUX).

| Métrica | Meta | Notas |
|---------|------|--------|
| **LCP** | **≤ 2,5 s** | Imagens acima da dobra: `fetchpriority="high"` e **nunca** `loading="lazy"` no elemento LCP. |
| **CLS** | **≤ 0,1** | Reservar espaço: `width`/`height` e/ou **`aspect-ratio`** para imagens e ícones. |
| **INP** | **≤ 200 ms** | Evitar trabalho pesado na main thread; ilhas pequenas e JS sob demanda. |
| **Imagens** | — | Preferir **`<Image />` do Astro** (otimização e formatos WebP/AVIF conforme configuração), em linha com [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md). |

---

## 3. Integração com backend (Laravel)

| Tópico | Diretriz |
|--------|----------|
| **API — conteúdo institucional** | Blog, serviços e páginas estáticas: dados obtidos com **`fetch` no frontmatter** (ou camada de dados) **no build** (SSG), gerando HTML estático. |
| **Atualização após mudanças no CMS** | Definir **webhook** (ou CI disparado pelo Laravel) para **novo build + deploy**; senão o HTML fica defasado em relação ao banco — ver [integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md). |
| **Auth — área do cliente** | **Fora do escopo imediato**; quando existir, JWT/cookies HttpOnly conforme [stack-tecnico-slc.md](../definicoes/stack-tecnico-slc.md). |
| **Dados no cliente** | Onde houver ilhas React no Astro, **TanStack Query** pode gerenciar cache e chamadas à API; no **repo admin (Laravel)**, o fluxo padrão é **Inertia + React** (sem Next.js). |
| **Segurança** | Cookies **HttpOnly** / **Secure** / **SameSite** quando houver sessão ou token; CSP e demais cabeçalhos em [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md). |

---

## 4. SEO e acessibilidade (WCAG 2.1)

| Tópico | Diretriz |
|--------|----------|
| **Semântica** | HTML5: **`main`**, **`section`**, **`article`**, etc.; **um `h1`** por página ([diretrizes-qualidade-site-slc.md](../definicoes/diretrizes-qualidade-site-slc.md)). |
| **Schema.org** | Injetar **JSON-LD** para **`Organization`**, **`ProfessionalService`** (ou `Service` conforme o modelo de negócio), **`LocalBusiness`** quando houver endereço — home, contato e serviços. |
| **A11y** | Contraste mínimo **AA** (validar **glassmorphism** e sobreposições sobre imagens); foco visível; teclado; WCAG 2.1 nível alvo **AA**. |

---

## 5. Regras de código (clean code)

| Tópico | Diretriz |
|--------|----------|
| **Nomenclatura** | **PascalCase** para componentes; **kebab-case** para nomes de arquivos quando for convenção do projeto. |
| **TypeScript** | **`strict`** habilitado; tipar **todas** as interfaces de resposta da API Laravel consumida pelo front. |
| **Immutabilidade** | Preferir estilo funcional; evitar efeitos colaterais fora de **`useEffect`**, **`useMemo`** ou handlers explícitos nas ilhas React. |

---

## Revisão

Atualizar este documento quando a versão exata do Laravel, domínios de API e estratégia de deploy (CDN, CI) estiverem fechados.
