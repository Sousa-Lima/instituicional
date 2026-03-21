# Base de cores — SLC (site / sistema)

Referência visual: logo horizontal colorido em [`sousa-lima-consultoria-logo-horizontal-colorido.png`](../sousa-lima-consultoria-logo-horizontal-colorido.png) (nome de arquivo em **kebab-case** para SEO e URLs estáveis).

## Paleta principal

| Token | Uso sugerido | Hex | Observação |
|--------|----------------|-----|------------|
| `--slc-navy` | Títulos, navegação, botões primários, texto de alto destaque | `#332C63` | Roxo-azulado escuro do “SOUSA LIMA” |
| `--slc-magenta` | Destaques, CTAs secundários, gráficos | `#A64B91` | Extremo do gradiente do ícone (lado esquerdo) |
| `--slc-purple` | Hover, ícones, chips | `#7D3C8C` | Transição do gradiente no ícone |
| `--slc-indigo` | Superfícies suaves, bordas de acento | `#534B91` | Parte do gradiente (lado direito do ícone) |
| `--slc-violet` | Gradientes, sombras coloridas | `#674E94` | Profundidade no ícone |
| `--slc-grey` | Subtítulos, legendas, bordas neutras | `#808080` | Tom do “CONSULTORIA” |

## Superfícies e texto

| Token | Uso | Hex |
|--------|-----|-----|
| `--slc-bg` | Fundo geral (modo claro) | `#FAF9FC` |
| `--slc-surface` | Cartões, painéis | `#FFFFFF` |
| `--slc-text` | Corpo de texto sobre fundo claro | `#1F1B2E` |
| `--slc-text-muted` | Texto secundário | `#5C566B` |

Os hex do logo foram amostrados a partir do arquivo; refine com ferramenta de design se precisar de precisão de marca.

## Gradiente do ícone (referência)

O símbolo combina dois fluxos de cor; para backgrounds ou bordas decorativas:

```css
--slc-gradient-icon: linear-gradient(
  135deg,
  #A64B91 0%,
  #7D3C8C 35%,
  #534B91 70%,
  #674E94 100%
);
```

## Bloco CSS (variáveis globais)

```css
:root {
  --slc-navy: #332c63;
  --slc-magenta: #a64b91;
  --slc-purple: #7d3c8c;
  --slc-indigo: #534b91;
  --slc-violet: #674e94;
  --slc-grey: #808080;

  --slc-bg: #faf9fc;
  --slc-surface: #ffffff;
  --slc-text: #1f1b2e;
  --slc-text-muted: #5c566b;

  --slc-primary: var(--slc-navy);
  --slc-accent: var(--slc-magenta);
  --slc-gradient-icon: linear-gradient(
    135deg,
    #a64b91 0%,
    #7d3c8c 35%,
    #534b91 70%,
    #674e94 100%
  );
}
```

## Acessibilidade

- Texto longo em **corpo**: preferir `--slc-text` sobre `--slc-bg` ou `--slc-surface`.
- Texto sobre `--slc-navy`: usar **branco** `#FFFFFF` ou cinza muito claro; validar contraste (mín. **4,5:1** para texto normal, **3:1** para títulos grandes).
- Evitar colocar texto pequeno só em `--slc-magenta` ou `--slc-grey` sobre fundo claro sem checar contraste.

## Uso na interface

| Elemento | Sugestão |
|----------|-----------|
| Cabeçalho / barra superior | Fundo `--slc-surface` ou `--slc-navy` com links claros |
| Botão primário | Fundo `--slc-navy`, texto branco |
| Botão secundário | Borda `--slc-indigo`, texto `--slc-navy` |
| Links | `--slc-magenta` ou `--slc-purple`, sublinhado no hover |
| Bordas e divisórias | `#E4E0ED` ou `--slc-grey` com opacidade reduzida |

Atualize esta página se a marca publicar guia oficial (Pantone/CMYK) diferente dos valores em tela.
