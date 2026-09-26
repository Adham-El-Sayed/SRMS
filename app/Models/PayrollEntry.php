<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One bonus or one deduction, on the day it happened. */
class PayrollEntry extends Model
{
    public const KINDS = ['bonus', 'deduction'];

    protected $fillable = [
        'user_id',
        'kind',
        'amount',
        'reason',
        'happened_on',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'happened_on' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isBonus(): bool
    {
        return $this->kind === 'bonus';
    }
}
