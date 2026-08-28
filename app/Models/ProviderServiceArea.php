<?php

namespace App\Models;

use Database\Factories\ProviderServiceAreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderServiceArea extends Model
{
    /** @use HasFactory<ProviderServiceAreaFactory> */
    use HasFactory;

    protected $fillable = ['provider_profile_id', 'city_id', 'radius'];

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
