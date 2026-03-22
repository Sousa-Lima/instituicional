/**
 * Chamadas à API a partir do browser (sem Bearer).
 * Exige que o Laravel aceite GET com Origin/Referer do site (CORS).
 */
import type { CaseStudyResource, ServiceResource } from '../types/api';

export function publicApiBase(): string {
	const b = import.meta.env.PUBLIC_API_BASE_URL ?? '';
	return String(b).replace(/\/$/, '');
}

async function fetchJsonPublic<T>(path: string): Promise<T> {
	const base = publicApiBase();
	if (!base) {
		throw new Error('PUBLIC_API_BASE_URL não definido');
	}
	const p = path.startsWith('/') ? path : `/${path}`;
	const url = `${base}${p}`;
	const res = await fetch(url, {
		headers: { Accept: 'application/json' },
	});
	if (!res.ok) {
		throw new Error(`API ${res.status}`);
	}
	return res.json() as Promise<T>;
}

export async function fetchServicesListPublic(): Promise<ServiceResource[]> {
	const data = await fetchJsonPublic<{ data: ServiceResource[] }>('/api/v1/services');
	return data.data ?? [];
}

export async function fetchCasesListPublic(): Promise<CaseStudyResource[]> {
	const data = await fetchJsonPublic<{ data: CaseStudyResource[] }>('/api/v1/cases');
	return data.data ?? [];
}

export async function fetchServiceBySlugPublic(slug: string): Promise<ServiceResource> {
	const data = await fetchJsonPublic<{ data: ServiceResource }>(`/api/v1/services/${encodeURIComponent(slug)}`);
	return data.data;
}

export async function fetchCaseBySlugPublic(slug: string): Promise<CaseStudyResource> {
	const data = await fetchJsonPublic<{ data: CaseStudyResource }>(`/api/v1/cases/${encodeURIComponent(slug)}`);
	return data.data;
}
