<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class ClassAttendanceSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'teacher';
    }

    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teaching_schedule_id' => ['nullable', 'exists:teaching_schedules,id'],
            'substitute_assignment_id' => ['nullable', 'exists:teacher_substitute_assignments,id'],
            'date' => ['required', 'date'],
            'jam_ke' => ['required', 'integer', 'min:1', 'max:12'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.student_id' => ['required', 'exists:students,id'],
            'rows.*.status' => ['required', 'in:hadir,izin,sakit,alpa'],
            'rows.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
