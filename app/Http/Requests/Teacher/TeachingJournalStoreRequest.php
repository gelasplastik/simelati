<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class TeachingJournalStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'teacher';
    }

    public function rules(): array
    {
        return [
            'class_attendance_session_id' => ['required', 'exists:class_attendance_sessions,id'],
            'pertemuan_ke' => ['required', 'integer', 'min:1'],
            'materi' => ['required', 'string'],
            'student_notes' => ['nullable', 'string'],
            'mastery_notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];
    }
}
