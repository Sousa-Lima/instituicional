#!/usr/bin/env python3
"""
Redimensiona e otimiza a logo horizontal SLC para uso em public/.
Entrada por defeito: ../docs/sousa-lima-consultoria-logo-horizontal-colorido.png
Saída: public/sousa-lima-consultoria-logo-horizontal-colorido.png (+ .webp opcional)

Requer: pip install Pillow
"""
from __future__ import annotations

import argparse
import os
from pathlib import Path

from PIL import Image


def main() -> None:
	parser = argparse.ArgumentParser(description="Otimiza a logo PNG para o site estático.")
	parser.add_argument(
		"--src",
		type=Path,
		default=None,
		help="Ficheiro PNG de origem (por defeito: docs/... no repositório slc)",
	)
	parser.add_argument(
		"--dest-dir",
		type=Path,
		default=None,
		help="Pasta public/ do frontend (por defeito: ../public relativo a scripts/)",
	)
	parser.add_argument(
		"--max-width",
		type=int,
		default=int(os.environ.get("LOGO_MAX_WIDTH", "1000")),
		help="Largura máxima em px (mantém proporção). Por defeito 1000.",
	)
	parser.add_argument(
		"--webp",
		action="store_true",
		help="Gera também .webp ao lado do PNG.",
	)
	args = parser.parse_args()

	scripts = Path(__file__).resolve().parent
	frontend = scripts.parent
	repo_slc = frontend.parent

	src = args.src
	if src is None:
		src = repo_slc / "docs" / "sousa-lima-consultoria-logo-horizontal-colorido.png"

	dest_dir = args.dest_dir
	if dest_dir is None:
		dest_dir = frontend / "public"

	base = "sousa-lima-consultoria-logo-horizontal-colorido"
	dst_png = dest_dir / f"{base}.png"
	dst_webp = dest_dir / f"{base}.webp"

	if not src.is_file():
		raise SystemExit(f"Origem inexistente: {src}")

	# PNG de marketing pode ser gigante; autorizar abrir (ficheiro confiável).
	Image.MAX_IMAGE_PIXELS = max(Image.MAX_IMAGE_PIXELS, 200_000_000)

	im = Image.open(src).convert("RGBA")
	w, h = im.size
	max_w = max(1, args.max_width)
	if w > max_w:
		ratio = max_w / w
		new_h = max(1, int(round(h * ratio)))
		im = im.resize((max_w, new_h), Image.Resampling.LANCZOS)

	dest_dir.mkdir(parents=True, exist_ok=True)
	im.save(dst_png, format="PNG", optimize=True, compress_level=9)

	kb = dst_png.stat().st_size // 1024
	print(f"PNG: {w}x{h} -> {im.size[0]}x{im.size[1]} | {dst_png} ({kb} KB)")

	if args.webp:
		im.save(dst_webp, format="WEBP", quality=88, method=6)
		kbw = dst_webp.stat().st_size // 1024
		print(f"WebP: {dst_webp} ({kbw} KB)")


if __name__ == "__main__":
	main()
