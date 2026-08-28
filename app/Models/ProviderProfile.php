<?php

namespace App\Models;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use Database\Factories\ProviderProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ProviderProfile extends Model
{
    /** @use HasFactory<ProviderProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'company_name', 'slug', 'eik', 'contact_name', 'phone', 'email',
        'website', 'description', 'city_id', 'address', 'service_radius', 'working_hours',
        'facebook', 'instagram', 'logo_path',
        'email_verified', 'phone_verified', 'company_verified', 'verified', 'verified_at',
        'active', 'submitted_at', 'approved_at', 'blocked_at', 'blocked_reason',
        'rating_avg', 'rating_count', 'profile_views',
    ];

    protected function casts(): array
    {
        return [
            'email_verified' => 'boolean',
            'phone_verified' => 'boolean',
            'company_verified' => 'boolean',
            'verified' => 'boolean',
            'active' => 'boolean',
            'verified_at' => 'datetime',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'blocked_at' => 'datetime',
            'rating_avg' => 'float',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function serviceAreas(): HasMany
    {
        return $this->hasMany(ProviderServiceArea::class);
    }

    public function leadAssignments(): HasMany
    {
        return $this->hasMany(LeadProvider::class);
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'lead_provider')
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Само одобрени, активни и неблокирани профили участват в matching-а. */
    public function scopeDispatchable(Builder $query): Builder
    {
        return $query->where('active', true)
            ->whereNotNull('approved_at')
            ->whereNull('blocked_at');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->dispatchable();
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    public function isDispatchable(): bool
    {
        return $this->active && $this->isApproved() && ! $this->isBlocked();
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions
            ->firstWhere('status', SubscriptionStatus::Active);
    }

    public function plan(): SubscriptionPlan
    {
        return $this->activeSubscription()?->plan ?? SubscriptionPlan::Free;
    }

    public function planSetting(string $key): mixed
    {
        return config("nt.plans.{$this->plan()->value}.{$key}");
    }

    /** Градовете, които доставчикът обслужва — базов град плюс обявените райони. */
    public function servedCityIds(): array
    {
        return collect([$this->city_id])
            ->merge($this->serviceAreas->pluck('city_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function hasOperator(): bool
    {
        return $this->equipment->contains(fn (Equipment $e) => $e->active && $e->operator_available);
    }

    public function hasEquipmentWithoutOperator(): bool
    {
        return $this->equipment->contains(fn (Equipment $e) => $e->active && ! $e->operator_only);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function locationLabel(): string
    {
        if (! $this->city) {
            return 'България';
        }

        return "{$this->city->name} и областта";
    }

    public function displayRating(): string
    {
        return number_format($this->rating_avg, 1, ',', '');
    }
}
