import { useEffect, useState } from 'react';

import { fetchServiceBySlugPublic, publicApiBase } from '../lib/api-public';
import type { ServiceResource } from '../types/api';

export default function ServiceDetailClient({ slug }: { slug: string }) {
	const [data, setData] = useState<ServiceResource | null>(null);
	const [err, setErr] = useState<string | null>(null);

	useEffect(() => {
		let cancelled = false;
		(async () => {
			if (!publicApiBase()) {
				if (!cancelled) setErr('PUBLIC_API_BASE_URL não está definido.');
				return;
			}
			try {
				const s = await fetchServiceBySlugPublic(slug);
				if (!cancelled) setData(s);
			} catch {
				if (!cancelled) setErr('Não foi possível carregar este serviço.');
			}
		})();
		return () => {
			cancelled = true;
		};
	}, [slug]);

	useEffect(() => {
		if (data?.title) {
			document.title = `${data.title} — Serviços | Sousa Lima Consultoria`;
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
			<p className="text-sm font-medium uppercase tracking-wider text-slc-navy">Serviço</p>
			<h1 className="font-display mt-2 text-4xl font-bold text-slc-navy">{data.title}</h1>
			{data.short_description && (
				<p className="mt-4 text-lg text-slc-text-muted">{data.short_description}</p>
			)}
			{data.content_html && (
				<div
					className="slc-content mt-10 max-w-none text-slc-text [&_a]:text-slc-purple [&_a]:underline [&_h2]:mt-8 [&_h2]:font-display [&_h2]:text-2xl [&_h3]:mt-6 [&_p]:leading-relaxed"
					dangerouslySetInnerHTML={{ __html: data.content_html }}
				/>
			)}
			<p className="mt-12">
				<a href="/servicos" className="text-sm font-medium text-slc-purple hover:text-slc-navy">
					← Todos os serviços
				</a>
			</p>
		</>
	);
}
