/// <reference types="astro/client" />

interface ImportMetaEnv {
	/** URL base da API Laravel, ex.: https://api.sousalimaconsultoria.com.br */
	readonly PUBLIC_API_BASE_URL: string;
}

interface ImportMeta {
	readonly env: ImportMetaEnv;
}
