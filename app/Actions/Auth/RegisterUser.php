<?php

namespace App\Actions\Auth;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Cria a conta e o perfil correspondente ao tipo escolhido (ONG ou voluntário).
 *
 * A ONG nasce com o nome informado no cadastro, e o voluntário nasce
 * "disponível" e sem localização (a localização exige consentimento explícito).
 */
class RegisterUser
{
    /**
     * @param  array<string, mixed>  $data  Dados validados por RegisterRequest
     *                                      (name, email, password, type, phone?, organization_name?).
     */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'type' => $data['type'],
                'phone' => $data['phone'] ?? null,
            ]);

            match ($user->type) {
                UserType::Organization => $user->organization()->create([
                    'name' => $data['organization_name'] ?? $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                ]),
                UserType::Volunteer => $user->volunteer()->create([
                    'phone' => $data['phone'] ?? null,
                ]),
            };

            return $user->load(['organization', 'volunteer']);
        });
    }
}
