<?php

namespace App\Models;

use App\Enums\LeadDuration;
use App\Enums\LeadStatus;
use App\Enums\OperatorRequirement;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    protected $fillable = [
        'reference', 'customer_id', 'equipment_category_id', 'category_unknown', 'equipment_type',
        'description', 'city_id', 'district', 'address',
        'requested_date', 'requested_time', 'date_flexible', 'duration',
        'operator_required', 'details',
        'contact_name', 'contact_phone', 'contact_email',
        'status', 'admin_notes', 'price',
        'consent_at', 'ip_address', 'user_agent', 'source', 'sent_count', 'anonymized_at',
    ];

    protected function casts(): array
    {
        return [
            'category_unknown' => 'boolean',
            'date_flexible' => 'boolean',
            'details' => 'array',
            'requested_date' => 'date',
            'consent_at' => 'datetime',
            'anonymized_at' => 'datetime',
            'price' => 'decimal:2',
            'status' => LeadStatus::class,
            'duration' => LeadDuration::class,
            'operator_required' => OperatorRequirement::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $lead) {
            $lead->reference ??= self::generateReference();
        });
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'NT-'.now()->format('y').'-'.Str::upper(Str::random(6));
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(LeadImage::class)->orderBy('sort_order');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LeadProvider::class);
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(ProviderProfile::class, 'lead_provider')
            ->using(LeadProvider::class)
            ->withPivot([
                'id', 'status', 'price', 'match_score', 'notes',
                'sent_at', 'viewed_at', 'accepted_at', 'declined_at', 'contacted_at', 'completed_at',
            ])
            ->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [LeadStatus::Completed, LeadStatus::Rejected, LeadStatus::Invalid]);
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->whereNotIn('status', [LeadStatus::Invalid, LeadStatus::Rejected]);
    }

    public function categoryLabel(): string
    {
        if ($this->category) {
            return $this->category->name;
        }

        return $this->category_unknown ? 'Клиентът не знае каква техника му трябва' : 'Друга техника';
    }

    public function locationLabel(): string
    {
        return collect([$this->city?->name, $this->district])->filter()->implode(', ') ?: 'Не е посочено';
    }

    public function dateLabel(): string
    {
        if (! $this->requested_date) {
            return $this->date_flexible ? 'Гъвкава дата' : 'Не е посочена';
        }

        $label = $this->requested_date->format('d.m.Y');

        if ($this->requested_time) {
            $label .= ', '.$this->requested_time;
        }

        return $this->date_flexible ? $label.' (гъвкаво)' : $label;
    }

    /** Цената на заявката за доставчик — според категорията. */
    public function leadPrice(): float
    {
        return (float) ($this->price ?? $this->category?->leadPrice() ?? config('nt.lead_price.default'));
    }

    /** Допълнителните характеристики с етикети за показване. */
    public function attributeLines(): array
    {
        $schema = config('equipment_attributes.'.($this->category?->slug ?? ''), []);
        $lines = [];

        foreach ($this->details ?? [] as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $field = $schema[$key] ?? null;
            $label = $field['label'] ?? $key;
            $lines[$label] = $field['options'][$value] ?? $value;
        }

        return $lines;
    }
}
