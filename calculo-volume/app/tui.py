from __future__ import annotations

from typing import Callable

from app.image_analysis import analisar_imagem_com_artefatos, estimar_proporcao_madeira
from app.service import calcular_river_table_por_proporcao, calcular_volume


def _linha(char: str = "=", size: int = 64) -> str:
    return char * size


def _cabecalho(titulo: str) -> None:
    print()
    print(_linha("="))
    print(titulo)
    print(_linha("="))


def _ler_float(prompt: str, min_value: float | None = None) -> float:
    while True:
        raw = input(prompt).strip().replace(",", ".")
        try:
            value = float(raw)
        except ValueError:
            print("Entrada invalida. Digite um numero.")
            continue

        if min_value is not None and value <= min_value:
            print(f"Valor deve ser maior que {min_value}.")
            continue

        return value


def _ler_opcao(prompt: str, opcoes: set[str]) -> str:
    while True:
        valor = input(prompt).strip()
        if valor in opcoes:
            return valor
        print(f"Opcao invalida. Escolha uma destas: {', '.join(sorted(opcoes))}")


def _ler_texto(prompt: str, obrigatorio: bool = False) -> str:
    while True:
        valor = input(prompt).strip()
        if valor or not obrigatorio:
            return valor
        print("Campo obrigatorio.")


def _barra_proporcao(proporcao: float, largura: int = 30) -> str:
    preenchido = int(round(proporcao * largura))
    vazio = largura - preenchido
    return f"[{'#' * preenchido}{'.' * vazio}] {proporcao * 100:.1f}%"


def _modo_formas_basicas() -> None:
    _cabecalho("Modo 1 - Formas basicas")
    print("1) caixa  2) cubo  3) cilindro  4) esfera")
    escolha = _ler_opcao("Escolha a forma: ", {"1", "2", "3", "4"})

    try:
        if escolha == "1":
            largura = _ler_float("Largura: ", 0)
            altura = _ler_float("Altura: ", 0)
            profundidade = _ler_float("Profundidade: ", 0)
            resultado = calcular_volume(
                "caixa",
                {
                    "largura": largura,
                    "altura": altura,
                    "profundidade": profundidade,
                },
            )
        elif escolha == "2":
            lado = _ler_float("Lado: ", 0)
            resultado = calcular_volume("cubo", {"lado": lado})
        elif escolha == "3":
            raio = _ler_float("Raio: ", 0)
            altura = _ler_float("Altura: ", 0)
            resultado = calcular_volume("cilindro", {"raio": raio, "altura": altura})
        else:
            raio = _ler_float("Raio: ", 0)
            resultado = calcular_volume("esfera", {"raio": raio})

        print()
        print("Resultado")
        print(_linha("-"))
        print(resultado.texto())
    except ValueError as exc:
        print(f"Erro: {exc}")


def _modo_river_table() -> None:
    _cabecalho("Modo 2 - River Table")
    diametro_mm = _ler_float("Diametro (mm): ", 0)
    altura_mm = _ler_float("Altura (mm): ", 0)
    fator_seguranca = _ler_float("Fator de seguranca [1.15]: ", 0)
    densidade = _ler_float("Densidade da resina [1.1]: ", 0)

    print()
    print("Entrada da proporcao de madeira")
    print("1) Manual")
    print("2) Por imagem")
    modo = _ler_opcao("Escolha: ", {"1", "2"})

    proporcao_madeira: float
    if modo == "1":
        proporcao_madeira = _ler_float("Proporcao da madeira (0-1): ", -1)
    else:
        caminho = _ler_texto("Caminho da imagem: ", obrigatorio=True)
        salvar_etapas = _ler_opcao(
            "Salvar versoes da analise para inspecao? (s/n): ", {"s", "n", "S", "N"}
        )
        analise = None
        try:
            if salvar_etapas.lower() == "s":
                saida_etapas = _ler_texto(
                    "Diretorio de saida (vazio = analysis-output/<nome_imagem>): "
                )
                analise = analisar_imagem_com_artefatos(
                    caminho,
                    output_dir=saida_etapas or None,
                )
                proporcao_madeira = analise.proporcao_madeira
            else:
                proporcao_madeira = estimar_proporcao_madeira(caminho)
        except (FileNotFoundError, ValueError) as exc:
            print(f"Erro ao analisar imagem: {exc}")
            return

    temperatura_raw = _ler_texto("Temperatura ambiente em C (opcional): ")
    temperatura = float(temperatura_raw.replace(",", ".")) if temperatura_raw else None

    try:
        resultado = calcular_river_table_por_proporcao(
            diametro_mm=diametro_mm,
            altura_mm=altura_mm,
            proporcao_madeira=proporcao_madeira,
            fator_seguranca=fator_seguranca,
            densidade_resina_g_cm3=densidade,
            temperatura_ambiente_c=temperatura,
        )
    except ValueError as exc:
        print(f"Erro: {exc}")
        return

    proporcao_resina = 1 - resultado.proporcao_madeira

    print()
    print("Painel River Table")
    print(_linha("-"))
    print(f"Madeira: {_barra_proporcao(resultado.proporcao_madeira)}")
    print(f"Resina : {_barra_proporcao(proporcao_resina)}")
    print(_linha("-"))
    print(resultado.texto())
    if modo == "2" and "analise" in locals() and analise is not None:
        print(_linha("-"))
        print("Arquivos gerados para inspecao:")
        print(f"- Diretorio: {analise.output_dir}")
        print(f"- Original anotada: {analise.original_anotada_path}")
        print(f"- Mascara do molde: {analise.mascara_molde_path}")
        print(f"- Mascara da madeira: {analise.mascara_madeira_path}")
        print(f"- Overlay final: {analise.overlay_path}")


def _confirmar_saida() -> bool:
    valor = _ler_opcao("Deseja sair? (s/n): ", {"s", "n", "S", "N"})
    return valor.lower() == "s"


def run_tui() -> int:
    acoes: dict[str, Callable[[], None]] = {
        "1": _modo_formas_basicas,
        "2": _modo_river_table,
    }

    while True:
        _cabecalho("calculo-volume - Interface Terminal")
        print("1) Formas basicas")
        print("2) River table")
        print("0) Sair")

        escolha = _ler_opcao("Escolha uma opcao: ", {"0", "1", "2"})

        if escolha == "0":
            print("Encerrando interface.")
            return 0

        acoes[escolha]()
        print()
        if _confirmar_saida():
            print("Encerrando interface.")
            return 0
