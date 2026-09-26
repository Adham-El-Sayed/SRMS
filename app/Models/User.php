<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employeeRecord()
    {
        return $this->hasOne(EmployeeRecord::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    /** Days marked absent in a month, which is what a manager asks about. */
    public function absencesIn($month): int
    {
        return $this->attendance()
            ->where('status', Attendance::ABSENT)
            ->whereYear('day', $month->year)
            ->whereMonth('day', $month->month)
            ->count();
    }

    public function payrollEntries()
    {
        return $this->hasMany(PayrollEntry::class);
    }

    /** Bonuses less deductions for a given month. */
    public function payrollTotal(string $kind, \Carbon\Carbon $month): float
    {
        return (float) $this->payrollEntries()
            ->where('kind', $kind)
            ->whereBetween('happened_on', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->sum('amount');
    }
}