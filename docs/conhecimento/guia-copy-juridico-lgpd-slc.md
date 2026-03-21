# Guia — copy, jurídico e cookies (LGPD)

Documento operacional para alinhar **conteúdo editorial**, **conformidade legal** e **implementação técnica** do site institucional da Sousa Lima Consultoria. Complementa [conteudo-juridico-semente.md](../definicoes/conteudo-juridico-semente.md), [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md) e [mapa-site-mvp-slc.md](mapa-site-mvp-slc.md).

---

## 1. Copy (textos do site)

### O que é

**Copywriting:** textos persuasivos e informativos (quem somos, serviços, diferenciais, CTAs) que substituem **placeholders** e referências iniciais ([copy-home-v1-slc.md](copy-home-v1-slc.md), [copy-servicos-verticais-v1-slc.md](copy-servicos-verticais-v1-slc.md)).

### O que contemplar

| Área | Entregável |
|------|------------|
| **Tom e posicionamento** | Voz alinhada à marca (enterprise, clareza, sem jargão vazio); coerência com [conteudo-mensagens-referencia.md](conteudo-mensagens-referencia.md). |
| **Substituição de placeholders** | KPIs com “X%”, prazos e uptime só com base real ou reformulação sem promessa numérica frágil. |
| **SEO on-page** | Títulos únicos por página, meta descriptions, alinhamento a [mapa-site-mvp-slc.md](mapa-site-mvp-slc.md) (slugs finais). |
| **Prova e credibilidade** | Cases, números e depoimentos só com autorização e factualidade. |
| **Formulários e CTAs** | Coerência com [formulario-contato-lead-slc.md](formulario-contato-lead-slc.md) (campos, LGPD). |

### Critério de “pronto”

- Textos **originais** (não copiar Sagitta/Venturus nem terceiros).
- Revisão interna de negócio (SLC) antes de jurídico onde houver promessa ou dados sensíveis.

---

## 2. Jurídico (políticas e termos)

### O que é

Documentos que definem **como** a SLC trata dados pessoais e **quais** são as regras de uso do site/serviços. Devem ser **redigidos ou validados por profissional habilitado** (advogado).

### O que contemplar

| Documento | Finalidade típica |
|-----------|---------------------|
| **Política de privacidade** | Bases legais, finalidades do tratamento, direitos do titular, contato do encarregado/DPO se aplicável, cookies na medida em que identifiquem pessoas. |
| **Termos de uso** | Uso do site, limitações de responsabilidade (com limites legais), propriedade intelectual, lei aplicável. |
| **Política de cookies** | Lista/categorias de cookies e tecnologias similares, finalidades, como gerir preferências (ligada ao banner). |

### Processo recomendado

1. **Rascunho** a partir da stack real (Laravel, formulários, e-mail, analytics previstos).
2. **Revisão jurídica** (contrato ou consultoria pontual).
3. **Publicação** em URLs estáveis (ex.: `/politica-de-privacidade`, `/termos-de-uso`, `/politica-de-cookies`) e links no rodapé e no fluxo de consentimento.
4. **Registos internos** da SLC: registro de operações de tratamento (ROPA) e canal de titulares, conforme política da empresa.

### O que esta documentação **não** faz

Não fornece modelos legais prontos — apenas estrutura o que deve existir e onde encaixa no projeto.

---

## 3. Cookies e LGPD (implementação técnica)

### O que é

Além do **texto** da política, a LGPD exige **controle técnico**: não tratar dados por cookies/scripts **não essenciais** sem consentimento válido.

### O que contemplar (checklist técnico)

| Requisito | Detalhe |
|-----------|---------|
| **Banner de consentimento** | Primeira camada: informar categorias (necessários, preferências, analytics, marketing conforme o caso). |
| **Gate de scripts** | Scripts de analytics (ex.: Google Analytics), tags de marketing e similares **só** após opt-in na categoria correspondente — ver [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md). |
| **Scripts estritamente necessários** | Ex.: sessão, CSRF, equilíbrio de carga — documentar como “necessários” e não sujeitos a opt-in, conforme parecer jurídico. |
| **Persistência da escolha** | Cookie/local seguro ou backend; respeitar em visitas seguintes. |
| **Revogação / alteração** | Link “Gerir cookies” (ou equivalente) no rodapé. |
| **CSP** | Content-Security-Policy alinhada às origens realmente usadas após consentimento — [normas-arquitetura-backend-infra.md](normas-arquitetura-backend-infra.md). |

### Ferramentas possíveis

Solução **própria** (banner + carregamento condicional no Astro) ou **terceira** (Cookiebot, OneTrust, Osano, etc.) desde que implementem a mesma lógica de **bloqueio prévio**.

### Ligação com o formulário de leads

Checkbox de consentimento alinhada à política — [formulario-contato-lead-slc.md](formulario-contato-lead-slc.md).

---

## Documentos relacionados

| Documento | Conteúdo |
|-----------|-----------|
| [dominios-e-ambiente.md](../definicoes/dominios-e-ambiente.md) | Domínio canônico (apex). |
| [compliance-ferramentas.md](../definicoes/compliance-ferramentas.md) | Consent Mode, analytics, CSP. |
| [planejamento-e-faltantes-site-institucional.md](planejamento-e-faltantes-site-institucional.md) | Checklist do projeto. |
