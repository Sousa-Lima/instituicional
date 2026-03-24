<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Http\Middleware\Authenticate as JwtAuthenticate;

/**
 * Endpoints usados pelo site / build: Bearer API_READ_TOKEN (CI) ou JWT (utilizadores).
 * GET de conteúdo publicado: também aceites sem Bearer quando Origin/Referer coincidem com CORS
 * (site estático que hidrata no browser sem expor o token).
 * POST /lead/contact: também aceite sem Bearer se o header Origin coincidir com CORS (formulário no site).
 */
class AuthenticateJwtOrApiReadToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $expected = (string) config('services.api.read_token', '');
        $bearer = (string) ($request->bearerToken() ?? '');

        if ($expected !== '' && $bearer !== '' && hash_equals($expected, $bearer)) {
            return $next($request);
        }

        if ($this->isPublicContentGetFromAllowedOrigin($request)) {
            return $next($request);
        }

        if ($expected === '' && app()->isLocal()) {
            return $next($request);
        }

        if ($this->isPublicLeadFromAllowedOrigin($request)) {
            return $next($request);
        }

        return app(JwtAuthenticate::class)->handle($request, $next);
    }

    /**
     * Leitura pública de conteúdo já publicado (lista/detalhe de serviços e cases, slugs para SSG).
     * Exige Origin ou Referer alinhado a {@see config('cors.allowed_origins')}.
     */
    private function isPublicContentGetFromAllowedOrigin(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        $path = $request->path();
        $prefixes = [
            'api/v1/services',
            'api/v1/cases',
            'api/v1/content/slugs',
        ];

        $matches = false;
        foreach ($prefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                $matches = true;
                break;
            }
        }

        if (! $matches) {
            return false;
        }

        return $this->originOrRefererMatchesCors($request);
    }

    /**
     * @param  array<int, string>  $allowed
     */
    private function originOrRefererMatchesCors(Request $request): bool
    {
        /** @var array<int, string> $allowed */
        $allowed = config('cors.allowed_origins', []);

        $origin = (string) $request->header('Origin', '');
        if ($origin !== '' && in_array($origin, $allowed, true)) {
            return true;
        }

        $referer = (string) $request->header('Referer', '');
        foreach ($allowed as $o) {
            if ($o === '') {
                continue;
            }
            $base = rtrim($o, '/');
            if ($referer === $base || str_starts_with($referer, $base.'/')) {
                return true;
            }
        }

        return false;
    }

    private function isPublicLeadFromAllowedOrigin(Request $request): bool
    {
        if (! $request->is('api/v1/lead/contact') || ! $request->isMethod('POST')) {
            return false;
        }

        $origin = (string) $request->header('Origin', '');
        if ($origin === '') {
            return false;
        }

        $allowed = config('cors.allowed_origins', []);

        return in_array($origin, $allowed, true);
    }
}
