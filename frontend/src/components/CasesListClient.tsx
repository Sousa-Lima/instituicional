import { useEffect, useState } from 'react';

import { fetchCasesListPublic, publicApiBase } from '../lib/api-public';
import type { CaseStudyResource } from '../types/api';

export default function CasesListClient() {
	const [items, setItems] = useState<CaseStudyResource[] | null>(null);
	const [err, setErr] = useState<string | null>(null);

	useEffect(() => {
		let cancelled = false;
		(async () => {
			if (!publicApiBase()) {
				if (!cancelled) setErr('PUBLIC_API_BASE_URL não está definido.');
				return;
			}
			try {
				const list = await fetchCasesListPublic();
				if (!cancelled) setItems(list);
			} catch {
				if (!cancelled) setErr('Não foi possível carregar os cases. Tente mais tarde.');
			}
		})();
		return () => {
			cancelled = true;
		};
	}, []);

	if (err) {
		return (
			<p className="mt-8 rounded-xl border border-dashed border-slc-grey/30 bg-slc-bg p-8 text-center text-slc-text-muted">
				{err}
			</p>
		);
	}

	if (items === null) {
		return (
			<div className="mt-12 grid gap-6 md:grid-cols-2" aria-busy="true">
				{[1, 2, 3, 4].map((i) => (
					<div
						key={i}
						className="h-48 animate-pulse rounded-xl border border-slc-grey/15 bg-white"
					/>
				))}
			</div>
		);
	}

	if (items.length === 0) {
		return (
			<p className="mt-12 rounded-xl border border-dashed border-slc-grey/30 bg-slc-bg p-8 text-center text-slc-text-muted">
				Nenhum case publicado na API.
			</p>
		);
	}

	return (
		<ul className="mt-12 grid gap-6 md:grid-cols-2">
			{items.map((c) => (
				<li key={c.id}>
					<a
						href={`/cases/${c.slug}`}
						className="block h-full rounded-xl border border-slc-grey/15 bg-white p-6 shadow-sm transition hover:border-slc-magenta/30 hover:shadow-md"
					>
						{c.featured && (
							<span className="inline-block rounded-full bg-slc-magenta/10 px-2 py-0.5 text-xs font-semibold text-slc-navy">
								Destaque
							</span>
						)}
						<h2 className="font-display mt-2 text-xl font-semibold text-slc-navy">{c.title}</h2>
						{(c.customer_name || c.sector) && (
							<p className="mt-2 text-sm text-slc-text-muted">
								{[c.customer_name, c.sector].filter(Boolean).join(' · ')}
							</p>
						)}
						{c.short_summary && (
							<p className="mt-3 text-sm text-slc-text-muted">{c.short_summary}</p>
						)}
						<span className="mt-4 inline-block text-sm font-medium text-slc-purple">Ler case →</span>
					</a>
				</li>
			))}
		</ul>
	);
}
