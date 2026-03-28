<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'quota',
        'icon_svg',
    ];

    protected $casts = [
        'quota' => 'integer',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_major')
            ->withPivot('preference')
            ->withTimestamps();
    }

    public function acceptedStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'accepted_major_id');
    }

    public function academicYears(): BelongsToMany
    {
        return $this->belongsToMany(AcademicYear::class, 'academic_year_major')
            ->withPivot(['quota', 'is_active'])
            ->withTimestamps();
    }

    public function quotaForYear(int $academicYearId): int
    {
        $pivot = $this->academicYears()->wherePivot('academic_year_id', $academicYearId)->first();
        return $pivot?->pivot->quota ?? $this->quota;
    }
}
