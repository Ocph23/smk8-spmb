<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class   Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'content',
        'link_text',
        'link_url',
        'is_pinned',
        'is_active',
        'publish_at',
        'expires_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_active' => 'boolean',
        'publish_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('is_active', true);
    }

    public function scopeForSidebar(Builder $query): Builder
    {
        return $query
            ->visible()
            ->orderByDesc('is_pinned')
            ->orderByDesc('publish_at')
            ->orderByDesc('created_at');
    }
}
