<?php

namespace App\Enums;

/**
 * Situação da participação de um voluntário em uma solicitação.
 *
 * Fluxo de interesse:
 *   interested ──► approved ──► completed
 *        │             │
 *        ├──► rejected ◄┘
 *        └──► cancelled (pelo voluntário, antes da conclusão)
 */
enum RequestVolunteerStatus: string
{
    case Interested = 'interested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Interested => 'Interessado',
            self::Approved => 'Aprovado',
            self::Rejected => 'Recusado',
            self::Cancelled => 'Cancelado',
            self::Completed => 'Concluído',
        };
    }

    /**
     * Transições que a ONG pode aplicar a partir do estado atual.
     *
     * @return list<self>
     */
    public function organizationTransitions(): array
    {
        return match ($this) {
            self::Interested => [self::Approved, self::Rejected],
            self::Approved => [self::Completed, self::Rejected],
            default => [],
        };
    }

    /** O voluntário pode desistir enquanto a participação não foi finalizada. */
    public function cancellableByVolunteer(): bool
    {
        return in_array($this, [self::Interested, self::Approved], true);
    }

    /** Participação "ativa": ocupa a vaga do voluntário na solicitação. */
    public function isActive(): bool
    {
        return in_array($this, [self::Interested, self::Approved], true);
    }
}
