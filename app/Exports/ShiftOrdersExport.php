<?php

namespace App\Exports;

use App\Models\Shift;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ShiftOrdersExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected Shift $shift) {}

    public function collection()
    {
        return $this->shift->orders()
            ->with(['table', 'items.product'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Table',
            'Client Name',
            'Client Phone',
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
            $order->client_name,
            $order->client_phone,
            $items,
            $order->payment_method === 'card' ? 'Visa' : 'Cash',
            $order->payment_status,
            $order->status,
            $order->total,
            $order->created_at->format('Y-m-d H:i'),
        ];
    }
}