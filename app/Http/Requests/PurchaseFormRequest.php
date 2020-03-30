<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseFormRequest extends FormRequest {
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
        $rules =  [
            'total' => 'required',
            'product_id' => 'required',
            'quantity' => 'required|min:1',
            'unit_price' => 'required|min:1',
            'sub_total' => 'required|min:1',
        ];

//        dd($this->request);

        foreach ($this->request->get('product_id') as $key => $val){
//            dd($key);
//            $rules['products['.$key.'][product_id]'] = 'required';
//            $rules['products['.$key.'][quantity]'] = 'required|min:1';
//            $rules['products['.$key.'][unit_price]'] = 'required|min:1';
//            $rules['products['.$key.'][sub_total]'] = 'required|min:1';
            $rules['product_id.'.$key] = 'required';
            $rules['quantity.'.$key] = 'required|min:1';
            $rules['unit_price.'.$key] = 'required|min:1';
            $rules['sub_total.'.$key] = 'required|min:1';
        }

        return $rules;
    }

    public function messages() {
        $messages = [];
        foreach ($this->request->get('product_id') as $key => $val){
            $messages['product_id.'.$key.'.required'] = "The product field is required.";
            $messages['quantity.'.$key.'.required'] = "The quantity field is required.";
            $messages['quantity.'.$key.'.min'] = "The quantity must be greater than or equal to :min .";
            $messages['unit_price.'.$key.'.required'] = "The unit price field is required.";
            $messages['unit_price.'.$key.'.min'] = "The unit price must be greater than or equal to :min .";
            $messages['sub_total.'.$key.'.required'] = "The product field is required.";
            $messages['sub_total.'.$key.'.min'] = "The sub total must be greater than or equal to :min .";
        }
        return $messages;
    }
}
