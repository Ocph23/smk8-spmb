<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
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
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query->whereNull('publish_at')
                    ->orWhere('publish_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    public function scopeForSidebar(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->orderByDesc('is_pinned')
            ->orderByDesc('publish_at')
            ->orderByDesc('created_at');
    }
}
