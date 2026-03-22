import { useEffect, useState } from 'react';

import { fetchServicesListPublic, publicApiBase } from '../lib/api-public';
import type { ServiceResource } from '../types/api';

type Variant = 'grid-2' | 'grid-3';

export default function ServicesListClient({ variant = 'grid-2' }: { variant?: Variant }) {
	const [items, setItems] = useState<ServiceResource[] | null>(null);
	const [err, setErr] = useState<string | null>(null);

	useEffect(() => {
		let cancelled = false;
		(async () => {
			if (!publicApiBase()) {
				if (!cancelled) setErr('PUBLIC_API_BASE_URL não está definido.');
				return;
			}
			try {
				const list = await fetchServicesListPublic();
				if (!cancelled) setItems(list);
			} catch {
				if (!cancelled) setErr('Não foi possível carregar os serviços. Tente mais tarde.');
			}
		})();
		return () => {
			cancelled = true;
		};
	}, []);

	const grid =
		variant === 'grid-3' ? 'md:grid-cols-3' : 'md:grid-cols-2';

	if (err) {
		return (
			<p className="mt-8 rounded-xl border border-dashed border-slc-grey/30 bg-slc-bg p-8 text-center text-slc-text-muted">
				{err}
			</p>
		);
	}

	if (items === null) {
		return (
			<div className={`mt-12 grid gap-6 ${grid}`} aria-busy="true">
				{[1, 2, 3, 4].map((i) => (
					<div
						key={i}
						className="h-40 animate-pulse rounded-xl border border-slc-grey/15 bg-white"
					/>
				))}
			</div>
		);
	}

	if (items.length === 0) {
		return (
			<p className="mt-12 rounded-xl border border-dashed border-slc-grey/30 bg-slc-bg p-8 text-center text-slc-text-muted">
				Nenhum serviço publicado na API.
			</p>
		);
	}

	return (
		<ul className={`mt-12 grid gap-6 ${grid}`}>
			{items.map((s) => (
				<li key={s.id}>
					<a
						href={`/servicos/${s.slug}`}
						className="block h-full rounded-xl border border-slc-grey/15 bg-white p-6 shadow-sm transition hover:border-slc-magenta/30 hover:shadow-md"
					>
						<h2 className="font-display text-xl font-semibold text-slc-navy">{s.title}</h2>
						{s.short_description && (
							<p className="mt-3 text-sm text-slc-text-muted">{s.short_description}</p>
						)}
						<span className="mt-4 inline-block text-sm font-medium text-slc-purple">Ver detalhes →</span>
					</a>
				</li>
			))}
		</ul>
	);
}
