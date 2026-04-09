<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnrollmentWave extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'wave_number',
        'status',
        'open_date',
        'close_date',
        'description',
    ];

    protected $casts = [
        'wave_number' => 'integer',
        'open_date'   => 'date',
        'close_date'  => 'date',
    ];

    /** Valid status transitions: from → [allowed to] */
    private const TRANSITIONS = [
        'draft'    => ['open'],
        'open'     => ['closed'],
        'closed'   => ['announced'],
        'announced' => [],
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function majors(): BelongsToMany
    {
        return $this->belongsToMany(Major::class, 'enrollment_wave_major')
            ->withPivot('quota')
            ->withTimestamps();
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isAnnounced(): bool
    {
        return $this->status === 'announced';
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }
}
