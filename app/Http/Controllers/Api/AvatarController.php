<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateAvatarRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Upload seguro do avatar: o arquivo é validado em UpdateAvatarRequest e
 * salvo com nome aleatório (hash) — o nome enviado pelo usuário é descartado.
 */
class AvatarController extends Controller
{
    public function store(UpdateAvatarRequest $request): UserResource
    {
        /** @var User $user */
        $user = $request->user();
        $previous = $user->avatar;

        $path = $request->file('avatar')?->store('avatars', 'public');
        $user->update(['avatar' => $path ?: null]);

        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        return UserResource::make($user);
    }

    public function destroy(Request $request): UserResource
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return UserResource::make($user);
    }
}
