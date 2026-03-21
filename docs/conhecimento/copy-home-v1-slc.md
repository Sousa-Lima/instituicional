# Copy v1 — Home (Sousa Lima Consultoria)

Proposta de texto para a **primeira dobra** e blocos principais da Home, alinhada a [conteudo-mensagens-referencia.md](conteudo-mensagens-referencia.md), [referencia-layout-sites.md](referencia-layout-sites.md) e ao mapa em [mapa-site-mvp-slc.md](mapa-site-mvp-slc.md).

**Status:** rascunho para revisão interna e **validação jurídica** onde aplicável; métricas **N**, **X**, **Y** são **placeholders** — substituir por dados reais ou remover até haver base.

**Nota técnica:** este conteúdo deve ser **fornecido pela API do admin (Laravel)** e **consumido pelo Astro no build** (SSG), para que o HTML das páginas estáticas já saia completo no deploy — sem depender de `fetch` no cliente para o texto principal. Isso favorece **LCP** (marcação e texto cedo) e **INP** (menos trabalho na main thread na primeira interação). Contrato e slugs: [contrato-api-build-time-slc.md](contrato-api-build-time-slc.md), [integracao-astro-ssg-laravel.md](integracao-astro-ssg-laravel.md); métricas: [qualidade-web-core-vitals.md](../definicoes/qualidade-web-core-vitals.md).

---

## 1. Hero (primeira dobra)

| Elemento | Texto |
|----------|--------|
| **Título (H1)** | Transformação digital com foco em resultados e prazos reais. |
| **Subtítulo** | Consultoria especializada em viabilizar soluções de software e infraestrutura. Entregamos o seu MVP em 30 a 90 dias, unindo agilidade técnica à visão de negócio. |
| **CTA primário (botão)** | Agendar diagnóstico gratuito |
| **CTA secundário (link)** | Conhecer nossos serviços |

*Sugestão de destino do CTA secundário:* âncora ou rota `/servicos`.

---

## 2. Serviços (resumo)

**Título (H2):** Soluções sob medida para o estágio do seu negócio.

| Card | Título (H3) | Descrição |
|------|-------------|-----------|
| 1 | Consultoria e processos | Otimização de fluxos para reduzir retrabalho e aumentar a eficiência operacional. |
| 2 | Desenvolvimento de software | Criação de sistemas robustos e MVPs escaláveis com stack moderna (Laravel + React). |
| 3 | Cloud e infraestrutura | Arquitetura de alta disponibilidade com Docker Swarm e foco em segurança. |

Alinhado ao [stack-tecnico-slc.md](../definicoes/stack-tecnico-slc.md) e à [base-publicacao.md](base-publicacao.md) (Swarm) onde fizer sentido para o público-alvo.

---

## 3. Diferencial SLC (método)

**Título (H2):** Parceiro estratégico, não apenas fornecedor.

**Corpo:** Acreditamos na validação por ciclos. Em vez de projetos infinitos, trabalhamos com entregas contínuas e feedback real, garantindo que o software evolua conforme a necessidade do mercado.

---

## 4. Prova social (credibilidade)

**Título (H2):** Resultados que geram valor.

**Métricas (exemplos conceituais — preencher ou ajustar):**

- +**N** projetos entregues com sucesso.
- **X**% de redução média em custos operacionais dos nossos clientes.
- **Y** dias é o nosso prazo médio para lançamento de uma primeira versão (fase 1).

Incluir, quando possível, **faixa de logotipos** e/ou depoimentos (ver [conteudo-mensagens-referencia.md](conteudo-mensagens-referencia.md)).

---

## 5. FAQ (conversão)

**Pergunta 1:** Qual o tempo médio para ver o projeto funcionando?

**Resposta:** Trabalhamos com modelos de 30, 60 ou 90 dias para a primeira fase (MVP), dependendo da complexidade.

**Pergunta 2:** Vocês atendem empresas que ainda estão validando a ideia?

**Resposta:** Sim. O nosso foco é alinhar o escopo ao estágio atual do seu negócio para evitar desperdício de recursos.

---

## 6. Rodapé (trecho institucional)

**Frase de fechamento:** Sousa Lima Consultoria — Conhecimento técnico a serviço da sua evolução.

**Links obrigatórios (LGPD):** Política de privacidade; política de cookies / preferências — ver [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md) e [mapa-site-mvp-slc.md](mapa-site-mvp-slc.md) (rodapé técnico).

---

## Semântica e SEO

- **Um único H1** na página (hero); hierarquia H2 → H3 nos blocos acima.
- Metadados da página (title, description) derivados do H1 e do subtítulo, com ajuste fino para SERP.
