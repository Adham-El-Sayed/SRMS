<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;
use App\Services\MenuService;
use App\Services\RecommendationService;
use Illuminate\Contracts\View\View;

class TableMenuController extends Controller
{
    public function __construct(
        private MenuService $menuService,
        private RecommendationService $recommendations,
    ) {}

    public function show(string $qr_token): View
    {
        $table = RestaurantTable::where('qr_token', $qr_token)
            ->firstOrFail();

        $menu = $this->menuService->getMenu();

        return view('menu.index', [
            'table' => $table,
            'menu' => $menu,
            'recommended' => $this->recommendations->bestPerCategory($menu),
            'popular' => $this->recommendations->popular(),
        ]);
    }
}