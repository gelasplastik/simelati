<?php

namespace App\Http\Requests\Admin;

use App\Models\SchoolCalendarEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'event_type' => ['required', Rule::in(SchoolCalendarEvent::EVENT_TYPES)],
            'operational_mode' => ['required', Rule::in(SchoolCalendarEvent::OPERATIONAL_MODES)],
            'color' => ['nullable', 'string', 'max:20', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'is_school_day' => ['nullable', 'boolean'],
            'disable_teacher_attendance' => ['nullable', 'boolean'],
            'disable_student_attendance' => ['nullable', 'boolean'],
            'disable_journal' => ['nullable', 'boolean'],
            'disable_substitute_generation' => ['nullable', 'boolean'],
            'disable_kpi_penalty' => ['nullable', 'boolean'],
            'show_on_dashboard' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
