<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id;

        return [
            'nisn' => ['required', 'string', 'max:50', Rule::unique('students', 'nisn')->ignore($studentId)],
            'nis' => ['nullable', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($studentId)],
            'full_name' => ['required', 'string', 'max:255'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
