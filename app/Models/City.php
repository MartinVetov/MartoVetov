<?php

namespace App\Models;

use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'region', 'latitude', 'longitude', 'population',
        'active', 'landing_enabled', 'seo_title', 'seo_description', 'seo_content',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'landing_enabled' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function providers(): HasMany
    {
        return $this->hasMany(ProviderProfile::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeWithLanding(Builder $query): Builder
    {
        return $query->where('active', true)->where('landing_enabled', true);
    }

    /** Разстояние в километри до друг град по формулата на хаверсинуса. */
    public function distanceTo(?City $other): ?float
    {
        if (! $other || ! $this->hasCoordinates() || ! $other->hasCoordinates()) {
            return null;
        }

        $earthRadius = 6371;
        $latFrom = deg2rad((float) $this->latitude);
        $lonFrom = deg2rad((float) $this->longitude);
        $latTo = deg2rad((float) $other->latitude);
        $lonTo = deg2rad((float) $other->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            sin($latDelta / 2) ** 2 +
            cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2
        ));

        return round($angle * $earthRadius, 1);
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function fullName(): string
    {
        return $this->name === $this->region
            ? $this->name
            : "{$this->name}, обл. {$this->region}";
    }
}
