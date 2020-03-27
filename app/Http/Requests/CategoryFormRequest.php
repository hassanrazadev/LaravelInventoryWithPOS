<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryFormRequest extends FormRequest {
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
            'category_name' => 'required|min:3',
            'category_code' => 'required|min:3|unique:categories,category_code,'.$this->category,
            'category_image' => $imageRule,
            'category_slug' => 'required|min:3|unique:categories,category_slug,'.$this->category,
            'parent_id' => 'nullable'
        ];
    }
}
