<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProviderApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Профилът ти в „Намери Техник“ е одобрен')
            ->greeting('Добре дошъл в „Намери Техник“!')
            ->line('Профилът ти е одобрен и вече може да получаваш заявки от клиенти в твоя район.')
            ->line('Увери се, че техниката и обслужваните градове са попълнени — така заявките ще стигат до теб по-точно.')
            ->action('Отвори профила си', route('provider.dashboard'))
            ->salutation('Поздрави, '.config('nt.brand'));
    }
}
