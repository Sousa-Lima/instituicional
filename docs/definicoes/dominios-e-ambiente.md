# Domínio, marca e ambiente

## Marca e presença online

| Conceito | Definição (preencher) |
|----------|------------------------|
| Nome comercial | Sousa Lima Consultoria |
| Domínio principal | `sousalimaconsultoria.com.br` |
| Uso de `www` | A definir (canônico com ou sem www) |
| Logo institucional (arquivo) | `docs/sousa-lima-consultoria-logo-horizontal-colorido.png` |

O apontamento DNS deve seguir o endereço IP ou hostname fornecido pela hospedagem ou pelo provedor de infraestrutura no momento do go-live.

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
| URL pública | Base usada em links e redirecionamentos. |
| Banco de dados | Host, porta, nome da base, usuário, credencial. |
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
