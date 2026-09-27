<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacultyAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['request_document_id', 'faculty_id', 'status', 'routed_at', 'returned_at'];

    protected function casts(): array
    {
        return ['routed_at' => 'datetime', 'returned_at' => 'datetime'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(RequestDocument::class, 'request_document_id');
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }
}
