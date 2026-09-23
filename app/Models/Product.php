<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Menu content changed: guests must see it on their next scan. */
    protected static function booted(): void
    {
        static::saved(fn () => \App\Services\MenuService::forget());
        static::deleted(fn () => \App\Services\MenuService::forget());
    }
}