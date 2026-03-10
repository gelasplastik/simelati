<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');
        $userId = $teacher?->user_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'employee_code' => ['required', 'string', 'max:50', Rule::unique('teachers', 'employee_code')->ignore($teacher?->id)],
            'password' => [$teacher ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }
}
