# SLC Calculo Volume — Copilot Instructions

## Stack
Python (CLI/API) com testes e Docker.

## Regras de Execução
- NUNCA executar python/pip no host para tarefas do projeto.
- Rodar sempre no container do calculo-volume.

## Qualidade
- Manter tipagem e validações de entrada.
- Incluir ou atualizar testes para mudanças de regra de cálculo.
- Evitar regressão numérica; documentar suposições no código.

## Infra
- Build e execução via Dockerfile/compose do projeto.
- Não expor serviços internos desnecessários.
