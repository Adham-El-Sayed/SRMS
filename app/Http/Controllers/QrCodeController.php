<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;
use App\Services\QrCodeService;
use Illuminate\Contracts\View\View;

class QrCodeController extends Controller
{
    public function __construct(
        private QrCodeService $qrCodeService
    ) {}

    public function show(RestaurantTable $table): View
    {
        $qr = $this->qrCodeService->generateForTable($table);

        return view('tables.qr', compact('table', 'qr'));
    }
}