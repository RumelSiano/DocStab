<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentEvent extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['request_document_id', 'actor_id', 'action', 'from_status', 'to_status', 'drawer_id', 'notes', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(RequestDocument::class, 'request_document_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
