<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Проверка на Cloudflare Turnstile. Ако ключовете липсват (локална среда),
 * защитата се пропуска, за да не блокира разработката.
 */
class TurnstileService
{
    public function enabled(): bool
    {
        return filled(config('nt.antispam.turnstile_secret_key'))
            && filled(config('nt.antispam.turnstile_site_key'));
    }

    public function siteKey(): ?string
    {
        return config('nt.antispam.turnstile_site_key');
    }

    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::timeout(5)->asForm()->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                array_filter([
                    'secret' => config('nt.antispam.turnstile_secret_key'),
                    'response' => $token,
                    'remoteip' => $ip,
                ])
            );

            return (bool) $response->json('success', false);
        } catch (\Throwable $e) {
            Log::warning('Turnstile проверката е неуспешна: '.$e->getMessage());

            // При техническа грешка не блокираме реални клиенти.
            return true;
        }
    }
}
