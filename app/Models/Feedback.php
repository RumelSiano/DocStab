<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';

    protected $fillable = ['request_id', 'service_rating', 'processing_rating', 'comments'];

    protected function casts(): array
    {
        return ['service_rating' => 'integer', 'processing_rating' => 'integer'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(DocumentRequest::class, 'request_id');
    }
}
