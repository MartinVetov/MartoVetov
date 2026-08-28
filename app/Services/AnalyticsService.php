<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Проследяване на основната фуния: посетител → заявка → доставчик → контакт → завършена работа.
 */
class AnalyticsService
{
    public const PAGE_VIEW = 'page_view';

    public const CATEGORY_VIEW = 'category_view';

    public const PROVIDER_VIEW = 'provider_view';

    public const LEAD_FORM_STARTED = 'lead_form_started';

    public const LEAD_FORM_STEP = 'lead_form_step';

    public const LEAD_FORM_COMPLETED = 'lead_form_completed';

    public const LEAD_FORM_ABANDONED = 'lead_form_abandoned';

    public const PROVIDER_REGISTERED = 'provider_registered';

    public const PROVIDER_ACTIVATED = 'provider_activated';

    public const LEAD_SENT = 'lead_sent';

    public const LEAD_VIEWED = 'lead_viewed';

    public const LEAD_ACCEPTED = 'lead_accepted';

    public const JOB_COMPLETED = 'job_completed';

    /** Имена на събитията, които приемаме от браузъра. */
    public const CLIENT_EVENTS = [
        self::LEAD_FORM_STARTED,
        self::LEAD_FORM_STEP,
        self::LEAD_FORM_ABANDONED,
        self::CATEGORY_VIEW,
    ];

    public function record(string $name, ?Model $subject = null, array $properties = []): ?AnalyticsEvent
    {
        return AnalyticsEvent::create([
            'name' => $name,
            'session_id' => $this->sessionId(),
            'user_id' => Auth::id(),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'url' => mb_substr((string) Request::fullUrl(), 0, 500),
            'referrer' => mb_substr((string) Request::header('referer'), 0, 500) ?: null,
            'properties' => $properties ?: null,
        ]);
    }

    protected function sessionId(): ?string
    {
        if (! Request::hasSession()) {
            return null;
        }

        return mb_substr(Request::session()->getId(), 0, 64);
    }
}
