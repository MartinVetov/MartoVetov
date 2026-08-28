<?php

namespace App\Models;

use App\Enums\PriceUnit;
use Database\Factories\EquipmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    /** @use HasFactory<EquipmentFactory> */
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'provider_profile_id', 'equipment_category_id', 'type', 'brand', 'model',
        'year', 'weight', 'description', 'specs', 'operator_available', 'operator_only',
        'price_from', 'price_unit', 'min_duration', 'active',
    ];

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'operator_available' => 'boolean',
            'operator_only' => 'boolean',
            'active' => 'boolean',
            'weight' => 'float',
            'price_from' => 'decimal:2',
            'price_unit' => PriceUnit::class,
        ];
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(EquipmentImage::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function title(): string
    {
        return trim(collect([
            $this->category?->name,
            $this->brand,
            $this->model,
            $this->type ? "({$this->type})" : null,
        ])->filter()->implode(' '));
    }

    public function priceLabel(): ?string
    {
        if ($this->price_from === null) {
            return null;
        }

        return 'от '.number_format((float) $this->price_from, 0, ',', ' ').' лв. '.$this->price_unit->label();
    }
}
