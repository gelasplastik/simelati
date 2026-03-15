<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReviewTeachingModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,rejected'],
            'admin_notes' => ['nullable', 'string'],
        ];
    }
}
