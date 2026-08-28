<?php

namespace App\Models;

use App\Enums\LeadProviderStatus;
use Database\Factories\LeadProviderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LeadProvider extends Pivot
{
    /** @use HasFactory<LeadProviderFactory> */
    use HasFactory;

    protected $table = 'lead_provider';

    public $incrementing = true;

    protected $fillable = [
        'lead_id', 'provider_profile_id', 'status', 'price', 'match_score', 'notes',
        'sent_at', 'viewed_at', 'accepted_at', 'declined_at', 'contacted_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadProviderStatus::class,
            'price' => 'decimal:2',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'contacted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, [
            LeadProviderStatus::Declined,
            LeadProviderStatus::Completed,
        ], true);
    }
}
