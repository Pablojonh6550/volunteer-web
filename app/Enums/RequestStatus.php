<?php

namespace App\Enums;

/**
 * Ciclo de vida de uma solicitação.
 *
 *   open ──► in_progress ──► completed
 *     │            │
 *     └────────────┴──────► cancelled
 *
 * `completed` e `cancelled` são estados finais.
 */
enum RequestStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Aberta',
            self::InProgress => 'Em andamento',
            self::Completed => 'Concluída',
            self::Cancelled => 'Cancelada',
        };
    }

    /**
     * Estados para os quais é permitido transitar a partir do atual.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open => [self::InProgress, self::Completed, self::Cancelled],
            self::InProgress => [self::Open, self::Completed, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return $status === $this || in_array($status, $this->allowedTransitions(), true);
    }

    /** Estado final: a solicitação não pode mais ser editada. */
    public function isFinal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    /** Solicitações neste estado aceitam novas manifestações de interesse. */
    public function acceptsInterest(): bool
    {
        return in_array($this, [self::Open, self::InProgress], true);
    }

    /**
     * Estados visíveis publicamente para voluntários.
     *
     * @return list<self>
     */
    public static function publicStatuses(): array
    {
        return [self::Open, self::InProgress];
    }
}
