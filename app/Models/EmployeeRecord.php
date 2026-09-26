<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** What the restaurant keeps on file about someone who works here. */
class EmployeeRecord extends Model
{
    protected $fillable = [
        'user_id',
        'national_id',
        'job_title',
        'phone',
        'salary',
        'hired_on',
        'notes',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'hired_on' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
