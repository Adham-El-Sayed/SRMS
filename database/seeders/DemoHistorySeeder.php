<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Shift;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A month of trading, so the reports, the dashboard and the recommendations
 * have something to say while the system is being shown.
 *
 * THIS IS DEMONSTRATION DATA. Every order it writes is marked, and running
 * it again clears its own orders first and writes a fresh month — it never
 * touches an order placed by a real person.
 *
 * The month is not random noise. Service runs at lunch and again in the
 * evening, weekends are busier than midweek, and a basket is put together
 * the way people actually eat: a main, something to drink, sometimes
 * something to start and something sweet after. Some dishes are written to
 * be favourites and some to go together, so "most ordered" and "people also
 * order" have real patterns to find rather than a flat spread.
 */
class DemoHistorySeeder extends Seeder
{
    /** Marks an order as ours, so a re-run only clears its own. */
    public const MARK = 'demo-history';

    private const DAYS = 30;

    public function run(): void
    {
        $staff = User::query()->orderBy('id')->get();

        if ($staff->isEmpty()) {
            $this->command?->error('No staff accounts to open shifts with.');
            return;
        }

        $menu = $this->menuByCourse();

        if (empty($menu['mains'])) {
            $this->command?->error('Seed the menu first: php artisan db:seed --class=ItalianMenuSeeder');
            return;
        }

        $this->clearPreviousRun();
        $this->closeAnyOpenShift();

        $tables = RestaurantTable::all();
        $orders = 0;
        $items = 0;
        $money = 0.0;

        for ($back = self::DAYS; $back >= 0; $back--) {
            $day = now()->subDays($back)->startOfDay();
            $busy = in_array($day->dayOfWeek, [4, 5], true);   // Thursday and Friday

            foreach ($this->servicesOf($day) as $service) {
                $shift = $this->openShift($staff->random(), $service['from'], $service['to'], $back === 0 && $service['last']);

                $howMany = random_int($busy ? 14 : 8, $busy ? 26 : 16);

                for ($i = 0; $i < $howMany; $i++) {
                    $placed = $service['from']->copy()->addMinutes(random_int(0, $service['from']->diffInMinutes($service['to'])));

                    $order = $this->placeOrder($menu, $tables, $shift, $placed, $back === 0);

                    $orders++;
                    $items += $order['lines'];
                    $money += $order['total'];
                }

                $this->settleShift($shift);
            }
        }

        RecommendationService::forget();

        $this->command?->info(sprintf(
            'Wrote %d orders (%d lines, %s EGP) across %d days.',
            $orders, $items, number_format($money, 2), self::DAYS + 1
        ));
        $this->command?->warn('All of it is marked "' . self::MARK . '" and is removed if this seeder runs again.');
    }

    /* ---------------------------------------------------------------- */

    private function clearPreviousRun(): void
    {
        $mine = Order::where('notes', self::MARK)->pluck('id');

        if ($mine->isEmpty()) {
            return;
        }

        DB::table('order_items')->whereIn('order_id', $mine)->delete();
        DB::table('order_change_requests')->whereIn('order_id', $mine)->delete();
        Order::whereIn('id', $mine)->delete();

        Shift::where('notes', self::MARK)->delete();

        $this->command?->warn('Cleared ' . $mine->count() . ' orders from the previous run.');
    }

    /**
     * The month ends with a shift still open, which only makes sense if no
     * other one is. Two open shifts at once is a state the application
     * itself refuses to create.
     */
    private function closeAnyOpenShift(): void
    {
        $open = Shift::where('status', 'open')->get();

        foreach ($open as $shift) {
            $shift->update([
                'status' => 'closed',
                'closed_at' => now(),
                'expected_cash' => $shift->liveTotal('cash'),
                'expected_card' => $shift->liveTotal('card'),
                'counted_cash' => $shift->liveTotal('cash'),
            ]);
        }

        if ($open->isNotEmpty()) {
            $this->command?->warn('Closed ' . $open->count() . ' shift(s) that were still open.');
        }
    }

    /** Lunch, then dinner. The evening is the busier of the two. */
    private function servicesOf($day): array
    {
        return [
            ['from' => $day->copy()->setTime(12, 0), 'to' => $day->copy()->setTime(17, 0), 'last' => false],
            ['from' => $day->copy()->setTime(18, 0), 'to' => $day->copy()->setTime(23, 30), 'last' => true],
        ];
    }

    private function openShift(User $by, $from, $to, bool $leaveOpen): Shift
    {
        return Shift::create([
            'user_id' => $by->id,
            'status' => $leaveOpen ? 'open' : 'closed',
            'opened_at' => $from->copy()->subMinutes(30),
            'closed_at' => $leaveOpen ? null : $to->copy()->addMinutes(30),
            'notes' => self::MARK,
        ]);
    }

    /** What the drawer should hold, written once the shift is over. */
    private function settleShift(Shift $shift): void
    {
        if ($shift->status !== 'closed') {
            return;
        }

        $cash = $shift->liveTotal('cash');
        $card = $shift->liveTotal('card');

        // A real count is rarely exact to the pound.
        $drift = [0, 0, 0, 0, -5, 5, -10, 10][random_int(0, 7)];

        $shift->update([
            'expected_cash' => $cash,
            'expected_card' => $card,
            'counted_cash' => max(0, round($cash + $drift, 2)),
        ]);
    }

    /* ---------------------------------------------------------------- */

    private function placeOrder(array $menu, $tables, Shift $shift, $placed, bool $today): array
    {
        [$type, $source] = $this->howItCame();

        $lines = $this->basket($menu, $type);
        $food = array_sum(array_column($lines, 'subtotal'));

        $delivery = $type === 'delivery' ? 25.00 : 0.00;
        $total = round($food + $delivery, 2);

        // Most orders are done and paid. A handful from today are still
        // moving, so the kitchen board and the payments page are not empty.
        $status = 'completed';
        $paid = true;

        if ($today && random_int(1, 100) <= 30) {
            $status = ['pending', 'confirmed', 'preparing', 'ready'][random_int(0, 3)];
            $paid = false;
        } elseif (random_int(1, 100) <= 3) {
            $status = 'cancelled';
            $paid = false;
        }

        $method = random_int(1, 100) <= 62 ? 'cash' : 'card';
        $who = $this->customer();

        $order = Order::create([
            'restaurant_table_id' => $type === 'dine_in' ? $tables->random()?->id : null,
            'order_type' => $type,
            'source' => $source,
            'client_name' => $type === 'dine_in' ? null : $who['name'],
            'client_phone' => $type === 'dine_in' ? null : $who['phone'],
            'delivery_address' => $type === 'delivery' ? $who['address'] : null,
            'delivery_fee' => $delivery,
            'driver_name' => $type === 'delivery' && $status === 'completed' ? $this->driver() : null,
            'delivery_status' => $type === 'delivery' ? ($status === 'completed' ? 'delivered' : 'waiting') : null,
            'shift_id' => $shift->id,
            'access_token' => Str::random(48),
            'status' => $status,
            'total' => $total,
            'payment_method' => $method,
            'payment_status' => $paid ? 'paid' : 'pending',
            'paid_at' => $paid ? $placed->copy()->addMinutes(random_int(20, 70)) : null,
            'notes' => self::MARK,
        ]);

        // Written straight so the timestamps are the ones this order happened at.
        Order::whereKey($order->id)->update([
            'created_at' => $placed,
            'updated_at' => $placed->copy()->addMinutes(random_int(5, 60)),
        ]);

        $rows = array_map(fn (array $line) => $line + [
            'order_id' => $order->id,
            'created_at' => $placed,
            'updated_at' => $placed,
        ], $lines);

        DB::table('order_items')->insert($rows);

        return ['lines' => count($rows), 'total' => $status === 'cancelled' ? 0 : $total];
    }

    /** Where the order came from, and how it reached the guest. */
    private function howItCame(): array
    {
        $roll = random_int(1, 100);

        return match (true) {
            $roll <= 46 => ['dine_in', 'qr'],
            $roll <= 68 => ['takeaway', 'counter'],
            $roll <= 88 => ['delivery', random_int(1, 100) <= 55 ? 'web' : 'counter'],
            default     => ['online', 'web'],
        };
    }

    /* ---------------------------------------------------------------- */

    /**
     * A basket, put together the way people eat rather than at random:
     * something main, something to drink, and sometimes a start and a sweet.
     */
    private function basket(array $menu, string $type): array
    {
        $eating = $type === 'dine_in';
        $party = $eating ? random_int(1, 4) : random_int(1, 2);

        $chosen = [];

        for ($i = 0; $i < $party; $i++) {
            $chosen[] = $this->pick($menu['mains']);
        }

        // Garlic bread goes with pizza far more often than on its own.
        $hasPizza = collect($chosen)->contains(fn ($p) => $p->category_id === ($menu['pizzaCourse'] ?? 0));

        if ($hasPizza && random_int(1, 100) <= 55) {
            $chosen[] = $menu['garlicBread'] ?? $this->pick($menu['starters']);
        } elseif (random_int(1, 100) <= ($eating ? 45 : 25)) {
            $chosen[] = $this->pick($menu['starters']);
        }

        if (random_int(1, 100) <= ($eating ? 85 : 70)) {
            $howMany = $eating ? $party : 1;

            for ($i = 0; $i < $howMany; $i++) {
                $chosen[] = $this->pick($menu['drinks']);
            }
        }

        if ($eating && random_int(1, 100) <= 30) {
            $chosen[] = $this->pick($menu['desserts']);
        }

        // The same dish twice becomes a quantity, not two lines.
        $lines = [];

        foreach ($chosen as $product) {
            if (! $product) {
                continue;
            }

            if (isset($lines[$product->id])) {
                $lines[$product->id]['quantity']++;
                $lines[$product->id]['subtotal'] = round(
                    $lines[$product->id]['quantity'] * (float) $product->price, 2
                );
                continue;
            }

            $lines[$product->id] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => 1,
                'unit_price' => $product->price,
                'subtotal' => round((float) $product->price, 2),
                'notes' => null,
            ];
        }

        return array_values($lines);
    }

    /**
     * Weighted towards the front of the list, so a few dishes become the
     * restaurant's favourites instead of everything selling equally.
     */
    private function pick(array $products): ?Product
    {
        if (empty($products)) {
            return null;
        }

        $count = count($products);
        $roll = random_int(1, 100);

        $index = match (true) {
            $roll <= 40 => random_int(0, (int) max(0, ceil($count * 0.2) - 1)),
            $roll <= 75 => random_int(0, (int) max(0, ceil($count * 0.5) - 1)),
            default     => random_int(0, $count - 1),
        };

        return $products[$index];
    }

    /** The menu, grouped the way a basket is built. */
    private function menuByCourse(): array
    {
        $courses = Category::with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->where('is_active', true)
            ->get();

        $of = function (array $words) use ($courses) {
            return $courses
                ->filter(fn ($c) => Str::contains($c->name, $words))
                ->flatMap->products
                ->values()
                ->all();
        };

        $pizza = $courses->first(fn ($c) => Str::contains($c->name, 'Pizza'));

        return [
            'mains' => $of(['Pizza', 'Pasta', 'Risotto', 'Main Courses', 'Panini']),
            'starters' => $of(['Starters', 'Soups', 'Salads']),
            'drinks' => $of(['Drinks']),
            'desserts' => $of(['Desserts']),
            'pizzaCourse' => $pizza?->id ?? 0,
            'garlicBread' => Product::where('name', 'like', '%Garlic Bread ·%')->first()
                ?? Product::where('name', 'like', '%خبز بالثوم%')->first(),
        ];
    }

    private function customer(): array
    {
        $names = ['أحمد سامي', 'مريم فؤاد', 'كريم عادل', 'نورهان مصطفى', 'يوسف حسن',
                  'سلمى إبراهيم', 'عمر الشناوي', 'هبة رمضان', 'محمود زكي', 'دينا صبري',
                  'طارق العزب', 'ريم لطفي', 'خالد منير', 'ياسمين فتحي', 'شريف جابر'];

        $streets = ['شارع فؤاد', 'طريق الحرية', 'شارع أبو قير', 'سموحة، شارع فوزي معاذ',
                    'الشاطبي، شارع بورسعيد', 'كفر عبده، شارع كمال الدين صلاح',
                    'ميامي، شارع خالد بن الوليد', 'جليم، شارع مصطفى فهمي'];

        return [
            'name' => $names[array_rand($names)],
            'phone' => '01' . [0, 1, 2, 5][random_int(0, 3)] . random_int(10000000, 99999999),
            'address' => $streets[array_rand($streets)] . '، عمارة ' . random_int(1, 90)
                . '، الدور ' . random_int(1, 9) . '، شقة ' . random_int(1, 40),
        ];
    }

    private function driver(): string
    {
        $drivers = ['محمد السيد', 'أحمد فوزي', 'إسلام رجب', 'مصطفى عبد الله'];

        return $drivers[array_rand($drivers)];
    }
}
