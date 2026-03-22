import { useEffect, useState } from 'react';

import { fetchServicesListPublic, publicApiBase } from '../lib/api-public';
import type { ServiceResource } from '../types/api';

const staticServiceCards = [
	{
		title: 'Consultoria e processos',
		body: 'Otimização de fluxos para reduzir retrabalho e aumentar a eficiência operacional.',
	},
	{
		title: 'Desenvolvimento de software',
		body: 'Criação de sistemas robustos e MVPs escaláveis com stack moderna (Laravel + React).',
	},
	{
		title: 'Cloud e infraestrutura',
		body: 'Arquitetura de alta disponibilidade com Docker Swarm e foco em segurança.',
	},
];

export default function HomeServicesClient() {
	const [items, setItems] = useState<ServiceResource[] | null>(null);

	useEffect(() => {
		let cancelled = false;
		(async () => {
			if (!publicApiBase()) {
				if (!cancelled) setItems([]);
				return;
			}
			try {
				const list = await fetchServicesListPublic();
				if (!cancelled) setItems(list);
			} catch {
				if (!cancelled) setItems([]);
			}
		})();
		return () => {
			cancelled = true;
		};
	}, []);

	if (items === null) {
		return (
			<div className="mt-12 grid gap-6 md:grid-cols-3" aria-busy="true">
				{[1, 2, 3].map((i) => (
					<div
						key={i}
						className="h-44 animate-pulse rounded-xl border border-slc-grey/15 bg-slc-bg/60"
					/>
				))}
			</div>
		);
	}

	if (items.length === 0) {
		return (
			<div className="mt-12 grid gap-6 md:grid-cols-3">
				{staticServiceCards.map((c) => (
					<div key={c.title} className="flex flex-col rounded-xl border border-slc-grey/15 bg-slc-bg/60 p-6">
						<h3 className="font-display text-lg font-semibold text-slc-navy">{c.title}</h3>
						<p className="mt-3 flex-1 text-sm text-slc-text-muted">{c.body}</p>
						<a href="/servicos" className="mt-4 text-sm font-medium text-slc-purple">
							Ver serviços →
						</a>
					</div>
				))}
			</div>
		);
	}

	return (
		<div className="mt-12 grid gap-6 md:grid-cols-3">
			{items.map((s) => (
				<a
					key={s.id}
					href={`/servicos/${s.slug}`}
					className="group flex flex-col rounded-xl border border-slc-grey/15 bg-slc-bg/60 p-6 transition hover:border-slc-magenta/35 hover:shadow-md"
				>
					<h3 className="font-display text-lg font-semibold text-slc-navy group-hover:text-slc-purple">
						{s.title}
					</h3>
					<p className="mt-3 flex-1 text-sm text-slc-text-muted">
						{s.short_description ?? 'Ver detalhes do serviço.'}
					</p>
					<span className="mt-4 text-sm font-medium text-slc-purple">Ver mais →</span>
				</a>
			))}
		</div>
	);
}
