<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required',
            'full_name' => 'required',
            'display_name' => 'sometimes',
            'email' => 'required|email|exists:users,email',
            'birth_day' => 'sometimes',
            'phone' => 'sometimes'
        ];
    }
}
