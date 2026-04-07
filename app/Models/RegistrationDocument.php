<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'description',
        'field_name',
        'accepted_types',
        'max_size',
        'is_required',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }

    public function studentDocuments(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function getAcceptedTypesArray(): array
    {
        return explode(',', $this->accepted_types);
    }

    public function getAcceptedMimeTypes(): array
    {
        $types = $this->getAcceptedTypesArray();
        $mimes = [];
        foreach ($types as $type) {
            switch (trim($type)) {
                case 'pdf':
                    $mimes[] = 'application/pdf';
                    break;
                case 'jpg':
                case 'jpeg':
                    $mimes[] = 'image/jpeg';
                    break;
                case 'png':
                    $mimes[] = 'image/png';
                    break;
            }
        }
        return $mimes;
    }
}