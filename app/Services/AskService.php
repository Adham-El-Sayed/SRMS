<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Order;
use App\Models\PayrollEntry;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use App\Support\Bilingual;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * The manager's question box.
 *
 * Every answer here is a query somebody wrote and checked. Nothing is
 * guessed, nothing is sent anywhere, and a question this does not
 * understand is answered with "I don't know that one" and a few it does —
 * which is a better answer than a confident wrong number.
 *
 * Questions arrive in Arabic or English. Arabic is folded first, so the
 * shape of the letters and the vowel marks do not matter.
 */
class AskService
{
    /**
     * Words that name a subject, kept in one place because more than one
     * handler asks about the same subject and they drifted apart once:
     * "who was absent this month" reached the handler for the month before
     * the one for absence, and that handler carried a shorter list.
     *
     * Each entry is the shortest distinctive stem, so the prefixes people
     * actually type are covered without listing every spelling: "نهارده"
     * catches النهارده and انهارده, "غاب" catches غاب and غابوا. Folding
     * takes care of the vowel marks and the shapes of alef and ya, but not
     * of hamza on ya, so غائب is listed beside غايب.
     */
    private const ABSENCE = ['غاب', 'غياب', 'غايب', 'غائب', 'absent', 'absence'];
    private const TODAY = ['نهارده', 'اليوم', 'today'];
    private const SALARY = ['مرتب', 'رواتب', 'راتب', 'salary', 'salaries', 'payroll'];

    /**
     * @return array{answer: string, detail: ?string, link: ?array, kind: string}
     */
    public function answer(string $question): array
    {
        $q = $this->fold($question);

        if ($q === '') {
            return $this->unknown();
        }

        foreach ($this->handlers() as $handler) {
            [$words, $run] = $handler;

            if ($this->mentions($q, $words)) {
                return $run($q);
            }
        }

        return $this->unknown();
    }

    /** What the box offers when it is opened, and when it is lost. */
    public function examples(): array
    {
        return [
            __('Revenue today'),
            __('Revenue this month'),
            __('How many orders today'),
            __('Best selling dish'),
            __('Unpaid orders'),
            __('Who is absent today'),
            __('Absences this month'),
            __('Who is on shift'),
            __('What is finished in the kitchen'),
            __('Salaries this month'),
        ];
    }

    /* ------------------------------------------------------------------ */

    private function handlers(): array
    {
        return [
            // Order matters: the more specific questions come first, so
            // "revenue this month" is not caught by the one about today.
            [['امس', 'مبارح', 'yesterday'], fn () => $this->takings(
                today()->subDay()->startOfDay(), today()->subDay()->endOfDay(), __('Yesterday')
            )],

            [['شهر', 'month'], function ($q) {
                if ($this->mentions($q, self::ABSENCE)) return $this->absencesThisMonth();
                if ($this->mentions($q, self::SALARY)) return $this->payroll();
                return $this->takings(now()->startOfMonth(), now()->endOfMonth(), now()->translatedFormat('F Y'));
            }],

            [['اسبوع', 'week'], fn () => $this->takings(
                now()->startOfWeek(), now()->endOfWeek(), __('This week')
            )],

            [self::ABSENCE, fn ($q) => $this->mentions($q, self::TODAY)
                ? $this->absentToday()
                : $this->absencesThisMonth()],

            [['وردية', 'شفت', 'shift'], fn () => $this->openShift()],

            // "مدفعتش" and "مدفوع" share no substring, so both are listed —
            // otherwise "الطلبات اللي مدفعتش" falls through to the handler
            // that merely counts orders.
            [['مدفوع', 'مدفعتش', 'مدفعوش', 'مستني', 'متبقي', 'محصلش', 'unpaid', 'not paid', 'owed', 'outstanding'],
                fn () => $this->unpaid()],

            [['اكتر', 'افضل', 'الاكثر', 'best', 'top', 'popular'], fn () => $this->bestSeller()],

            [['خلصان', 'خلص', 'ناقص', 'finished', 'sold out', 'soldout'], fn () => $this->soldOut()],

            [self::SALARY, fn () => $this->payroll()],

            [['كام طلب', 'عدد الطلبات', 'طلبات', 'how many orders', 'orders'], fn ($q) => $this->orderCount($q)],

            // The broadest one last.
            [['ايراد', 'مبيعات', 'فلوس', 'دخل', 'حصلنا', 'revenue', 'sales', 'takings', 'income'],
                fn () => $this->takings(today()->startOfDay(), today()->endOfDay(), __('Today'))],

            [self::TODAY,
                fn () => $this->takings(today()->startOfDay(), today()->endOfDay(), __('Today'))],
        ];
    }

    /* ---------- the answers ---------- */

    private function takings($from, $to, string $label): array
    {
        $orders = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->get(['total', 'payment_method', 'payment_status']);

        $paid = $orders->where('payment_status', 'paid');

        return [
            'kind' => 'money',
            'answer' => $this->money($paid->sum('total')),
            'detail' => __(':label · :count orders · cash :cash · card :card · :waiting still to collect', [
                'label' => $label,
                'count' => $orders->count(),
                'cash' => $this->money($paid->where('payment_method', 'cash')->sum('total')),
                'card' => $this->money($paid->where('payment_method', 'card')->sum('total')),
                'waiting' => $this->money($orders->where('payment_status', '!=', 'paid')->sum('total')),
            ]),
            'link' => ['label' => __('Reports'), 'url' => route('reports.monthly')],
        ];
    }

    private function orderCount(string $q): array
    {
        $today = ! $this->mentions($q, ['الشهر', 'شهر', 'month']);

        $from = $today ? today()->startOfDay() : now()->startOfMonth();
        $to = $today ? today()->endOfDay() : now()->endOfMonth();

        $orders = Order::whereBetween('created_at', [$from, $to])->get(['status', 'order_type']);

        $byType = $orders->where('status', '!=', 'cancelled')
            ->groupBy('order_type')
            ->map->count()
            ->map(fn ($n, $type) => \App\Support\Settings::typeLabel($type) . ' ' . $n)
            ->implode(' · ');

        return [
            'kind' => 'count',
            'answer' => (string) $orders->where('status', '!=', 'cancelled')->count(),
            'detail' => ($today ? __('Today') : now()->translatedFormat('F Y')) . ' · ' . $byType
                . ' · ' . __(':n cancelled', ['n' => $orders->where('status', 'cancelled')->count()]),
            'link' => ['label' => __('Kitchen'), 'url' => route('kitchen.orders')],
        ];
    }

    private function unpaid(): array
    {
        $orders = Order::where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->get(['id', 'total']);

        return [
            'kind' => 'money',
            'answer' => $this->money($orders->sum('total')),
            'detail' => trans_choice('{0}Nothing is waiting to be collected.|{1}One order has not been paid.|[2,*]:count orders have not been paid.',
                $orders->count(), ['count' => $orders->count()]),
            'link' => ['label' => __('Payments'), 'url' => route('cash-payments.index')],
        ];
    }

    private function bestSeller(): array
    {
        $best = (new RecommendationService())->popular(3);

        if ($best->isEmpty()) {
            return [
                'kind' => 'none',
                'answer' => __('Nothing has sold often enough yet.'),
                'detail' => null,
                'link' => null,
            ];
        }

        return [
            'kind' => 'text',
            'answer' => Bilingual::lead($best->first()->name),
            'detail' => __('Then: :rest', [
                'rest' => $best->skip(1)->map(fn ($p) => Bilingual::lead($p->name))->implode(' · '),
            ]) . ' · ' . __('Over the last 30 days'),
            'link' => ['label' => __('Reports'), 'url' => route('reports.monthly')],
        ];
    }

    private function soldOut(): array
    {
        $out = Product::whereNotNull('sold_out_at')->where('is_active', true)->get(['name']);

        return [
            'kind' => $out->isEmpty() ? 'none' : 'text',
            'answer' => $out->isEmpty()
                ? __('Nothing is marked finished.')
                : $out->map(fn ($p) => Bilingual::lead($p->name))->implode(' · '),
            'detail' => $out->isEmpty() ? null : trans_choice('{1}One dish is off the menu right now.|[2,*]:count dishes are off the menu right now.',
                $out->count(), ['count' => $out->count()]),
            'link' => ['label' => __('Availability'), 'url' => route('kitchen.stock')],
        ];
    }

    private function openShift(): array
    {
        $shift = Shift::where('status', 'open')->latest('opened_at')->first();

        if (! $shift) {
            return [
                'kind' => 'none',
                'answer' => __('No shift is open.'),
                'detail' => null,
                'link' => ['label' => __('Shift'), 'url' => route('shifts.current')],
            ];
        }

        return [
            'kind' => 'text',
            'answer' => $shift->user?->name ?? __('Unknown'),
            'detail' => __('Open since :time · cash :cash · card :card · :unpaid still to collect', [
                'time' => $shift->opened_at->format('H:i'),
                'cash' => $this->money($shift->liveTotal('cash')),
                'card' => $this->money($shift->liveTotal('card')),
                'unpaid' => $this->money($shift->outstandingTotal()),
            ]),
            'link' => ['label' => __('Shift'), 'url' => route('shifts.current')],
        ];
    }

    /**
     * Attendance arrived after the rest of this, so an installation that
     * has not run its migrations yet has no table to read. Without this the
     * question comes back as a 500 the manager cannot interpret; with it,
     * it comes back saying which command to run.
     */
    private function attendanceMissing(): ?array
    {
        if (Schema::hasTable('attendance')) {
            return null;
        }

        return [
            'kind' => 'note',
            'answer' => __('Attendance is not set up on this installation yet. Run: php artisan migrate'),
            'detail' => null,
            'link' => null,
        ];
    }

    private function absentToday(): array
    {
        if ($missing = $this->attendanceMissing()) {
            return $missing;
        }

        $marks = Attendance::with('user')
            ->whereDate('day', today())
            ->whereIn('status', [Attendance::ABSENT, Attendance::LEAVE])
            ->get();

        if ($marks->isEmpty()) {
            return [
                'kind' => 'none',
                'answer' => __('Nobody is marked absent today.'),
                'detail' => null,
                'link' => ['label' => __('Attendance'), 'url' => route('attendance.index')],
            ];
        }

        return [
            'kind' => 'text',
            'answer' => $marks->map(fn ($m) => $m->user?->name)->filter()->implode(' · '),
            'detail' => $marks->map(fn ($m) => $m->user?->name . ' — ' . Attendance::label($m->status)
                . ($m->note ? ' (' . $m->note . ')' : ''))->implode(' · '),
            'link' => ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ];
    }

    private function absencesThisMonth(): array
    {
        if ($missing = $this->attendanceMissing()) {
            return $missing;
        }

        $tally = Attendance::with('user')
            ->where('status', Attendance::ABSENT)
            ->whereYear('day', now()->year)
            ->whereMonth('day', now()->month)
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => ['name' => $rows->first()->user?->name, 'days' => $rows->count()])
            ->sortByDesc('days')
            ->values();

        if ($tally->isEmpty()) {
            return [
                'kind' => 'none',
                'answer' => __('No absences this month.'),
                'detail' => null,
                'link' => ['label' => __('Attendance'), 'url' => route('attendance.index')],
            ];
        }

        return [
            'kind' => 'text',
            'answer' => trans_choice('{1}:count day of absence this month|[2,*]:count days of absence this month',
                $tally->sum('days'), ['count' => $tally->sum('days')]),
            'detail' => $tally->map(fn ($row) => $row['name'] . ' ' . $row['days'])->implode(' · '),
            'link' => ['label' => __('Attendance'), 'url' => route('attendance.index')],
        ];
    }

    private function payroll(): array
    {
        $salaries = (float) User::query()
            ->join('employee_records', 'employee_records.user_id', '=', 'users.id')
            ->sum('employee_records.salary');

        $entries = PayrollEntry::query()
            ->whereYear('happened_on', now()->year)
            ->whereMonth('happened_on', now()->month)
            ->get();

        $bonus = (float) $entries->where('kind', 'bonus')->sum('amount');
        $cut = (float) $entries->where('kind', 'deduction')->sum('amount');

        return [
            'kind' => 'money',
            'answer' => $this->money($salaries + $bonus - $cut),
            'detail' => __('Salaries :base · bonuses :bonus · deductions :cut · for :month', [
                'base' => $this->money($salaries),
                'bonus' => $this->money($bonus),
                'cut' => $this->money($cut),
                'month' => now()->translatedFormat('F Y'),
            ]),
            'link' => ['label' => __('Employee Records'), 'url' => route('employees.index')],
        ];
    }

    private function unknown(): array
    {
        return [
            'kind' => 'unknown',
            'answer' => __('I do not know that one yet.'),
            'detail' => __('Try one of these:'),
            'link' => null,
        ];
    }

    /* ---------- helpers ---------- */

    private function money(float $amount): string
    {
        return number_format($amount, 2) . ' ' . __('EGP');
    }

    private function mentions(string $haystack, array $words): bool
    {
        foreach ($words as $word) {
            if (Str::contains($haystack, $this->fold($word))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Arabic is written with and without its short vowels and with several
     * shapes of the same letter, so both the question and the words it is
     * matched against are folded to one form first.
     */
    private function fold(string $text): string
    {
        $text = Str::lower(trim($text));
        $text = preg_replace('/[\x{064B}-\x{0652}\x{0640}]/u', '', $text);
        $text = preg_replace('/[\x{0623}\x{0625}\x{0622}\x{0671}]/u', "\u{0627}", $text);
        $text = preg_replace('/\x{0649}/u', "\u{064A}", $text);
        $text = preg_replace('/\x{0629}/u', "\u{0647}", $text);

        return preg_replace('/\s+/u', ' ', $text);
    }
}
