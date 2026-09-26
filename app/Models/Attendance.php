<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One person's day. Absent is the interesting one: it is what the manager
 * asks about, so it is a status held on a real row rather than the absence
 * of a row — a missing row only means nobody has marked that day yet.
 */
class Attendance extends Model
{
    protected $table = 'attendance';

    public const PRESENT = 'present';
    public const ABSENT = 'absent';
    public const LEAVE = 'leave';

    public const STATUSES = [self::PRESENT, self::ABSENT, self::LEAVE];

    protected $fillable = [
        'user_id', 'day', 'status', 'arrived_at', 'left_at', 'note', 'recorded_by',
    ];

    protected $casts = [
        'day' => 'date',
        'arrived_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function label(string $status): string
    {
        return match ($status) {
            self::PRESENT => __('Present'),
            self::ABSENT => __('Absent'),
            self::LEAVE => __('On leave'),
            default => $status,
        };
    }

    /** How long they were in, once both ends of the day are known. */
    public function hours(): ?float
    {
        if (! $this->arrived_at || ! $this->left_at) {
            return null;
        }

        return round($this->arrived_at->diffInMinutes($this->left_at) / 60, 1);
    }
}
