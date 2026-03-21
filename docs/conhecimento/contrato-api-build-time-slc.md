# Contrato de API — build-time (Astro) ↔ admin (Laravel)

Proposta técnica alinhada ao [stack-tecnico-slc.md](../definicoes/stack-tecnico-slc.md), [integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md), [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md) e [qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md).

**Conteúdo editorial (ex.: Home):** textos acordados em [copy-home-v1-slc.md](copy-home-v1-slc.md) devem ser **armazenados no Laravel** e **entregues via API** para o Astro **no `build`** (SSG). Assim o HTML estático já inclui copy e estrutura principal, favorecendo **LCP** (pintura precoce do conteúdo) e **INP** (menos JS obrigatório na primeira interação para exibir texto).

---

## 1. Endpoints de build-time (descoberta de caminhos)

Para o Astro gerar rotas estáticas (**SSG**), o build precisa saber **quais URLs existem**.

| Item | Definição |
|------|-----------|
| **Endpoint** | `GET /api/v1/content/slugs` |
| **Função** | Listar slugs **ativos** de serviços, cases e posts do blog (ou equivalente no modelo de dados). |
| **Resposta** | Lista **enxuta**: idealmente apenas **`slug`**, **`updated_at`** (ISO 8601) e identificador do **tipo** de recurso (ex.: `kind: "service" | "case" | "post"`), para cache e invalidação. |
| **Boas práticas** | Endpoint **leve** (sem corpo pesado); paginação se a lista crescer muito. |

O Astro usa esse retorno em `getStaticPaths` (ou equivalente) para gerar uma página por slug.

---

## 2. Contrato de dados (TypeScript strict)

O frontend (**Astro**, TypeScript **`strict`**) deve espelhar o contrato da API para o build **falhar cedo** se faltar campo obrigatório.

### Exemplo: serviços (referência Sagitta / Venturus — cards + página interna)

```typescript
/** Contrato de API — entidade Serviço (exemplo) */
interface ServiceContract {
  id: string;
  slug: string;
  title: string;
  short_description: string; // cards da home / listagens
  content_html: string; // corpo rico na página interna
  icon_name: string; // ex.: ícone Lucide / mapeamento Shadcn
  category: 'consultoria' | 'software' | 'cloud';
  seo: {
    meta_title: string;
    meta_description: string;
    og_image: string;
  };
  order: number; // ordenação na vitrine
}
```

Estender o mesmo rigor a **cases** e **posts** com interfaces dedicadas (`CaseContract`, `PostContract`, etc.).

---

## 3. Imagens e performance (LCP / CLS)

| Regra | Detalhe |
|-------|---------|
| **Contrato** | A API **não** deve enviar só a URL da imagem: incluir **`width`** e **`height`** (dimensões originais ou de exibição planejada). |
| **Motivo** | Permite ao **`<Image />` do Astro** (e `aspect-ratio` / reserva de espaço) reduzir **CLS** e melhorar **LCP** ao priorizar o recurso certo — ver [qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md). |

Exemplo de campo em anexo a `seo` ou objeto de mídia:

```typescript
hero_image?: {
  url: string;
  width: number;
  height: number;
  alt: string;
};
```

(Ajustar nomes ao modelo real do Laravel.)

---

## 4. Gatilho de rebuild (webhook de publicação)

O site é **estático**: após alterar conteúdo no **admin**, é preciso **novo build + deploy**.

| Item | Definição |
|------|-----------|
| **Fluxo** | No admin, ação **“Publicar”** dispara um **Job** Laravel. |
| **Destino** | URL de **dispatch** do CI do **repositório frontend** (GitHub Actions `repository_dispatch`, GitLab `trigger`, etc.) para iniciar build Astro e deploy no **Docker Swarm**. |

### Payload sugerido (JSON)

```json
{
  "event": "content.published",
  "model": "Service",
  "id": "123",
  "timestamp": "2023-10-27T10:00:00Z",
  "secret": "TOKEN_DE_SEGURANCA_SLC"
}
```

- **`secret`:** valor conhecido só pelo admin e pelo pipeline (armazenado como **secret** no CI). Validar com comparação **timing-safe**; preferir **HTTPS** apenas.
- Alternativa: assinatura **HMAC** no header em evoluções futuras.

Rebuild completo é aceitável no início; otimizações (build parcial) podem vir depois.

---

## 5. Segurança e cabeçalhos

### Autenticação do build (consumo da API pelo Astro)

| Item | Definição |
|------|-----------|
| **Método** | Header **`Authorization: Bearer <token>`** com token de **leitura** só para o pipeline de build. |
| **Config** | Variável **`API_READ_TOKEN`** (ou nome equivalente) **no CI do frontend**, nunca commitada. |
| **Escopo** | Token apenas para endpoints necessários ao SSG (`/api/v1/content/...`). |

### Sanitização de HTML

| Responsável | Ação |
|-------------|------|
| **Laravel (admin)** | Sanitizar **`content_html`** na gravação (ex.: lista permitida de tags/atributos, ou biblioteca de sanitização). |
| **Astro** | Ao renderizar HTML vindo da API, usar mecanismo que **não** trate string como HTML confiável sem critério (evitar XSS); alinhar com **CSP** abaixo. |

### Content Security Policy (CSP)

Definir política no **servidor que entrega o site estático** e/ou **edge**, coerente com scripts permitidos (analytics após consentimento — [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md)) e com [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md).

---

## Evolução

Versionar a API em **`/api/v1/`**; mudanças que quebram o contrato exigem deploy coordenado ou janela de compatibilidade documentada entre os **dois repositórios**.
