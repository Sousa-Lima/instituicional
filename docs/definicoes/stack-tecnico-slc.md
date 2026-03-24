# Stack técnico — SLC (definição atual)

## Repositórios — **não** é monorepo

O código fica em **dois projetos Git separados**:

| Projeto | Papel | Stack |
|---------|--------|--------|
| **Frontend (site)** | Site institucional público | **Astro** + **Tailwind CSS** |
| **Admin** | API, CMS/conteúdo, painel visual | **Laravel** + **Filament** + **Tailwind CSS** |

Cada repositório tem **próprio CI/CD**, versionamento e deploy. A comunicação entre eles é por **HTTP (API)** e, no fluxo de publicação do site estático, por **build** + **webhook** entre pipelines — ver [integracao-astro-ssg-laravel.md](../conhecimento/integracao-astro-ssg-laravel.md).

## Visão geral técnica

| Camada | Tecnologia |
|--------|------------|
| **Frontend (site)** | **Astro** + **Tailwind CSS** |
| **Admin (Laravel)** | **Laravel** + **Filament** + **Tailwind CSS** |

### Por que Filament no admin (em vez de front custom separado)?

- **Filament** acelera CRUD de conteúdo e operação diária (Services, Cases, Leads) com pouco código.
- O painel fica no próprio **Laravel** (guard `web`), reduzindo complexidade de integração e deploy.
- Mantém foco da equipa no contrato da API e no ciclo de publicação SSG do frontend.

**Next.js** só deve entrar no mapa se houver decisão explícita de **micro-serviço** ou **app separada** (ex.: produto autônomo em outro repositório), integrada ao Laravel **só via API**.

## Frontend (site) + Admin (Laravel)

- **Repo do frontend (Astro):** marketing, SSG, performance — consome a API exposta pelo **repo admin (Laravel)** apenas **no build**; ver [integracao-astro-ssg-laravel.md](../conhecimento/integracao-astro-ssg-laravel.md).
- **Repo admin (Laravel + Filament):** API para o site, gestão de conteúdo e painel visual em `/admin`.

**Tailwind** unifica estilo entre Astro e o admin Filament, alinhado à [paleta de cores](paleta-cores.md).

Contrato HTTP entre frontend e admin no build: [contrato-api-build-time-slc.md](../conhecimento/contrato-api-build-time-slc.md).

## Área do cliente

**Fora do escopo imediato** do site institucional; evolui na stack Laravel quando priorizado.
