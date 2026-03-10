<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $assignmentId = $this->route('assignment')?->id;

        return [
            'teacher_id' => ['required', 'exists:teachers,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => [
                'required',
                'exists:classes,id',
                Rule::unique('assignments')->ignore($assignmentId)->where(function ($query) {
                    return $query
                        ->where('teacher_id', $this->teacher_id)
                        ->where('subject_id', $this->subject_id)
                        ->where('class_id', $this->class_id);
                }),
            ],
        ]; 
    }
}
