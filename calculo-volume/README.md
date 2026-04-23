# calculo-volume

Microserviço Python para cálculo de volume geométrico.

Status atual:
- Entrada via CLI e API HTTP.
- Saída textual (texto simples), conforme fase inicial.
- Preparado para evolução futura para frontend Web.

## Especificacao tecnica

- Documento principal: `docs/especificacao-calculo-river-tables.md`
- Conteudo: formulas de calculo, regras de negocio, conversoes de unidade e contrato de dados sugerido.
- Versao V2 (cenario 1000 mm x 50 mm + visao computacional): `especificacao_modulo_calculo_V2.md`

## Formas suportadas

- `caixa`: requer `largura`, `altura`, `profundidade`
- `cubo`: requer `lado`
- `cilindro`: requer `raio`, `altura`
- `esfera`: requer `raio`

## Requisitos

- Python 3.11+

## Deploy remoto (Docker Swarm + Traefik)

Para publicar em `volume.sousalimaconsultoria.com.br`, use o manifesto:
- `deploy/volume-swarm.yaml`

### 1) Build e push da imagem

```bash
cd /srv/sistemas/slc/calculo-volume
docker build -t eolimabr/calculo-volume:latest .
docker push eolimabr/calculo-volume:latest
```

### 2) Deploy da stack no manager Swarm

```bash
cd /srv/sistemas/slc/calculo-volume
docker stack deploy -c deploy/volume-swarm.yaml slc-volume
```

### 3) Validacao

```bash
docker service ls | grep slc-volume
docker service logs -f slc-volume_volume_app
curl https://volume.sousalimaconsultoria.com.br/health
```

### 4) DNS

- Criar/ajustar registro `A` (ou `CNAME`) de `volume.sousalimaconsultoria.com.br` para o IP publico do manager com Traefik.
- O certificado TLS e emitido automaticamente pelo Traefik (`certresolver=le`).

## Instalação

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

## Uso via CLI (saída texto)

```bash
python cli.py caixa --largura 2 --altura 3 --profundidade 4
python cli.py cubo --lado 5
python cli.py cilindro --raio 2 --altura 10
python cli.py esfera --raio 3

# River table (manual por proporcao)
python cli.py river --diametro-mm 1000 --altura-mm 50 --proporcao-madeira 0.80

# River table (via imagem)
python cli.py river --diametro-mm 1000 --altura-mm 50 --imagem input-images/exemplo.png

# River table (via imagem + artefatos visuais da analise)
python cli.py river --diametro-mm 1000 --altura-mm 50 --imagem input-images/exemplo.png --salvar-etapas

# Definindo diretorio de saida dos artefatos
python cli.py river --diametro-mm 1000 --altura-mm 50 --imagem input-images/exemplo.png --salvar-etapas --saida-etapas analysis-output/caso-01
```

Interface grafica de terminal (modo interativo):

```bash
python cli.py tui
```

O modo TUI oferece:
- menu por opcoes numericas;
- formulario guiado para river table;
- painel textual com barras de proporcao de madeira e resina.

Regra de negocio ativa para o modo river:
- a proporcao de madeira deve ser maior que 50% (resina sempre menor que madeira).

Diretorio de imagens para teste:
- `input-images/`

Diretorio padrao dos artefatos da analise:
- `analysis-output/<nome_da_imagem>/`

Arquivos gerados por analise:
- `01_original_anotada.png`
- `02_mascara_molde.png`
- `03_mascara_madeira.png`
- `04_overlay_classificacao.png`

## Uso via API

Executar:

```bash
uvicorn app.api:app --host 0.0.0.0 --port 8080
```

Interface grafica (marcacao manual de madeira e furos):

1. Suba o servico FastAPI.
2. Abra no navegador: `http://localhost:8080/ui`
3. Carregue a imagem.
4. Marque com pincel:
   - madeira (verde)
   - furos/resina (vermelho)
5. Clique em `Calcular` para obter volume e sugestao.

Observacao:
- A interface aplica a regra de negocio de que resina deve ser menor que madeira,
  usando `madeira_efetiva = madeira_marcada - furos_marcados`.

Healthcheck:

```bash
curl http://localhost:8080/health
```

Resposta em texto:

```bash
curl "http://localhost:8080/calcular-texto?forma=caixa&largura=2&altura=3&profundidade=4"
```

Resposta JSON (útil para integração futura):

```bash
curl -X POST http://localhost:8080/calcular \
  -H "content-type: application/json" \
  -d '{"forma":"caixa","parametros":{"largura":2,"altura":3,"profundidade":4}}'
```

Endpoint especifico para river table:

```bash
curl -X POST http://localhost:8080/river/calcular \
  -H "content-type: application/json" \
  -d '{
    "diametro_mm":1000,
    "altura_mm":50,
    "proporcao_madeira":0.80,
    "fator_seguranca":1.15,
    "densidade_resina_g_cm3":1.1
  }'
```

Endpoint texto (river):

```bash
curl "http://localhost:8080/river/calcular-texto?diametro_mm=1000&altura_mm=50&proporcao_madeira=0.80"
```

## Estrutura

- `app/service.py`: regra de negócio do cálculo.
- `app/api.py`: endpoints FastAPI.
- `cli.py`: interface de linha de comando com saída textual.

## Próxima evolução

- Expor endpoint para uso por frontend Web.
- Adicionar persistência de histórico de cálculos.
- Adicionar autenticação se necessário.

## Testes

Rodar testes automatizados:

```bash
pytest -q
```
