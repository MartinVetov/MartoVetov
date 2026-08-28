<?php

namespace App\Models;

use Database\Factories\EquipmentImageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EquipmentImage extends Model
{
    /** @use HasFactory<EquipmentImageFactory> */
    use HasFactory;

    protected $fillable = ['equipment_id', 'path', 'alt', 'sort_order'];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
