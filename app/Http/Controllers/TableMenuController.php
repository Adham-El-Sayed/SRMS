<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;
use App\Services\MenuService;
use Illuminate\Contracts\View\View;

class TableMenuController extends Controller
{
    public function __construct(
        private MenuService $menuService
    ) {}

    public function show(string $qr_token): View
    {
        $table = RestaurantTable::where('qr_token', $qr_token)
            ->firstOrFail();

        $menu = $this->menuService->getMenu();

        return view('menu.index', compact('table', 'menu'));
    }
}