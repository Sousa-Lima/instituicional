# Qualidade de site — Core Web Vitals e diagnóstico (Google)

Referência para exigências de performance e SEO técnico alinhadas ao que o Google usa em **PageSpeed Insights** / **Search Console** e como **sinais de experiência** em busca. Os limiares “bons” podem ser atualizados pelo Google; confira sempre a [documentação oficial de Web Vitals](https://web.dev/vitals) e o [Search Central](https://developers.google.com/search/docs/appearance/core-web-vitals).

## Core Web Vitals (principais)

São as métricas que o Google trata oficialmente como parte da experiência de página (e influenciam ranqueamento junto a outros fatores).

### LCP — Largest Contentful Paint (carregamento)

| | |
|---|---|
| **O que é** | Tempo até o **maior elemento visível** na viewport (imagem hero, banner ou bloco de texto grande) estar renderizado. |
| **Meta “boa”** | ≤ **2,5 s** |
| **Direção de otimização** | Priorizar o que define o LCP (geralmente imagem ou texto acima da dobra): formatos modernos (AVIF/WebP), tamanho adequado, `fetchpriority="high"` no recurso LCP quando for imagem, pré-carregar fontes críticas, evitar CSS/JS que atrasem a primeira pintura; servidor e CDN rápidos ajudam o caminho até o recurso. |

### INP — Interaction to Next Paint (interatividade)

| | |
|---|---|
| **O que é** | Substituiu o antigo **FID** como Core Web Vital. Mede a **latência das interações** (cliques, toques) ao longo da visita: quão rápido a página dá **feedback visual** depois de uma ação. |
| **Meta “boa”** | ≤ **200 ms** |
| **Direção de otimização** | Reduzir trabalho pesado na **main thread** (long tasks), dividir ou adiar JS não essencial, otimizar handlers de eventos, considerar `requestIdleCallback` / chunking para trabalho pesado; menos terceiros bloqueando a thread melhora o INP. |

### CLS — Cumulative Layout Shift (estabilidade visual)

| | |
|---|---|
| **O que é** | Mede quanto o layout **“pula”** durante o carregamento (ex.: anúncio ou fonte que empurra conteúdo). |
| **Meta “boa”** | ≤ **0,1** (adimensional) |
| **Direção de otimização** | Reservar espaço para imagens e embeds (`width`/`height` ou `aspect-ratio`), evitar inserção tardia acima da dobra, cuidar com fontes (FOIT/FOUT), animações que não alterem layout de forma inesperada. |

## Métricas de diagnóstico (dados de laboratório)

Úteis para entender **por que** as Core Web Vitals podem estar ruins; valores “bons” abaixo são referências típicas em ferramentas tipo Lighthouse (podem variar por versão).

| Métrica | Definição | Meta “boa” (referência) |
|---------|-----------|-------------------------|
| **FCP** — First Contentful Paint | Tempo até o primeiro conteúdo (texto/imagem) aparecer. | ≤ **1,8 s** |
| **TBT** — Total Blocking Time | Soma dos intervalos em que a main thread ficou bloqueada por tarefas longas. | ≤ **200 ms** |
| **SI** — Speed Index | Quão rápido o conteúdo é exibido visualmente durante o carregamento. | ≤ **3,4 s** |
| **TTFB** — Time to First Byte | Tempo até o primeiro byte da resposta HTTP (servidor/rede). | ≤ **800 ms** |

## Uso no projeto SLC

- Tratar **LCP, INP e CLS** como checklist em **homologação** antes de publicar páginas críticas (home, landing, formulários).
- **TTFB** alto: olhar hospedagem, cache, origem (CDN) e backend antes de só otimizar front-end.
- Relatórios do **PageSpeed Insights** combinam campo (CrUX) e laboratório; priorize problemas que aparecem para usuários reais.
