<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class LateEntryRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'teacher';
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'jam_ke' => ['required', 'integer', 'min:1', 'max:12'],
            'request_type' => ['required', 'in:attendance,journal,both'],
            'reason' => ['required', 'string'],
        ];
    }
}
