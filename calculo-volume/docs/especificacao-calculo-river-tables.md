# Especificacao de Calculo - River Tables e Woodworking

## Objetivo

Definir a logica matematica e os requisitos tecnicos para estimar volume de resina em projetos com:
- Molde geometrico (cilindrico na versao atual).
- Madeira irregular ocupando parte do volume.

Esta especificacao orienta o microservico Python do projeto `calculo-volume`.

## 1) Variaveis de Entrada (Inputs)

### A. Dimensoes do Molde (Cilindro)

- `D` (diametro) em cm.
- `R` (raio) em cm, onde `R = D / 2`.
- `H` (altura/espessura do vazamento) em cm.

Exemplo base:
- `D = 100 cm`
- `R = 50 cm`
- `H = 10 cm`

### B. Dimensoes da Madeira (Aproximacao Retangular)

- `L_max` (comprimento maximo da madeira) em cm.
- `W_med` (largura media) em cm.
- `T_w` (espessura efetiva da madeira dentro da resina) em cm.

Calculo sugerido para largura media:

`W_med = (W1 + W2 + W3 + ... + Wn) / n`

Recomendacao:
- Usar no minimo 3 pontos de largura (`n >= 3`).

## 2) Logica Matematica

### Passo 1: Volume Total do Recipiente (`V_t`)

O molde e tratado como cilindro perfeito:

`V_t = pi * R^2 * H`

Exemplo aplicado:
- `V_t = 3.14159 * 50^2 * 10 = 78_539.75 cm^3`
- Conversao: `78_539.75 cm^3 = 78.54 L`

### Passo 2: Volume de Deslocamento da Madeira (`V_m`)

Estimativa por area de cobertura superficial:

`V_m = (L_max * W_med) * T_w`

Observacao:
- Esta e uma aproximacao inicial pratica.
- Em geometrias muito irregulares, o erro pode aumentar.

### Passo 3: Volume Liquido de Resina (`V_r`)

`V_r = (V_t - V_m) * k`

Onde:
- `k` = coeficiente de seguranca.
- Sugestao inicial: `k = 1.10` (10% de sobra).

Regras de validacao:
- Se `V_m > V_t`, retornar erro de entrada inconsistente.
- Se `V_r < 0`, retornar erro e solicitar revisao das medidas.

## 3) Conversoes de Unidade

### cm^3 para Litros

`Volume (L) = Volume (cm^3) / 1000`

### Litros para Kg (massa de resina)

Entrada adicional necessaria:
- `densidade_resina` (em g/cm^3), por exemplo `1.1`.

Como `1 g/cm^3 = 1 kg/L`, a formula pratica e:

`Peso (kg) = Volume (L) * densidade_resina`

Exemplo:
- `Volume = 29.19 L`
- `densidade = 1.1 kg/L`
- `Peso = 32.11 kg`

## 4) Tratamento de Irregularidade (Fase Evolutiva)

Para maior precisao, implementar modo por proporcao de area via imagem zenital:

1. Usuario envia foto superior do molde com madeira.
2. Software detecta area util dentro do circulo do molde.
3. Software calcula proporcao de pixels ocupados por madeira (`p_madeira`).
4. Volume da madeira por proporcao:

`V_m_img = V_t * p_madeira`

5. Volume de resina:

`V_r_img = (V_t - V_m_img) * k`

Notas tecnicas:
- Exigir calibracao da imagem (escala real por diametro conhecido).
- Aplicar mascara circular para limitar calculo ao interior do molde.
- Permitir ajuste manual da segmentacao para melhorar confiabilidade.

## 5) Parametros de Processo (Regras de Negocio)

### Temperatura de trabalho

- Referencia operacional: `23 C` estavel.
- O sistema deve exibir aviso quando temperatura informada estiver fora da faixa recomendada pelo fabricante.

### Exotermia e limite termico

Cada tipo de resina deve ter parametros:
- `tipo_resina`: `deep_pour` ou `coating`.
- `h_max_cm`: altura maxima por camada.
- `volume_max_l`: volume maximo por derramamento.

Regra:
- Alertar risco termico se `H > h_max_cm` ou se `V_r` exceder `volume_max_l`.

### Fator de absorcao da madeira

Madeiras porosas podem absorver parte da resina no primer/selagem inicial.

Parametro adicional:
- `a` = fator de absorcao no intervalo recomendado de `0.02` a `0.05`.

Aplicacao:

`V_final = V_r * (1 + a)`

Exemplo com 3%:
- `a = 0.03`
- `V_final = V_r * 1.03`

## 6) Exemplo de Saida (Mock)

- Volume Bruto: `78.54 L`
- Volume Madeira (estimado): `52.00 L`
- Volume Resina Puro: `26.54 L`
- Total com Seguranca (10%): `29.19 L`

Opcional com absorcao (3%):
- Total Final: `30.07 L`

## 7) Contrato de Dados Sugerido para o Microservico

### Entrada (payload)

```json
{
  "molde": {
    "formato": "cilindro",
    "diametro_cm": 100,
    "altura_cm": 10
  },
  "madeira": {
    "comprimento_max_cm": 80,
    "larguras_cm": [50, 45, 42],
    "espessura_cm": 10
  },
  "coeficiente_seguranca": 1.10,
  "densidade_resina_g_cm3": 1.1,
  "fator_absorcao": 0.03,
  "resina": {
    "tipo": "deep_pour",
    "h_max_cm": 5,
    "volume_max_l": 20
  },
  "temperatura_ambiente_c": 23
}
```

### Saida (payload)

```json
{
  "volume_total_cm3": 78539.75,
  "volume_total_l": 78.54,
  "volume_madeira_cm3": 52000.0,
  "volume_resina_puro_l": 26.54,
  "volume_resina_com_seguranca_l": 29.19,
  "volume_final_l": 30.07,
  "peso_estimado_kg": 33.08,
  "alertas": [
    "Risco termico: altura informada acima do limite da resina selecionada"
  ]
}
```

## 8) Criticos de Implementacao

- Manter todas as contas internas em `cm^3` e converter apenas na apresentacao.
- Arredondar na saida (2 casas para litros/kg), sem perder precisao interna.
- Validar entradas obrigatorias e valores positivos.
- Registrar alertas de processo separadamente de erros bloqueantes.
- Versionar esta especificacao ao evoluir o algoritmo (imagem, novos moldes, novas resinas).
