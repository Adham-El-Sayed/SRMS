<?php

namespace App\Exports;

use App\Exports\Concerns\SanitizesCells;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use App\Models\Shift;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ShiftOrdersExport extends DefaultValueBinder implements WithCustomValueBinder, FromCollection, WithHeadings, WithMapping
{
    use SanitizesCells;

    public function __construct(protected Shift $shift) {}

    public function collection()
    {
        return $this->shift->orders()
            ->with(['table', 'items'])
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
        $items = $this->itemLines($order);

        return [
            $order->id,
            $order->table?->number ?? 'N/A',
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