# Base para publicação (futuro)

Documento de partida para quando o serviço for colocado em produção. Ajuste datas, nomes de stack e detalhes conforme a infraestrutura real.

## Objetivo

Disponibilizar o site e a aplicação web da **Sousa Lima Consultoria** com HTTPS, boa disponibilidade e processo de deploy repetível.

## Arquitetura alvo (visão geral)

| Camada | Função |
|--------|--------|
| **Entrada (proxy reverso)** | Terminação TLS, roteamento por hostname, redirecionamento HTTP → HTTPS. |
| **Servidor web** | Arquivos estáticos e encaminhamento de PHP (ou runtime equivalente) para a aplicação. |
| **Aplicação** | Lógica de negócio, painéis administrativos, APIs. |
| **Dados** | Banco relacional e, se necessário, cache e filas. |
| **Jobs** | Workers de fila e agendador de tarefas, se o produto exigir. |

Orquestração adotada para o momento: **Docker Swarm** com rede overlay para serviços internos e rede pública para o proxy ([planejamento-e-faltantes-site-institucional.md](planejamento-e-faltantes-site-institucional.md)).

**Stack de referência (frontend nginx, static, Laravel, worker):** [`deploy/slc.yaml`](../deploy/slc.yaml) — `docker stack deploy -c deploy/slc.yaml slc`. Exige Traefik com resolver `le`, rede `traefik-public` e secrets nomeados no próprio ficheiro; PostgreSQL e Redis devem existir onde `DB_HOST` / `REDIS_HOST` apontarem.

## Pré-requisitos no ambiente

- Cluster Swarm inicializado e nós com Docker atualizado.
- Rede overlay compartilhada com o proxy (ex.: nome reservado para tráfego externo).
- Proxy com suporte a **Let’s Encrypt** (ou outro emissor de certificados) e entrypoints para HTTP e HTTPS.
- **DNS**: registros A (e AAAA, se aplicável) apontando para o endereço público que recebe o tráfego.

## Checklist de publicação (rascunho)

1. Definir domínio canônico (com ou sem `www`) e política de redirecionamento.
2. Conferir variáveis de ambiente de produção (URL da app, segredos, conexões de banco e fila).
3. Subir ou atualizar a stack com réplicas e limites de recurso alinhados à carga esperada.
4. Validar certificado TLS e cabeçalhos de segurança (HSTS, conforme política).
5. Testar fluxos críticos (login, formulários, e-mails transacionais).

## Evolução desta base

Registre em `definicoes/` decisões fixas (nomes comerciais, SLA desejado, política de backup). Registre aqui em `conhecimento/` runbooks e incidentes quando começarem a ocorrer.

Normas técnicas de stack (SSG/ISR, imagens, terceiros, cabeçalhos de segurança): [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md).
