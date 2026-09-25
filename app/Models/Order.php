<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** How an order reaches the guest. */
    public const TYPES = ['dine_in', 'takeaway', 'delivery', 'online'];

    /** Orders the kitchen is still working on. */
    public const ACTIVE_STATUSES = ['pending', 'confirmed', 'preparing', 'ready'];

    public const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['preparing', 'cancelled'],
        'preparing' => ['ready', 'cancelled'],
        'ready' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    protected $fillable = [
        'restaurant_table_id',
        'order_type',
        'source',
        'delivery_address',
        'delivery_fee',
        'driver_name',
        'delivery_status',
        'shift_id',
        'access_token',
        'client_name',
        'client_phone',
        'status',
        'subtotal',
        'fees',
        'total',
        'payment_method',
        'payment_status',
        'paid_at',
        'notes',
    ];

    /** The guest's secret never leaves the server in a serialised order. */
    protected $hidden = [
        'access_token',
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'fees' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(OrderChangeRequest::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /** Constant-time check of the token a guest presents for this order. */
    public function tokenMatches(?string $token): bool
    {
        return is_string($token)
            && $this->access_token !== null
            && hash_equals($this->access_token, $token);
    }

    public function typeLabel(): string
    {
        return \App\Support\Settings::typeLabel($this->order_type ?? 'dine_in');
    }

    public function isDelivery(): bool
    {
        return $this->order_type === 'delivery';
    }

    /** Takeaway, delivery and online orders have no table. */
    public function seatsGuests(): bool
    {
        return $this->order_type === 'dine_in';
    }
}
