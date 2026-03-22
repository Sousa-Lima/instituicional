/**
 * Quando o `fetch` da API falha no build e `BUILD_SLUGS_SERVICES` / `BUILD_SLUGS_CASES` não estão definidos,
 * usamos estes slugs para ainda gerar HTML estático (conteúdo completo vem da API no browser).
 * Atualize quando publicar novos serviços/cases ou defina as variáveis de ambiente no CI.
 */
export const FALLBACK_SERVICE_SLUGS: readonly string[] = ['consultoria-processos'];

export const FALLBACK_CASE_SLUGS: readonly string[] = ['modernizacao-plataforma-logistica-ai'];
