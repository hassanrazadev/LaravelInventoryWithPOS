<?php

namespace App\Http\Requests;

class CustomerFormRequest extends ContactFormRequest
{
    public function rules(): array
    {
        return $this->contactRules('customers', $this->route('customer'));
    }
}
