/**
 * Chamadas à API Laravel no tempo de build (SSG).
 * Usa `PUBLIC_API_BASE_URL` e, quando existir, `API_READ_TOKEN` (não PUBLIC — não vai ao bundle).
 *
 * Nota: no `astro build`, o Vite nem sempre preenche `process.env` a partir do `.env` neste módulo.
 * Carregamos explicitamente `frontend/.env` e usamos `import.meta.env.PUBLIC_*` como fallback (Astro).
 */
import { config as loadDotenv } from 'dotenv';
import { resolve } from 'node:path';

import type { CaseStudyResource, ServiceResource } from '../types/api';

// No bundle do Vite, `import.meta.url` fica em `dist/chunks/`; não use isso para achar `.env`.
// `astro build` corre com cwd na raiz do frontend (`/app` no Docker).
const envPath = resolve(process.cwd(), '.env');
loadDotenv({ path: envPath });

function publicBaseUrl(): string {
	const fromMeta =
		typeof import.meta !== 'undefined' && import.meta.env?.PUBLIC_API_BASE_URL
			? String(import.meta.env.PUBLIC_API_BASE_URL)
			: '';
	const fromProcess = process.env.PUBLIC_API_BASE_URL ?? '';
	return (fromMeta || fromProcess).replace(/\/$/, '');
}

function apiReadToken(): string {
	return process.env.API_READ_TOKEN ?? '';
}

function buildBaseUrl(): string {
	const base = publicBaseUrl();
	if (!base) {
		throw new Error('PUBLIC_API_BASE_URL ausente no ambiente de build');
	}
	return base;
}

function buildHeaders(): HeadersInit {
	const token = apiReadToken();
	return {
		Accept: 'application/json',
		...(token ? { Authorization: `Bearer ${token}` } : {}),
	};
}

async function fetchJson<T>(path: string): Promise<T> {
	const base = buildBaseUrl();
	const p = path.startsWith('/') ? path : `/${path}`;
	const url = `${base}${p}`;
	const res = await fetch(url, { headers: buildHeaders() });
	if (!res.ok) {
		throw new Error(`API ${res.status}: ${url}`);
	}
	return res.json() as Promise<T>;
}

export async function fetchServicesList(): Promise<ServiceResource[]> {
	const data = await fetchJson<{ data: ServiceResource[] }>('/api/v1/services');
	return data.data ?? [];
}

export async function fetchServicesListSafe(): Promise<ServiceResource[]> {
	try {
		return await fetchServicesList();
	} catch {
		return [];
	}
}

export async function fetchCasesList(): Promise<CaseStudyResource[]> {
	const data = await fetchJson<{ data: CaseStudyResource[] }>('/api/v1/cases');
	return data.data ?? [];
}

export async function fetchCasesListSafe(): Promise<CaseStudyResource[]> {
	try {
		return await fetchCasesList();
	} catch {
		return [];
	}
}
