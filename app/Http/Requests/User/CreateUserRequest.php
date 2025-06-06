<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required',
            'display_name' => 'sometimes',
            'email' => 'required|email|unique:users,email',
            'birth_day' => 'sometimes',
        ];
    }
}
