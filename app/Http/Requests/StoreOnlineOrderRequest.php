<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * An order from the website. Unlike a table's order, the restaurant has no
 * idea who this is, so a name and a reachable phone number are required —
 * and an address whenever it has to be driven somewhere.
 */
class StoreOnlineOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.product_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],

            'client_name' => ['required', 'string', 'max:100'],
            'client_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{5,20}$/'],

            'fulfilment' => ['required', Rule::in(['collection', 'delivery'])],
            'delivery_address' => [
                Rule::requiredIf(fn () => $this->input('fulfilment') === 'delivery'),
                'nullable', 'string', 'max:500',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'client_name' => __('Name'),
            'client_phone' => __('Phone Number'),
            'delivery_address' => __('Delivery address'),
        ];
    }
}
