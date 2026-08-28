<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadReceivedForCustomer;
use App\Notifications\NewLeadForAdmin;
use App\Services\LeadDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

/**
 * Обработва новопостъпила заявка: известява администраторите и — ако
 * автоматичното изпращане е включено — я насочва към подходящи доставчици.
 */
class ProcessNewLead implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    public function handle(LeadDispatchService $dispatcher): void
    {
        $this->lead->loadMissing(['category', 'city']);

        // Потвърждение към клиента — обещано е още във формата.
        Notification::route('mail', $this->lead->contact_email)
            ->notify(new LeadReceivedForCustomer($this->lead));

        $admins = User::where('role', 'admin')->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewLeadForAdmin($this->lead));
        } else {
            Notification::route('mail', config('nt.admin_email'))
                ->notify(new NewLeadForAdmin($this->lead));
        }

        $dispatcher->autoDispatch($this->lead);
    }
}
