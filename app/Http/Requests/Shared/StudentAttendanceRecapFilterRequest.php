<?php

namespace App\Http\Requests\Shared;

use Illuminate\Foundation\Http\FormRequest;

class StudentAttendanceRecapFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'teacher'], true);
    }

    public function rules(): array
    {
        return [
            'class_id' => ['nullable', 'exists:classes,id'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'year' => ['nullable', 'integer', 'min:2024', 'max:2100'],
        ];
    }
}
