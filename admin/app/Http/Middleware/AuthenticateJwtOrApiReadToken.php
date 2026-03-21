<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Http\Middleware\Authenticate as JwtAuthenticate;

/**
 * Endpoints usados pelo site / build: Bearer API_READ_TOKEN (CI) ou JWT (utilizadores).
 */
class AuthenticateJwtOrApiReadToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.api.read_token', '');
        $bearer = (string) ($request->bearerToken() ?? '');

        if ($expected !== '' && $bearer !== '' && hash_equals($expected, $bearer)) {
            return $next($request);
        }

        if ($expected === '' && app()->isLocal()) {
            return $next($request);
        }

        return app(JwtAuthenticate::class)->handle($request, $next);
    }
}
