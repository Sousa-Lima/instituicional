/** Formato alinhado a ServiceResource (Laravel). */
export interface ServiceResource {
	id: string;
	slug: string;
	title: string;
	short_description: string | null;
	content_html: string | null;
	icon_name: string | null;
	category: string | null;
	seo: Record<string, unknown> | null;
	order: number;
}

/** Formato alinhado a CaseStudyResource (Laravel). */
export interface CaseStudyResource {
	id: string;
	slug: string;
	status: string;
	featured: boolean;
	title: string;
	customer_name: string | null;
	sector: string | null;
	short_summary: string | null;
	content_html: string | null;
	metrics: unknown;
	main_image: {
		url: string;
		alt?: string;
		width?: number;
		height?: number;
	} | null;
	seo: Record<string, unknown> | null;
}
