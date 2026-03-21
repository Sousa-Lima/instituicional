/**
 * Cliente HTTP para a API Laravel (build-time e, no futuro, rotas server).
 * Variável pública: PUBLIC_API_BASE_URL (sem barra final).
 */

export function getApiBaseUrl(): string {
	const base = import.meta.env.PUBLIC_API_BASE_URL;
	if (typeof base !== 'string' || base === '') {
		return '';
	}
	return base.replace(/\/$/, '');
}

export function apiUrl(path: string): string {
	const base = getApiBaseUrl();
	const p = path.startsWith('/') ? path : `/${path}`;
	if (!base) {
		return p;
	}
	return `${base}${p}`;
}
