<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDutyTeacherAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'day_of_week' => ['required', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])],
            'teacher_id' => [
                'required',
                'integer',
                Rule::exists('teachers', 'id'),
                Rule::unique('duty_teacher_assignments')->where(fn ($q) => $q->where('day_of_week', $this->input('day_of_week'))),
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.unique' => 'Guru sudah ditugaskan sebagai guru piket pada hari tersebut.',
        ];
    }
}

