# Formulário de contato segmentado — leads B2B

Proposta para **fechar o ciclo de conversão** nas páginas de serviço e no [mapa-site-mvp-slc.md](mapa-site-mvp-slc.md) (`/contato`). O formulário é uma **ilha de interatividade** no **Astro** (React + Shadcn ou componente nativo com JS mínimo), enviando dados ao **Laravel** em tempo de execução (**não** no build SSG).

Alinhado a [copy-servicos-verticais-v1-slc.md](copy-servicos-verticais-v1-slc.md), [diretrizes-qualidade-site-slc.md](../definicoes/diretrizes-qualidade-site-slc.md), [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md), [qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md) e [projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md).

---

## 1. Estrutura UX e conversão

O formulário deve ser **contextual**: o campo de **interesse** vem **pré-selecionado** conforme a vertical da página atual (ex.: página de Software → “Desenvolvimento de software / MVP”).

### Dados de identificação

| Campo | Regra |
|-------|--------|
| **Nome completo** | Texto, obrigatório. |
| **E-mail corporativo** | Obrigatório; validação para **preferir domínio empresarial** (heurística ou lista de domínios genéricos a alertar — reforço B2B). |
| **Empresa** | Obrigatório. |
| **Cargo** | Obrigatório (qualificação **enterprise**). |

### Segmentação de interesse (select)

| Valor enviado à API (`interest`) | Rótulo ao utilizador |
|----------------------------------|----------------------|
| `process` | Consultoria de processos |
| `software` | Desenvolvimento de software / MVP |
| `cloud` | Cloud e infraestrutura |

Pré-seleção por rota: mapear `/servicos/...` → um dos três valores.

### Qualificação de projeto

| Campo | Opções |
|-------|--------|
| **Estágio do negócio** | Ideação · Validação · Escala · Operação consolidada (valores estáveis no JSON abaixo). |
| **Mensagem / desafio** | Texto livre (problema técnico ou de negócio). |

---

## 2. Implementação técnica (Astro + Laravel)

| Tema | Orientação |
|------|------------|
| **Ilha** | `client:visible` ou `client:load` apenas no bloco do formulário para não inflacionar JS na primeira pintura — favorece **LCP** e **INP**. |
| **Feedback (INP)** | Estado “A carregar…” / “Enviado” visível em **menos de 100 ms** após o envio — [diretrizes-qualidade-site-slc.md](../definicoes/diretrizes-qualidade-site-slc.md). |
| **Acessibilidade (WCAG 2.1 AA)** | Cada campo com **`<label>`** associado (`for`/`id`); erros com texto claro e **contraste** adequado — [projeto-astro-laravel-institutional.md](projeto-astro-laravel-institutional.md). |
| **LGPD** | Checkbox obrigatória: *“Li e aceito a [Política de privacidade](/politica-de-privacidade)”* (link real); sem marcar, não envia — [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md). |
| **Segurança** | **CSRF** (token Laravel / cookie de sessão ou Sanctum conforme desenho); **reCAPTCHA v3** (ou equivalente) preferencialmente **carregado após** primeira interação no formulário ou sob consentimento, para não prejudicar **LCP** na página. |

---

## 3. Endpoint — `POST /api/v1/lead/contact`

**Uso:** submissão pública de lead (runtime). Autenticação: conforme política (ex.: apenas CSRF + rate limit + captcha; **não** usar `API_READ_TOKEN` do build).

### Corpo JSON (contrato mínimo + campos alinhados ao UX)

```json
{
  "name": "string",
  "email": "string",
  "company": "string",
  "job_title": "string",
  "interest": "process | software | cloud",
  "business_stage": "ideation | validation | scale | operations",
  "message": "string",
  "consent_lgpd": true,
  "source_path": "/servicos/desenvolvimento-software"
}
```

| Campo | Obrigatório | Notas |
|-------|-------------|--------|
| `name`, `email`, `company`, `job_title` | Sim | B2B. |
| `interest` | Sim | Enum alinhado ao select. |
| `business_stage` | Sim | Alinha MVP ao estágio. |
| `message` | Recomendado | Pode ser obrigatório no front. |
| `consent_lgpd` | Sim | Deve ser `true` para aceitar. |
| `source_path` | Opcional | Rota de origem para analytics e CRM. |

Versão **mínima** se o primeiro slice for mais enxuto (compatível com a proposta inicial):

```json
{
  "name": "string",
  "email": "string",
  "company": "string",
  "interest": "software",
  "message": "string",
  "consent_lgpd": true
}
```

Evoluir para o corpo completo quando `job_title` e `business_stage` estiverem no front.

### Respostas esperadas

- **201** — lead criado; corpo opcional com `id` do lead.
- **422** — erros de validação (campos por chave).
- **429** — rate limit.

---

## 4. Relação com o restante da documentação

- Contrato agregado da API: [contrato-api-build-time-slc.md](contrato-api-build-time-slc.md) §6.
- CORS e `APP_URL` do site: [dominios-e-ambiente.md](../definicoes/dominios-e-ambiente.md).

Com isto, o fluxo **página de serviço → CTA → formulário → Laravel** fica especificado para implementação nos **dois repositórios**.
