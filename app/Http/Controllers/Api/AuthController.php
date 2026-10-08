<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Autenticação com Laravel Sanctum em dois modos:
 *
 *  1. SPA (padrão): o frontend Vue chama GET /sanctum/csrf-cookie e depois
 *     /api/auth/login. A sessão fica em cookie HttpOnly — nenhum token é
 *     exposto ao JavaScript (protege contra roubo de token via XSS).
 *  2. Token: clientes externos (Postman, app mobile) que não enviam cookie de
 *     sessão recebem um token Bearer na resposta do login/cadastro.
 */
class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUser $registerUser): JsonResponse
    {
        $user = $registerUser->handle($request->validated());

        return $this->authenticated($request, $user, Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        /** @var User $user */
        $user = Auth::guard('web')->user();

        return $this->authenticated($request, $user->load(['organization', 'volunteer']));
    }

    public function logout(Request $request): Response
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        } else {
            Auth::guard('web')->logout();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }

    private function authenticated(Request $request, User $user, int $status = Response::HTTP_OK): JsonResponse
    {
        $payload = ['data' => UserResource::make($user)->resolve($request)];

        if ($request->hasSession()) {
            Auth::guard('web')->login($user, $request->boolean('remember'));
            // Evita fixação de sessão.
            $request->session()->regenerate();
        } else {
            $payload['token'] = $user->createToken($request->userAgent() ?? 'api')->plainTextToken;
        }

        return response()->json($payload, $status);
    }
}
