from __future__ import annotations

from dataclasses import dataclass
from math import pi
from typing import Dict


@dataclass(frozen=True)
class ResultadoCalculo:
    forma: str
    volume: float
    unidade: str = "u^3"

    def texto(self) -> str:
        return f"Volume da forma '{self.forma}': {self.volume:.4f} {self.unidade}"


@dataclass(frozen=True)
class ResultadoRiverTable:
    volume_bruto_l: float
    proporcao_madeira: float
    volume_madeira_l: float
    volume_resina_liq_l: float
    volume_total_sugerido_l: float
    peso_estimado_kg: float
    alertas: list[str]

    def texto(self) -> str:
        alertas_txt = " | ".join(self.alertas) if self.alertas else "nenhum"
        return (
            f"Capacidade total do molde: {self.volume_bruto_l:.2f} L\n"
            f"Proporcao de madeira: {self.proporcao_madeira * 100:.1f}%\n"
            f"Volume de resina puro: {self.volume_resina_liq_l:.2f} L\n"
            f"Total sugerido com seguranca: {self.volume_total_sugerido_l:.2f} L\n"
            f"Peso estimado: {self.peso_estimado_kg:.2f} kg\n"
            f"Alertas: {alertas_txt}"
        )


def _validar_positivo(valor: float, nome: str) -> None:
    if valor <= 0:
        raise ValueError(f"O parâmetro '{nome}' deve ser maior que zero.")


def _arred2(valor: float) -> float:
    return round(valor, 2)


def calcular_river_table_por_proporcao(
    diametro_mm: float,
    altura_mm: float,
    proporcao_madeira: float,
    fator_seguranca: float = 1.15,
    densidade_resina_g_cm3: float = 1.1,
    temperatura_ambiente_c: float | None = None,
    temperatura_alvo_c: float = 23.0,
) -> ResultadoRiverTable:
    _validar_positivo(diametro_mm, "diametro_mm")
    _validar_positivo(altura_mm, "altura_mm")
    _validar_positivo(fator_seguranca, "fator_seguranca")
    _validar_positivo(densidade_resina_g_cm3, "densidade_resina_g_cm3")

    if not 0 <= proporcao_madeira <= 1:
        raise ValueError("O parametro 'proporcao_madeira' deve estar entre 0 e 1.")

    # Regra de negocio acordada: area/volume de resina deve ser menor que o de madeira.
    if proporcao_madeira <= 0.5:
        raise ValueError(
            "Regra violada: resina deve ser menor que madeira. "
            "Use proporcao_madeira maior que 0.50."
        )

    raio_cm = diametro_mm / 20
    altura_cm = altura_mm / 10

    volume_bruto_cm3 = pi * (raio_cm**2) * altura_cm
    volume_bruto_l = volume_bruto_cm3 / 1000

    volume_madeira_l = volume_bruto_l * proporcao_madeira
    volume_resina_liq_l = volume_bruto_l - volume_madeira_l
    volume_total_sugerido_l = volume_resina_liq_l * fator_seguranca
    peso_estimado_kg = volume_total_sugerido_l * densidade_resina_g_cm3

    alertas: list[str] = []
    if temperatura_ambiente_c is not None and abs(temperatura_ambiente_c - temperatura_alvo_c) > 2:
        alertas.append(
            "Temperatura fora da faixa ideal de processo (referencia 23 C)."
        )

    return ResultadoRiverTable(
        volume_bruto_l=_arred2(volume_bruto_l),
        proporcao_madeira=proporcao_madeira,
        volume_madeira_l=_arred2(volume_madeira_l),
        volume_resina_liq_l=_arred2(volume_resina_liq_l),
        volume_total_sugerido_l=_arred2(volume_total_sugerido_l),
        peso_estimado_kg=_arred2(peso_estimado_kg),
        alertas=alertas,
    )


def calcular_volume(forma: str, parametros: Dict[str, float]) -> ResultadoCalculo:
    forma_normalizada = forma.strip().lower()

    if forma_normalizada == "caixa":
        largura = parametros.get("largura")
        altura = parametros.get("altura")
        profundidade = parametros.get("profundidade")
        if largura is None or altura is None or profundidade is None:
            raise ValueError("Forma 'caixa' requer largura, altura e profundidade.")
        _validar_positivo(largura, "largura")
        _validar_positivo(altura, "altura")
        _validar_positivo(profundidade, "profundidade")
        volume = largura * altura * profundidade

    elif forma_normalizada == "cubo":
        lado = parametros.get("lado")
        if lado is None:
            raise ValueError("Forma 'cubo' requer lado.")
        _validar_positivo(lado, "lado")
        volume = lado**3

    elif forma_normalizada == "cilindro":
        raio = parametros.get("raio")
        altura = parametros.get("altura")
        if raio is None or altura is None:
            raise ValueError("Forma 'cilindro' requer raio e altura.")
        _validar_positivo(raio, "raio")
        _validar_positivo(altura, "altura")
        volume = pi * (raio**2) * altura

    elif forma_normalizada == "esfera":
        raio = parametros.get("raio")
        if raio is None:
            raise ValueError("Forma 'esfera' requer raio.")
        _validar_positivo(raio, "raio")
        volume = (4 / 3) * pi * (raio**3)

    else:
        raise ValueError(
            "Forma inválida. Use uma das opções: caixa, cubo, cilindro, esfera."
        )

    return ResultadoCalculo(forma=forma_normalizada, volume=volume)
