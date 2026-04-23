from __future__ import annotations

from dataclasses import dataclass
from pathlib import Path


@dataclass(frozen=True)
class ResultadoAnaliseImagem:
    proporcao_madeira: float
    output_dir: str
    original_anotada_path: str
    mascara_molde_path: str
    mascara_madeira_path: str
    overlay_path: str


def _carregar_dependencias() -> tuple[object, object, object]:
    try:
        import numpy as np
        from PIL import Image, ImageDraw
    except ImportError as exc:
        raise ValueError(
            "Dependencias de imagem ausentes. Instale numpy e Pillow para usar --imagem."
        ) from exc

    return np, Image, ImageDraw


def _calcular_mascaras(rgb: object, np: object) -> tuple[object, object]:
    h, w, _ = rgb.shape
    cy, cx = h // 2, w // 2
    radius = int(min(h, w) * 0.45)

    yy, xx = np.ogrid[:h, :w]
    circle_mask = (xx - cx) ** 2 + (yy - cy) ** 2 <= radius**2

    r = rgb[:, :, 0].astype(np.int16)
    g = rgb[:, :, 1].astype(np.int16)
    b = rgb[:, :, 2].astype(np.int16)

    brightness = (r + g + b) / 3

    wood_mask = (
        (r > 60)
        & (g > 45)
        & (b > 20)
        & (r > g * 0.9)
        & (g > b * 0.75)
        & ((r - b) > 8)
        & (brightness > 35)
        & (brightness < 220)
    )

    return circle_mask, wood_mask


def _gerar_output_dir(caminho_imagem: str, output_dir: str | None) -> Path:
    if output_dir:
        destino = Path(output_dir)
    else:
        nome_base = Path(caminho_imagem).stem
        destino = Path("analysis-output") / nome_base

    destino.mkdir(parents=True, exist_ok=True)
    return destino


def analisar_imagem_com_artefatos(
    caminho_imagem: str,
    output_dir: str | None = None,
) -> ResultadoAnaliseImagem:
    path = Path(caminho_imagem)
    if not path.exists():
        raise FileNotFoundError(f"Imagem nao encontrada: {caminho_imagem}")

    np, Image, ImageDraw = _carregar_dependencias()

    image = Image.open(path).convert("RGB")
    rgb = np.asarray(image, dtype=np.uint8)
    h, w, _ = rgb.shape
    cy, cx = h // 2, w // 2
    radius = int(min(h, w) * 0.45)

    circle_mask, wood_mask = _calcular_mascaras(rgb, np)

    total_pixels = int(np.count_nonzero(circle_mask))
    if total_pixels == 0:
        raise ValueError("Nao foi possivel identificar area util do molde na imagem.")

    wood_pixels = int(np.count_nonzero(circle_mask & wood_mask))
    proporcao = wood_pixels / total_pixels

    destino = _gerar_output_dir(caminho_imagem, output_dir)

    original_anotada = image.copy()
    draw = ImageDraw.Draw(original_anotada)
    draw.ellipse((cx - radius, cy - radius, cx + radius, cy + radius), outline=(0, 255, 255), width=4)

    mascara_molde_arr = np.zeros((h, w), dtype=np.uint8)
    mascara_molde_arr[circle_mask] = 255

    mascara_madeira_arr = np.zeros((h, w), dtype=np.uint8)
    mascara_madeira_arr[circle_mask & wood_mask] = 255

    overlay_arr = rgb.copy()
    # Madeira em verde, resina em vermelho dentro do molde.
    overlay_arr[circle_mask & wood_mask] = np.array([60, 200, 80], dtype=np.uint8)
    overlay_arr[circle_mask & ~wood_mask] = np.array([220, 70, 70], dtype=np.uint8)

    original_anotada_path = destino / "01_original_anotada.png"
    mascara_molde_path = destino / "02_mascara_molde.png"
    mascara_madeira_path = destino / "03_mascara_madeira.png"
    overlay_path = destino / "04_overlay_classificacao.png"

    original_anotada.save(original_anotada_path)
    Image.fromarray(mascara_molde_arr).save(mascara_molde_path)
    Image.fromarray(mascara_madeira_arr).save(mascara_madeira_path)
    Image.fromarray(overlay_arr).save(overlay_path)

    return ResultadoAnaliseImagem(
        proporcao_madeira=proporcao,
        output_dir=str(destino),
        original_anotada_path=str(original_anotada_path),
        mascara_molde_path=str(mascara_molde_path),
        mascara_madeira_path=str(mascara_madeira_path),
        overlay_path=str(overlay_path),
    )


def estimar_proporcao_madeira(caminho_imagem: str) -> float:
    """Estima a proporcao de madeira dentro do circulo do molde.

    Heuristica inicial:
    - considera o maior circulo central da imagem como area util do molde;
    - classifica madeira por faixa de cor RGB tipica de tons amadeirados.
    """
    path = Path(caminho_imagem)
    if not path.exists():
        raise FileNotFoundError(f"Imagem nao encontrada: {caminho_imagem}")
    resultado = analisar_imagem_com_artefatos(caminho_imagem)
    return resultado.proporcao_madeira
