<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH'], true);

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'exists:roles,name'],
            'email_verified' => ['nullable', 'boolean'],
        ];
    }
}
