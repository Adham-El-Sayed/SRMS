<?php

namespace App\Services;

use App\Models\RestaurantTable;
use Illuminate\Database\Eloquent\Collection;

class TableService
{
    public function getAll(): Collection
    {
        return RestaurantTable::orderBy('number')->get();
    }

    public function create(array $data): RestaurantTable
    {
        return RestaurantTable::create($data);
    }

    public function update(
        RestaurantTable $table,
        array $data
    ): RestaurantTable {
        $table->update($data);

        return $table->refresh();
    }
    public function delete(RestaurantTable $table): void
    {
    $table->delete();
    }
}