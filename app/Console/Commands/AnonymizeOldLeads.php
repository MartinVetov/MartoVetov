<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * GDPR: заявките пазят лични данни само толкова, колкото е нужно.
 * След изтичане на срока за съхранение контактите се заличават, а
 * статистиката (категория, град, дата) остава за анализ.
 */
class AnonymizeOldLeads extends Command
{
    protected $signature = 'nt:anonymize-leads
                            {--days= : Срок за съхранение в дни (по подразбиране от конфигурацията)}
                            {--dry-run : Само показва кои заявки биха били анонимизирани}';

    protected $description = 'Заличава личните данни в стари заявки според срока за съхранение.';

    public function handle(): int
    {
        // Нула е валиден срок (заличи всичко), затова проверяваме за null.
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : (int) config('nt.retention_days');
        $cutoff = now()->subDays($days);

        $leads = Lead::query()
            ->whereNull('anonymized_at')
            ->where('created_at', '<', $cutoff)
            ->with('images')
            ->get();

        if ($leads->isEmpty()) {
            $this->info('Няма заявки за анонимизиране.');

            return self::SUCCESS;
        }

        $this->info("Намерени {$leads->count()} заявки, създадени преди {$cutoff->format('d.m.Y')} г.");

        if ($this->option('dry-run')) {
            $this->table(
                ['Номер', 'Създадена', 'Град'],
                $leads->map(fn (Lead $l) => [$l->reference, $l->created_at->format('d.m.Y'), $l->city?->name])->all()
            );

            return self::SUCCESS;
        }

        foreach ($leads as $lead) {
            $this->anonymize($lead);
        }

        $this->info("Анонимизирани са {$leads->count()} заявки.");

        return self::SUCCESS;
    }

    protected function anonymize(Lead $lead): void
    {
        foreach ($lead->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $lead->forceFill([
            'contact_name' => 'Заличено',
            'contact_phone' => '',
            'contact_email' => 'anonim@'.parse_url(config('app.url'), PHP_URL_HOST).'.invalid',
            'address' => null,
            'district' => null,
            'ip_address' => null,
            'user_agent' => null,
            'admin_notes' => null,
            'anonymized_at' => now(),
        ])->save();
    }
}
