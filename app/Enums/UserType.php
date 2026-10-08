<?php

namespace App\Enums;

/**
 * Perfil do usuário na plataforma.
 *
 * Define qual ambiente (ONG ou Voluntário) o usuário acessa e é a base da
 * autorização por tipo de usuário (middleware `user.type` e Policies).
 */
enum UserType: string
{
    case Organization = 'organization';
    case Volunteer = 'volunteer';

    public function label(): string
    {
        return match ($this) {
            self::Organization => 'ONG',
            self::Volunteer => 'Voluntário',
        };
    }
}
