<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Login e emissão de JWT (painel / integrações).
     */
    #[OA\Post(
        path: '/api/v1/auth/login',
        operationId: 'v1AuthLogin',
        description: 'Autenticação por email/password; devolve JWT (Bearer).',
        tags: ['Auth'],
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Token emitido'),
            new OA\Response(response: 401, description: 'Credenciais inválidas'),
            new OA\Response(response: 422, description: 'Validação'),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['message' => 'Credenciais inválidas.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Utilizador autenticado (JWT).
     */
    #[OA\Get(
        path: '/api/v1/auth/me',
        operationId: 'v1AuthMe',
        tags: ['Auth'],
        security: [['jwtAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
        ]);
    }

    /**
     * Invalida o JWT atual.
     */
    #[OA\Post(
        path: '/api/v1/auth/logout',
        operationId: 'v1AuthLogout',
        tags: ['Auth'],
        security: [['jwtAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Sessão JWT invalidada'),
            new OA\Response(response: 401, description: 'Não autenticado'),
        ]
    )]
    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Logout efetuado.']);
    }

    /**
     * Renovar access token (enviar Authorization: Bearer com token ainda válido para refresh).
     */
    #[OA\Post(
        path: '/api/v1/auth/refresh',
        operationId: 'v1AuthRefresh',
        tags: ['Auth'],
        security: [['jwtAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Novo token'),
            new OA\Response(response: 401, description: 'Token inválido'),
        ]
    )]
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }
}
