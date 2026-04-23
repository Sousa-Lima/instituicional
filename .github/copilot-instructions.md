# SLC Monorepo — Copilot Instructions

## Contexto
Monorepo com múltiplos subprojetos: admin (Laravel), frontend (Astro) e calculo-volume (Python).

## Regras Gerais
- NUNCA executar php/composer/artisan/npm/node/python/pip no host para tarefas dos apps.
- Executar sempre em container do subprojeto correspondente.
- Em produção, usar Docker Swarm + Traefik quando aplicável.

## Isolamento
- Cada subdiretório possui stack e comandos próprios.
- Não misturar dependências, envs e comandos entre admin/frontend/calculo-volume.

## Segurança
- Não expor banco/cache internamente críticos ao host em produção.
- HTTPS obrigatório em serviços públicos.

## Google Analytics (GA4)

**Por subprojeto**:

### Admin (Laravel)
- Config: `config/services.php` → `'google_analytics' => ['measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID')]`
- Partial: `resources/views/partials/google-analytics.blade.php`
- Include: `@include('partials.google-analytics')`
- Env: `GOOGLE_ANALYTICS_MEASUREMENT_ID=G-XXXXXXXXXX` em `.env.production`

### Frontend (Astro)
- Variável: `PUBLIC_GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX`
- Componente: `src/components/GoogleAnalytics.astro`
- Layout: Include em `<head>`

### Calculo-Volume (Python)
- Não é necessário (API interna)
