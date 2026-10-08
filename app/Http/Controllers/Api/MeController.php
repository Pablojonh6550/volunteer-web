<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\DeleteAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Requests\Account\UpdatePasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Conta do usuário autenticado: dados, senha e exclusão (direito de
 * eliminação de dados pessoais — LGPD).
 */
class MeController extends Controller
{
    public function show(Request $request): UserResource
    {
        return UserResource::make($this->currentUser($request)->load(['organization', 'volunteer.categories']));
    }

    public function update(UpdateAccountRequest $request): UserResource
    {
        $user = $this->currentUser($request);
        $user->update($request->validated());

        return UserResource::make($user->load(['organization', 'volunteer.categories']));
    }

    public function updatePassword(UpdatePasswordRequest $request): Response
    {
        $this->currentUser($request)->update(['password' => $request->validated('password')]);

        return response()->noContent();
    }

    public function destroy(DeleteAccountRequest $request): Response
    {
        $user = $this->currentUser($request);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->tokens()->delete();
        Auth::guard('web')->logout();
        // Perfis, solicitações e participações são removidos em cascata (FKs).
        $user->delete();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }

    private function currentUser(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
