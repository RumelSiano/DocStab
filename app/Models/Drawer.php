<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drawer extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'label', 'is_active'];

    protected function casts(): array
    {
        return ['number' => 'integer', 'is_active' => 'boolean'];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RequestDocument::class, 'drawer_id');
    }
}
