<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * Guests type their name, phone and item notes. A spreadsheet would normally
 * treat text starting with =, +, - or @ as a formula and run it when a manager
 * opens the export (formula/CSV injection). Any such text is stored as plain
 * text instead, so it shows exactly as typed and never executes. Numbers such
 * as totals are untouched and still add up.
 *
 * Use in an export that extends DefaultValueBinder and implements
 * WithCustomValueBinder.
 */
trait SanitizesCells
{
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    protected function itemLines($order): string
    {
        return $order->items->map(function ($item) {
            $line = $item->quantity . 'x ' . $item->product_name;

            return $item->notes ? $line . ' (' . $item->notes . ')' : $line;
        })->implode(', ');
    }
}
