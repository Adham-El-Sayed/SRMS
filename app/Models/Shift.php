<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'user_id', 'status', 'opened_at', 'closed_at', 'counted_cash', 'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'counted_cash' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected function paidOrders()
    {
        return $this->orders()
            ->where('payment_status', 'paid');
    }

      public function systemCashTotal(): float
    {
        return (float) $this->paidOrders()
            ->where('payment_method', 'cash')->sum('total');
    }

    public function systemVisaTotal(): float
    {
        return (float) $this->paidOrders()
            ->where('payment_method', 'card')->sum('total');
    }

    public function cashDifference(): ?float
    {
        if ($this->counted_cash === null) {
            return null;
        }

        return round((float) $this->counted_cash - $this->systemCashTotal(), 2);
    }
}