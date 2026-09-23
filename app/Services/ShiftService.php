<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
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
        // A lock stops two people pressing "open" at the same moment and
        // ending up with two open shifts.
        return Cache::lock('shifts:open', 10)->block(5, function () use ($user) {
            if ($this->currentOpenShift()) {
                throw new InvalidArgumentException(__('A shift is already open. Close it before starting a new one.'));
            }

            return Shift::create([
                'user_id' => $user->id,
                'status' => 'open',
                'opened_at' => now(),
            ]);
        });
    }

    public function close(Shift $shift, float $countedCash, ?string $notes = null): Shift
    {
        return DB::transaction(function () use ($shift, $countedCash, $notes) {

            $shift = Shift::whereKey($shift->id)->lockForUpdate()->firstOrFail();

            if ($shift->status !== 'open') {
                throw new InvalidArgumentException(__('This shift is already closed.'));
            }

            // Freeze what the system expected, so a closed shift never changes.
            $shift->update([
                'status' => 'closed',
                'closed_at' => now(),
                'counted_cash' => $countedCash,
                'expected_cash' => $shift->liveTotal('cash'),
                'expected_card' => $shift->liveTotal('card'),
                'notes' => $notes,
            ]);

            return $shift->fresh();
        });
    }

    /** Cash orders served under this shift that nobody has marked as paid yet. */
    public function unpaidOrders(Shift $shift)
    {
        return Order::query()
            ->where('shift_id', $shift->id)
            ->where('payment_status', 'pending')
            ->where('status', '!=', 'cancelled')
            ->get();
    }
}
