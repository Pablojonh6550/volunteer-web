<?php

namespace App\Notifications;

use App\Models\RequestVolunteer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa o voluntário que a ONG aprovou, recusou ou concluiu sua participação.
 */
class ParticipationStatusChanged extends Notification implements ShouldQueue
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
        $status = mb_strtolower($this->participation->status->label());

        return (new MailMessage)
            ->subject("Sua participação foi atualizada: {$status}")
            ->line("Sua participação na solicitação \"{$request->title}\" agora está: {$status}.")
            ->action('Minhas atividades', url('/voluntario/atividades'));
    }
}
