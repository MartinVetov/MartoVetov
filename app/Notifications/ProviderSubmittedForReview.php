<?php

namespace App\Notifications;

use App\Models\ProviderProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProviderSubmittedForReview extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProviderProfile $profile) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Нов доставчик за одобрение: '.$this->profile->company_name)
            ->line($this->profile->company_name.' изпрати профила си за одобрение.')
            ->line('Град: '.($this->profile->city?->name ?? 'не е посочен'))
            ->line('Телефон: '.$this->profile->phone)
            ->action('Прегледай профила', route('admin.providers.show', $this->profile))
            ->salutation('Поздрави, '.config('nt.brand'));
    }
}
