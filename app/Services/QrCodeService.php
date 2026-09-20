<?php

namespace App\Services;

use App\Models\RestaurantTable;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    public function generateForTable(RestaurantTable $table): string
    {
        $url = route('tables.menu', [
            'qr_token' => $table->qr_token,
        ]);

        return QrCode::size(300)
            ->generate($url);
    }
}