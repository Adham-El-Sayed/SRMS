<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderChangeRequest extends Model
{
    protected $fillable = [
        'order_id',
        'reason',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** What staff are being called about. */
    public function reasonLabel(): string
    {
        return $this->reason === 'edited'
            ? __('Changed their order')
            : __('Asked for a waiter');
    }
}