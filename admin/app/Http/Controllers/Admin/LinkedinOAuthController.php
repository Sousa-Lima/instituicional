<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class LinkedinOAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $clientId = (string) config('services.linkedin.client_id');
        $redirectUri = (string) config('services.linkedin.redirect_uri');
        $scopes = $this->scopes();

        abort_if(
            $clientId === '' || $redirectUri === '' || $scopes === [],
            500,
            'Configuração OAuth do LinkedIn incompleta.',
        );

        $state = bin2hex(random_bytes(20));

        $request->session()->put('linkedin_oauth_state', $state);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => implode(' ', $scopes),
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away("https://www.linkedin.com/oauth/v2/authorization?{$query}");
    }

    public function callback(Request $request): View
    {
        $this->authorizeAdmin($request->user());

        if ($request->filled('error')) {
            return $this->renderResult(error: (string) ($request->query('error_description') ?: $request->query('error')));
        }

        $expectedState = (string) $request->session()->pull('linkedin_oauth_state', '');
        $state = (string) $request->query('state', '');

        abort_unless($expectedState !== '' && hash_equals($expectedState, $state), 403, 'State OAuth inválido.');

        $code = (string) $request->query('code', '');
        $clientId = (string) config('services.linkedin.client_id');
        $clientSecret = (string) config('services.linkedin.client_secret');
        $redirectUri = (string) config('services.linkedin.redirect_uri');

        abort_if($code === '', 422, 'Callback do LinkedIn sem code.');
        abort_if(
            $clientId === '' || $clientSecret === '' || $redirectUri === '',
            500,
            'Configuração OAuth do LinkedIn incompleta.',
        );

        $response = Http::asForm()
            ->acceptJson()
            ->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

        if ($response->failed()) {
            return $this->renderResult(
                error: (string) ($response->json('error_description') ?: $response->json('error') ?: $response->body()),
            );
        }

        $accessToken = (string) $response->json('access_token', '');

        if ($accessToken === '') {
            return $this->renderResult(error: 'LinkedIn não retornou access_token.');
        }

        return $this->renderResult(
            accessToken: $accessToken,
            expiresIn: (int) $response->json('expires_in', 0),
        );
    }

    private function authorizeAdmin(mixed $user): void
    {
        abort_unless(
            $user instanceof User && $user->canAccessPanel(Filament::getPanel('admin')),
            403,
        );
    }

    /**
     * @return array<int, string>
     */
    private function scopes(): array
    {
        $scopes = config('services.linkedin.scopes', []);

        if (is_array($scopes)) {
            return array_values(array_filter(array_map(
                static fn (mixed $scope): string => trim((string) $scope),
                $scopes,
            )));
        }

        return array_values(array_filter(preg_split('/[\s,]+/', (string) $scopes) ?: []));
    }

    private function renderResult(?string $accessToken = null, int $expiresIn = 0, ?string $error = null): View
    {
        return view('admin.linkedin.callback', [
            'accessToken' => $accessToken,
            'authorUrn' => (string) config('services.linkedin.author_urn'),
            'error' => $error,
            'expiresAt' => $expiresIn > 0 ? now()->addSeconds($expiresIn) : null,
            'redirectUri' => (string) config('services.linkedin.redirect_uri'),
            'secretName' => 'slc_sousalima_linkedin_token',
            'scopes' => $this->scopes(),
        ]);
    }
}