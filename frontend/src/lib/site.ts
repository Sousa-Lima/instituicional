/**
 * URL canónica do site (astro.config → site).
 */
export function getSiteOrigin(site: URL | undefined): string {
	if (site) {
		return site.origin.replace(/\/$/, '');
	}
	return 'https://sousalimaconsultoria.com.br';
}

/** Caminho (ex. /logo.png) ou URL absoluta → URL absoluta para meta OG/Twitter. */
export function absoluteUrl(site: URL | undefined, pathOrUrl: string): string {
	if (pathOrUrl.startsWith('https://') || pathOrUrl.startsWith('http://')) {
		return pathOrUrl;
	}
	const origin = getSiteOrigin(site);
	const path = pathOrUrl.startsWith('/') ? pathOrUrl : `/${pathOrUrl}`;
	return `${origin}${path}`;
}

export const SITE_NAME = 'Sousa Lima Consultoria';

/** Imagem por defeito para og:image / twitter (1200×630 — WhatsApp / Facebook / LinkedIn). */
export const DEFAULT_OG_IMAGE_PATH = '/og-social.png';

export const DEFAULT_OG_IMAGE_WIDTH = 1200;
export const DEFAULT_OG_IMAGE_HEIGHT = 630;
