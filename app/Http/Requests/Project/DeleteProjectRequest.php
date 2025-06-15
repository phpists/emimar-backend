<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class DeleteProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:projects,id',
        ];
    }
}
