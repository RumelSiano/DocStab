<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimStub extends Model
{
    use HasFactory;

    protected $fillable = ['request_id', 'reference'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(DocumentRequest::class, 'request_id');
    }
}
