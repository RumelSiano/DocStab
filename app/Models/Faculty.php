<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use HasFactory;

    protected $table = 'faculty';

    protected $fillable = ['name', 'department', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(FacultyAssignment::class, 'faculty_id');
    }
}
