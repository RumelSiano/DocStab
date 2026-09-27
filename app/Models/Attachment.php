<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = ['request_id', 'uploaded_by', 'original_name', 'path', 'mime_type', 'size'];

    protected $hidden = ['path'];

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(DocumentRequest::class, 'request_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
