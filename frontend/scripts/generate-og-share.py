#!/usr/bin/env python3
"""
Gera public/og-social.png (1200×630) para pré-visualizações (WhatsApp, Facebook, LinkedIn).
Compõe o logotipo horizontal sobre fundo da marca.
Requer: pip install Pillow
"""
from __future__ import annotations

import argparse
from pathlib import Path

from PIL import Image, ImageDraw


def main() -> None:
	parser = argparse.ArgumentParser()
	parser.add_argument(
		"--logo",
		type=Path,
		default=None,
		help="PNG do logotipo (por defeito: public/...logo horizontal...png)",
	)
	parser.add_argument(
		"--out",
		type=Path,
		default=None,
		help="Ficheiro de saída (por defeito: public/og-social.png)",
	)
	args = parser.parse_args()

	scripts = Path(__file__).resolve().parent
	frontend = scripts.parent
	logo = args.logo
	if logo is None:
		logo = frontend / "public" / "sousa-lima-consultoria-logo-horizontal-colorido.png"
	out = args.out
	if out is None:
		out = frontend / "public" / "og-social.png"

	if not logo.is_file():
		raise SystemExit(f"Logo não encontrada: {logo}")

	Image.MAX_IMAGE_PIXELS = max(Image.MAX_IMAGE_PIXELS, 200_000_000)

	W, H = 1200, 630
	# Fundo: gradiente suave (slc-bg → branco) — #faf9fc → #ffffff
	base = Image.new("RGBA", (W, H), (0, 0, 0, 0))
	draw = ImageDraw.Draw(base)
	for y in range(H):
		t = y / max(H - 1, 1)
		r = int(0xFA + (0xFF - 0xFA) * t)
		g = int(0xF9 + (0xFF - 0xF9) * t)
		b = int(0xFC + (0xFF - 0xFC) * t)
		draw.line([(0, y), (W, y)], fill=(r, g, b, 255))

	# Margem lateral sutil (marca)
	draw.rectangle([0, 0, W, 6], fill=(0x33, 0x2C, 0x63, 255))

	im = Image.open(logo).convert("RGBA")
	lw, lh = im.size
	# Largura máxima do logo na faixa 1200px (margens ~100px)
	max_logo_w = 920
	if lw > max_logo_w:
		ratio = max_logo_w / lw
		im = im.resize((max_logo_w, int(round(lh * ratio))), Image.Resampling.LANCZOS)

	lw, lh = im.size
	x = (W - lw) // 2
	y = (H - lh) // 2
	base.paste(im, (x, y), im)

	out.parent.mkdir(parents=True, exist_ok=True)
	base.convert("RGB").save(out, format="PNG", optimize=True, compress_level=9)
	kb = out.stat().st_size // 1024
	print(f"OK: {out} ({W}×{H}) — {kb} KB")


if __name__ == "__main__":
	main()
