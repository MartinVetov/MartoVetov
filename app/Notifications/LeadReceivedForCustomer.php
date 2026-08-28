<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Потвърждение към клиента веднага след подаване на заявка.
 *
 * Съзнателно не обещаваме конкретен брой оферти — обещаваме само това,
 * което системата гарантира: че заявката е приета и ще бъде насочена.
 */
class LeadReceivedForCustomer extends Notification implements ShouldQueue
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

        $mail = (new MailMessage)
            ->subject('Заявка № '.$lead->reference.' е получена')
            ->greeting('Здравей, '.$lead->contact_name.'!')
            ->line('Получихме твоята заявка. Ето какво записахме:')
            ->line('**Номер на заявката:** '.$lead->reference)
            ->line('**Техника:** '.$lead->categoryLabel().($lead->equipment_type ? ' — '.$lead->equipment_type : ''))
            ->line('**Локация:** '.$lead->locationLabel())
            ->line('**Дата:** '.$lead->dateLabel())
            ->line('**Продължителност:** '.$lead->duration->label())
            ->line('**Оператор:** '.$lead->operator_required->shortLabel());

        if ($lead->description) {
            $mail->line('**Задачата:** '.$lead->description);
        }

        return $mail
            ->line('**Какво следва.** Насочваме заявката към доставчици на техника в твоя район, които разполагат с подходяща машина. Тези от тях, които са свободни за твоята дата, ще се свържат директно с теб по телефона или имейла.')
            ->action('Виж заявката си', route('leads.thanks', $lead))
            ->line('Запази номера на заявката — ще ти трябва, ако имаш въпрос към нас.')
            ->line('Ако това съобщение е стигнало до теб по грешка, просто го игнорирай — няма да получиш повече писма по тази заявка.')
            ->salutation('Поздрави, '.config('nt.brand').' · '.config('nt.contact.email'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'reference' => $this->lead->reference,
        ];
    }
}
