<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestDocument extends Model
{
    use HasFactory;

    protected $fillable = ['request_id', 'document_type_id', 'drawer_id', 'status', 'received_at', 'ready_at', 'claimed_at'];

    protected function casts(): array
    {
        return ['received_at' => 'datetime', 'ready_at' => 'datetime', 'claimed_at' => 'datetime'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(DocumentRequest::class, 'request_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function drawer(): BelongsTo
    {
        return $this->belongsTo(Drawer::class, 'drawer_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(FacultyAssignment::class, 'request_document_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(DocumentEvent::class, 'request_document_id');
    }
}
