<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductFormRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        if ($this->method() == "PUT") {
            $imageRule = 'nullable';
        }else{
            $imageRule = 'required';
        }
        return [
            'category_id' => 'required',
            'product_name' => 'required|min:5',
            'product_code' => 'required|min:5|unique:products,product_code,'.$this->product,
            'sale_price' => 'required|min:1',
            'product_image' => $imageRule,
            'product_slug' => 'required|unique:products,product_slug,'.$this->product,
        ];
    }

    public function messages() {
        return [
            'category_id.required' => 'The category field is required',
        ];
    }
}
