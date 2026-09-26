<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Product;

class Category extends Model
{
    protected $fillable = [
        'name',
        'sort_order',
        'description',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function products(): HasMany
    {
    return $this->hasMany(Product::class);
    }

    /** Menu content changed: guests must see it on their next scan. */
    protected static function booted(): void
    {
        static::saved(fn () => \App\Services\MenuService::forget());
        static::deleted(fn () => \App\Services\MenuService::forget());
    }
}