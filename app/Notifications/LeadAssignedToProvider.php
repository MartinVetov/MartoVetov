<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Models\LeadProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Известие към доставчик за нова заявка. Каналите са изнесени в метода via(),
 * за да може по-късно да се добавят SMS или push без промяна по логиката.
 */
class LeadAssignedToProvider extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead, public LeadProvider $assignment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead;

        $mail = (new MailMessage)
            ->subject('Нова заявка за техника — '.$lead->categoryLabel().', '.($lead->city?->name ?? 'България'))
            ->greeting('Здравей!')
            ->line('Имаш нова заявка от клиент, който търси техника в твоя район.')
            ->line('**Техника:** '.$lead->categoryLabel().($lead->equipment_type ? ' — '.$lead->equipment_type : ''))
            ->line('**Град:** '.$lead->locationLabel())
            ->line('**Дата:** '.$lead->dateLabel())
            ->line('**Продължителност:** '.$lead->duration->label())
            ->line('**Оператор:** '.$lead->operator_required->shortLabel())
            ->line('**Задачата:** '.$lead->description);

        return $mail
            ->action('Виж заявката', route('provider.leads.show', $this->assignment->id))
            ->line('Данните за контакт с клиента са видими в профила ти, след като приемеш заявката.')
            ->salutation('Поздрави, '.config('nt.brand'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'assignment_id' => $this->assignment->id,
        ];
    }
}
