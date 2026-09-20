<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'restaurant_table_id',
        'shift_id',
        'client_name',
        'client_phone',
        'status',
        'subtotal',
        'fees',
        'total',
        'payment_method',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'fees' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(
            RestaurantTable::class,
            'restaurant_table_id'
        );
    }
        public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    public const TRANSITIONS = [
    'pending' => ['confirmed', 'cancelled'],
    'confirmed' => ['preparing', 'cancelled'],
    'preparing' => ['ready', 'cancelled'],
    'ready' => ['completed', 'cancelled'],
    'completed' => [],
    'cancelled' => [],
];

    public function canTransitionTo(string $newStatus): bool
        {
            return in_array(
                $newStatus,
                self::TRANSITIONS[$this->status] ?? [],
                true
            );
        }
}