/// <reference types="astro/client" />

interface ImportMetaEnv {
	/** URL base da API Laravel, ex.: https://api.sousalimaconsultoria.com.br */
	readonly PUBLIC_API_BASE_URL: string;
}

interface ImportMeta {
	readonly env: ImportMetaEnv;
}

/** Consentimento de cookies (CookieConsent.astro) — útil para integrar GA4 / tags após consentimento. */
interface Window {
	slcOpenCookiePreferences?: () => void;
	slcOnConsentUpdate?: (payload: SlcConsentPayload) => void;
}

/** Formato em localStorage `slc_consent_v1`. */
interface SlcConsentPayload {
	v: number;
	necessary: boolean;
	preferences: boolean;
	analytics: boolean;
	marketing: boolean;
	ts: string;
}
