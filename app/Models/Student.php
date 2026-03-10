<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'registration_number',
        'full_name',
        'nik',
        'nisn',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'religion',
        'address',
        'phone',
        'email',
        'parent_name',
        'parent_phone',
        'file_ijazah',
        'file_kk',
        'file_akta',
        'file_pas_photo',
        'verification_status',
        'verification_note',
        'is_accepted',
        'accepted_major_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_accepted' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function majors(): BelongsToMany
    {
        return $this->belongsToMany(Major::class, 'student_major')
            ->withPivot('preference')
            ->withTimestamps()
            ->orderBy('preference');
    }

    public function acceptedMajor(): BelongsTo
    {
        return $this->belongsTo(Major::class, 'accepted_major_id');
    }
}
