<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'restaurant_table_id' => [
                'required',
                'integer',
                'exists:restaurant_tables,id',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'client_name' => ['nullable', 'string', 'max:255'],
            
            'client_phone' => ['nullable', 'string', 'max:20'],
            
            'payment_method' => ['required', 'in:cash,card'],
            
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}