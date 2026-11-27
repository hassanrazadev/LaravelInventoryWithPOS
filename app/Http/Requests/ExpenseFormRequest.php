<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_name' => ['required', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:50'],
            'expense_amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'expense_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
