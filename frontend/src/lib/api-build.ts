/**
 * Chamadas à API Laravel no tempo de build (SSG).
 * Usa `PUBLIC_API_BASE_URL` e, quando existir, `API_READ_TOKEN` (não PUBLIC — não vai ao bundle).
 *
 * `BUILD_API_BASE_URL` (opcional): URL interna só no build (ex.: rede Docker / host) — tem prioridade sobre `PUBLIC_*`.
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

const FETCH_RETRIES = 3;
const FETCH_TIMEOUT_MS = 45_000;

/** Lista de slugs (serviços ou cases) vinda de env no CI, ex.: `slug-a,slug-b`. */
export function parseSlugEnvList(value: string | undefined): string[] {
	return (value ?? '')
		.split(',')
		.map((s) => s.trim())
		.filter(Boolean);
}

function buildTimeBaseUrl(): string {
	const internal = (process.env.BUILD_API_BASE_URL ?? '').replace(/\/$/, '');
	if (internal) {
		return internal;
	}
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
	const base = buildTimeBaseUrl();
	if (!base) {
		throw new Error('Defina PUBLIC_API_BASE_URL ou BUILD_API_BASE_URL no ambiente de build');
	}
	return base;
}

async function fetchWithRetry(url: string, init: RequestInit): Promise<Response> {
	let lastErr: unknown;
	for (let attempt = 0; attempt < FETCH_RETRIES; attempt++) {
		const controller = new AbortController();
		const timer = setTimeout(() => controller.abort(), FETCH_TIMEOUT_MS);
		try {
			const res = await fetch(url, { ...init, signal: controller.signal });
			clearTimeout(timer);
			if (res.status >= 500 && attempt < FETCH_RETRIES - 1) {
				await new Promise((r) => setTimeout(r, 600 * (attempt + 1)));
				continue;
			}
			return res;
		} catch (e) {
			clearTimeout(timer);
			lastErr = e;
			if (attempt < FETCH_RETRIES - 1) {
				await new Promise((r) => setTimeout(r, 600 * (attempt + 1)));
				continue;
			}
			throw e;
		}
	}
	throw lastErr;
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
	const res = await fetchWithRetry(url, { headers: buildHeaders() });
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
