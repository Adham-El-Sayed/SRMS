<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRestaurantTableRequest;
use App\Services\TableService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\UpdateRestaurantTableRequest;
use App\Models\RestaurantTable;
use App\Services\QrCodeService;

class RestaurantTableController extends Controller
{
    public function __construct(
        private TableService $tableService,
        private QrCodeService $qrCodeService
    ) {}

    public function index(): View
    {
        $tables = $this->tableService->getAll();

        return view('tables.index', compact('tables'));
    }

    // 👇 ضيفها هنا
    public function create(): View
    {
        return view('tables.create');
    }

    public function store(StoreRestaurantTableRequest $request): RedirectResponse
    {
        $this->tableService->create(
            $request->validated()
        );

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table created successfully.');
    }
    
    public function edit(RestaurantTable $table): View
    {
    return view('tables.edit', compact('table'));
    }
    
    public function update(
    UpdateRestaurantTableRequest $request,
    RestaurantTable $table
    ): RedirectResponse {
    $this->tableService->update(
        $table,
        $request->validated()
    );

    return redirect()
        ->route('tables.index')
        ->with('success', 'Table updated successfully.');
    }
    
    public function destroy(RestaurantTable $table): RedirectResponse
    {
    $this->tableService->delete($table);

    return redirect()
        ->route('tables.index')
        ->with('success', 'Table deleted successfully.');
    }
    
    public function qr(RestaurantTable $table): View
    {
    $qrCode = $this->qrCodeService->generateForTable($table);

    return view('tables.qr', compact('table', 'qrCode'));
    }
    
}