<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDutyReportByAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string'],
            'teacher_rows' => ['array'],
            'teacher_rows.*.id' => ['required', 'integer'],
            'teacher_rows.*.verified_status' => ['required', 'in:present,leave,absent'],
            'teacher_rows.*.reason' => ['nullable', 'string'],
            'teacher_rows.*.has_substitute' => ['nullable', 'boolean'],
            'teacher_rows.*.notes' => ['nullable', 'string'],
            'student_rows' => ['array'],
            'student_rows.*.id' => ['required', 'integer'],
            'student_rows.*.total_students' => ['required', 'integer', 'min:0'],
            'student_rows.*.present_count' => ['required', 'integer', 'min:0'],
            'student_rows.*.sick_count' => ['required', 'integer', 'min:0'],
            'student_rows.*.izin_count' => ['required', 'integer', 'min:0'],
            'student_rows.*.alpa_count' => ['required', 'integer', 'min:0'],
            'student_rows.*.late_count' => ['required', 'integer', 'min:0'],
            'student_rows.*.notes' => ['nullable', 'string'],
        ];
    }
}
