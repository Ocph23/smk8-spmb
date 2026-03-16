<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

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
        'street',
        'rt',
        'rw',
        'dusun',
        'district',
        'postal_code',
        'phone',
        'email',
        'password',
        'parent_name',
        'mother_name',
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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_accepted' => 'boolean',
        'password' => 'hashed',
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

    public function inbox(): HasMany
    {
        return $this->hasMany(Inbox::class);
    }
}
