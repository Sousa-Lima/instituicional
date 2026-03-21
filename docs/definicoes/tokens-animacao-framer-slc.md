# Tokens de animação — Framer Motion + Astro (SLC)

Diretrizes para **consistência visual enterprise**: animações **discretas e suaves**, alinhadas ao **budget de 1,5 MB** por página e às **Astro Islands** com **Framer Motion** ([diretrizes-qualidade-site-slc.md](diretrizes-qualidade-site-slc.md), [projeto-astro-laravel-institutional.md](../conhecimento/projeto-astro-laravel-institutional.md)). Respeitar sempre **`prefers-reduced-motion`**.

---

## 1. Easings (curvas)

| Token | Valor (cubic-bezier) | Uso |
|-------|----------------------|-----|
| **`ease-out-expo`** | `[0.16, 1, 0.3, 1]` | Entradas de secção (fade-in / slide-up). |
| **`standard-smart`** | `[0.4, 0, 0.2, 1]` | Hover em botões e links de navegação. |

No CSS equivalente, reutilizar os mesmos valores em `transition-timing-function` para estados que não passam pelo Framer Motion.

---

## 2. Durações e atrasos

Objetivo: **feedback imediato** nos CTAs (alinhado a **INP ≤ 200 ms** no contexto geral do site — [qualidade-web-core-vitals.md](qualidade-web-core-vitals.md)) e entradas sem sensação de “teatro”.

| Token | Valor | Uso |
|-------|--------|-----|
| **`fast`** | `0.1s` ou `0.15s` | Hover e estados **active** de CTAs. |
| **`moderate`** | `0.4s` | Entrada de componentes principais (ex.: cards de serviços). |
| **`stagger`** | `0.1s` | Intervalo entre itens em listas (ex.: métricas num Case). |

---

## 3. Presets (referência — frontend Astro)

Ficheiro sugerido: `src/lib/motion-tokens.ts` (ou equivalente no repositório do site).

```typescript
/** Curvas — manter sincronizadas com a tabela §1 */
export const EASE_OUT_EXPO = [0.16, 1, 0.3, 1] as const;
export const STANDARD_SMART = [0.4, 0, 0.2, 1] as const;

/** Durações em segundos (Framer Motion) */
export const DURATION = {
  fast: 0.15,
  moderate: 0.4,
  stagger: 0.1,
} as const;

export const fadeInUp = {
  initial: { opacity: 0, y: 20 },
  animate: { opacity: 1, y: 0 },
  transition: { duration: DURATION.moderate, ease: EASE_OUT_EXPO },
};

export const staggerContainer = {
  animate: {
    transition: { staggerChildren: DURATION.stagger },
  },
};
```

---

## 4. `prefers-reduced-motion`

Conforme [projeto-astro-laravel-institutional.md](../conhecimento/projeto-astro-laravel-institutional.md): desativar ou simplificar movimento quando o utilizador o pedir (WCAG).

```typescript
import { useReducedMotion } from "framer-motion";

const shouldReduceMotion = useReducedMotion();

const animateProps = shouldReduceMotion
  ? { opacity: 1, y: 0 } // Estático ou só opacidade curta
  : fadeInUp;
```

Preferir **sem deslocamento** (`y: 0`) ou **apenas fade** curto; evitar animações longas ou em loop.

---

## 5. Performance no Astro

| Prática | Motivo |
|---------|--------|
| **`client:visible`** em ilhas que carregam Framer Motion | O JS da animação só entra quando o bloco **entra na viewport** — protege **LCP** e reduz trabalho inicial na main thread. |
| **Ilhas pequenas** | Contribui para **INP** e para o budget de **1,5 MB** — [diretrizes-qualidade-site-slc.md](diretrizes-qualidade-site-slc.md). |
| **Partytown para scripts de terceiros** | Analytics/tags em worker libertam a **main thread** para interação e animações de UI — alinhar a [compliance-ferramentas.md](compliance-ferramentas.md) e CSP. |

---

## Revisão

Ajustar tokens após auditoria de performance (PSI / Web Vitals) ou se o design system evoluir, mantendo o princípio **enterprise: discrição antes de espetáculo**.
