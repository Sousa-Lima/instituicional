# Normas de arquitetura — backend e infraestrutura

Normas para o site e serviços web da **Sousa Lima Consultoria**, alinhadas a [qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md) (LCP, TBT, INP) e à [base de publicação](base-publicacao.md).

## 1. Prioridade SSG / ISR

| Diretriz | Detalhe |
|----------|---------|
| **Páginas institucionais e de cases** | Preferir **SSG** (Static Site Generation) ou **ISR** (Incremental Static Regeneration), conforme o framework adotado (ex.: Next.js, Nuxt, Astro, etc.). |
| **Objetivo** | HTML pré-renderizado em CDN/edge, **TTFB** baixo e melhor previsibilidade de cache. |
| **Exceções** | Páginas fortemente dinâmicas ou áreas autenticadas podem usar SSR/CSR; justificar desvio do padrão. |

Conteúdo vindo do **Laravel** para site **Astro SSG**: a API é usada **no build**; é preciso **rebuild/deploy** quando o CMS mudar — ver [integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md).

## 2. Pipeline de imagens

| Diretriz | Detalhe |
|----------|---------|
| **Marcação** | Servir imagens com **`<picture>`** (`<source type="image/avif">`, `<source type="image/webp">`, `<img>` fallback) quando houver mais de um formato. |
| **Formatos** | **WebP** e **AVIF** como preferência; JPEG/PNG quando necessário para compatibilidade. |
| **Responsivo** | **`srcset`** e **`sizes`** (ou serviço de resize/ CDN) para dimensionamento **dinâmico** por viewport e DPR. |
| **Ligação com LCP** | O elemento LCP costuma ser imagem; priorizar o recurso crítico (tamanho, formato, prioridade de carregamento). Ver [qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md). |

## 3. Scripts de terceiros (sandboxing e performance)

| Diretriz | Detalhe |
|----------|---------|
| **Carregamento** | Scripts não essenciais (ex.: **GTM**, **Analytics**) com **`defer`** ou carregamento **após** interação / consentimento (LGPD), evitando bloqueio na análise inicial. |
| **Main thread** | Reduzir impacto em **TBT** e **INP**: evitar scripts síncronos na cabeça; considerar **offload para Web Worker** (ex.: [Partytown](https://partytown.builder.io/) ou equivalente) quando a stack permitir — nem todo tag manager roda em worker sem ajuste de CSP e integração. |
| **Política** | Novos terceiros passam por revisão (necessidade, peso, alternativa mais leve). |

## 4. Cabeçalhos de segurança

| Diretriz | Detalhe |
|----------|---------|
| **CSP** — Content Security Policy | Política **explícita** (`default-src`, `script-src`, `style-src`, `img-src`, `connect-src`, `frame-src`…). Ajustar **fontes** para scripts de analytics e GTM (hashes, nonces ou hosts permitidos); testar em staging antes de produção. |
| **HSTS** | HTTP Strict Transport Security em produção (HTTPS obrigatório); prazo e `includeSubDomains` conforme domínio e subdomínios. |
| **`X-Frame-Options`** ou **`frame-ancestors`** | Evitar clickjacking; em conjunto com CSP, preferir `frame-ancestors` na CSP quando unificado. |
| **Marca** | Proteção contra injeção de conteúdo e framing indevido; alinhar com time de marketing para não quebrar embeds legítimos. |

## Revisão

Atualizar este documento quando a stack (framework, CDN, provedor de DNS) estiver fechada e após a primeira auditoria de segurança e performance.
