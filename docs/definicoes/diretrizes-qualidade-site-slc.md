# Diretrizes de qualidade — estilo referência (Sagitta + Venturus)

Diretrizes para o site institucional da **Sousa Lima Consultoria**, alinhadas à inspiração de estrutura em [referencia-layout-sites.md](../conhecimento/referencia-layout-sites.md), à [paleta de cores](paleta-cores.md) e às métricas em [qualidade-web-core-vitals.md](qualidade-web-core-vitals.md).

## 1. Hierarquia “enterprise” e HTML5 semântico

- Usar **tags semânticas** (`header`, `nav`, `main`, `section`, `article`, `aside`, `footer`) — evitar `div` genérica onde existir elemento com significado.
- O **`main`** deve envolver o núcleo da página e conter **seções claras**, na ordem que fizer sentido para o conteúdo:
  - **Hero** — primeira dobra (não existe elemento `<hero>` no HTML5; usar `section` com `id`/`aria-labelledby` ou primeiro `section` com título adequado).
  - **Serviços** — `section` (ex.: `#services`).
  - **Cases** — `section` (ex.: `#cases`).
  - **Prova social** — `section` (ex.: `#social-proof`: números, logos, depoimentos).
  - **FAQ** — `section` (ex.: `#faq`) ou `aside` se for secundário.
- Uma única **`h1`** por página; demais títulos em ordem **`h2` → `h3`** sem saltos.
- Navegação principal em **`nav`** com links para âncoras ou rotas internas coerentes.

## 2. Performance (budget)

| Regra | Detalhe |
|-------|---------|
| **Peso por página** | Máximo **1,5 MB** de transferência total (HTML + CSS + JS + imagens + fontes) na carga inicial típica; preferir lazy-load fora da dobra e formatos compactos. |
| **APIs** | Se houver chamadas a API, respostas com **`Cache-Control`** explícito e alinhado ao tipo de dado (ex.: dados estáveis com `max-age` razoável; nada de `no-store` sem necessidade). |
| **Ligação com Core Web Vitals** | O budget ajuda LCP e TTFB; revisar [qualidade-web-core-vitals.md](qualidade-web-core-vitals.md) em cada release. |
| **Animações (Framer Motion)** | Ilhas pequenas, **`client:visible`** quando possível, tokens centralizados — [tokens-animacao-framer-slc.md](tokens-animacao-framer-slc.md). |

## 3. Conversão e UX

| Regra | Detalhe |
|-------|---------|
| **CTA** | Botões de ação devem ter **feedback visual imediato** em **hover** e **active** — transição perceptível em **menos de 100 ms** (CSS `transition` curta ou estado instantâneo), sem atraso que prejudique o [INP](qualidade-web-core-vitals.md). |
| **Formulários** | Formulários com muitos campos: **validação no cliente** (antes do envio), mensagens claras; **máscaras** com `inputmode`, `pattern`, `autocomplete` e APIs nativas quando possível (acessibilidade e teclado mobile). |

## 4. Arquitetura de conteúdo e SEO

- Textos focados em **entidades** do negócio (consultoria, software, cloud, processos, setores atendidos) — vocabulário consistente entre títulos, parágrafos e metadados (**SEO semântico** / entity-first), sem keyword stuffing.
- Páginas de **cases**: usar **breadcrumb** (`nav` com `aria-label="Breadcrumb"` + lista estruturada ou JSON-LD `BreadcrumbList` quando aplicável) para rastreamento e contexto.
- Manter coerência com [conteudo-mensagens-referencia.md](../conhecimento/conteudo-mensagens-referencia.md) (MVP, resultados, provas).

## Revisão

Atualizar este documento se o budget de 1,5 MB ou as seções mínimas forem ajustados após protótipo ou auditoria de performance.
