<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TeacherLeaveSubstituteUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'substitute_teacher_id' => ['nullable', 'exists:teachers,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
