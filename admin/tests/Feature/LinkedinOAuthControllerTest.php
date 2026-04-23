<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LinkedinOAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('FILAMENT_ADMIN_EMAILS=admin@example.com');
        $_ENV['FILAMENT_ADMIN_EMAILS'] = 'admin@example.com';
        $_SERVER['FILAMENT_ADMIN_EMAILS'] = 'admin@example.com';

        config([
            'services.linkedin.author_urn' => 'urn:li:organization:112813111',
            'services.linkedin.client_id' => 'client-id-123',
            'services.linkedin.client_secret' => 'client-secret-456',
            'services.linkedin.redirect_uri' => 'https://api.example.com/admin/integrations/linkedin/callback',
            'services.linkedin.scopes' => ['w_organization_social'],
        ]);
    }

    public function test_connect_redirects_admin_to_linkedin_authorization(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        $response = $this->actingAs($user)->get(route('admin.linkedin.connect'));

        $response->assertRedirect();

        $location = (string) $response->headers->get('Location');

        $this->assertStringStartsWith('https://www.linkedin.com/oauth/v2/authorization?', $location);

        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        $this->assertSame('code', $query['response_type'] ?? null);
        $this->assertSame('client-id-123', $query['client_id'] ?? null);
        $this->assertSame('https://api.example.com/admin/integrations/linkedin/callback', $query['redirect_uri'] ?? null);
        $this->assertSame('w_organization_social', $query['scope'] ?? null);
        $this->assertNotEmpty($query['state'] ?? null);
    }

    public function test_callback_exchanges_code_and_renders_token(): void
    {
        Http::fake([
            'https://www.linkedin.com/oauth/v2/accessToken' => Http::response([
                'access_token' => 'linkedin-token-xyz',
                'expires_in' => 3600,
            ]),
        ]);

        $user = User::factory()->create(['email' => 'admin@example.com']);

        $response = $this->actingAs($user)
            ->withSession(['linkedin_oauth_state' => 'state-123'])
            ->get(route('admin.linkedin.callback', [
                'code' => 'oauth-code-123',
                'state' => 'state-123',
            ]));

        $response->assertOk();
        $response->assertSee('linkedin-token-xyz');
        $response->assertSee('docker secret create slc_sousalima_linkedin_token -', false);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://www.linkedin.com/oauth/v2/accessToken'
                && $request['grant_type'] === 'authorization_code'
                && $request['code'] === 'oauth-code-123'
                && $request['client_id'] === 'client-id-123'
                && $request['client_secret'] === 'client-secret-456';
        });
    }

    public function test_callback_renders_error_when_linkedin_denies_authorization(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        $response = $this->actingAs($user)
            ->withSession(['linkedin_oauth_state' => 'state-123'])
            ->get(route('admin.linkedin.callback', [
                'error' => 'access_denied',
                'error_description' => 'User denied access',
                'state' => 'state-123',
            ]));

        $response->assertOk();
        $response->assertSee('User denied access');
    }
}