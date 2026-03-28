<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'start_year', 'end_year', 'status', 'description'];

    protected $casts = [
        'start_year' => 'integer',
        'end_year'   => 'integer',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function majors(): BelongsToMany
    {
        return $this->belongsToMany(Major::class, 'academic_year_major')
            ->withPivot(['quota', 'is_active'])
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public static function getActive(): ?self
    {
        return static::where('status', 'active')->first();
    }
}
