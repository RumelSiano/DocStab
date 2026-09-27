<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public function isStaff(): bool
    {
        return in_array($this->role, ['sta', 'admin'], true);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class, 'requestor_id');
    }

    protected $attributes = ['role' => 'requestor', 'is_active' => true];

    protected $fillable = ['name', 'email', 'password', 'program', 'contact_number'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_active' => 'boolean', 'email_verified_at' => 'datetime'];
    }
}
