<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $classId = $this->route('class')?->id;

        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('classes', 'name')->ignore($classId)],
            'is_active' => ['nullable', 'boolean'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ];
    }
}
