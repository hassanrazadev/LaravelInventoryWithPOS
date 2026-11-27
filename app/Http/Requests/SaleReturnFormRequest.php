<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleReturnFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if (in_array($this->method(), ['PUT', 'PATCH'], true)) {
            return [
                'return_price' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
                'reason' => ['nullable', 'string', 'max:500'],
            ];
        }

        return [
            'order_id' => ['required', 'integer', Rule::exists('orders', 'id')->whereNull('deleted_at')],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'return_price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'restock' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
