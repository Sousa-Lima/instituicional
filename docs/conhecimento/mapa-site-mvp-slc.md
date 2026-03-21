# Mapa do site — MVP institucional SLC

Proposta alinhada a [diretrizes-qualidade-site-slc.md](../definicoes/diretrizes-qualidade-site-slc.md), [planejamento-e-faltantes-site-institucional.md](planejamento-e-faltantes-site-institucional.md), referências [Sagitta Digital](https://sagittadigital.com.br/) / [Venturus](https://www.venturus.org.br/) ([referencia-layout-sites.md](referencia-layout-sites.md)) e [conteudo-mensagens-referencia.md](conteudo-mensagens-referencia.md).

**Foco:** performance (PSI, Core Web Vitals), conversão **B2B** e autoridade institucional.

As **URLs** são sugestão; fechar slugs no admin e no `GET /api/v1/content/slugs` — [contrato-api-build-time-slc.md](contrato-api-build-time-slc.md).

---

## Visão geral — navegação principal

| Rota | Papel |
|------|--------|
| `/` | Home (conversão principal) |
| `/servicos`, `/servicos/[slug]` | Serviços (visão geral + páginas internas) |
| `/cases`, `/cases/[slug]` | Portfólio e case individual |
| `/sobre` | Institucional |
| `/blog` ou `/insights` (+ `/[slug]`) | Conteúdo / SEO |
| `/contato` | Lead B2B |

Políticas e rodapé técnico: ver [§ Rodapé e compliance](#5-rodapé-e-compliance-lgpd).

---

## 1. Home — página de conversão principal

Estrutura: **promessa → método → prova** (conversão + credibilidade).

**Copy v1 proposta (textos):** [copy-home-v1-slc.md](copy-home-v1-slc.md).

| Bloco | Conteúdo |
|-------|----------|
| **Hero** | Proposta de valor clara; **CTA** primário e secundário — ver copy v1. |
| **Serviços (resumo)** | Cards compactos com **ícone**, título e descrição curta das verticais **Consultoria**, **Software**, **Cloud**; link para `/servicos` e para cada vertical. |
| **Metodologia (diferencial)** | Foco em **MVP** e entregas em **ciclos** (faixa **30–90 dias** como referência de mercado) para reduzir receio de “projeto infinito” — coerente com [conteudo-mensagens-referencia.md](conteudo-mensagens-referencia.md). |
| **Prova social** | Números agregados (**+clientes**, **+projetos**, etc., quando houver base) + **faixa de logotipos** (marcas autorizadas). |
| **Depoimentos / FAQ** | Credibilidade e esclarecimento de objeções **perto do rodapé** (estilo referência Sagitta: depoimentos + FAQ). |

---

## 2. Serviços — arquitetura “enterprise”

Inspirado no modelo de autoridade por linhas (referência Venturus), adaptado à SLC.

**Copy v1 das três verticais:** [copy-servicos-verticais-v1-slc.md](copy-servicos-verticais-v1-slc.md).

| Página | Função |
|--------|--------|
| **Landing `/servicos`** | Visão geral das competências; entrada para as verticais. |
| **Consultoria de processos** `/servicos/[slug]` | Eficiência, redução de retrabalho, alinhamento a negócio (ajustar slug final). |
| **Desenvolvimento de software (MVP)** | Processo ágil, entregas incrementais; menção a stack quando fizer sentido (**Laravel**, **React**, etc.) — alinhado ao [stack-tecnico-slc.md](../definicoes/stack-tecnico-slc.md). |
| **Cloud e infraestrutura** | Disponibilidade, segurança; exemplos: **Docker Swarm**, **TLS**, boas práticas de deploy — coerente com [base-publicacao.md](base-publicacao.md). |

Cada interna: entregas, diferenciais, CTA para contato; **breadcrumbs** obrigatórios (lista → interna).

---

## 3. Cases de sucesso (portfólio)

| Página | Função |
|--------|--------|
| **Lista `/cases`** | Galeria: título, **tags** de tecnologia/setor, **tempo de leitura** (opcional), imagem de capa. |
| **Case `/cases/[slug]`** | Estrutura sugerida: **Problema → Solução → Resultado numérico** (ex.: “redução de Y% no tempo de processamento”), com autorização do cliente. |

**Navegação:** **breadcrumbs** obrigatórios (Início → Cases → Título do case).

---

## 4. Institucional e autoridade

| Página | Função |
|--------|--------|
| **Sobre a SLC** `/sobre` | História, posicionamento **consultivo** (“parceiro, não apenas fornecedor”), valores e time (se aplicável). |
| **Blog / Insights** | Artigos técnicos e de negócio para **SEO semântico**; definir **um** slug raiz (`/blog` **ou** `/insights`). |
| **Contato** `/contato` | Formulário **segmentado** B2B (empresa, cargo, interesse, estágio, mensagem, LGPD) — [formulario-contato-lead-slc.md](formulario-contato-lead-slc.md); [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md). |

---

## 5. Rodapé e compliance (LGPD)

| Elemento | Detalhe |
|-----------|---------|
| **Navegação secundária** | Atalhos para as principais seções (serviços, cases, sobre, blog, contato). |
| **Área legal** | Links para **Política de privacidade**, **Termos de uso**, **Política de cookies** (URLs sugeridas: `/politica-de-privacidade`, `/termos-de-uso`, `/cookies`). |
| **Gestão de cookies** | **Implementação técnica**: botão flutuante ou link persistente para **revogar/ajustar** consentimento — [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md). |
| **Dados institucionais** | CNPJ, endereço (se público), redes sociais; coerente com JSON-LD — [projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md). |

---

## 6. SEO e acessibilidade (padrão global)

| Requisito | Detalhe |
|-----------|---------|
| **Breadcrumbs** | Obrigatórios em **serviços** (lista → interna) e **cases** (lista → interna); recomendados onde houver profundidade de URL. |
| **JSON-LD** | **`Organization`**, **`ProfessionalService`**, **`LocalBusiness`** quando aplicável. |
| **Semântica HTML5** | `header`, `nav`, `main`, `section`, `footer`; **um `h1`** por página. |
| **Performance** | Imagens com `<Image />` Astro, metas LCP/CLS — [qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md), [diretrizes-qualidade-site-slc.md](../definicoes/diretrizes-qualidade-site-slc.md). |

---

## Evolução pós-MVP

Novas seções (carreiras, parcerias, materiais ricos) após validação de métricas e operação do admin.
