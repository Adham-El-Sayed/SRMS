<?php

namespace App\Http\Controllers;

use App\Services\MenuService;
use Illuminate\Contracts\View\View;

class MenuController extends Controller
{
    public function __construct(
        private MenuService $menuService
    ) {}

    public function index(): View
    {
        $menu = $this->menuService->getMenu();

        return view('menu.index', compact('menu'));
    }
}