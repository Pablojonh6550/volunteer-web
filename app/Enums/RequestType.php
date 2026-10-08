<?php

namespace App\Enums;

/**
 * Natureza de uma solicitação publicada por uma ONG.
 *
 * Também é usado para classificar categorias (uma categoria pertence a um tipo).
 */
enum RequestType: string
{
    case Donation = 'donation';
    case Service = 'service';
    case Volunteering = 'volunteering';

    public function label(): string
    {
        return match ($this) {
            self::Donation => 'Doação',
            self::Service => 'Serviço',
            self::Volunteering => 'Voluntariado',
        };
    }
}
