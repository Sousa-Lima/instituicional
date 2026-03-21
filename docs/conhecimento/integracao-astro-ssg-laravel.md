# Integração Astro (SSG) + Laravel — conteúdo e builds

O **site (Astro)** e o **admin (Laravel)** vivem em **repositórios separados** (não monorepo). O contrato entre eles é a **API** (URL base, autenticação de build se houver, formato JSON) e a **orquestração de CI** (webhook de um lado disparando pipeline do outro).

## Comportamento do SSG

No **Static Site Generation**, o Astro **consome a API Laravel (ou endpoints de conteúdo) apenas no momento do `build`**, não em cada visita do usuário. O HTML gerado é estático até o próximo deploy.

## Risco: site estático desatualizado

Se o conteúdo institucional (blog, serviços, cases) for gerido no **Laravel** e o Astro só buscar dados no build, **alterações no banco não aparecem no site** até que um **novo build** seja executado e publicado.

## Estratégias recomendadas

| Estratégia | Descrição |
|------------|-----------|
| **Webhook + CI/CD** | Ao salvar/publicar conteúdo no **admin (Laravel)**, disparar evento que aciona o **pipeline do repositório do frontend** (ex.: `repository_dispatch` no GitHub, pipeline filho no GitLab, ou URL de deploy) para **build Astro + deploy** (Swarm). Exige segredos/token entre os dois repos. |
| **Rebuild agendado** | Cron que roda build/deploy em intervalo fixo (menos imediato, mais simples). |
| **Rebuild manual** | Aceitável em fases iniciais; documentar o processo. |
| **SSR ou ISR no Astro** | Alternativa arquitetural: páginas dinâmicas ou revalidação incremental — **muda premissas** de custo e infra; avaliar se o ganho justifica sair do SSG puro. |

## Contrato de API (build time)

Detalhes normativos: endpoints (`GET /api/v1/content/slugs`), interfaces TypeScript, imagens (width/height), webhook de publicação, **Bearer** no build e sanitização — ver **[contrato-api-build-time-slc.md](contrato-api-build-time-slc.md)**.

## Resumo

- **SSG** = API no **build**, não no browser para conteúdo pré-renderizado.
- **Webhook (ou equivalente)** é a forma típica de não deixar o site “congelado” em relação ao Laravel.
- Ver também [contrato-api-build-time-slc.md](contrato-api-build-time-slc.md), [planejamento-e-faltantes-site-institucional.md](planejamento-e-faltantes-site-institucional.md) e [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md).
