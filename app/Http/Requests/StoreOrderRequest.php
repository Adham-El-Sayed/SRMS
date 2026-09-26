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
            // The table is identified by its QR secret, never by its plain ID.
            'table_token' => ['required', 'string', 'max:64', 'exists:restaurant_tables,qr_token'],

            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.product_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],

            'client_name' => ['nullable', 'string', 'max:100'],
            'client_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{5,20}$/'],
            'payment_method' => ['required', 'in:cash,card'],
        ];
    }
}
