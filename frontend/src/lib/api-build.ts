/**
 * Chamadas à API Laravel no tempo de build (SSG).
 * Usa `PUBLIC_API_BASE_URL` e, quando existir, `API_READ_TOKEN` (não PUBLIC — não vai ao bundle).
 */
import type { CaseStudyResource, ServiceResource } from '../types/api';

function buildBaseUrl(): string {
	const base = (process.env.PUBLIC_API_BASE_URL ?? '').replace(/\/$/, '');
	if (!base) {
		throw new Error('PUBLIC_API_BASE_URL ausente no ambiente de build');
	}
	return base;
}

function buildHeaders(): HeadersInit {
	const token = process.env.API_READ_TOKEN ?? '';
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
