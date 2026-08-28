<?php

namespace App\Models;

use Database\Factories\LeadImageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LeadImage extends Model
{
    /** @use HasFactory<LeadImageFactory> */
    use HasFactory;

    protected $fillable = ['lead_id', 'path', 'sort_order'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
