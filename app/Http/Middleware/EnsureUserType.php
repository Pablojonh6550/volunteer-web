<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe rotas a um tipo de usuário.
 *
 * Uso: ->middleware('user.type:organization') ou 'user.type:volunteer'.
 * Complementa as Policies (que decidem por recurso) com um bloqueio por perfil.
 */
class EnsureUserType
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $type): Response
    {
        if ($request->user()?->type !== UserType::from($type)) {
            abort(403, 'Este recurso não está disponível para o seu perfil.');
        }

        return $next($request);
    }
}
