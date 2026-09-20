<?php

namespace App\Models;

use App\Enums\TableStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RestaurantTable extends Model
{
    protected $fillable = [
        'number',
        'capacity',
        'status',
        'qr_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => TableStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RestaurantTable $table) {
            if (empty($table->qr_token)) {
                $table->qr_token = (string) Str::uuid();
            }
        });
    }
}