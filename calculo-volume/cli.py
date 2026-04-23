from __future__ import annotations

import argparse
import sys

from app.image_analysis import analisar_imagem_com_artefatos, estimar_proporcao_madeira
from app.service import calcular_river_table_por_proporcao, calcular_volume
from app.tui import run_tui


def _parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        prog="calculo-volume",
        description="Calcula volume de formas geométricas e retorna em texto.",
    )

    subparsers = parser.add_subparsers(dest="forma", required=True)

    caixa = subparsers.add_parser("caixa", help="Volume de caixa")
    caixa.add_argument("--largura", type=float, required=True)
    caixa.add_argument("--altura", type=float, required=True)
    caixa.add_argument("--profundidade", type=float, required=True)

    cubo = subparsers.add_parser("cubo", help="Volume de cubo")
    cubo.add_argument("--lado", type=float, required=True)

    cilindro = subparsers.add_parser("cilindro", help="Volume de cilindro")
    cilindro.add_argument("--raio", type=float, required=True)
    cilindro.add_argument("--altura", type=float, required=True)

    esfera = subparsers.add_parser("esfera", help="Volume de esfera")
    esfera.add_argument("--raio", type=float, required=True)

    river = subparsers.add_parser("river", help="Calculo para river table")
    river.add_argument("--diametro-mm", type=float, required=True)
    river.add_argument("--altura-mm", type=float, required=True)
    river.add_argument("--proporcao-madeira", type=float)
    river.add_argument("--imagem", type=str)
    river.add_argument("--fator-seguranca", type=float, default=1.15)
    river.add_argument("--densidade", type=float, default=1.1)
    river.add_argument("--temperatura", type=float)
    river.add_argument(
        "--salvar-etapas",
        action="store_true",
        help="Salva versoes intermediarias da analise de imagem.",
    )
    river.add_argument(
        "--saida-etapas",
        type=str,
        help="Diretorio de saida para os artefatos da analise.",
    )

    subparsers.add_parser("tui", help="Interface interativa no terminal")

    return parser


def main() -> int:
    parser = _parser()
    args = parser.parse_args()

    forma = args.forma

    if forma == "tui":
        return run_tui()

    if forma == "river":
        proporcao_madeira = args.proporcao_madeira
        analise = None
        if args.imagem:
            try:
                if args.salvar_etapas:
                    analise = analisar_imagem_com_artefatos(args.imagem, args.saida_etapas)
                    proporcao_madeira = analise.proporcao_madeira
                else:
                    proporcao_madeira = estimar_proporcao_madeira(args.imagem)
            except (FileNotFoundError, ValueError) as exc:
                print(f"Erro: {exc}", file=sys.stderr)
                return 1

        if proporcao_madeira is None:
            print(
                "Erro: informe --proporcao-madeira ou --imagem para o modo river.",
                file=sys.stderr,
            )
            return 1

        try:
            resultado_river = calcular_river_table_por_proporcao(
                diametro_mm=args.diametro_mm,
                altura_mm=args.altura_mm,
                proporcao_madeira=proporcao_madeira,
                fator_seguranca=args.fator_seguranca,
                densidade_resina_g_cm3=args.densidade,
                temperatura_ambiente_c=args.temperatura,
            )
        except ValueError as exc:
            print(f"Erro: {exc}", file=sys.stderr)
            return 1

        print(resultado_river.texto())
        if analise is not None:
            print("\nArtefatos da analise:")
            print(f"- Diretorio: {analise.output_dir}")
            print(f"- Original anotada: {analise.original_anotada_path}")
            print(f"- Mascara do molde: {analise.mascara_molde_path}")
            print(f"- Mascara da madeira: {analise.mascara_madeira_path}")
            print(f"- Overlay final: {analise.overlay_path}")
        return 0

    parametros = {
        k: v for k, v in vars(args).items() if k != "forma" and v is not None
    }

    try:
        resultado = calcular_volume(forma, parametros)
    except ValueError as exc:
        print(f"Erro: {exc}", file=sys.stderr)
        return 1

    print(resultado.texto())
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
