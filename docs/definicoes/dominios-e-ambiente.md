# Domínio, marca e ambiente

## Marca e presença online

| Conceito | Definição |
|----------|-----------|
| Nome comercial | Sousa Lima Consultoria |
| Domínio principal | `sousalimaconsultoria.com.br` |
| **URL canónica (decidido)** | **`https://sousalimaconsultoria.com.br`** — **sem** `www` como endereço principal. |
| `www` | **Redirecionamento 301** de `https://www.sousalimaconsultoria.com.br` → URL canónica (evita conteúdo duplicado no Google). Implementado no Traefik no serviço `frontend` do [`deploy/slc.yaml`](../../deploy/slc.yaml). |
| Logo institucional (arquivo) | `docs/sousa-lima-consultoria-logo-horizontal-colorido.png` |

### SEO e links internos

- Gerar sitemaps, `hreflang` (se houver multi-idioma no futuro) e **tags canónicas** no Astro apontando para o host **apex**.
- Evitar links internos misturando `www` e apex; padronizar **apex** em menus, CTAs e e-mails transacionais.

O apontamento DNS deve incluir registos **A** (ou **AAAA**) para o apex e para **`www`** (ambos para o mesmo destino de entrada do Traefik), para o certificado TLS e o redirecionamento funcionarem.

## Ambientes

| Ambiente | Propósito |
|----------|-----------|
| Produção | Tráfego real de usuários e clientes. |
| Homologação / staging | Testes antes de promover alterações (recomendado quando o time crescer). |

Valores sensíveis (senhas, chaves de API) não devem ser versionados; usar cofres de segredo ou variáveis injetadas no orquestrador.

## Variáveis de ambiente (conceito)

Sem amarrar a um framework específico, a aplicação web costuma precisar de:

| Tipo | Exemplos de preocupação |
|------|-------------------------|
| URL pública | Base usada em links e redirecionamentos — alinhar à **URL canónica** (apex). |
| Banco de dados | Host, porta, nome da base, utilizador, credencial. |
| Sessão e cache | Onde guardar sessão e cache em produção. |
| E-mail | SMTP ou provedor transacional, remetente padrão. |
| Multitenancy (se houver) | Domínio base para subdomínios de cliente e hosts extras para páginas centrais. |
| Build SSG (CI do **frontend**) | URL base da API do admin, **`API_READ_TOKEN`** (Bearer, leitura só para build), segredos do webhook de rebuild — ver [contrato-api-build-time-slc.md](../conhecimento/contrato-api-build-time-slc.md). |

Documente os nomes exatos das variáveis em **cada** repositório (frontend e admin) quando estiverem definidos.

## Glossário

| Termo | Uso neste projeto |
|-------|---------------------|
| **Central** | Área ou dados globais da operação (não específicos de um cliente isolado). |
| **Tenant** | Instância ou cliente com dados segregados, quando o modelo for multitenant. |
