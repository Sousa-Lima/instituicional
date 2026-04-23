# SLC Admin — Copilot Instructions

## Stack
Laravel + Filament (admin).

## Regras de Execução
- NUNCA executar php/composer/artisan no host.
- Sempre executar comandos no container do admin.
- Rodar migrations/testes/lint somente dentro do container.

## Filament
- Seguir padrão do projeto para Resources/Pages.
- Evitar queries sem escopo apropriado de autorização.

## Infra e Segurança
- Banco/cache internos em produção.
- HTTPS obrigatório quando publicado.
- Validar entradas com Form Requests e políticas.
