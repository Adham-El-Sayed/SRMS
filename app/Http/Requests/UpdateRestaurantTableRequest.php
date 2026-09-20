<?php

namespace App\Http\Requests;

use App\Enums\TableStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateRestaurantTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('restaurant_tables', 'number')
                    ->ignore($this->route('table')),
            ],

            'capacity' => [
                'required',
                'integer',
                'min:1',
                'max:20',
            ],

            'status' => [
                'required',
                new Enum(TableStatus::class),
            ],
        ];
    }
}