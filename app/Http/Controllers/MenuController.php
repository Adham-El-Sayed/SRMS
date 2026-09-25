<?php

namespace App\Http\Controllers;

use App\Services\MenuService;
use App\Services\RecommendationService;
use Illuminate\Contracts\View\View;

class MenuController extends Controller
{
    public function __construct(
        private MenuService $menuService,
        private RecommendationService $recommendations,
    ) {}

    public function index(): View
    {
        $menu = $this->menuService->getMenu();

        return view('menu.index', [
            'menu' => $menu,
            'recommended' => $this->recommendations->bestPerCategory($menu),
            'popular' => $this->recommendations->popular(),
        ]);
    }
}