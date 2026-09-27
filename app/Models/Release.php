<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Release extends Model
{
    use HasFactory;

    protected $fillable = ['request_id', 'released_by', 'claimant_name', 'claimant_identifier', 'is_representative', 'authorization_reference', 'acknowledged_at'];

    protected function casts(): array
    {
        return ['is_representative' => 'boolean', 'acknowledged_at' => 'datetime'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(DocumentRequest::class, 'request_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReleaseItem::class, 'release_id');
    }
}
