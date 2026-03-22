import { useEffect, useState } from 'react';

import { fetchCaseBySlugPublic, publicApiBase } from '../lib/api-public';
import type { CaseStudyResource } from '../types/api';

export default function CaseStudyDetailClient({ slug }: { slug: string }) {
	const [data, setData] = useState<CaseStudyResource | null>(null);
	const [err, setErr] = useState<string | null>(null);

	useEffect(() => {
		let cancelled = false;
		(async () => {
			if (!publicApiBase()) {
				if (!cancelled) setErr('PUBLIC_API_BASE_URL não está definido.');
				return;
			}
			try {
				const c = await fetchCaseBySlugPublic(slug);
				if (!cancelled) setData(c);
			} catch {
				if (!cancelled) setErr('Não foi possível carregar este case.');
			}
		})();
		return () => {
			cancelled = true;
		};
	}, [slug]);

	useEffect(() => {
		if (data?.title) {
			document.title = `${data.title} — Cases | Sousa Lima Consultoria`;
		}
	}, [data]);

	if (err) {
		return <p className="text-slc-text-muted">{err}</p>;
	}

	if (!data) {
		return <p className="animate-pulse text-slc-text-muted">A carregar…</p>;
	}

	return (
		<>
			<p className="text-sm font-medium uppercase tracking-wider text-slc-navy">Case</p>
			<h1 className="font-display mt-2 text-4xl font-bold text-slc-navy">{data.title}</h1>
			{(data.customer_name || data.sector) && (
				<p className="mt-3 text-slc-text-muted">
					{[data.customer_name, data.sector].filter(Boolean).join(' · ')}
				</p>
			)}
			{data.short_summary && (
				<p className="mt-6 text-lg text-slc-text-muted">{data.short_summary}</p>
			)}
			{data.content_html && (
				<div
					className="slc-content mt-10 max-w-none text-slc-text [&_a]:text-slc-purple [&_a]:underline [&_h2]:mt-8 [&_h2]:font-display [&_h2]:text-2xl [&_h3]:mt-6 [&_p]:leading-relaxed"
					dangerouslySetInnerHTML={{ __html: data.content_html }}
				/>
			)}
			<p className="mt-12">
				<a href="/cases" className="text-sm font-medium text-slc-purple hover:text-slc-navy">
					← Todos os cases
				</a>
			</p>
		</>
	);
}
