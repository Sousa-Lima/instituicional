# Stack técnico — SLC (definição atual)

## Repositórios — **não** é monorepo

O código fica em **dois projetos Git separados**:

| Projeto | Papel | Stack |
|---------|--------|--------|
| **Frontend (site)** | Site institucional público | **Astro** + **Tailwind CSS** |
| **Admin** | API, CMS/conteúdo, painel com Inertia | **Laravel** + **Inertia.js** + **React** + **Tailwind CSS** |

Cada repositório tem **próprio CI/CD**, versionamento e deploy. A comunicação entre eles é por **HTTP (API)** e, no fluxo de publicação do site estático, por **build** + **webhook** entre pipelines — ver [integracao-astro-ssg-laravel.md](../conhecimento/integracao-astro-ssg-laravel.md).

## Visão geral técnica

| Camada | Tecnologia |
|--------|------------|
| **Frontend (site)** | **Astro** + **Tailwind CSS** |
| **Admin (Laravel)** | **Laravel** + **Inertia.js** + **React** + **Tailwind CSS** |

### Por que não Next.js na mesma app Inertia (admin)?

- **Inertia** liga **Laravel** a um front **React ou Vue** com **rotas e páginas dirigidas pelo Laravel** — não usa o roteador de **Next.js** (App Router/Pages).
- **Next.js** é um framework **Node** com roteamento e SSR próprios; colocá-lo **junto** de Laravel+Inertia na mesma aplicação costuma gerar **sobreposição** (dois modelos de rota, dois deploys conceituais) sem necessidade clara.
- **Padrão recomendado:** **Laravel + Inertia + React** (ou Vue), um único fluxo de páginas Inertia.

**Next.js** só deve entrar no mapa se houver decisão explícita de **micro-serviço** ou **app separada** (ex.: produto autônomo em outro repositório), integrada ao Laravel **só via API** — não como terceiro front acoplado ao Inertia.

## Frontend (site) + Admin (Laravel)

- **Repo do frontend (Astro):** marketing, SSG, performance — consome a API exposta pelo **repo admin (Laravel)** apenas **no build**; ver [integracao-astro-ssg-laravel.md](../conhecimento/integracao-astro-ssg-laravel.md).
- **Repo admin (Laravel + Inertia + React):** API para o site, gestão de conteúdo e telas do painel via Inertia.

**Tailwind** unifica estilo entre Astro e o front Inertia/React, alinhado à [paleta de cores](paleta-cores.md).

## Área do cliente

**Fora do escopo imediato** do site institucional; evolui na stack Laravel + Inertia + React quando priorizado.
