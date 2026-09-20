<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ShiftService
{
    public function currentOpenShift(): ?Shift
    {
        return Shift::query()
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();
    }

    public function open(User $user): Shift
    {
        if ($this->currentOpenShift()) {
            throw new InvalidArgumentException(
                'A shift is already open. Close it before starting a new one.'
            );
        }

        return Shift::create([
            'user_id' => $user->id,
            'status' => 'open',
            'opened_at' => now(),
        ]);
    }

    public function close(Shift $shift, float $countedCash, ?string $notes = null): Shift
    {
        return DB::transaction(function () use ($shift, $countedCash, $notes) {
            if ($shift->status !== 'open') {
                throw new InvalidArgumentException('This shift is already closed.');
            }

            $shift->update([
                'status' => 'closed',
                'closed_at' => now(),
                'counted_cash' => $countedCash,
                'notes' => $notes,
            ]);

            return $shift->fresh();
        });
    }
}