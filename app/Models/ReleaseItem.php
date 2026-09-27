<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReleaseItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['release_id', 'request_document_id', 'drawer_id'];

    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class, 'release_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(RequestDocument::class, 'request_document_id');
    }

    public function drawer(): BelongsTo
    {
        return $this->belongsTo(Drawer::class, 'drawer_id');
    }
}
