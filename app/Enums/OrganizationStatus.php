<?php

namespace App\Enums;

/**
 * Situação cadastral da ONG. Apenas ONGs ativas aparecem para voluntários.
 */
enum OrganizationStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativa',
            self::Inactive => 'Inativa',
        };
    }
}
