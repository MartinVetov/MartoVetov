<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * GDPR: право на достъп. Събира всички лични данни, свързани с даден
 * имейл или телефон, в един JSON файл, който да бъде предоставен на лицето.
 */
class ExportPersonalData extends Command
{
    protected $signature = 'nt:export-personal-data {identifier : Имейл или телефон}';

    protected $description = 'Изнася личните данни, свързани с даден имейл или телефон.';

    public function handle(): int
    {
        $identifier = trim($this->argument('identifier'));

        $leads = Lead::query()
            ->where('contact_email', $identifier)
            ->orWhere('contact_phone', $identifier)
            ->with(['category', 'city', 'assignments.providerProfile'])
            ->get();

        $user = User::where('email', $identifier)->orWhere('phone', $identifier)->first();

        if ($leads->isEmpty() && ! $user) {
            $this->warn('Няма намерени данни за '.$identifier);

            return self::SUCCESS;
        }

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'identifier' => $identifier,
            'profil' => $user ? [
                'ime' => $user->name,
                'email' => $user->email,
                'telefon' => $user->phone,
                'rolya' => $user->role->label(),
                'sazdaden_na' => $user->created_at?->toIso8601String(),
            ] : null,
            'zayavki' => $leads->map(fn (Lead $lead) => [
                'nomer' => $lead->reference,
                'sazdadena_na' => $lead->created_at?->toIso8601String(),
                'tehnika' => $lead->categoryLabel(),
                'grad' => $lead->city?->name,
                'opisanie' => $lead->description,
                'ime' => $lead->contact_name,
                'telefon' => $lead->contact_phone,
                'email' => $lead->contact_email,
                'izpratena_kam' => $lead->assignments->pluck('providerProfile.company_name')->all(),
            ])->all(),
        ];

        $file = storage_path('app/gdpr-'.now()->format('Ymd-His').'.json');
        file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Данните са записани в: {$file}");
        $this->line('Заявки: '.$leads->count().($user ? ', намерен профил' : ''));

        return self::SUCCESS;
    }
}
