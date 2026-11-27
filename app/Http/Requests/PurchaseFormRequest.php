<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'supplier_id' => 'nullable|exists:suppliers,id',
            'total' => 'required|numeric|min:0',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|integer|min:1',
            'unit_price' => 'required|array|min:1',
            'unit_price.*' => 'required|numeric|min:0.01',
            'sub_total' => 'required|array|min:1',
            'sub_total.*' => 'required|numeric|min:0.01',
        ];
    }

    public function messages()
    {
        return [
            'product_id.*.required' => 'The product field is required.',
            'quantity.*.required' => 'The quantity field is required.',
            'quantity.*.min' => 'The quantity must be greater than or equal to :min .',
            'unit_price.*.required' => 'The unit price field is required.',
            'unit_price.*.min' => 'The unit price must be greater than or equal to :min .',
            'sub_total.*.required' => 'The product field is required.',
            'sub_total.*.min' => 'The sub total must be greater than or equal to :min .',
        ];
    }
}
