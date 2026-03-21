# Compliance — ferramentas (LGPD e medição)

Objetivo: atender **LGPD** e boas práticas de **privacidade** sem abandonar métricas úteis para evolução do site.

## Consentimento de cookies — implementação técnica

O consentimento **não** pode ser só texto jurídico na página. É necessário:

| Requisito | Detalhe |
|-----------|---------|
| **Bloqueio prévio** | Scripts de analytics/marketing **não** devem carregar antes do consentimento nas categorias exigidas (exceto estritamente necessários). |
| **Registro da escolha** | Persistir preferência (cookie/local seguro ou backend) e respeitar em visitas seguintes. |
| **Revogação** | Link “gerenciar cookies” ou equivalente para alterar a escolha. |
| **Integração com CSP** | Ajustar **Content-Security-Policy** para permitir apenas origens necessárias após opt-in ([normas-arquitetura-backend-infra.md](../conhecimento/normas-arquitetura-backend-infra.md)). |

Ferramentas que encapsulam isso:

| Tipo | Ferramentas / abordagens típicas |
|------|-----------------------------------|
| **Banner / gestão de consentimento** | **Cookiebot**, **OneTrust**, **Osano**, **Consent Manager**, ou implementação própria com a mesma lógica de **gate** de scripts. |
| **Categorias** | Necessários / preferências / analytics / marketing — **injetar** GTM/GA apenas após opt-in quando aplicável. |

## Analytics e performance real

| Tipo | Ferramentas / abordagens típicas |
|------|-----------------------------------|
| **Analytics com modo privacidade** | **Google Analytics 4** com **Consent Mode**; **Plausible** / **Fathom** / **Matomo** como alternativas mais enxutas. |
| **Core Web Vitals em produção** | **Search Console** (CrUX); opcional **web-vitals** no front para endpoint próprio com dados minimizados. |

## Documentos legais

- **Política de privacidade**, **Termos de uso** e **Política de cookies** com base jurídica revisada por profissional habilitado.
- **Registro de atividades de tratamento** e canal de titulares conforme política interna da SLC.

## Segurança de cabeçalhos

Manter alinhamento com [normas-arquitetura-backend-infra.md](../conhecimento/normas-arquitetura-backend-infra.md) (CSP, HSTS, framing).

Atualizar esta lista quando a stack de deploy (Swarm) e os domínios estiverem fixos.
