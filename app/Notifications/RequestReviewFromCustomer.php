<?php

namespace App\Notifications;

use App\Models\LeadProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Покана към клиента да остави отзив. Линкът е подписан и с давност,
 * за да не е нужен профил.
 */
class RequestReviewFromCustomer extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeadProvider $assignment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'reviews.create',
            now()->addDays(30),
            ['assignment' => $this->assignment->id]
        );

        return (new MailMessage)
            ->subject('Как мина работата с '.$this->assignment->providerProfile->company_name.'?')
            ->greeting('Здравей!')
            ->line('Заявка № '.$this->assignment->lead->reference.' е отбелязана като завършена.')
            ->line('Отдели минута и сподели как мина — това помага на другите клиенти да изберат правилния доставчик.')
            ->action('Остави отзив', $url)
            ->salutation('Благодарим, '.config('nt.brand'));
    }
}
