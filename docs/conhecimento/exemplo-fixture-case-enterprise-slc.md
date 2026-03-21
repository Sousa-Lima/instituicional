# Exemplo de fixture — Case study “Enterprise” (JSON)

**Finalidade:** validar **robustez do layout** no Astro com um payload completo do contrato **`CaseStudy`** ([contrato-api-build-time-slc.md](contrato-api-build-time-slc.md) §2.2): SEO, métricas, imagem com dimensões e HTML rico — alinhado ao [mapa-site-mvp-slc.md](mapa-site-mvp-slc.md) (cases: problema → solução → resultado), [conteudo-mensagens-referencia.md](conteudo-mensagens-referencia.md) (métricas e autoridade), referência [Venturus](referencia-layout-sites.md) para blocos numéricos, e [projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md) (JSON-LD `Organization` / `ProfessionalService`).

**Dados fictícios** — não representam cliente real; substituir em produção.

---

## JSON de exemplo

```json
{
  "id": "uuid-slc-2024-001",
  "slug": "modernizacao-plataforma-logistica-ai",
  "status": "published",
  "featured": true,

  "title": "Modernização de ecossistema logístico com arquitetura de micro-serviços",
  "customer_name": "LogTech Brasil S.A.",
  "sector": "Logística e transportes",
  "short_summary": "Redução drástica de downtime e escalabilidade para suportar +200% de volume de pedidos em datas sazonais.",

  "content_html": "<section><h2>O desafio</h2><p>A LogTech enfrentava gargalos críticos durante a Black Friday, com latência de API superior a 2 segundos e quedas constantes no processamento de pedidos.</p></section><section><h2>Nossa solução</h2><p>Implementamos uma nova camada de backend em <strong>Laravel 12</strong> integrada a um frontend <strong>Astro</strong> (SSG) para consultas rápidas. A infraestrutura foi orquestrada em <strong>Docker Swarm</strong>, alinhada à base de publicação da SLC.</p></section><section><h2>O resultado</h2><p>O sistema foi entregue em 75 dias (fase 1), permitindo que a empresa operasse com 99,9% de uptime no período de maior carga do ano.</p></section>",

  "metrics": [
    { "label": "Redução de latência", "value": "85%" },
    { "label": "Aumento de conversão", "value": "12%" },
    { "label": "Tempo de entrega (MVP)", "value": "75 dias" }
  ],

  "main_image": {
    "url": "https://api.sousalimaconsultoria.com.br/storage/cases/logtech-hero.png",
    "alt": "Dashboard de monitoramento logístico em tempo real desenvolvido pela Sousa Lima Consultoria",
    "width": 1200,
    "height": 630
  },

  "seo": {
    "meta_title": "Case LogTech: modernização de software | Sousa Lima Consultoria",
    "meta_description": "Veja como a SLC ajudou a LogTech a reduzir o tempo de resposta em 85% e escalar a operação para a Black Friday.",
    "og_image": "https://api.sousalimaconsultoria.com.br/storage/cases/og-logtech.png",
    "keywords": [
      "Consultoria de software",
      "Laravel",
      "Astro SSG",
      "Docker Swarm",
      "Logística"
    ]
  }
}
```

*(Domínio da API alinhado a [dominios-e-ambiente.md](../definicoes/dominios-e-ambiente.md); ajustar ambiente de staging se necessário.)*

---

## Notas para teste de layout no Astro

| Tema | Orientação |
|------|------------|
| **Performance (LCP / CLS)** | Usar `main_image.width` e `main_image.height` no **`<Image />` do Astro** para reservar espaço e limitar CLS — [qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md), [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md). |
| **Acessibilidade** | Repassar `main_image.alt` ao elemento de imagem (ou equivalente otimizado) para **WCAG 2.1 AA** — [projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md). |
| **Métricas B2B** | O array `metrics` alimenta blocos de credibilidade no estilo enterprise ([Venturus](referencia-layout-sites.md)); coerente com [conteudo-mensagens-referencia.md](conteudo-mensagens-referencia.md). |
| **SEO** | `seo` gera meta tags; combinar com **JSON-LD** (`Organization`, `ProfessionalService`, `LocalBusiness` quando aplicável) no template da página de case — [mapa-site-mvp-slc.md](mapa-site-mvp-slc.md), [projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md). |
| **HTML rico** | `content_html` deve ser **sanitizado no Laravel** e renderizado no Astro com cuidado (XSS / CSP) — [contrato-api-build-time-slc.md](contrato-api-build-time-slc.md) §5. |

Este fixture pode ser salvo como ficheiro em `src/fixtures/` ou usado em testes E2E quando o repositório Astro existir.
