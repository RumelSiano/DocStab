<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-users');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'role' => ['required', Rule::in(['requestor', 'sta', 'admin'])], 'is_active' => ['required', 'boolean'],
            'password' => [$this->route('user') ? 'nullable' : 'required', Password::min(12)],
        ];
    }
}
