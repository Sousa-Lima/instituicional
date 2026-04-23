#!/usr/bin/env python3
"""Gera um unico arquivo .doc a partir de todos os .md de um diretorio.

Estrategia:
- Coleta recursivamente todos os arquivos Markdown.
- Monta um Markdown consolidado com separadores por arquivo.
- Tenta converter para HTML usando `pandoc` (se disponivel).
- Se `pandoc` nao existir, tenta o pacote Python `markdown`.
- Como ultimo fallback, grava o conteudo em bloco <pre>.

O arquivo de saida usa extensao `.doc`, mas contem HTML compativel com Word.
"""

from __future__ import annotations

import argparse
import html
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path


def coletar_markdowns(diretorio_base: Path) -> list[Path]:
    arquivos = [p for p in diretorio_base.rglob("*.md") if p.is_file()]
    return sorted(arquivos, key=lambda p: str(p.relative_to(diretorio_base)).lower())


def montar_markdown_unificado(arquivos: list[Path], diretorio_base: Path) -> str:
    blocos: list[str] = ["# Documentacao Unificada", ""]

    for arquivo in arquivos:
        caminho_relativo = arquivo.relative_to(diretorio_base)
        titulo = f"## {caminho_relativo.as_posix()}"
        blocos.append(titulo)
        blocos.append("")

        conteudo = arquivo.read_text(encoding="utf-8", errors="replace").strip()
        blocos.append(conteudo)
        blocos.append("")
        blocos.append("---")
        blocos.append("")

    return "\n".join(blocos).strip() + "\n"


def markdown_para_html(md_texto: str) -> str:
    pandoc = shutil.which("pandoc")
    if pandoc:
        with tempfile.TemporaryDirectory() as temp_dir:
            temp = Path(temp_dir)
            md_path = temp / "consolidado.md"
            html_path = temp / "consolidado.html"
            md_path.write_text(md_texto, encoding="utf-8")

            comando = [pandoc, "-f", "markdown", "-t", "html5", str(md_path), "-o", str(html_path)]
            subprocess.run(comando, check=True)
            return html_path.read_text(encoding="utf-8", errors="replace")

    try:
        import markdown  # type: ignore

        return markdown.markdown(md_texto, extensions=["extra", "toc"])
    except Exception:
        # Fallback seguro sem dependencias externas.
        return f"<pre>{html.escape(md_texto)}</pre>"


def encapsular_html_para_word(corpo_html: str, titulo: str) -> str:
    return f"""<!DOCTYPE html>
<html lang=\"pt-BR\">
<head>
  <meta charset=\"utf-8\" />
  <title>{html.escape(titulo)}</title>
  <style>
    body {{ font-family: Calibri, Arial, sans-serif; line-height: 1.45; margin: 2cm; }}
    h1, h2, h3 {{ color: #1f2937; }}
    code {{ background: #f3f4f6; padding: 2px 4px; border-radius: 4px; }}
    pre {{ background: #f8fafc; padding: 12px; border: 1px solid #e5e7eb; overflow-x: auto; }}
    hr {{ border: none; border-top: 1px solid #d1d5db; margin: 24px 0; }}
  </style>
</head>
<body>
{corpo_html}
</body>
</html>
"""


def gerar_doc_unico(diretorio_entrada: Path, arquivo_saida: Path) -> None:
    if not diretorio_entrada.exists() or not diretorio_entrada.is_dir():
        raise FileNotFoundError(f"Diretorio invalido: {diretorio_entrada}")

    arquivos_md = coletar_markdowns(diretorio_entrada)
    if not arquivos_md:
        raise RuntimeError(f"Nenhum arquivo .md encontrado em: {diretorio_entrada}")

    markdown_unificado = montar_markdown_unificado(arquivos_md, diretorio_entrada)
    corpo_html = markdown_para_html(markdown_unificado)
    documento_html = encapsular_html_para_word(corpo_html, "Documentacao Unificada")

    arquivo_saida.parent.mkdir(parents=True, exist_ok=True)
    arquivo_saida.write_text(documento_html, encoding="utf-8")


def parse_args(argv: list[str]) -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Converte todos os .md de um diretorio em um unico arquivo .doc"
    )
    parser.add_argument(
        "--input-dir",
        default="docs",
        help="Diretorio de entrada contendo arquivos Markdown (padrao: docs)",
    )
    parser.add_argument(
        "--output",
        default="docs/documentacao-unificada.doc",
        help="Arquivo .doc de saida (padrao: docs/documentacao-unificada.doc)",
    )
    return parser.parse_args(argv)


def main(argv: list[str]) -> int:
    args = parse_args(argv)

    diretorio_entrada = Path(args.input_dir).resolve()
    arquivo_saida = Path(args.output).resolve()

    try:
        gerar_doc_unico(diretorio_entrada, arquivo_saida)
        print(f"Arquivo gerado com sucesso: {arquivo_saida}")
        return 0
    except Exception as exc:
        print(f"Erro ao gerar .doc: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main(sys.argv[1:]))