# Especificacao do Modulo de Calculo: Software de Resina para Marcenaria (V2)

## 1. Visao geral do modulo

Este modulo e responsavel pelo processamento de dados e calculo volumetrico de resina epoxi para projetos que combinam:
- moldes geometricos regulares;
- insercoes de materiais irregulares, como madeira live edge.

O modulo deve aceitar entradas manuais e, futuramente, entradas baseadas em visao computacional.

## 2. Parametros de entrada e variaveis de ambiente

### A. Dimensoes do molde (entrada manual)

Dados do projeto atual:
- diametro_mm: 1000
- altura_mm: 50

### B. Variaveis calculadas automaticamente (internas)

- raio_cm = diametro_mm / 20 = 50 cm
- altura_cm = altura_mm / 10 = 5 cm
- fator_seguranca = 1.15

Sugestao: 15% de margem devido a absorcao e perdas em superfices grandes de madeira.

### C. Parametros de processo e resina

- densidade_resina: 1.1 g/cm3 (idealmente configuravel)
- temperatura_alvo: 23 C

## 3. Logica matematica atualizada (1000 x 50 mm)

Esta secao define o backend dos calculos.

### Passo 1: volume bruto total (V_bruto)

O molde e tratado como um cilindro perfeito.

Formula:
- V = pi * r^2 * h

Valores:
- pi = 3.14159
- r = 50 cm
- h = 5 cm

Calculo:
- V_bruto = 3.14159 * (50^2) * 5
- V_bruto = 3.14159 * 2500 * 5
- V_bruto = 39,269.9 cm3

Conversao para litros:
- V_bruto_L = 39,269.9 / 1000 = 39.27 L

### Passo 2: area de cobertura da madeira (estimativa)

Para este exemplo, adota-se estimativa visual pela foto de referencia.

- proporcao_madeira_est = 0.80

### Passo 3: volume de deslocamento da madeira (V_madeira)

Formula:
- V_madeira = V_bruto_L * proporcao_madeira_est

Calculo:
- V_madeira = 39.27 * 0.80 = 31.42 L

### Passo 4: volume liquido de resina necessario (V_resina)

Formula:
- V_resina_liq = V_bruto_L - V_madeira

Calculo:
- V_resina_liq = 39.27 - 31.42 = 7.85 L

### Passo 5: aplicacao do fator de seguranca (V_total_sugestao)

Formula:
- V_total_sugestao = V_resina_liq * fator_seguranca

Calculo:
- V_total_sugestao = 7.85 * 1.15 = 9.03 L

Resultado sugerido para compra:
- 9.1 L

## 4. Integracao da visao computacional: roteiro para o software

### A. Fluxo do usuario

1. Usuario posiciona a madeira no molde final.
2. Usuario tira foto zenital (top-down), com todo o circulo visivel.
3. Usuario envia upload da imagem para o software.

### B. Processamento de imagem (backend)

1. Deteccao do circulo:
- usar OpenCV para detectar o circulo externo do molde;
- isso define a area total valida da analise.

2. Segmentacao:
- separar madeira e fundo do molde;
- opcoes: GrabCut, threshold assistido, ou modelo treinado.

3. Contagem de pixels:
- pixels_totais_circulo = pixels dentro do circulo detectado;
- pixels_madeira = pixels classificados como madeira.

4. Proporcao real da madeira:
- proporcao_madeira_real = pixels_madeira / pixels_totais_circulo

### C. Aplicacao no calculo

No calculo final, usar proporcao_madeira_real no lugar de proporcao_madeira_est.

Beneficio:
- reduz estimativa subjetiva;
- aumenta repetibilidade do resultado;
- diminui erro operacional.

## 5. Mockup de saida de dados (interface)

- Dimensoes do molde: 1000 mm x 50 mm
- Capacidade total do molde: 39.3 L
- Proporcao da madeira (analise de imagem): 79%
- Volume de resina puro: 8.3 L
- Fator de seguranca (15%): +1.2 L
- Total sugerido para o projeto: 9.5 L

## 6. Regras de validacao recomendadas

- diametro_mm > 0
- altura_mm > 0
- 0 <= proporcao_madeira <= 1
- fator_seguranca >= 1
- densidade_resina > 0

Regras adicionais:
- se V_madeira > V_bruto_L, rejeitar entrada por inconsistencia;
- se temperatura ambiente estiver fora da faixa recomendada da resina, emitir alerta;
- se altura ou volume exceder limites de exotermia do tipo de resina, emitir alerta de risco termico.

## 7. Contrato de dados sugerido (entrada/saida)

### Entrada (JSON)

{
  "diametro_mm": 1000,
  "altura_mm": 50,
  "proporcao_madeira": 0.80,
  "fator_seguranca": 1.15,
  "densidade_resina_g_cm3": 1.1,
  "temperatura_ambiente_c": 23,
  "resina": {
    "tipo": "deep_pour",
    "altura_maxima_cm": 5,
    "volume_maximo_l": 12
  }
}

### Saida (JSON)

{
  "volume_bruto_l": 39.27,
  "volume_madeira_l": 31.42,
  "volume_resina_liq_l": 7.85,
  "volume_total_sugerido_l": 9.03,
  "peso_estimado_kg": 9.93,
  "alertas": []
}

Observacao do peso:
- peso_estimado_kg = volume_total_sugerido_l * densidade_resina_g_cm3
