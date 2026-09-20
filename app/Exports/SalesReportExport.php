<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected Carbon $from,
        protected Carbon $to
    ) {}

    public function collection()
    {
        return Order::query()
            ->whereBetween('created_at', [$this->from, $this->to])
            ->with(['table', 'shift.user', 'items.product'])
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Table',
            'Staff',
            'Items',
            'Payment Method',
            'Payment Status',
            'Order Status',
            'Total (EGP)',
            'Created At',
        ];
    }

    public function map($order): array
    {
        $items = $order->items->map(function ($item) {

            $line = $item->quantity . 'x ' . ($item->product?->name ?? 'Unknown');

            if ($item->notes) {
                $line .= ' (' . $item->notes . ')';
            }

            return $line;

        })->implode(', ');

        return [
            $order->id,
            $order->table->number ?? 'N/A',
            $order->shift?->user?->name ?? 'N/A',
            $items,
            $order->payment_method === 'card' ? 'Visa' : 'Cash',
            $order->payment_status,
            $order->status,
            $order->total,
            $order->created_at->format('Y-m-d H:i'),
        ];
    }
}