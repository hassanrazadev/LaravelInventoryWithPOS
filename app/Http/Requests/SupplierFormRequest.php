<?php

namespace App\Http\Requests;

class SupplierFormRequest extends ContactFormRequest
{
    public function rules(): array
    {
        return $this->contactRules('suppliers', $this->route('supplier'));
    }
}
