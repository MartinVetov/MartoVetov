<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Jobs\ProcessNewLead;
use App\Models\Lead;
use App\Models\LeadImage;
use App\Support\CategoryAttributes;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Приемане на нова заявка: запис, снимки, защита от дубликати и стартиране
 * на обработката (известия и подбор на доставчици).
 */
class LeadIntakeService
{
    public function __construct(
        protected ImageUploadService $images,
        protected AnalyticsService $analytics,
    ) {}

    /**
     * @param  array<string, mixed>  $data  Валидирани данни от формата.
     * @param  array<int, UploadedFile>  $files
     */
    public function create(array $data, array $files, Request $request): Lead
    {
        $lead = DB::transaction(function () use ($data, $files, $request) {
            $lead = Lead::create([
                'customer_id' => Auth::id(),
                'equipment_category_id' => $data['equipment_category_id'] ?? null,
                'category_unknown' => (bool) ($data['category_unknown'] ?? false),
                'equipment_type' => $data['equipment_type'] ?? null,
                'description' => $data['description'],
                'city_id' => $data['city_id'] ?? null,
                'district' => $data['district'] ?? null,
                'address' => $data['address'] ?? null,
                'requested_date' => $data['requested_date'] ?? null,
                'requested_time' => $data['requested_time'] ?? null,
                'date_flexible' => (bool) ($data['date_flexible'] ?? false),
                'duration' => $data['duration'],
                'operator_required' => $data['operator_required'],
                'details' => CategoryAttributes::sanitize(
                    $data['category_slug'] ?? null,
                    $data['details'] ?? []
                ) ?: null,
                'contact_name' => $data['contact_name'],
                'contact_phone' => $data['contact_phone'],
                'contact_email' => $data['contact_email'],
                'status' => LeadStatus::New,
                'price' => $data['price'] ?? null,
                'consent_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                'source' => $data['source'] ?? 'web',
            ]);

            $this->storeImages($lead, $files);

            return $lead;
        });

        $this->analytics->record(AnalyticsService::LEAD_FORM_COMPLETED, $lead, [
            'category' => $lead->category?->slug,
            'city' => $lead->city?->slug,
        ]);

        ProcessNewLead::dispatch($lead);

        return $lead;
    }

    /**
     * Открива заявка от същия телефон за същата категория в рамките на
     * конфигурирания прозорец, за да не се създават дубликати.
     */
    public function findRecentDuplicate(array $data): ?Lead
    {
        $minutes = (int) config('nt.antispam.duplicate_window_minutes');

        return Lead::query()
            ->where('contact_phone', $data['contact_phone'] ?? '')
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->when(
                $data['equipment_category_id'] ?? null,
                fn ($q, $categoryId) => $q->where('equipment_category_id', $categoryId),
                fn ($q) => $q->whereNull('equipment_category_id')
            )
            ->latest('id')
            ->first();
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    protected function storeImages(Lead $lead, array $files): void
    {
        $max = (int) config('nt.uploads.max_lead_images');

        foreach (array_slice(array_filter($files), 0, $max) as $index => $file) {
            LeadImage::create([
                'lead_id' => $lead->id,
                'path' => $this->images->store($file, 'leads/'.$lead->id),
                'sort_order' => $index,
            ]);
        }
    }
}
