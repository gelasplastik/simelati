<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSessionOverrideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'override_allowed' => ['nullable', 'boolean'],
            'override_reason' => ['nullable', 'string'],
            'override_expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
