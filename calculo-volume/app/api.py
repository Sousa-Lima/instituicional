from __future__ import annotations

from pathlib import Path
from typing import Dict

from fastapi import FastAPI, HTTPException, Query
from fastapi.responses import HTMLResponse, PlainTextResponse, RedirectResponse
from pydantic import BaseModel, Field

from app.image_analysis import estimar_proporcao_madeira
from app.service import calcular_river_table_por_proporcao, calcular_volume


class CalculoRequest(BaseModel):
    forma: str = Field(..., description="Nome da forma geométrica")
    parametros: Dict[str, float] = Field(
        ..., description="Parâmetros numéricos necessários para a forma"
    )


class CalculoResponse(BaseModel):
    forma: str
    volume: float
    unidade: str
    texto: str


class RiverCalculoRequest(BaseModel):
    diametro_mm: float = Field(..., gt=0)
    altura_mm: float = Field(..., gt=0)
    fator_seguranca: float = Field(default=1.15, gt=0)
    densidade_resina_g_cm3: float = Field(default=1.1, gt=0)
    temperatura_ambiente_c: float | None = None
    proporcao_madeira: float | None = Field(default=None, ge=0, le=1)
    imagem_path: str | None = None


class RiverCalculoResponse(BaseModel):
    volume_bruto_l: float
    proporcao_madeira: float
    volume_madeira_l: float
    volume_resina_liq_l: float
    volume_total_sugerido_l: float
    peso_estimado_kg: float
    alertas: list[str]
    texto: str


app = FastAPI(title="calculo-volume", version="0.1.0")
UI_INDEX_FILE = Path(__file__).resolve().parent / "ui" / "index.html"


@app.get("/health")
def health() -> Dict[str, str]:
    return {"status": "ok"}


@app.get("/")
def root() -> RedirectResponse:
    return RedirectResponse(url="/ui", status_code=307)


@app.get("/ui", response_class=HTMLResponse)
def ui() -> HTMLResponse:
    if not UI_INDEX_FILE.exists():
        raise HTTPException(status_code=500, detail="Arquivo da interface nao encontrado.")
    return HTMLResponse(UI_INDEX_FILE.read_text(encoding="utf-8"))


@app.post("/calcular", response_model=CalculoResponse)
def calcular(payload: CalculoRequest) -> CalculoResponse:
    try:
        resultado = calcular_volume(payload.forma, payload.parametros)
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc

    return CalculoResponse(
        forma=resultado.forma,
        volume=resultado.volume,
        unidade=resultado.unidade,
        texto=resultado.texto(),
    )


@app.get("/calcular-texto", response_class=PlainTextResponse)
def calcular_texto(
    forma: str,
    largura: float | None = Query(default=None),
    altura: float | None = Query(default=None),
    profundidade: float | None = Query(default=None),
    lado: float | None = Query(default=None),
    raio: float | None = Query(default=None),
) -> str:
    parametros = {
        "largura": largura,
        "altura": altura,
        "profundidade": profundidade,
        "lado": lado,
        "raio": raio,
    }

    parametros_filtrados = {k: v for k, v in parametros.items() if v is not None}

    try:
        resultado = calcular_volume(forma, parametros_filtrados)
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc

    return resultado.texto()


@app.post("/river/calcular", response_model=RiverCalculoResponse)
def calcular_river(payload: RiverCalculoRequest) -> RiverCalculoResponse:
    proporcao_madeira = payload.proporcao_madeira

    if payload.imagem_path:
        try:
            proporcao_madeira = estimar_proporcao_madeira(payload.imagem_path)
        except (FileNotFoundError, ValueError) as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc

    if proporcao_madeira is None:
        raise HTTPException(
            status_code=400,
            detail="Informe proporcao_madeira ou imagem_path para calcular river table.",
        )

    try:
        resultado = calcular_river_table_por_proporcao(
            diametro_mm=payload.diametro_mm,
            altura_mm=payload.altura_mm,
            proporcao_madeira=proporcao_madeira,
            fator_seguranca=payload.fator_seguranca,
            densidade_resina_g_cm3=payload.densidade_resina_g_cm3,
            temperatura_ambiente_c=payload.temperatura_ambiente_c,
        )
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc

    return RiverCalculoResponse(
        volume_bruto_l=resultado.volume_bruto_l,
        proporcao_madeira=resultado.proporcao_madeira,
        volume_madeira_l=resultado.volume_madeira_l,
        volume_resina_liq_l=resultado.volume_resina_liq_l,
        volume_total_sugerido_l=resultado.volume_total_sugerido_l,
        peso_estimado_kg=resultado.peso_estimado_kg,
        alertas=resultado.alertas,
        texto=resultado.texto(),
    )


@app.get("/river/calcular-texto", response_class=PlainTextResponse)
def calcular_river_texto(
    diametro_mm: float = Query(..., gt=0),
    altura_mm: float = Query(..., gt=0),
    fator_seguranca: float = Query(default=1.15, gt=0),
    densidade_resina_g_cm3: float = Query(default=1.1, gt=0),
    temperatura_ambiente_c: float | None = Query(default=None),
    proporcao_madeira: float | None = Query(default=None, ge=0, le=1),
    imagem_path: str | None = Query(default=None),
) -> str:
    proporcao = proporcao_madeira

    if imagem_path:
        try:
            proporcao = estimar_proporcao_madeira(imagem_path)
        except (FileNotFoundError, ValueError) as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc

    if proporcao is None:
        raise HTTPException(
            status_code=400,
            detail="Informe proporcao_madeira ou imagem_path para calcular river table.",
        )

    try:
        resultado = calcular_river_table_por_proporcao(
            diametro_mm=diametro_mm,
            altura_mm=altura_mm,
            proporcao_madeira=proporcao,
            fator_seguranca=fator_seguranca,
            densidade_resina_g_cm3=densidade_resina_g_cm3,
            temperatura_ambiente_c=temperatura_ambiente_c,
        )
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc

    return resultado.texto()
