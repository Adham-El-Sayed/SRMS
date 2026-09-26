<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRestaurantTableRequest;
use App\Services\TableService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\UpdateRestaurantTableRequest;
use App\Models\Order;
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
            ->with('success', __('Table created successfully.'));
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
        ->with('success', __('Table updated successfully.'));
    }
    
    public function destroy(RestaurantTable $table): RedirectResponse
    {
        // Orders keep a link to their table for the reports; a table with
        // history stays, and its QR code simply stops being printed.
        if (Order::where('restaurant_table_id', $table->id)->exists()) {
            return redirect()
                ->route('tables.index')
                ->with('error', __('This table has orders in its history, so it can\'t be deleted.'));
        }

        $this->tableService->delete($table);

        return redirect()
            ->route('tables.index')
            ->with('success', __('Table deleted successfully.'));
    }
    
    
}