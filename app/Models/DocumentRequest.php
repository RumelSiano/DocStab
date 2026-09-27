<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = ['requestor_id', 'created_by', 'tracking_reference', 'purpose', 'notes', 'needed_date', 'completed_at'];

    protected function casts(): array
    {
        return ['needed_date' => 'date:Y-m-d', 'completed_at' => 'datetime'];
    }

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RequestDocument::class, 'request_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'request_id');
    }

    public function stub(): HasOne
    {
        return $this->hasOne(ClaimStub::class, 'request_id');
    }

    public function releases(): HasMany
    {
        return $this->hasMany(Release::class, 'request_id');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class, 'request_id');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->when($user->role === 'requestor', fn ($query) => $query->where('requestor_id', $user->id));
    }

    public function loadDetails(): static
    {
        return $this->load(['requestor:id,name,email,program,contact_number', 'documents.type', 'documents.drawer', 'documents.assignments.faculty', 'documents.events', 'attachments', 'stub', 'feedback', 'releases.items']);
    }
}
