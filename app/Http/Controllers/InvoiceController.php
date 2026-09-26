<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function show(Order $order): Response
    {
        $order->load(['table', 'items']);

        // The PDF library can't join Arabic letters, so invoices are always
        // rendered in English whatever language the screen is in.
        $pdf = $this->inEnglish(fn () => Pdf::loadView('invoices.show', ['order' => $order]));

        return $pdf->stream("invoice-{$order->id}.pdf");
    }
}
