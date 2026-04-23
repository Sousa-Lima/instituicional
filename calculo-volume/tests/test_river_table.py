from __future__ import annotations

from math import isclose

import pytest
from PIL import Image, ImageDraw

from app.image_analysis import estimar_proporcao_madeira
from app.service import calcular_river_table_por_proporcao


def test_calculo_river_table_base_1000x50() -> None:
    resultado = calcular_river_table_por_proporcao(
        diametro_mm=1000,
        altura_mm=50,
        proporcao_madeira=0.80,
        fator_seguranca=1.15,
        densidade_resina_g_cm3=1.1,
    )

    assert isclose(resultado.volume_bruto_l, 39.27, abs_tol=0.02)
    assert isclose(resultado.volume_madeira_l, 31.42, abs_tol=0.03)
    assert isclose(resultado.volume_resina_liq_l, 7.85, abs_tol=0.03)
    assert isclose(resultado.volume_total_sugerido_l, 9.03, abs_tol=0.03)


def test_regra_resina_menor_que_madeira() -> None:
    with pytest.raises(ValueError, match="resina deve ser menor"):
        calcular_river_table_por_proporcao(
            diametro_mm=1000,
            altura_mm=50,
            proporcao_madeira=0.45,
            fator_seguranca=1.15,
            densidade_resina_g_cm3=1.1,
        )


def test_estimativa_proporcao_madeira_por_imagem(tmp_path) -> None:
    img_path = tmp_path / "mock.png"

    size = 500
    img = Image.new("RGB", (size, size), (210, 210, 210))
    draw = ImageDraw.Draw(img)

    # Círculo do molde (cinza claro).
    draw.ellipse((25, 25, size - 25, size - 25), fill=(190, 190, 190))

    # Madeira ocupando uma grande faixa inferior (tons amarronzados).
    draw.polygon(
        [(45, 240), (455, 240), (455, 455), (45, 455)],
        fill=(150, 110, 70),
    )

    img.save(img_path)

    proporcao = estimar_proporcao_madeira(str(img_path))

    assert 0.50 < proporcao < 0.95
