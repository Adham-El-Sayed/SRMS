<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'user_id', 'status', 'opened_at', 'closed_at',
        'counted_cash', 'expected_cash', 'expected_card', 'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'counted_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'expected_card' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Money actually taken in this shift, by method, from paid, non-cancelled orders. */
    public function liveTotal(string $method): float
    {
        return (float) $this->orders()
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('payment_method', $method)
            ->sum('total');
    }

    public function systemCashTotal(): float
    {
        return $this->status === 'closed' && $this->expected_cash !== null
            ? (float) $this->expected_cash
            : $this->liveTotal('cash');
    }

    public function systemVisaTotal(): float
    {
        return $this->status === 'closed' && $this->expected_card !== null
            ? (float) $this->expected_card
            : $this->liveTotal('card');
    }

    /** Positive = more cash in the drawer than expected; negative = short. */
    public function cashDifference(): ?float
    {
        if ($this->counted_cash === null) {
            return null;
        }

        return round((float) $this->counted_cash - $this->systemCashTotal(), 2);
    }
}
