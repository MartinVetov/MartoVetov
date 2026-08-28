<?php

namespace App\Models;

use Database\Factories\EquipmentCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentCategory extends Model
{
    /** @use HasFactory<EquipmentCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'short_name', 'icon', 'description',
        'seo_title', 'seo_description', 'seo_content', 'faq',
        'lead_price', 'sort_order', 'featured', 'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'featured' => 'boolean',
            'faq' => 'array',
            'lead_price' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    /** Типовете техника в категорията, използвани в заявките. */
    public function typeOptions(): array
    {
        return $this->children->pluck('name', 'name')->all();
    }

    /** Цена на заявка за категорията с резервна стойност от конфигурацията. */
    public function leadPrice(): float
    {
        return (float) ($this->lead_price ?? config('nt.lead_price.default'));
    }

    public function metaTitle(): string
    {
        return $this->seo_title ?: "{$this->name} под наем | ".config('nt.brand');
    }

    public function metaDescription(): string
    {
        return $this->seo_description
            ?: "Търсиш {$this->name} под наем? Опиши задачата си и ще намерим подходящи доставчици в твоя район.";
    }
}
