<?php

namespace App\Notifications;

use App\Models\RequestVolunteer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa a ONG que um voluntário demonstrou interesse em uma solicitação.
 *
 * Enviada pela fila (ShouldQueue) e somente após o commit da transação.
 * Não inclui dados de contato do voluntário: eles só ficam visíveis na
 * plataforma após a aprovação (controle de exposição de dados pessoais).
 */
class VolunteerInterestReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public RequestVolunteer $participation)
    {
        $this->afterCommit();
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->participation->request;

        return (new MailMessage)
            ->subject('Novo voluntário interessado')
            ->greeting('Olá!')
            ->line("Um voluntário demonstrou interesse na solicitação \"{$request->title}\".")
            ->action('Ver interessados', url("/ong/solicitacoes/{$request->id}/voluntarios"))
            ->line('Acesse a plataforma para aprovar ou recusar a participação.');
    }
}
