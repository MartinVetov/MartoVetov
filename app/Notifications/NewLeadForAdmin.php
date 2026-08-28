<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadForAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead;

        return (new MailMessage)
            ->subject("Нова заявка {$lead->reference} — {$lead->categoryLabel()}")
            ->greeting('Нова заявка в „Намери Техник“')
            ->line("Заявка № {$lead->reference}")
            ->line('Техника: '.$lead->categoryLabel().($lead->equipment_type ? ' — '.$lead->equipment_type : ''))
            ->line('Локация: '.$lead->locationLabel())
            ->line('Дата: '.$lead->dateLabel())
            ->line('Продължителност: '.$lead->duration->label())
            ->line('Оператор: '.$lead->operator_required->shortLabel())
            ->line('Клиент: '.$lead->contact_name.', '.$lead->contact_phone.', '.$lead->contact_email)
            ->line('Описание на задачата:')
            ->line($lead->description)
            ->action('Отвори заявката', route('admin.leads.show', $lead))
            ->salutation('Поздрави, '.config('nt.brand'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'reference' => $this->lead->reference,
        ];
    }
}
